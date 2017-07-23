<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/23/17 8:18 PM
 */

require 'config.php';

// is file writable?
if (!is_readable($config['postsFile']))
{
    throw new Exception('File ' . $config['postsFile'] . ' is not readable.', 403 );
}

session_start();

$scope = array('publish_actions', 'publish_pages', 'manage_pages', 'user_managed_groups');
$access_token = $config['fb']['access_token'];
if(isset($_SESSION['fb_access_token']))
{
    $access_token = $_SESSION['fb_access_token'];
}

$options = array(
    'app_id' => $config['fb']['app_id'],
    'app_secret' => $config['fb']['app_secret'],
    'default_graph_version' => $config['fb']['api_version']
);
if($access_token !== null)
{
    $options['default_access_token'] = $access_token;
}

// init Facebook object
$fb = new \Facebook\Facebook($options);

// get login url for exception case
$helper = $fb->getRedirectLoginHelper();
$auth_url = $helper->getLoginUrl($config['fb']['callback_url'], $scope);

$code = isset($_REQUEST['code']) ? $_REQUEST['code'] : '';
// if we have not access token
if($access_token == null)
{
    // and hove not code
    if(empty($code))
    {
        // login as user and back with code
        header('Location: ' . $auth_url);
        exit;
    }

    // if we have a code, we are get access token from code
    $at_info = $fb->getOAuth2Client()->getAccessTokenFromCode($code, $config['fb']['callback_url']);
    $_SESSION['fb_access_token'] = $at_info->getValue();
    $fb->setDefaultAccessToken($at_info);
}

// get posts for uploading
$ch = fopen($config['postsFile'], 'r');
$posts = fread($ch, filesize($config['postsFile']));
fclose($ch);

// decode from json
$posts = json_decode($posts);
if(empty($posts))
{
    throw new Exception('It\'s nothing to post');
}
//print_r($posts[0]);

try
{
    $fb->post($config['fb']['group_id'].'/photos', array(
        'url'       => $posts[0]->attachments[0]->photo->photo_130,
        'message'   => $posts[0]->text
    ));
}
catch (\Facebook\Exceptions\FacebookAuthenticationException $ex)
{
    echo 'Error message: ' . $ex->getMessage();
    // clear session
    unset($_SESSION['fb_access_token']);
    header('Location: ' . $auth_url);
    exit;
}
catch (\Facebook\Exceptions\FacebookResponseException $ex)
{
    echo 'Error message: ' . $ex->getMessage();
    // clear session
    unset($_SESSION['fb_access_token']);
    header('Location: ' . $auth_url);
    exit;
}
catch (Exception $ex)
{
    print_r($ex);
    echo 'Error message: ' . $ex->getMessage();
}

echo 'Success!';