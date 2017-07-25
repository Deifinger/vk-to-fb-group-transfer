<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 6:32 PM
 */
namespace VKToFB\Vk;

use \VKToFB\Social;
use \VK\VK;

/**
 * Class Facebook
 *
 * Used $_SESSION['fb_access_token']
 */
class VKontakte extends Social
{
    private $vk = null;

    function __construct(array $options)
    {
        $options = array_merge($this->_defaults(), $options);

        $options = $this->_init($options);

        $this->scopes = $options['scopes'];
        $this->accessToken = $options['access_token'];
        $this->callbackUrl = $options['callback_url'];

    }

    protected function _init($options)
    {
        // if we have access_token in session
        if(isset($_SESSION['vk_access_token']))
        {
            // save it in options
            $options['access_token'] = $_SESSION['vk_access_token'];
        }

        // init vk object
        $this->vk = new VK($options['app_id'], $options['app_secret'], $options['access_token']);
        $this->vk->setApiVersion($options['api_version']);

        return $options;
    }

    protected function _clearSession()
    {
        unset($_SESSION['vk_access_token']);
    }

    // get login url
    public function getLoginUrl() : string
    {
        if(empty($this->authUrl))
        {
            $scopesStr = implode(',', $this->scopes);
            $this->authUrl = $this->vk->getAuthorizeURL($scopesStr, $this->callbackUrl);
        }

        return $this->authUrl;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getAccessTokenFromCode($code = '') : string
    {
        if(!is_string($code) || empty($code))
        {
            throw new \Exception("Code is empty or incorrect");
        }

        // if we have a code, we are get access token from code
        $atInfo = $this->vk->getAccessToken($code, $this->callbackUrl);
        $this->accessToken = $_SESSION['vk_access_token'] = $atInfo['access_token'];

        return $atInfo['access_token'];
    }

    /**
     * @return VK
     */
    public function getVK() : VK
    {
        return $this->vk;
    }
}
