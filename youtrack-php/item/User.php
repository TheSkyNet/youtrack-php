<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:08
 * A class describing a youtrack user.
 * @todo Add methods for hashing and comparison.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class User extends YouTrackObject
{
    /**
     * User constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}

