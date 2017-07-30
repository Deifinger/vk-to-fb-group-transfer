<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/23/17 8:18 PM
 */

use \VKToFB\Fb\Facebook;
use \VKToFB\Fb\FacebookRequestor;
use \VKToFB\Fb\FBPost;
use \VKToFB\Vk\HelperVideo;
use \VKToFB\Vk\HelperAlbum;
use VKToFB\Config;
use VKToFB\Logger;

require 'init.php';

$logger = Logger::getLogger();

// is file readable?
if (!is_readable($config['postsFile'])
    || !is_readable($config['videosFile'])
    || !is_readable($config['albumsFile']))
{
    die($config['postsFile'].', '.$config['videosFile'].' or '.$config['albumsFile'].
        ' files are not readable.' );
}

$fb = new Facebook(array(
    'scopes'        => array('publish_actions', 'publish_pages', 'manage_pages', 'user_managed_groups'),
    'access_token'  => $config['fb']['access_token'],
    'app_id'        => $config['fb']['app_id'],
    'app_secret'    => $config['fb']['app_secret'],
    'default_graph_version' => $config['fb']['api_version'],
    'callback_url'  => $config['fb']['callback_url']
));


$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
$offset = isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0;
$count = isset($_REQUEST['count']) ? $_REQUEST['count'] : 10;
$autopost = isset($_REQUEST['auto']) ? !!$_REQUEST['auto'] : false;

// if we have not access token
$fb->updateAccessTokenIfNeed($code);

// get posts for uploading
$ch = fopen($config['postsFile'], 'r');
$posts = fread($ch, filesize($config['postsFile']));
fclose($ch);

// decode from json
$posts = json_decode($posts);
if(empty($posts))
{
    die('There are nothing to post');
}

// get videos for uploading
$ch = fopen($config['videosFile'], 'r');
$videos = fread($ch, filesize($config['videosFile']));
fclose($ch);

$videos = json_decode($videos);

// get albums for uploading
$ch = fopen($config['albumsFile'], 'r');
$albums = fread($ch, filesize($config['albumsFile']));
fclose($ch);

$albums = json_decode($albums);

/*foreach ($posts as $i => $post)
{
    if(isset($post->attachments))
    {
        foreach ($post->attachments as $ind => $attachment)
        {
            if($ind == 0)
            {
                echo $i.'. ' . $post->id . ': ';
            }
            echo $attachment->type.', ';
        }
        echo '<br>';
    }
}

//print_r($posts[2]);
exit;*/

$FBRequestor = new FacebookRequestor($fb->getFB());
$VideoHelper = new HelperVideo();
$AlbumHelper = new HelperAlbum();

