<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/23/17 8:18 PM
 */

use \VKToFB\Fb\Facebook;
use \VKToFB\Fb\FacebookPublisher;
use \VKToFB\Fb\FBPost;
use \VKToFB\Vk\HelperVideo;

require 'config.php';

// is file readable?
if (!is_readable($config['postsFile']) || !is_readable($config['videosFile']))
{
    die($config['postsFile'].' or '.$config['videosFile'].' files are not readable.' );
}

session_start();

$fb = new Facebook(array(
    'scopes'        => array('publish_actions', 'publish_pages', 'manage_pages', 'user_managed_groups'),
    'access_token'  => $config['fb']['access_token'],
    'app_id'        => $config['fb']['app_id'],
    'app_secret'    => $config['fb']['app_secret'],
    'default_graph_version' => $config['fb']['api_version'],
    'callback_url'  => $config['fb']['callback_url']
));


$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
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
    die('It\'s nothing to post');
}

// get videos for uploading
$ch = fopen($config['videosFile'], 'r');
$videos = fread($ch, filesize($config['videosFile']));
fclose($ch);

$videos = json_decode($videos);
//var_dump($videos);

foreach ($posts as $ind => $post)
{
    if(isset($post->attachments))
    {
        echo $ind . ': ' . $post->attachments[0]->type . '<br>';
    }
}

//print_r($posts[2]);
//exit;

$FBPublisher = new FacebookPublisher($fb->getFB());
$FBPost = new FBPost($posts[52]);
$VideoHelper = new HelperVideo();
$attachment = $FBPost->getAttachments()[0];

try
{
    switch ($FBPost->getType())
    {
        case 'photo':
        case 'album':

            $FBPublisher->postPhoto(
                $config['fb']['group_id'],
                $attachment->getUrl(),
                $attachment->getCaption(),
                $FBPost->getText()
            );
            break;

        case 'text':

            $FBPublisher->postText(
                $config['fb']['group_id'],
                $FBPost->getText()
            );
            break;

        case 'link':

            $FBPublisher->postLink(
                $config['fb']['group_id'],
                $attachment->getUrl(),
                $FBPost->getText()
            );
            break;

        case 'video':

            $video = $VideoHelper->findVideoById($videos, $attachment->getVKId());

            if($video === false)
            {
                die('Video is empty');
            }
            /*var_dump($VideoHelper->getVideoUrl($video));
            exit;
*/
            $FBPublisher->postVideo(
                $config['fb']['group_id'],
                $VideoHelper->getVideoUrl($video),
                $attachment->getCaption(),
                $attachment->getDesc()
            );
            break;

        default:
            die('Unknown post type');
    }

}
catch (\Facebook\Exceptions\FacebookAuthenticationException $ex)
{
    echo 'Error message: ' . $ex->getMessage();
    $fb->forceAuthUser();
}
catch (\Facebook\Exceptions\FacebookResponseException $ex)
{
    echo 'Error message: (#'.$ex->getCode().' - '.$ex->getSubErrorCode().') '.
        $ex->getMessage();
    //$fb->forceAuthUser();
}
catch (Exception $ex)
{
    echo 'Error message: ' . $ex->getMessage();
}

echo 'Success!';