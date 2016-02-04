<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 16:43
 * A class describing a youtrack build.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Build extends YouTrackObject
{
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}
