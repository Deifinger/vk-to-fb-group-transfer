<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/30/17 12:47 AM
 */

namespace VKToFB\Vk;


class HelperAlbum
{
    public function __construct() {}

    public function findAlbumById($VKAlbums, $albumId)
    {
        $album = array_filter(
            $VKAlbums,
            function ($a) use ($albumId) {
                return $a->id == $albumId;
            }
        );

        if(empty($album))
        {
            return false;
        }

        return current($album);
    }

}