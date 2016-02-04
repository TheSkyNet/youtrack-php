<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:11
 * A class describing a youtrack comment.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Comment extends YouTrackObject
{
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }

    public function getAuthor()
    {
        return $this->youtrack->get_user($this->__get('author'));
    }
}

