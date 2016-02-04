<?php

/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 15:49
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class EnumBundle extends YouTrackObject
{

    protected $_name = '';
    protected $_values = [];

    /**
     * EnumBundle constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }

    /**
     * @return string
     */
    public function toXML()
    {
        $xml = "<enumeration name='{$this->name}'>";
        foreach ($this->_values as $value) {
            $xml .= "<value>{$value}</value>";
        }
        $xml .= '</enumeration>';

        return $xml;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * @param \SimpleXMLElement $xml
     */
    protected function _updateAttributes(\SimpleXMLElement $xml)
    {
        $this->_name = (string)$xml->attributes()->name;
    }

    protected function _updateChildrenAttributes(\SimpleXMLElement $xml)
    {
        foreach ($xml->children() as $node) {
            $this->_values[] = (string)$node;
        }
    }
}
