<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/25/17 4:48 PM
 */

namespace VKToFB\Vk;


class VKontakteAPI
{
    private $vk; // VKontakte

    public function __construct(VKontakte $vk)
    {
        $this->vk = $vk;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @param int $amount - if 0 gets all items
     * @param int $maxCount - max count by request (api value)
     * @return array
     * @throws \Exception
     */
    private function _getPageStaff(
        string $method,
        array $parameters,
        int $amount = 0,
        int $maxCount = 100 )
    {
        $items = array();
        // 100 - default value for right logic
        $vkAmountItems = ($amount > 0 ? $amount : $maxCount);
        $curAmountItems = 0;
        do
        {
            $diff = min($maxCount, $vkAmountItems - $curAmountItems);
            $parameters = array_merge($parameters, array(
                'offset'    => $curAmountItems,
                'count'     => $diff
            ));
            $res = $this->vk->getVK()->api($method, $parameters);

            if(isset($res['error']))
            {
                throw new \Exception($res['error']['error_msg'], $res['error']['error_code']);
            }

            $items = array_merge($items, $res['response']['items']);
            $curAmountItems += $diff;

            $vkAmountItems = ($amount > 0 ? $amount : $res['response']['count']);
        }
        while($vkAmountItems > $curAmountItems);

        return $items;
    }

    /**
     * Get wall posts
     *
     * @param string $pageId
     * @param int $amount - if 0 returns all posts
     * @return array
     * @throws \Exception
     */
    public function getPagePosts(string $pageId, int $amount = 0) : array
    {
        $posts = $this->_getPageStaff('wall.get', array(
            'owner_id'  => $pageId
        ), $amount, 100);

        return $posts;
    }

    public function getPageVideos(string $pageId, int $amount = 0) : array
    {
        $videos = $this->_getPageStaff('video.get', array(
            'owner_id'  => $pageId
        ), $amount, 200);

        return $videos;
    }

    // TODO: make some elastic approach
    public function getPageAlbums(string $ownerId, int $amount = 0) : array
    {
        $result = array();

        $albums = $this->_getPageStaff('photos.getAlbums', array(
            'owner_id'  => $ownerId
        ), $amount, 200);

        $count = sizeof($albums);
        for ($i = 0; $i < $count; $i++)
        {
            $album = $albums[$i];

            // TODO: use photos.getAll with offsets if albums very much
            $photos = $this->_getPageStaff('photos.get', array(
                'owner_id'  => $ownerId,
                'album_id'  => $album['id']
            ), $amount, 1000);
            $albumRes = array(
                'id'            => $album['id'],
                'title'         => $album['title'],
                'description'   => $album['description'],
                'photos'        => $photos
            );

            $result[] = $albumRes;
            // sleep on 0.4 sec, because it's very quickly for API
            usleep ( 400000 );
        }

        return $result;
    }
}