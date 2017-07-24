<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:01 PM
 */

namespace VKToFB;

abstract class Attachment
{
    public function __construct($VKAttachment)
    {
        if(!is_object($VKAttachment))
        {
            throw new \Exception("Incorrect attachment structure");
        }
    }

    /**
     * @param $obj - object that has "photo_"-started properties
     * @return string - url
     */
    protected function _getBiggestPhotoUrl($obj) : string
    {
        $props = get_object_vars($obj);
        $biggestPhotoUrl = '';
        foreach ($props as $prop => $val)
        {
            if(strpos($prop, 'photo_') !== false)
            {
                $biggestPhotoUrl = $val;
            }
        }

        return $biggestPhotoUrl;
    }

    abstract function getUrl();
    abstract function getCaption();
}