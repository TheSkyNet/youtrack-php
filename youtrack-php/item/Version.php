<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 16:44
 * A class describing a youtrack version.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Version extends YouTrackObject
{
    /**
     * Version constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
        $check = $this->__get('description');
        if (empty($check)) {
            $this->__set('description', '');
        }
        $check = $this->__get('releaseDate');
        if (empty($check)) {
            $this->__set('releaseDate', NULL);
        }
    }
}

