<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:08
 * A class describing a youtrack group.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Group extends YouTrackObject
{
    /**
     * Group constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}

