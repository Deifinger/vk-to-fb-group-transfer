<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/24/17 8:39 PM
 */

namespace VKToFB;


abstract class AttachmentFactoryAbstract
{
    abstract static function createAttachment($VKAttachment);
}