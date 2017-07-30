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

        if(!isset($VKPost->attachments))
        {
            return;
        }

        $this->_getAttachments($VKPost->attachments);
    }

    private function _getAttachments($VKAttachments)
    {
        $count = sizeof($VKAttachments);
        for ($i = 0; $i < $count; $i++)
        {
            $type = $VKAttachments[$i]->type;
            if($i == 0) $this->type = $type;

            $this->attachments[] = AttachmentFactory::createAttachment($VKAttachments[$i]);
        }
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

    /**
     * @return array \Attachments\Attachment
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

}