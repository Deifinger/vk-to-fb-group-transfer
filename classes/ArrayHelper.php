<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/27/17 7:48 PM
 */

namespace VKToFB;


class ArrayHelper
{
    public static function mergeArrayDeep(&$array1, $array2)
    {
        foreach ($array2 as $key => $value)
        {
            // if both arrays use deep merge
            $bothArrays = isset($array1[$key]) && is_array($array1[$key]) && is_array($value);
            if($bothArrays)
            {
                ArrayHelper::mergeArrayDeep($array1[$key], $value);
            }
            else
            {
                $array1[$key] = $value;
            }

        }
    }
}