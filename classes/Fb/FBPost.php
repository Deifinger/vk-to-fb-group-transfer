<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 7:38 PM
 */

namespace VKToFB\Fb;

use VKToFB\Fb\Attachments\AttachmentFactory;

class FBPost
{
    private $type; // by first attachment
    private $id = 0; // in most for debugging
    private $text = '';
    private $copied = false;
    private $attachments = array(); // \Attachments\Attachment

    public function __construct($VKPost)
    {
        if(!is_object($VKPost) || !isset($VKPost->text))
        {
            throw new \Exception("Incorrect post structure");
        }

        $this->_fillFields($VKPost);
    }

    private function _fillFields($VKPost)
    {
        $this->type = 'text';
        $this->text = $VKPost->text;
        $this->id = $VKPost->id;
        $this->copied = isset($VKPost->copy_history) && sizeof($VKPost->copy_history) > 0;

        if(!isset($VKPost->attachments))
        {
            return;
        }

        $this->setAttachments($VKPost->attachments);
    }

    public function getId()
    {
        return $this->id;
    }
    public function getType()
    {
        return $this->type;
    }
    public function getText()
    {
        return $this->text;
    }
    public function isCopied()
    {
        return $this->copied;
    }

    /**
     * @param string $type
     * @return array of \Attachments\Attachment
     */
    public function getAttachments(string $type = '')
    {
        if(empty($type))
        {
            return $this->attachments;
        }
        else
        {
            return array_filter($this->attachments, function ($at) use ($type) {
                $type = ucfirst(strtolower($type));
                return is_a($at, 'VKToFB\Fb\Attachments\\'.$type.'Attachment');
            });
        }
    }
    public function setAttachments($VKAttachments, bool $isAlbumPhotos = false)
    {
        $count = sizeof($VKAttachments);
        for ($i = 0; $i < $count; $i++)
        {
            // TODO: change this case
            // Ad Hoc for albums
            if($isAlbumPhotos === true)
            {
                $VKAttachments[$i]->albumAdHoc = true;
                $VKAttachments[$i]->type = 'photo';
            }
            // TODO: set the type of post more safely
            if($i == 0) $this->type = $VKAttachments[$i]->type;

            $this->attachments[] = AttachmentFactory::createAttachment($VKAttachments[$i]);
        }
    }
}