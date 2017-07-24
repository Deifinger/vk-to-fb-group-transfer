<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:29 PM
 */

namespace VKToFB;


class VideoAttachment extends Attachment
{
    private $VKId;
    private $url;
    private $caption;
    private $desc;

    public function __construct($VKAttachment)
    {
        parent::__construct($VKAttachment);

        $this->VKId = $VKAttachment->video->id;
        $this->caption = $VKAttachment->video->title;
        $this->desc = $VKAttachment->video->description;
    }

    private function _getVideoUrl()
    {
    }

    public function getUrl()
    {
        return $this->url;
    }
    public function getCaption()
    {
        return $this->caption;
    }
    public function getDesc()
    {
        return $this->desc;
    }
    public function getVKId()
    {
        return $this->VKId;
    }
}