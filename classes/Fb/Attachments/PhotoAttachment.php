<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:05 PM
 */

namespace VKToFB\Fb\Attachments;


class PhotoAttachment extends Attachment
{
    private $VKId;
    private $url;
    private $caption;

    public function __construct($VKAttachment)
    {
        parent::__construct($VKAttachment);

        $this->VKId = $VKAttachment->id;
        $this->url = $this->_getPhotoUrl($VKAttachment);
        $this->caption = isset($VKAttachment->text) ? $VKAttachment->text : '';
    }

    private function _getPhotoUrl($VKAttachment)
    {
        return $this->_getBiggestPhotoUrl($VKAttachment);
    }

    public function getVKId()
    {
        return $this->VKId;
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