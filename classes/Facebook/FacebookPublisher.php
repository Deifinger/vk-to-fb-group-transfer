<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 10:47 PM
 */

namespace VKToFB;

use \Facebook\Facebook;


class FacebookPublisher
{
    private $fb = null; // \Facebook\Facebook

    public function __construct(Facebook $fb)
    {
        if($fb instanceof Facebook === false) {
            throw new \Exception("Set the \Facebook\Facebook object in constructor");
        }

        $this->fb = $fb;
    }

    public function postPhoto($pageId, $url, $caption, $message)
    {
        return $this->fb->post($pageId.'/photos', array(
            'url'       => $url,
            'caption'   => $caption,
            'message'   => $message
        ));
    }

    public function postLink($pageId, $url, $message)
    {
        return $this->fb->post($pageId.'/feed', array(
            'link'       => $url,
            'message'   => $message
        ));
    }

    public function postText($pageId, $message)
    {
        return $this->fb->post($pageId.'/feed', array(
            'message'   => $message
        ));
    }

    public function postVideo($pageId, $url, $caption, $description)
    {
        return $this->fb->post($pageId.'/videos', array(
            'url'           => $url,
            'caption'       => $caption,
            'description'   => $description
        ));
    }
}