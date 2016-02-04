<?php

/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 16:04
 * A class describing a youtrack custom field.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class CustomField extends YouTrackObject
{
    var $name;
    var $emptyText;
    var $params;

    /**
     * CustomField constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }
}

