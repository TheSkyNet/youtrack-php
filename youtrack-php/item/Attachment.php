<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:09
 * A class describing a youtrack attachment.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Attachment extends YouTrackObject
{
    /**
     * Attachment constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->youtrack->getAttachmentContent($this->__get('url'));
    }
}

