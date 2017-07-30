<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/29/17 3:21 PM
 */

namespace VKToFB\Fb\Structures;


class GraphMedia
{
    public $media_fbid;

    public function __construct($mediaFbId)
    {
        $this->media_fbid = $mediaFbId;
    }
}