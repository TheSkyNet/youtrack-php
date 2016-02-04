<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 22:04
 * A class describing a youtrack subsystem.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Subsystem extends YouTrackObject
{
    /**
     * Subsystem constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}

