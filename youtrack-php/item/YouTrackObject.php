<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:52
 * A class describing a youtrack object.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class YouTrackObject
{
    public $youtrack = NULL;
    public $attributes = [];

    /**
     * YouTrackObject constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     * @throws \Exception
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {

        $this->youtrack = $youtrack;
        if (!empty($xml)) {
            if (!($xml instanceof \SimpleXMLElement)) {
                throw new \Exception("An instance of SimpleXMLElement expected!");
            }
            $this->_updateAttributes($xml);
            $this->_updateChildrenAttributes($xml);
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     */
    protected function _updateAttributes(\SimpleXMLElement $xml)
    {

        foreach ($xml->xpath('/*') as $node) {
            foreach ($node->attributes() as $key => $value) {
                $this->attributes["$key"] = (string)$value;
            }
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     */
    protected function _updateChildrenAttributes(\SimpleXMLElement $xml)
    {
        foreach ($xml->children() as $node) {
            foreach ($node->attributes() as $key => $value) {
                if ($key == 'name') {
                    $this->__set($value, (string)$node->value);
                } else {
                    $this->__set($key, (string)$value);
                }
            }
        }
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if (!empty($this->attributes["$name"])) {
            return $this->attributes["$name"];
        }
        return NULL;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->attributes["$name"] = $value;
    }
}




