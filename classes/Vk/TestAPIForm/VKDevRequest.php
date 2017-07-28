<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/27/17 6:04 PM
 */

namespace VKToFB\Vk\TestAPIForm;


use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Client;
use \VKToFB\ArrayHelper;

class VKDevRequest
{
    private $baseUrl;

    public function __construct($baseUrl = 'https://vk.com/dev/')
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $method
     * @param $endpoint
     * @param array $options - $options['request'] is GuzzleHttp\Client::request's options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    public function request(string $method = 'GET', $endpoint, array $options = array())
    {
        $referer = $this->baseUrl;
        // if it's POST and we have form params
        if(strtoupper($method) == 'POST'
            && isset($options['request'])
            && isset($options['request']['form_params']))
        {
            $referer = $this->_getReferer($options['request']['form_params']);
        }

        // init headers
        $headers = array(
            'host' => 'vk.com',
            'origin' => 'https://vk.com',
            'referer' => $referer,
            'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
            'user-agent' => 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/59.0.3071.109 Chrome/59.0.3071.109 Safari/537.36',
            'accept-encoding' => 'gzip, deflate, br',
            'accept-language' => 'en-US,en;q=0.8',
            'content-Type' => 'application/x-www-form-urlencoded',
        );
        $request = array(
            'headers'       => $headers
        );
        // init request options
        if(isset($options['request']))
        {
            ArrayHelper::mergeArrayDeep($request, $options['request']);
        }

        // init cookie
        $cookieJar = isset($options['cookieJar']) ? $options['cookieJar'] : new CookieJar();

        // init base url
        $baseUrl = isset($options['baseUrl']) ? $options['baseUrl'] : $this->baseUrl;

        $client = new Client(array(
            'base_uri'=> $baseUrl,
            'cookies' => $cookieJar
        ));
        return $client->request($method, $endpoint, $request);
    }

    protected function _getReferer($postParams)
    {
        $paramsOnly =  array();
        // if key of post param starts from "param_" string
        foreach ($postParams as $key => $val)
        {
            if(strpos($key, 'param_') === 0)
            {
                $paramsOnly[str_replace('param_', '', $key)] = $val;
            }
        }
        return $this->baseUrl.'?'.$this->_params($paramsOnly);
    }

    protected function _params(array $params)
    {
        foreach ($params as $key => $val)
        {
            if($val !== '')
            {
                $params['params['.$key.']'] = $val;
            }
            unset($params[$key]);
        }
        return http_build_query($params, '', '&amp;');
    }
}