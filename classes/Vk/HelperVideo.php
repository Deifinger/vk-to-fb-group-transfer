<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/25/17 9:16 PM
 */

namespace VKToFB\Vk;


class HelperVideo
{
    public function __construct() {}

    public function findVideoById($VKVideos, $videoId)
    {
        $video = array_filter(
            $VKVideos,
            function ($v) use ($videoId) {
                return $v->id == $videoId;
            }
        );
        //var_dump($VKVideos);

        if(empty($video))
        {
            return false;
        }

        return current($video);
    }

    /**
     * @param $video
     * @return array|string
     */
    public function getVideoUrlInfo($video)
    {
        $res = array(
            'url'           => '',
            'embeddable'    => false
        );

        if(!isset($video->files))
        {
            return $res;
        }

        $res['url'] = $this->_getMostQualityVideo($video->files);

        if(empty($res['url']) && isset($video->files->external))
        {
            $res = array(
                'url'           => $video->files->external,
                'embeddable'    => true
            );
        }

        return $res;
    }

    /**
     * @param $obj - object that has "mp4_"-started properties
     * @return string - url
     */
    protected function _getMostQualityVideo($obj) : string
    {
        $props = get_object_vars($obj);
        $videoUrl = '';
        foreach ($props as $prop => $val)
        {
            if(strpos($prop, 'mp4_') !== false)
            {
                $videoUrl = $val;
            }
        }

        return $videoUrl;
    }
}