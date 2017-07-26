<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:40 PM
 */

namespace VKToFB\Fb\Attachments;


class AttachmentFactory extends AttachmentFactoryAbstract
{
    public function __construct()
    {
    }

    public static function createAttachment($VKAttachment)
    {
        $attachment = null;
        switch($VKAttachment->type)
        {
            case "photo":
                $attachment = new PhotoAttachment($VKAttachment);
                break;
            case "album":
                $attachment = new AlbumAttachment($VKAttachment);
                break;
            case "link":
                $attachment = new LinkAttachment($VKAttachment);
                break;
            case "video":
                $attachment = new VideoAttachment($VKAttachment);
                break;
            default:
                $attachment = new PhotoAttachment($VKAttachment);
        }

        return $attachment;
    }
}