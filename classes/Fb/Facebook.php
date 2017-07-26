<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 6:32 PM
 */
namespace VKToFB\Fb;

use \VKToFB\Social;
use \Facebook\Facebook as FB;

/**
 * Class Facebook
 *
 * Used $_SESSION['fb_access_token']
 */
class Facebook extends Social
{
    private $fb = null;

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
        if(isset($_SESSION['fb_access_token']))
        {
            // save it in options
            $options['access_token'] = $_SESSION['fb_access_token'];
        }

        // if access token is not null
        if($options['access_token'] !== null)
        {
            // set it like default access token
            $options['default_access_token'] = $options['access_token'];
        }

        // init fb object
        $this->fb = new FB($options);

        return $options;
    }

    protected function _clearSession()
    {
        unset($_SESSION['fb_access_token']);
    }

    protected function _authUser()
    {
        // login as user and back with code
        header('Location: ' . $this->getLoginUrl());
        exit;
    }

    public function getLoginUrl() : string
    {
        if(empty($this->authUrl))
        {
            $helper = $this->fb->getRedirectLoginHelper();
            $this->authUrl = $helper->getLoginUrl($this->callbackUrl, $this->scopes);
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
        $atInfo = $this->fb->getOAuth2Client()->getAccessTokenFromCode($code, $this->callbackUrl);

        $accessToken = $atInfo->getValue();
        $_SESSION['fb_access_token'] = $accessToken;
        $this->fb->setDefaultAccessToken($accessToken);

        return $accessToken;
    }

    /**
     * @return FB
     */
    public function getFB() : FB
    {
        return $this->fb;
    }
}
