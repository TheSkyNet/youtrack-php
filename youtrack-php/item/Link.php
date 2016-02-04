<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:09
 * A class describing a youtrack link.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Link extends YouTrackObject
{
    /**
     * Link constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}
