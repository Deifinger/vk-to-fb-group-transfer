<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/21/17 5:21 PM
 */

use VKToFB\Vk\VKontakte;
use VKToFB\Vk\VKontakteAPI;
use VKToFB\Vk\TestAPIForm\VKDevForm;
use VKToFB\Config;
use VKToFB\Logger;

require 'init.php';

// is file writable?
if (!is_writable($config['postsFile']))
{
    echo 'File ' . $config['postsFile'] . ' is not writable.';
    return;
}

if(!isset($_GET['target']) || !in_array($_GET['target'], array('videos', 'posts', 'albums')))
{
    echo 'Incorrect target.';
    return;
}

$target = $_GET['target'];
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';
$vk = new VKontakte(array(
    'scopes'        => array('wall', 'video'),
    'access_token'  => $config['vk']['access_token'],
    'app_id'        => $config['vk']['app_id'],
    'app_secret'    => $config['vk']['app_secret'],
    'api_version'   => $config['vk']['api_version'],
    'default_graph_version' => $config['vk']['api_version'],
    'callback_url'  => $config['vk']['callback_url'].'?target='.$target
));

$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
// if we have not access token
$vk->updateAccessTokenIfNeed($code);

$fileData = '';

// TODO: write function for getting less code
try
{
    if ($target == 'posts')
    {
        $posts = array();
        $vkAPI = new VKontakteAPI($vk);
        $posts = $vkAPI->getPagePosts($config['vk']['group_id']);

        $posts = array_reverse($posts);
        $fileData = json_encode($posts);
    }
    elseif ($target == 'videos')
    {
        $videos = array();
        if (empty($tag)) {
            $vkAPI = new VKontakteAPI($vk);
            $videos = $vkAPI->getPageVideos($config['vk']['group_id']);
        } elseif ($tag == 'dev') {
            $devForm = new VKDevForm(
                Config::get('vk.login'),
                Config::get('vk.password'),
                Config::get('vk.cookiePath'));//getDevHash

            $videosResponse = $devForm->requestGetVideos(array(
                'param_owner_id' => Config::get('vk.group_id'),
                'param_v' => Config::get('vk.api_version')
            ));
            $videos = $videosResponse->items;
        }

        $fileData = json_encode($videos);
    }
    elseif ($target == 'albums')
    {
        $vkAPI = new VKontakteAPI($vk);
        $albums = $vkAPI->getPageAlbums($config['vk']['group_id']);

        $fileData = json_encode($albums);
    }

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

$ch = fopen($config[$target.'File'], 'w');
fwrite($ch, $fileData);
fclose($ch);

echo 'Success!';






