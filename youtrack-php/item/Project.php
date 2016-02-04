<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 22:06
 * A class describing a youtrack project.
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Project extends YouTrackObject
{
    /**
     * Project constructor.
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
    public function getSubsystems()
    {
        return $this->youtrack->getSubsystems($this->id);
    }

    /**
     * @param $name
     * @param $is_default
     * @param $default_assignee_login
     * @return mixed
     */
    public function createSubsystem($name, $is_default, $default_assignee_login)
    {
        return $this->youtrack->createSubsystem($this->id, $name, $is_default, $default_assignee_login);
    }
}

