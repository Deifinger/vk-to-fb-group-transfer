<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 6:32 PM
 */
namespace VKToFB;

use \Facebook\Facebook as FB;

/**
 * Class Facebook
 *
 * Used $_SESSION['fb_access_token']
 */
class Facebook
{
    private $fb = null;
    private $scopes = [];
    private $accessToken = null;
    private $callbackUrl = '';
    private $authUrl = '';

    function __construct(array $options)
    {
        $defaults = [
            'scopes'        => '',
            'access_token'  => null,
            'app_id'        => '',
            'app_secret'    => '',
            'default_graph_version' => '',
            'callback_url'  => ''
        ];
        $options = array_merge($defaults, $options);

        $options = $this->_initFB($options);

        $this->scopes = $options['scopes'];
        $this->accessToken = $options['access_token'];
        $this->callbackUrl = $options['callback_url'];

    }

    private function _initFB($options)
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

    private function _clearSession()
    {
        unset($_SESSION['fb_access_token']);
    }

    private function _authUser()
    {
        // login as user and back with code
        header('Location: ' . $this->getAuthUrl());
        exit;
    }

    // get login url
    public function getLoginUrl() : string
    {
        if(empty($this->authUrl))
        {
            $helper = $this->fb->getRedirectLoginHelper();
            $this->authUrl = $helper->getLoginUrl($this->callbackUrl, $this->scopes);
        }

        return $this->authUrl;
    }

    public function getAccessToken() : string
    {
        return $this->accessToken;
    }

    public function getAuthUrl() : string
    {
        return $this->authUrl;
    }

    public function getAccessTokenFromCode($code) : string
    {
        if(!is_string($code) || empty($code))
        {
            throw new Exception("Code is empty or incorrect");
        }

        // if we have a code, we are get access token from code
        $atInfo = $this->fb->getOAuth2Client()->getAccessTokenFromCode($code, $this->callbackUrl);

        $accessToken = $atInfo->getValue();
        $_SESSION['fb_access_token'] = $accessToken;
        $this->fb->setDefaultAccessToken($accessToken);

        return $accessToken;
    }

    public function updateAccessTokenIfNeed($code = '') : string
    {
        if($this->getAccessToken() == null)
        {
            // if have not code
            if(empty($code))
            {
                $this->_authUser();
            }

            $this->getAccessTokenFromCode($code);
        }

        return $this->getAccessToken();
    }

    public function forceAuthUser()
    {
        $this->_clearSession();
        $this->_authUser();
    }

    /**
     * @return FB
     */
    public function getFB() : FB
    {
        return $this->fb;
    }
}
