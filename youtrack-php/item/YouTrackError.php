<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:53
 * A class describing a youtrack error.
 */
namespace app\services\youTrack\exception;

use app\services\youTrack\Connection;

class YouTrackError extends \YouTrackObject
{
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }

    protected function _update_attributes(\SimpleXMLElement $xml)
    {
        foreach ($xml->xpath('/error') as $node) {
            $this->attributes['error'] = (string)$node;
        }
    }
}