$defAlbumName = Config::get('fb.default_album_name');
$albumCounter = 0;
//$offset = 12;
//$count = 1;
$amount = sizeof($posts);
for ($i = $offset; $i < min($amount, $offset + $count); $i++)
{
    $FBPost = new FBPost($posts[$i]);
    // get only first attachment because facebook don't accessible for posting
    // mutli-attachments posts by API
    $attachments = $FBPost->getAttachments();
    $attachment = isset($attachments[0]) ? $attachments[0] : null;

    $logger->addInfo("$i: ".$FBPost->getType().' id('.$FBPost->getId().')');
    try
    {
        switch ($FBPost->getType())
        {
            case 'photo':

                // TODO: write batcher
                $countAttachments = sizeof($attachments);
                // TODO: check this theory
                // supposedly if first attachment is photo, another too
                if($countAttachments > 1)
                {
                    // TODO: commented code below is works, but now there are error:
                    // TODO: @see https://developers.facebook.com/bugs/806764979491825/
                    /*
                    $medias = array();
                    for($y = 0; $y < 2; $y++)
                    {
                        // TODO: create some try/catch if smth wrong with only one attachment
                        $photoResponse = $FBPublisher->uploadToAlbum(
                            Config::get('fb.chronics_album_id'),
                            $attachments[$y]
                        );
                        if(!$photoResponse->isError())
                        {
                            $id = $photoResponse->getDecodedBody()['id'];
                            $medias[] = new \VKToFB\Fb\Structures\GraphMedia($id);
                        }

                    }

                    // TODO: Write total text checking
                    $text = $FBPost->getText();
                    if(empty($text)) $text = $attachment->name;
                    if(empty($text)) $text = $attachment->description;
                    if(empty($text)) $text = '.';

                    $FBPublisher->postTextWithPhotos(
                        $config['fb']['group_id'],
                        $text,
                        $medias
                    );
                    */

                    $albumRequest = $FBRequestor->createAlbum(
                        $config['fb']['group_id'],
                        $defAlbumName . ++$albumCounter,
                        $FBPost->getText()
                    );
                    $albumId = $fb->getFB()->getClient()->sendRequest( $albumRequest )->getDecodedBody()['id'];

                    $photoRequests = array();
                    for($y = 0; $y < $countAttachments; $y++)
                    {
                        $photoRequests[] = $FBRequestor->uploadToAlbum(
                            $albumId,
                            $attachments[$y],
                            false
                        );
                    }
                    $fb->getFB()->sendBatchRequest($photoRequests);

                }
                else
                {
                    $FBRequestor->postPhoto(
                        $config['fb']['group_id'],
                        $attachment->getUrl(),
                        $attachment->getCaption(),
                        $FBPost->getText()
                    );
                }
                break;

            case 'album':

                $album = $AlbumHelper->findAlbumById($albums, $attachment->getVKId());

                // If we have not video, the post make no sense
                if($album === false)
                {
                    $logger->addWarning("$i: id(".$FBPost->getId().') - Album is empty');
                    continue;
                }

                $description = empty($attachment->getCaption()) ? $FBPost->getText() : $attachment->getCaption();
                $albumRequest = $FBRequestor->createAlbum(
                    $config['fb']['group_id'],
                    $attachment->getTitle(),
                    $description
                );
                $albumId = $fb->getFB()->getClient()->sendRequest( $albumRequest )->getDecodedBody()['id'];

                $countPhotos = sizeof($album->photos);
                for($y = 0; $y < ceil($countPhotos / 50); $y++)
                {
                    $photoRequests = array();
                    // 50 - max batch requests
                    for($x = 0; $x < min(50, $countPhotos - $y * 50); $x++)
                    {
                        $photoRequests[] = $FBRequestor->uploadToAlbum(
                            $albumId,
                            new \VKToFB\Fb\Attachments\PhotoAttachment($album->photos[$y * 50 + $x]),
                            false
                        );
                    }
                    $fb->getFB()->sendBatchRequest($photoRequests);
                }

                break;

            case 'text':

                $FBRequestor->postText(
                    $config['fb']['group_id'],
                    $FBPost->getText()
                );
                break;

            case 'link':

                $FBRequestor->postLink(
                    $config['fb']['group_id'],
                    $attachment->getUrl(),
                    $FBPost->getText()
                );
                break;

            case 'video':

                $video = $VideoHelper->findVideoById($videos, $attachment->getVKId());

                // If we have not video, the post make no sense
                if($video === false)
                {
                    $logger->addWarning("$i: id(".$FBPost->getId().') - Video is empty');
                    continue;
                }

                $res = $VideoHelper->getVideoUrlInfo($video);
                $FBRequestor->postVideo(
                    $config['fb']['group_id'],
                    $res['url'],
                    $attachment->getCaption(),
                    $attachment->getDesc(),
                    $res['embeddable']
                );
                break;

            default:
                die('Unknown post type');
        }

    }
    catch (\Facebook\Exceptions\FacebookAuthenticationException $ex)
    {
        $logger->addError("$i: id(".$FBPost->getId().') - (#'.$ex->getCode().') '.$ex->getMessage());
        $fb->forceAuthUser();
    }
    catch (\Facebook\Exceptions\FacebookAuthorizationException $ex)
    {
        $logger->addError("$i: id(".$FBPost->getId().') - (#'.$ex->getCode().') '.$ex->getMessage());
        echo 'Error message:' . $ex->getMessage();
    }
    catch (\Facebook\Exceptions\FacebookResponseException $ex)
    {
        $error = $ex->getresponseData()['error'];
        $message = "$i: id(".$FBPost->getId().') - (#'.$ex->getCode().' - '.$ex->getSubErrorCode().') ';
        $message .= isset($error['error_user_title']) ? $error['error_user_title'] : $ex->getMessage();
        $logger->addError($message);
        echo 'Error message:' . $ex->getMessage();
    }
    catch (Exception $ex)
    {
        $logger->addError("$i: id(".$FBPost->getId().') - (#'.$ex->getCode().') '.$ex->getMessage());
        echo 'Error message: ' . $ex->getMessage();
    }
}

if($autopost == true && $offset + $count < $amount)
{
    header('Location: '.Config::get('fb.callback_url').'?'.http_build_query(array(
            'offset' => $offset + $count,
            'count' => $count,
            'auto' => $autopost
        )));
}

echo 'Success!';