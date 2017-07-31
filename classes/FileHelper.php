<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/30/17 4:00 PM
 */

namespace VKToFB;


class FileHelper
{
    public function __construct()
    {
    }

    public function readFile(string $filePath, bool $decodeJson = false)
    {
        $ch = fopen($filePath, 'r');
        $result = fread($ch, filesize($filePath));
        fclose($ch);

        if($decodeJson == true)
        {
            $result = json_decode($result);
        }

        return $result;
    }
}