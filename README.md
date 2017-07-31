# VK To FB Group Transfer

This is a solution for transferring VK group to Facebook Page/Group.
It's not a production version at it's very unstable. Please, keep it mind before usage.

The project have two modules:
 - module that getting information from VK. You can see how to use it
 in `getVKGroup.php` file. All information saves in .json files.
   It gets next GET-parameters:
   
   ```php
   // Used to obtain certain information
   $_GET['target'] = ''; // 'posts', 'videos', 'albums'
   // Used with 'target' = 'videos'. Getting for getting full information if
   // your application is not approved.
   // Please, use "dev" mode if you really have no time to approve application.
   $_GET['tag']    = '' // 'dev'
   
   ```
   
 - second module uploading all information to Facebook. You can see how to use
   it in `setPostsToFB.php` file.
   It gets next GET-parameters:
   
   ```php
      // From what post start to upload
      $_GET['offset']  = 0; // index of post in json-collection. For getting
      // it you can to look into logs
      // How many posts to upload at a time
      $_GET['count']   = 10 // recommended value
      // If you set 1, the script will automatically load all posts by reloading
      // page with new 'offset' value
      $_GET['auto']    = 0 // or 1
      
      ```

## Preparation

Before starting you need to [create application in VK](https://vk.com/apps?act=manage)
and [create application in Facebook](https://developers.facebook.com/docs/apps/register).

If you want to transfer video posts, you need to
[approve VK app](https://vk.com/dev/auth_direct) by admins for getting direct links
to video files as said [here](https://vk.com/dev/video.get). BUT if you need to transfer
group and you have no time for approving, this project have "dev" mode. About it you can
read below.

After that steps you need to type your config to `config.php` file in root of project
or create `local.config.php` and rewrite the default config information there.

You required to rewrite:
```php
<?php
$config['domain']      = 'http://yourdomain.here';
$config['vk']          = array(
    'callback_url'      => $config['domain'] . '/getVKGroup.php',
    'app_id'            => '12345678',
    'app_secret'        => 'WZcHxmRyourappsecretL4GOo83',
    'group_id'          => '-123456789',
    // used for reference from Facebook to shared posts in VK
    'screen_name'       => 'vk_group_screen_name'   
);
$config['fb']          = array(
    'callback_url'      => $config['domain'] . '/setPostsToFB.php',
    'app_id'            => '1324567890123465',
    'app_secret'        => 'd7c92ff4yourappsecretd51b8d5a3',
    'group_id'          => '465789515489',
    // your chronics album id
    'chronics_album_id' => '542876768678678'
);
```

If you want to use "dev" mode, rewrite 'login' and 'password' fields
```php 
    'login'             => 'your_vk_login',
    'password'          => 'your_vk_password'
```
 You can rewrite access token if something went wrong and you have
 the correct access token. For getting right access token you can go
 to [explorer](https://developers.facebook.com/tools/explorer/).
```php 
$config['fb']['access_token']```      => 'your_access_token'
```

## Usage

For understanding how to use this solution look to `getVKGroup.php`
and `setPostsToFB.php` files.

## How it works

Please, use if it you really no time to approve application.

For using "dev" mode, please, turn off 2-Step Verification.

"dev" mode downloads video information through VK Test API Form
(if you logged in VK you can see it below at [page](https://vk.com/dev/video.get)).
When start "dev" mode, the application is authorized in VK using your login and password,
gets cookies and goes to the VK Test API Form, parses the hash and sends queries to
https://vk.com/dev for getting information.

## Issues

 - Some videos are not uploaded and are disconnects by timeout
 - If you upload the big album, script can to crash. For seeing details
   go to `logs/log.log`. And start from crashed post
 - VK post with multiple photos uploads to Facebook like album with default name
 - All information loads to .json files. Need to add DB support
 - Used the unstable dependency [biganfa/vk-auth](https://github.com/biganfa/vk-auth)
 - If access token session is expired (Facebook module) need to remove session manually
   (maybe, to create a special gui-button?)

## About 
 ### Requirements
 - Project works with PHP 7.0 or above.
 
 ### Submitting bugs and feature requests
 Bugs and feature request are tracked on
 [GitHub](https://github.com/Deifinger/vk-to-fb-group-transfer/issues)
 
 ### License
 VK To FB Group Transfer is licensed under the MIT License - see the `LICENSE` file
 for details

