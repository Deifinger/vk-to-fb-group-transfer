<?php
/**
 * Created by Ruslan Kostikov
 * Date: 7/29/17 2:17 PM
 */

namespace VKToFB\Fb\Structures;



class GraphLink
{
    public $name;
    public $description;
    public $picture;
    public $link;

    public function __construct($name, $description, $picture, $link)
    {
        $this->name = $name;
        $this->description = $description;
        $this->picture = $picture;
        $this->link = $link;
    }
}