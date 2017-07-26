<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:05 PM
 */

namespace VKToFB\Fb\Attachments;


class PhotoAttachment extends Attachment
{
    private $url;
    private $caption;

    public function __construct($VKAttachment)
    {
        parent::__construct($VKAttachment);

        $this->url = $this->_getPhotoUrl($VKAttachment);
        $this->caption = $VKAttachment->photo->text;
    }

    private function _getPhotoUrl($VKAttachment)
    {
        $photoObj = $VKAttachment->photo;
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
}