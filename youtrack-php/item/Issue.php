<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:11
 * A class describing a youtrack issue.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Issue extends YouTrackObject
{
    private $links = [];
    private $attachments = [];
    private $comments = [];

    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        parent::__construct($xml, $youtrack);
        if (!empty($xml)) {
            if (!empty($this->attributes['links'])) {
                $links = [];
                foreach ($xml->xpath('//field[@name="links"]') as $node) {
                    foreach ($node->children() as $link) {
                        $links[(string)$link] = [
                            'type' => (string)$link->attributes()->type,
                            'role' => (string)$link->attributes()->role,
                        ];
                    }
                }
                $this->__set('links', $links);
            }
            if (!empty($this->attributes['attachments'])) {
                $attachments = array();
                foreach ($xml->xpath('//field[@name="attachments"]') as $node) {
                    foreach ($node->children() as $attachment) {
                        $attachments[(string)$attachment] = [
                            'url' => (string)$attachment->attributes()->url,
                        ];
                    }
                }
                $this->__set('attachments', $attachments);
            }
        }
    }

    public function getReporter()
    {
        return $this->youtrack->get_user($this->__get('reporterName'));
    }

    public function hasAssignee()
    {
        $name = $this->__get('assigneeName');
        return !empty($name);
    }

    public function getAssignee()
    {
        return $this->youtrack->getUser($this->__get('assigneeName'));
    }

    public function getUpdater()
    {
        return $this->youtrack->getUser($this->__get('updaterName'));
    }

    public function getComments()
    {
        if (empty($this->comments)) {
            $this->comments = $this->youtrack->getComments($this->__get('id'));
        }
        return $this->comments;
    }

    public function getAttachments()
    {
        if (empty($this->attachments)) {
            $this->attachments = $this->youtrack->getAttachments($this->__get('id'));
        }
        return $this->attachments;
    }

    public function getLinks()
    {
        if (empty($this->links)) {
            $this->links = $this->youtrack->getLinks($this->__get('id'));
        }
        return $this->links;
    }
}
