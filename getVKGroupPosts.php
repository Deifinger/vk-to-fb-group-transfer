<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/21/17 5:21 PM
 */

require 'config.php';

// is file writable?
if (!is_writable($config['postsFile']))
{
    throw new Exception('File ' . $config['postsFile'] . ' is not writable.', 403 );
}

session_start();

$scope = 'wall';
$access_token = $config['vk']['access_token'];
if($access_token == null)
{
    $access_token = isset($_SESSION['vk_access_token']) ? $_SESSION['vk_access_token'] : null;
}

$vk = new VK\VK($config['vk']['app_id'], $config['vk']['app_secret'], $access_token);
$vk->setApiVersion($config['vk']['api_version']);

$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
// if we have not access token
if($access_token == null)
{
    // and have not code
    if(empty($code))
    {
        // login as user and back with code
        $auth_url = $vk->getAuthorizeURL($scope, $config['vk']['callback_url']);

        header('Location: ' . $auth_url);
        exit;
    }

    // if we have a code, we are get access token from code
    $at_info = $vk->getAccessToken($code, $config['vk']['callback_url']);
    $_SESSION['vk_access_token'] = $at_info['access_token'];
}

$posts = array();
$vk_amount_posts = 100; // first value for right logic
$cur_amount_posts = 0;
do
{
    $diff = min(100, $vk_amount_posts - $cur_amount_posts);
    $res = $vk->api('wall.get', array(
        'owner_id'  => $config['vk']['group_id'],
        'offset'    => $cur_amount_posts,
        'count'     => $diff // 100 - max posts by request
    ));

    if(isset($res['error']))
    {
        // if "User authorization failed: invalid access_token"
        if($res['error']['error_code'] == 5)
        {
            // clear session
            unset($_SESSION['vk_access_token']);
            // and reload page
            header('Location: ' . $config['vk']['callback_url']);
            exit;
        }

        throw new Exception($res['error']['error_msg'], $res['error']['error_code']);
    }

    $posts = array_merge($posts, $res['response']['items']);
    $cur_amount_posts += $diff;

    $vk_amount_posts = $res['response']['count'];
}
while($vk_amount_posts > $cur_amount_posts);

$posts = array_reverse($posts);
$posts = json_encode($posts);

$ch = fopen($config['postsFile'], 'w');
fwrite($ch, $posts);
fclose($ch);

echo 'Success!';






