<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 16:07
 * A class describing a youtrack issue link type.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class IssueLinkType extends YouTrackObject
{
    /**
     * IssueLinkType constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}

