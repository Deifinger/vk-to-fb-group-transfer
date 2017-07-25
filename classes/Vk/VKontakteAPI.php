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
     * Get wall posts
     *
     * @param string $groupId
     * @param int $amount - if 0 returns all posts
     * @return array
     * @throws \Exception
     */
    public function getWallPosts(string $groupId, int $amount = 0) : array
    {
        $posts = array();
        $vk_amount_posts = 100; // first value for right logic
        $cur_amount_posts = 0;
        do
        {
            $diff = min(100, $vk_amount_posts - $cur_amount_posts);
            $res = $this->vk->getVK()->api('wall.get', array(
                'owner_id'  => $groupId,
                'offset'    => $cur_amount_posts,
                'count'     => $diff // 100 - max posts by request
            ));

            if(isset($res['error']))
            {
                throw new \Exception($res['error']['error_msg'], $res['error']['error_code']);
            }

            $posts = array_merge($posts, $res['response']['items']);
            $cur_amount_posts += $diff;

            $vk_amount_posts = ($amount > 0 ? $amount : $res['response']['count']);
        }
        while($vk_amount_posts > $cur_amount_posts);

        return $posts;
    }
}