<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/27/17 2:49 PM
 */

namespace VKToFB\Vk\TestAPIForm;

use GuzzleHttp\Cookie\FileCookieJar;
use Psr\Log\InvalidArgumentException;
use VkAuth\VkAuthAgent;
use VKToFB\Logger;

class VKDevForm
{
    private $login;
    private $pass;
    private $cookiePath;
    private $cookieJar = null;
    private $logFilePath;
    private $authAgent = null;
    private $hash = '';

    public function __construct($login, $pass,
                                string $cookiePath = '',
                                string $logFilePath = null)
    {
        $this->login = $login;
        $this->pass = $pass;
        $this->cookiePath = $cookiePath;
        $this->logFilePath = $logFilePath;
    }

    /**
     * @return null|VkAuthAgent
     */
    public function getAuthAgent()
    {
        if($this->authAgent === null)
        {
            $this->_auth();
        }
        return $this->authAgent;
    }

    public function getCookieJar()
    {
        if($this->cookieJar === null)
        {
            $cookiePath = $this->getCookiePath();
            // if cookie file is empty or extremely small size of file
            if(filesize($cookiePath) < 10)
            {
                $this->_auth();
            }
            else
            {
                $this->cookieJar = new FileCookieJar($cookiePath);
            }
        }
        return $this->cookieJar;
    }

    public function getCookiePath()
    {
        if(empty($this->cookiePath))
        {
            return 'VKDevForm.cookie';
        }
        return $this->cookiePath;
    }

    private function _auth()
    {
        $this->authAgent = new VkAuthAgent($this->login, $this->pass,
            $this->logFilePath, array(Logger::getLogger(), 'addDebug'));
        $cookieFileJar = new FileCookieJar($this->getCookiePath());
        $authCookieJar = $this->authAgent->getAuthorizedCookieJar();
        $authCookieJar->clearSessionCookies();

        foreach ($authCookieJar as $key => $cookie)
        {
            $cookieFileJar->setCookie($cookie);
        }

        $this->cookieJar = $cookieFileJar;
    }

    public function getHash()
    {

        if(empty($this->hash))
        {
            $this->_updateHash();
        }

        return $this->hash;
    }

    private function _updateHash()
    {
        $VKRequest = new VKDevRequest();
        $res = $VKRequest->request('GET', 'video.get', array(
            'cookieJar' =>  $this->getCookieJar()
        ));

        preg_match("/Dev\.methodRun\('(.+)', this\)/", $res->getBody(), $matches);
        return $this->hash = $matches[1];
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $postParams
     * @param array $headers
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function request(string $method, string $endpoint,
                            array $postParams = array(), array $headers = array())
    {
        $postParams = array_merge(array(
            'act'           => 'a_run_method',
            'al'            => 1,
            'hash'          => $this->getHash(),
            'method'        => $endpoint,
            'param_count'   => 100,
            'param_extended'=> 0,
            'param_offset'  => 0,
            'param_owner_id'=> '',
            'param_v'       => 5.67
        ), $postParams);
        if(empty($postParams['param_owner_id']))
        {
            throw new \InvalidArgumentException('Argument $postParams["param_owner_id"] is empty');
        }

        $VKRequest = new VKDevRequest();
        return $VKRequest->request($method, $endpoint, array(
            'cookieJar'     => $this->getCookieJar(),
            'request'       => array(
                'headers'       => $headers,
                'form_params'   => $postParams,
            )
        ));
    }

    public function requestGetVideos($postParams = array())
    {
        $endpoint = 'video.get';
        $postParams = array_merge(array(
            'method'        => $endpoint,
            'param_count'   => 200,
            'param_owner_id'=> '',
            'param_v'       => 5.67
        ), $postParams);

        $headers = array(
            'accept'            => '*/*',
            'x-requested-with'  => 'XMLHttpRequest',
        );

        $response = $this->request('POST', $endpoint, $postParams, $headers)->getBody();

        $validator = new VKResponseValidator();
        $response = $validator->checkResponse($response);

        return \GuzzleHttp\json_decode($response)->response;
    }


}