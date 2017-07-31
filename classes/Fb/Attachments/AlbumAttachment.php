<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:24 PM
 */

namespace VKToFB\Fb\Attachments;


class AlbumAttachment extends Attachment implements \Countable, \IteratorAggregate
{
    private $VKId;
    private $url;
    private $title;
    private $desc;
    private $count = 0;
    private $photos = array();

    public function __construct($VKAttachment)
    {
        parent::__construct($VKAttachment);

        $this->VKId = $VKAttachment->album->id;
        $this->url = $this->_getPhotoUrl($VKAttachment);
        $this->title = $VKAttachment->album->title;
        $this->desc = $VKAttachment->album->description;
    }

    private function _getPhotoUrl($VKAttachment)
    {
        $photoObj = $VKAttachment->album->thumb;
        return $this->_getBiggestPhotoUrl($photoObj);
    }

    public function count()
    {
        return $this->count;
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->photos);
    }

    public function addPhoto(PhotoAttachment $photo)
    {
        for ($i = 0; $i < $this->count; $i++)
        {
            if($this->photos[$i]->getVKId() === $photo->getVKId())
            {
                return false;
            }
        }
        $this->photos[] = $photo;
        ++$this->count;
        return false;
    }
    public function removePhoto(PhotoAttachment $photo)
    {
        for ($i = 0; $i < $this->count; $i++)
        {
            if($this->photos[$i]->getId() === $photo->getVKId())
            {
                unset($this->photos[$i]);
                return true;
            }
        }
        --$this->count;
        return false;
    }

    public function getVKId()
    {
        return $this->VKId;
    }
    public function getUrl()
    {
        return $this->url;
    }
    public function getTitle()
    {
        return $this->title;
    }
    public function getCaption()
    {
        return $this->desc;
    }
    public function getPhotos()
    {
        return $this->photos;
    }
}