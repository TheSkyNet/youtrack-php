<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 15:50
 * A class describing a youtrack project custom field.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class ProjectCustomField extends YouTrackObject
{
    /**
     * ProjectCustomField constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }

    /**
     * @param \SimpleXMLElement $xml
     * @throws NotImplementedException
     */
    protected function _updateChildrenAttributes(\SimpleXMLElement $xml)
    {
        throw new NotImplementedException("_update_children_attributes(xml)");
    }
}

