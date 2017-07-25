<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/23/17 11:42 PM
 */

require 'vendor/autoload.php';

$config['domain']       = 'http://localhost';
$config['postsFile']    = 'vkPosts.json';
$config['videosFile']   = 'vkVideos.json';
$config['vk']           = array(
    'callback_url'      => $config['domain'] . '/getVKGroup.php',
    'app_id'            => '*****',
    'app_secret'        => '*****',
    'group_id'          => '-****',
    'access_token'      => null,
    'api_version'       => 5.67
);
$config['fb']           = array(
    'callback_url'      => $config['domain'] . '/setPostsToFB.php',
    'app_id'            => '*****',
    'app_secret'        => '*****',
    'group_id'          => '*****',
    'access_token'      => null,
    'api_version'       => 'v2.10'
);

if(file_exists('local.config.php'))
{
    include 'local.config.php';

    // TODO: create function for merging all nested array
    // array_merge_recursive is not our way
    $lconfig['vk'] = array_merge($config['vk'], $lconfig['vk']);
    $lconfig['fb'] = array_merge($config['fb'], $lconfig['fb']);
    $config = array_merge($config, $lconfig);
}