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
            // TODO: call type property as photo in every attachment
            case "photo":
                // TODO: remove ad hoc case
                if(isset($VKAttachment->albumAdHoc))
                {
                    $item = $VKAttachment;
                }
                else
                {
                    $item = $VKAttachment->photo;
                }
                $attachment = new PhotoAttachment($item);
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