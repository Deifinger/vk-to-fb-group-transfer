<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/23/17 8:18 PM
 */

use VKToFB\Fb\Facebook;
use VKToFB\Fb\FacebookPublisher;
use VKToFB\Fb\FBPost;
use VKToFB\FileHelper;
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


$fileHelper = new FileHelper();
// get posts for uploading
$posts = $fileHelper->readFile(Config::get('postsFile'), true);
if(empty($posts))
{
    die('There are nothing to post');
}

$videos = $fileHelper->readFile(Config::get('videosFile'), true);
$albums = $fileHelper->readFile(Config::get('albumsFile'), true);

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

$publisher = new FacebookPublisher($fb->getFB(), $logger);
$publisher->loadAlbums($albums);
$publisher->loadVideos($videos);

//$offset = 119;
//$count = 1;
$amount = sizeof($posts);
for ($i = $offset; $i < min($amount, $offset + $count); $i++)
{
    $post = new FBPost($posts[$i]);

    $logger->addDebug('{ind}: starts', array(
        'ind'       => $i,
        'postType'  => $post->getType(),
        'postId'    => $post->getId()
    ));
    try
    {
        $publisher->publish($post);
    }
    catch (\Facebook\Exceptions\FacebookAuthenticationException $ex)
    {
        $logger->addError("$i: id(".$post->getId().') - (#'.$ex->getCode().') '.$ex->getMessage());
        $fb->forceAuthUser();
    }
    catch (\Facebook\Exceptions\FacebookAuthorizationException $ex)
    {
        $logger->addError("$i: id(".$post->getId().') - (#'.$ex->getCode().') '.$ex->getMessage());
        echo 'Error message:' . $ex->getMessage();
    }
    catch (\Facebook\Exceptions\FacebookResponseException $ex)
    {
        $error = $ex->getresponseData()['error'];
        $message = "$i: id(".$post->getId().') - (#'.$ex->getCode().' - '.$ex->getSubErrorCode().') ';
        $message .= isset($error['error_user_title']) ? $error['error_user_title'] : $ex->getMessage();
        $logger->addError($message);
        echo 'Error message:' . $ex->getMessage();
    }
    catch (Exception $ex)
    {
        $logger->addError("$i: id(".$post->getId().') - (#'.$ex->getCode().') '.$ex->getMessage());
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