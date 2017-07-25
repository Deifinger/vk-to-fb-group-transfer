<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/21/17 5:21 PM
 */

use VKToFB\Vk\VKontakte;
use \VKToFB\Vk\VKontakteAPI;

require 'config.php';

// is file writable?
if (!is_writable($config['postsFile']))
{
    echo 'File ' . $config['postsFile'] . ' is not writable.';
    return;
}

if(!isset($_GET['target']) || !in_array($_GET['target'], array('videos', 'posts')))
{
    echo 'Incorrect target.';
    return;
}

session_start();

$target = $_GET['target'];
$vk = new VKontakte(array(
    'scopes'        => array('wall', 'video'),
    'access_token'  => $config['vk']['access_token'],
    'app_id'        => $config['vk']['app_id'],
    'app_secret'    => $config['vk']['app_secret'],
    'api_version'   => $config['vk']['api_version'],
    'default_graph_version' => $config['vk']['api_version'],
    'callback_url'  => $config['vk']['callback_url'].'?target='.$target
));
$vkAPI = new VKontakteAPI($vk);


$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
// if we have not access token
$vk->updateAccessTokenIfNeed($code);

$fileData = '';

// TODO: write function for getting less code
if($target == 'posts')
{
    $posts = array();
    try
    {
        $posts = $vkAPI->getPagePosts($config['vk']['group_id']);
    }
    catch (Exception $ex)
    {
        if($ex->getCode() == 5)
        {
            $vk->forceAuthUser();
        }
        else
        {
            echo $ex->getMessage();
        }
    }

    $posts = array_reverse($posts);
    $fileData = json_encode($posts);
}
elseif($target == 'videos')
{
    $videos = array();
    try
    {
        $videos = $vkAPI->getPageVideos($config['vk']['group_id']);
    }
    catch (Exception $ex)
    {
        if($ex->getCode() == 5)
        {
            $vk->forceAuthUser();
        }
        else
        {
            echo $ex->getMessage();
        }
    }

    $fileData = json_encode($videos);
}

$ch = fopen($config[$target.'File'], 'w');
fwrite($ch, $fileData);
fclose($ch);

echo 'Success!';






