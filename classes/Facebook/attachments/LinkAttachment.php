<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:21 PM
 */

namespace VKToFB;


class LinkAttachment extends Attachment
{
    private $url;
    private $photoUrl;
    private $caption;

    public function __construct($VKAttachment)
    {
        parent::__construct($VKAttachment);

        $this->photoUrl = $this->_getPhotoUrl($VKAttachment);
        $this->url = $VKAttachment->link->url;
        $this->caption = $VKAttachment->link->text;
    }

    private function _getPhotoUrl($VKAttachment)
    {
        $photoObj = $VKAttachment->link->photo;
        return $this->_getBiggestPhotoUrl($photoObj);
    }

    public function getUrl()
    {
        return $this->url;
    }
    public function getCaption()
    {
        return $this->caption;
    }
    public function getPhotoUrl()
    {
        return $this->photoUrl;
    }
}