<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/28/17 7:57 PM
 */

namespace VKToFB\Vk\TestAPIForm;


use VKToFB\Exceptions\BadVKTestAPIResponseException;

class VKResponseValidator
{
    public function __construct()
    {
    }

    public function checkResponse($response)
    {
        $response = $this->_getResponseIfParseble($response);
        if($response !== null && $this->_checkResponseOnErrors($response))
        {
            $jsonStr = $this->_convertResponse($response[5]);
            return $jsonStr;
        }

        return null;
    }


    private function _getResponseIfParseble($response)
    {
        $response = explode('<!>', $response);
        if(!is_numeric($response[0]) && isset($response[5]))
        {
            return null;
        }

        return $response;
    }

    private function _checkResponseOnErrors($response)
    {
        if($response[4] != 0)
        {
            throw new BadVKTestAPIResponseException($response[5]);
        }
        return true;
    }

    private function _convertResponse($response)
    {
        return mb_convert_encoding($response, 'UTF-8','Windows-1251');
    }

}