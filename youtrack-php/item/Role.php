<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 22:07
 * A class describing a youtrack role.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Role extends YouTrackObject
{
    /**
     * Role constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}

