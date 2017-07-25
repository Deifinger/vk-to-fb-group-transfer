<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/25/17 2:35 PM
 */

namespace VKToFB;


abstract class Social
{
    protected $scopes = [];
    protected $accessToken = null;
    protected $callbackUrl = '';
    protected $authUrl = '';

    protected function _defaults()
    {
        return array(
            'scopes'        => '',
            'access_token'  => null,
            'app_id'        => '',
            'app_secret'    => '',
            'api_version'   => '',
            'default_graph_version' => '',
            'callback_url'  => ''
        );
    }

    protected function _authUser()
    {
        // login as user and back with code
        header('Location: ' . $this->getLoginUrl());
        exit;
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

    // init social object
    abstract protected function _init($options);
    abstract protected function _clearSession();
    abstract public function getLoginUrl();
    abstract public function getAccessToken();
    abstract public function getAccessTokenFromCode($code = '');

}