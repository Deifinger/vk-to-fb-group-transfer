<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/30/17 2:30 PM
 */

namespace VKToFB\Fb;


use VKToFB\Config;
use Facebook\Facebook as FB;
use VKToFB\Fb\FacebookRequestor;
use Monolog\Logger;
use VKToFB\Fb\Attachments\PhotoAttachment;
use VKToFB\Vk\HelperAlbum;
use \VKToFB\Vk\HelperVideo;

class FacebookPublisher
{
    private $fb;
    private $logger = null;
    private $requestor = null;

    private static $albumCounter = 0;
    private $ownerId = 0;

    private $albums = array();
    private $videos = array();

    private $wallPostsTmplUrl = '';

    public function __construct(FB $fb, Logger $logger = null)
    {
        $this->fb = $fb;
        $this->logger = $logger;
        $this->requestor = new FacebookRequestor($fb);

        $this->ownerId = Config::get('fb.group_id');

        $this->wallPostsTmplUrl = sprintf('https://vk.com/%s?w=wall%s_{postId}',
            Config::get('vk.screen_name'),
            (string)Config::get('vk.group_id'));
    }

    // TODO: rewrite to ObjectCollections
    public function loadAlbums(array &$albums)
    {
        $this->albums = $albums;
    }
    public function loadVideos(array &$videos)
    {
        $this->videos = $videos;
    }

    // TODO: what if not all attachments of same type?
    // TODO: write more smart publisher
    public function publish(FBPost $post)
    {
        $this->_log('info', 'Publishing {postType}', array(
            'postType'  => $post->getType(),
            'postId'    => $post->getId()
        ));

        switch ($post->getType())
        {
            case 'photo':
                return $this->_publishPhoto($post);
            case 'album':
                return $this->_publishAlbum($post);
            case 'text':
                return $this->_publishText($post);
            case 'link':
                return $this->_publishLink($post);
            case 'video':
                return $this->_publishVideo($post);
            default:
                return false;
        }

    }

    private function _publishPhoto(FBPost $post)
    {
        $defAlbumName = Config::get('fb.default_album_name');

        $attachments = $post->getAttachments('photo');
        // TODO: write batcher
        $countPhotos = sizeof($attachments);
        if($countPhotos > 1)
        {
            // TODO: commented code below is works, but now there are error:
            // TODO: @see https://developers.facebook.com/bugs/806764979491825/
            /*
            $medias = array();
            for($y = 0; $y < 2; $y++)
            {
                // TODO: create some try/catch if smth wrong with only one attachment
                $photoResponse = $FBPublisher->uploadToAlbum(
                    Config::get('fb.chronics_album_id'),
                    $attachments[$y]
                );
                if(!$photoResponse->isError())
                {
                    $id = $photoResponse->getDecodedBody()['id'];
                    $medias[] = new \VKToFB\Fb\Structures\GraphMedia($id);
                }

            }

            // TODO: Write total text checking
            $text = $FBPost->getText();
            if(empty($text)) $text = $attachment->name;
            if(empty($text)) $text = $attachment->description;
            if(empty($text)) $text = '.';

            $FBPublisher->postTextWithPhotos(
                $config['fb']['group_id'],
                $text,
                $medias
            );
            */

            $albumTitle = $defAlbumName . ++$this::$albumCounter;
            $this->_uploadAlbumWithPhoto($albumTitle,
                $post->getText(), $attachments);

        }
        else
        {
            $this->requestor->postPhoto(
                $this->ownerId,
                $attachments[0]->getUrl(),
                $attachments[0]->getCaption(),
                $post->getText()
            );
        }
        return true;
    }

    private function _publishAlbum(FBPost $post)
    {
        $albumHelper = new HelperAlbum();
        $attachments = $post->getAttachments();
        $albumAttach = $attachments[0];

        $album = $albumHelper->findAlbumById($this->albums, $albumAttach->getVKId());

        // If we have not album, the post make no sense
        if($album === false)
        {
            $this->_log('warning', 'id({postId}) - Album is empty', array(
                'postId' => $post->getId()
            ));
            return false;
        }

        // TODO: prepare album before publishing
        foreach ($album->photos as $photo)
        {
            $albumAttach->addPhoto( new PhotoAttachment($photo) );
        }

        $description = empty($albumAttach->getCaption()) ? $post->getText() : $albumAttach->getCaption();
        $this->_uploadAlbumWithPhoto($albumAttach->getTitle(),
            $description, $albumAttach->getPhotos());

        return true;
    }

    private function _publishText(FBPost $post)
    {
        if($post->isCopied())
        {
            $this->requestor->postLink(
                $this->ownerId,
                $this->_getVKPostUrl($post->getId()),
                $post->getText()
            );
        }
        else
        {
            $this->requestor->postText(
                $this->ownerId,
                $post->getText()
            );
        }
        return true;
    }

    private function _publishLink(FBPost $post)
    {
        $this->requestor->postLink(
            $this->ownerId,
            $post->getAttachments()[0]->getUrl(),
            $post->getText()
        );
        return true;
    }

    private function _publishVideo(FBPost $post)
    {
        $VideoHelper = new HelperVideo();
        $attachments = $post->getAttachments('video');

        $video = $VideoHelper->findVideoById($this->videos, $attachments[0]->getVKId());

        // If we have not video, the post make no sense
        if($video === false)
        {
            $this->_log('warning', 'Video is empty', array(
                'postId'    => $post->getId()
            ));
            return false;
        }

        $res = $VideoHelper->getVideoUrlInfo($video);
        // if video is embeddable
        if($res['embeddable'])
        {
            // post it like link
            $this->requestor->postLink(
                $this->ownerId,
                $res['url'],
                $attachments[0]->getCaption()."\n\n".$attachments[0]->getDesc()
            );
        }
        else
        {
            // if not embeddable post like video
            $this->requestor->postVideo(
                $this->ownerId,
                $res['url'],
                $attachments[0]->getCaption(),
                $attachments[0]->getDesc()
            );
        }

        return true;
    }

    private function _uploadAlbumWithPhoto(
        string $albumTitle,
        string $albumDesc,
        array $photos)
    {
        $albumRequest = $this->requestor->createAlbum(
            $this->ownerId,
            $albumTitle,
            $albumDesc
        );
        $albumId = $this->fb->getClient()->sendRequest( $albumRequest )->getDecodedBody()['id'];

        $countPhotos = sizeof($photos);
        for($y = 0; $y < ceil($countPhotos / 50); $y++)
        {
            $photoRequests = array();
            // 50 - max batch requests
            for($x = 0; $x < min(50, $countPhotos - $y * 50); $x++)
            {
                $photoRequests[] = $this->requestor->uploadToAlbum(
                    $albumId,
                    $photos[$y * 50 + $x],
                    false
                );
            }
            $this->fb->sendBatchRequest($photoRequests);
        }
    }

    private function _getVKPostUrl($postId)
    {
        return str_replace('{postId}', $postId, $this->wallPostsTmplUrl);
    }

    private function _log(string $level = 'Info', string $message = '', array $context = array())
    {
        if(empty($level)) return false;

        $level = ucfirst(strtolower($level));

        $this->logger->{'add'.$level}($message, $context);
    }
}