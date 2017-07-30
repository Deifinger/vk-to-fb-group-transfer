<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 10:47 PM
 */

namespace VKToFB\Fb;

use \Facebook\Facebook;
use VKToFB\Fb\Attachments\PhotoAttachment;
use VKToFB\Fb\Structures\GraphMedia;

// TODO: write FacebookBatcher
// TODO: refactor methods for more elastic
class FacebookRequestor
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

    public function createAlbum($pageId, $name, $message)
    {
        return $this->fb->request('POST', $pageId.'/albums', array(
            'message'   => $message,
            'name'       => $name,
            //'link'      => 'http://facebook.com/',
            //'child_attachments' => $child_attachments,
            //'multi_share_optimized' => true
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

    public function postTextWithPhotos($pageId, $message, array $attachedMedia = array())
    {
        array_map(function ($media) {
            if($media instanceof GraphMedia == false)
            {
                throw new \InvalidArgumentException(
                    "attachedMedia argument must be an array of GraphMedia objects"
                );
            }
        }, $attachedMedia);

        return $this->fb->post($pageId.'/feed', array(
            'message'           => $message,
            'attached_media'    => '[{"media_fbid":"1505028389563611"},{"media_fbid":"1505028416230275"}]'
        ));
    }

    public function postVideo($pageId, $url, $caption, $description, bool $isEmbeddable = false)
    {
        return $this->fb->post($pageId.'/videos', array(
            'file_url'      => $url,
            'title'         => $caption,
            'description'   => $description,
            'embeddable'    => $isEmbeddable
        ));
    }

    public function uploadToAlbum($albumId, PhotoAttachment $photo, $noStory = true)
    {
        return $this->fb->request('POST',$albumId.'/photos', array(
            'url'       => $photo->getUrl(),
            'message'   => $photo->getCaption(),
            'no_story'  => $noStory
        ));
    }
}