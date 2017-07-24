<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/23/17 8:18 PM
 */

use \VKToFB\Facebook;

require 'config.php';

// is file writable?
if (!is_readable($config['postsFile']))
{
    throw new Exception('File ' . $config['postsFile'] . ' is not readable.', 403 );
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
    throw new \Exception('It\'s nothing to post');
}

foreach ($posts as $ind => $post)
{
    if(isset($post->attachments))
    {
        echo $ind . ': ' . $post->attachments[0]->type . '<br>';
    }
}

//print_r($posts[2]);
//exit;

$FBPublisher = new \VKToFB\FacebookPublisher($fb->getFB());
$FBPost = new \VKToFB\FBPost($posts[4]);

try
{
    switch ($FBPost->getType())
    {
        case 'photo':
        case 'album':

            $FBPublisher->postPhoto(
                $config['fb']['group_id'],
                $FBPost->getAttachments()[0]->getUrl(),
                $FBPost->getAttachments()[0]->getCaption(),
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
                $FBPost->getAttachments()[0]->getUrl(),
                $FBPost->getText()
            );
            break;

        case 'video':
            break;
        default:
            throw new Exception('Unknown post type');
    }

}
catch (\Facebook\Exceptions\FacebookAuthenticationException $ex)
{
    echo 'Error message: ' . $ex->getMessage();
    $fb->forceAuthUser();
}
catch (\Facebook\Exceptions\FacebookResponseException $ex)
{
    echo 'Error message: ' . $ex->getMessage();
    $fb->forceAuthUser();
}
catch (Exception $ex)
{
    echo 'Error message: ' . $ex->getMessage();
}

echo 'Success!';