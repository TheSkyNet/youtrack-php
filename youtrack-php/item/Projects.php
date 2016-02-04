<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 22:05
 */
namespace app\services\youTrack\item;

use app\services\youTrack\Connection;

class Projects extends YouTrackObject
{
    /**
     * Projects constructor.
     * @param \SimpleXMLElement|NULL $xml
     * @param Connection|NULL $youtrack
     */
    public function __construct(\SimpleXMLElement $xml = NULL, Connection $youtrack = NULL)
    {
        $projects = [];
        foreach ($xml->children() as $xmlx) {

            array_push($projects, new Project($xmlx, $youtrack));
        }

        $this->projects = $projects;
    }


}

