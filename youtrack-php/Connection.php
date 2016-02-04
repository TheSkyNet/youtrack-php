<?php
namespace app\services\youTrack;

use app\services\youTrack\item\NotImplementedException;


/**
 * A class for connecting to a youtrack instance.
 *
 * @author Jens Jahnke <jan0sch@gmx.net>
 * @author Kevin Morton <kev@kevs.biz>
 * Created at: 29.03.11 16:13
 */
class Connection
{
    private $http = NULL;
    private $url = '';
    private $base_url = '';
    private $headers = [];
    private $cookies = [];
    private $debug_verbose = FALSE; // Set to TRUE to enable verbose logging of curl messages.
    private $user_agent = 'Mozilla/5.0'; // Use this as user agent string.
    private $verify_ssl = FALSE;

    /**
     * Connection constructor.
     * @param $url
     * @param $login
     * @param $password
     */
    public function __construct($url, $login, $password)
    {
        $this->http = curl_init();
        $this->url = $url;
        $this->base_url = $url . '/youtrack/rest';
        $this->_login($login, $password);
    }

    /**
     * @param $login
     * @param $password
     * @throws item\YouTrackException
     */
    protected function _login($login, $password)
    {
        curl_setopt($this->http, CURLOPT_POST, TRUE);
        curl_setopt($this->http, CURLOPT_HTTPHEADER, array('Content-Length: 1')); // Workaround for login problems when running behind lighttpd proxy @see http://redmine.lighttpd.net/issues/1717
        curl_setopt($this->http, CURLOPT_URL, $this->base_url . '/user/login?login=' . urlencode($login) . '&password=' . urlencode($password));
        curl_setopt($this->http, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->http, CURLOPT_HEADER, TRUE);
        curl_setopt($this->http, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
        curl_setopt($this->http, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->http, CURLOPT_VERBOSE, $this->debug_verbose);
        curl_setopt($this->http, CURLOPT_POSTFIELDS, "a");
        $content = curl_exec($this->http);
        $response = curl_getinfo($this->http);
        if ((int)$response['http_code'] != 200) {
            throw new item\YouTrackException('/user/login', $response, $content);
        }
        $cookies = array();
        preg_match_all('/^Set-Cookie: (.*?)=(.*?)$/sm', $content, $cookies, PREG_SET_ORDER);
        foreach ($cookies as $cookie) {
            $parts = parse_url($cookie[0]);
            $this->cookies[] = $parts['path'];
        }
        $this->headers[CURLOPT_HTTPHEADER] = array('Cache-Control: no-cache');
        curl_close($this->http);
    }

    /**
     * @param $id
     * @return Issue
     */
    public function getIssue($id)
    {
        $issue = $this->_get('/issue/' . $id);
        return new  item\Issue($issue);
    }

    /**
     * @param $url
     * @return \SimpleXMLElement
     */
    protected function _get($url)
    {
        return $this->_requestXml('GET', $url);
    }

    /**
     * @param $method
     * @param $url
     * @param null $body
     * @param int $ignore_status
     * @return \SimpleXMLElement
     * @throws YouTrackException
     * @throws \Exception
     */
    protected function _requestXml($method, $url, $body = NULL, $ignore_status = 0)
    {
        $r = $this->_request($method, $url, $body, $ignore_status);
        $response = $r['response'];
        $content = $r['content'];
        if (!empty($response['content_type'])) {
            if (preg_match('/application\/xml/', $response['content_type']) || preg_match('/text\/xml/', $response['content_type'])) {
                return simplexml_load_string($content);
            }
        }
        return $content;
    }

    /**
     * Execute a request with the given parameters and return the response.
     *
     *
     * @param string $method The http method (GET, PUT, POST).
     * @param string $url The request url.
     * @param string $body Data that should be send or the filename of the file if PUT is used.
     * @param int $ignore_status Ignore the given http status code.
     * @return array An exception is thrown if an error occurs.
     * @throws \Exception An exception is thrown if an error occurs.
     * @throws item\YouTrackException
     */
    protected function _request($method, $url, $body = NULL, $ignore_status = 0)
    {
        $this->http = curl_init($this->base_url . $url);
        $headers = $this->headers;
        if ($method == 'PUT' || $method == 'POST') {
            $headers[CURLOPT_HTTPHEADER][] = 'Content-Type: application/xml; charset=UTF-8';
            $headers[CURLOPT_HTTPHEADER][] = 'Content-Length: ' . mb_strlen($body);
        }
        switch ($method) {
            case 'GET':
                curl_setopt($this->http, CURLOPT_HTTPGET, TRUE);
                break;
            case 'PUT':
                $handle = NULL;
                // Check if we got a file or just a string of data.
                if (file_exists($body)) {
                    $size = filesize($body);
                    if (!$size) {
                        throw new \Exception("Can't open file $body!");
                    }
                    $handle = fopen($body, 'r');
                } else {
                    $size = mb_strlen($body);
                    $handle = fopen('data://text/plain,' . $body, 'r');
                }
                curl_setopt($this->http, CURLOPT_PUT, TRUE);
                curl_setopt($this->http, CURLOPT_INFILE, $handle);
                curl_setopt($this->http, CURLOPT_INFILESIZE, $size);
                break;
            case 'POST':
                curl_setopt($this->http, CURLOPT_POST, TRUE);
                if (!empty($body)) {
                    curl_setopt($this->http, CURLOPT_POSTFIELDS, $body);
                }
                break;
            default:
                throw new \Exception("Unknown method $method!");
        }
        curl_setopt($this->http, CURLOPT_HTTPHEADER, $headers[CURLOPT_HTTPHEADER]);
        curl_setopt($this->http, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($this->http, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->http, CURLOPT_SSL_VERIFYPEER, $this->verify_ssl);
        curl_setopt($this->http, CURLOPT_VERBOSE, $this->debug_verbose);
        curl_setopt($this->http, CURLOPT_COOKIE, implode(';', $this->cookies));
        $content = curl_exec($this->http);
        $response = curl_getinfo($this->http);
        curl_close($this->http);
        if ((int)$response['http_code'] != 200 && (int)$response['http_code'] != 201 && (int)$response['http_code'] != $ignore_status) {
            throw new item\YouTrackException($url, $response, $content);
        }

        return array(
            'content' => $content,
            'response' => $response,
        );
    }

    /**
     * @return array
     */
    public function getAccessibleProjects()
    {
        $xml = $this->_get('/project/all');
        $projects = [];

        foreach ($xml->children() as $node) {
            $node = new item\Project(new \SimpleXMLElement($node->asXML()));
            $projects[] = $node;
        }
        return $projects;
    }

    /**
     * @param $id
     * @return array
     * @throws YouTrackException
     * @throws \Exception
     */
    public function getComments($id)
    {
        $comments = [];
        $req = $this->_request('GET', '/issue/' . urlencode($id) . '/comment');
        $xml = simplexml_load_string($req['content']);
        foreach ($xml->children() as $node) {
            $comments[] = new item\Comment($node);
        }
        return $comments;
    }

    /**
     * @param $id
     * @return array
     * @throws YouTrackException
     * @throws \Exception
     */
    public function getAttachments($id)
    {
        $attachments = [];
        $req = $this->_request('GET', '/issue/' . urlencode($id) . '/attachment');
        $xml = simplexml_load_string($req['content']);
        foreach ($xml->children() as $node) {
            $attachments[] = new item\Comment($node);
        }
        return $attachments;
    }

    /**
     * @param $url
     * @return string
     * @throws \Exception
     */
    public function getAttachmentContent($url)
    {
        //TODO Switch to curl for better error handling.
        $file = file_get_contents($url);
        if ($file === FALSE) {
            throw new \Exception("An error occured while trying to retrieve the following file: $url");
        }
        return $file;
    }

    /**
     * @param $issue_id
     * @param Attachment $attachment
     * @throws NotImplementedException
     */
    public function createAttachmentFromAttachment($issue_id, Attachment $attachment)
    {
        throw new NotImplementedException("create_attachment_from_attachment(issue_id, attachment)");
    }

    /**
     * @param $issue_id
     * @param $name
     * @param $content
     * @param string $author_login
     * @param null $content_type
     * @param null $content_length
     * @param null $created
     * @param string $group
     * @throws NotImplementedException
     */
    public function createAttachment($issue_id, $name, $content, $author_login = '', $content_type = NULL, $content_length = NULL, $created = NULL, $group = '')
    {
        throw new NotImplementedException("create_attachment(issue_id, name, content, ...)");
    }

    /**
     * @param $id
     * @param bool $outward_only
     * @return array
     * @throws YouTrackException
     * @throws \Exception
     */
    public function getLinks($id, $outward_only = FALSE)
    {
        $links = [];
        $req = $this->_request('GET', '/issue/' . urlencode($id) . '/link');
        $xml = simplexml_load_string($req['content']);
        foreach ($xml->children() as $node) {
            if (($node->attributes()->source != $id) || !$outward_only) {
                $links[] = new item\Link($node);
            }
        }
        return $links;
    }

    /**
     * @param $login
     * @return  item\User
     */
    public function getUser($login)
    {
        return new item\User($this->_get('/admin/user/' . urlencode($login)));
    }

    /**
     * @return array
     */
    public function getUsersAll()
    {
        $xml = $this->_get('/admin/user/');
        $users = [];
        foreach ($xml->children() as $user) {
            $users[] = new item\User(new \SimpleXMLElement($user->asXML()));
        }
        return $users;
    }

    /**
     * @param $user
     * @return \SimpleXMLElement|void
     */
    public function createUser($user)
    {
        return $this->importUsers([$user]);
    }

    /**
     * @param $users
     * @return \SimpleXMLElement|void
     */
    public function importUsers($users)
    {
        if (count($users) <= 0) {
            return;
        }
        $xml = "<list>\n";
        foreach ($users as $user) {
            $xml .= "  <user";
            foreach ($user as $key => $value) {
                $xml .= " $key=" . urlencode($value);
            }
            $xml .= " />\n";
        }
        $xml .= "</list>";
        return $this->_requestXml('PUT', '/import/users', $xml, 400);
    }

    /**
     * @param $login
     * @param $full_name
     * @param $email
     * @param $jabber
     */
    public function createUserDetailed($login, $full_name, $email, $jabber)
    {
        $this->importUsers([['login' => $login, 'fullName' => $full_name, 'email' => $email, 'jabber' => $jabber]]);
    }

    /**
     * @param $project_id
     * @param $assignee_group
     * @param $xml
     */
    public function importIssuesXml($project_id, $assignee_group, $xml)
    {
        $issues = $xml;
        $issue_count = 0;
        $issues_array = [];
        $issue_array = [];

        foreach ($issues as $i => $issue) {
            foreach ($issue as $i_ => $issue_) {
                $issue_array[(string)$issue_->attributes()->name] = $issue_->value;
            }
            $issue_array[$issue_count] = $issue_array;
            $summary = $issues_array[$issue_count]['summary'];
            $this->createIssue($project_id, $summary, [
                'assignee' => $issues_array[$issue_count]['assignee'],
                'summary' => $issues_array[$issue_count]['summary'],
                'description' => $issues_array[$issue_count]['description'],
                'priority' => $issues_array[$issue_count]['Priority'],
                'type' => $issues_array[$issue_count]['Type'],
                'subsystem' => $issues_array[$issue_count]['Subsystem'],
                'state' => $issues_array[$issue_count]['State'],
                'affectsVersion' => $issues_array[$issue_count]['Affects Version'],
                'fixedVersion' => $issues_array[$issue_count]['Fixed Version'],
                'fixedInBuild' => $issues_array[$issue_count]['Fixed In Build'],
            ]);
            $issue_count++;
        }
        /**
         * This method has been structured to easily support the debugging of
         * your xml files. To do so simply output the $issues_array with your own
         * methods.
         */
    }

    /**
     * creates an issue with properties from $params
     *
     * may be this is an general $params value:
     * <code>
     *  $params = array(
     * 'project' => (string)$project,
     * 'assignee' => (string)$assignee,
     * 'summary' => (string)$summary,
     * 'description' => (string)$description,
     * 'priority' => (string)$priority,
     * 'type' => (string)$type,
     * 'subsystem' => (string)$subsystem,
     * 'state' => (string)$state,
     * 'affectsVersion' => (string)$affectsVersion,
     * 'fixedVersion' => (string)$fixedVersion,
     * 'fixedInBuild' => (string)$fixedInBuild,
     * );
     * </code>
     *
     * @param string $project the obligatory project name
     * @param string $summary the obligatory issue summary
     * @param array $params optional additional parameters for the new issue (look into your personal youtrack instance!)
     * @return item\Issue
     */
    public function createIssue($project, $summary, $params = [])
    {

        $params['project'] = (string)$project;
        $params['summary'] = (string)$summary;
        array_walk($params, function (&$value) {
            // php manual: If funcname needs to be working with the actual values of the array,
            //  specify the first parameter of funcname as a reference. Then, any changes made to
            //  those elements will be made in the original array itself.
            $value = (string)$value;
        });
        $issue = $this->_requestXml('POST', '/issue?' . http_build_query($params, '', '&'));
        return new item\Issue($issue);
    }

    /**
     * @param $links
     * @throws NotImplementedException
     */
    public function importLinks($links)
    {
        throw new NotImplementedException("import_links({$links})");
    }

    /**
     * @param $project_id
     * @param $assignee_group
     * @param $issues
     * @throws NotImplementedException
     */
    public function importIssues($project_id, $assignee_group, $issues)
    {
        throw new NotImplementedException("import_issues({$project_id}, {$assignee_group}, {$issues})");
    }

    /**
     * @param $project_id
     * @return Project
     */
    public function getProject($project_id)
    {
        return new item\Project($this->_get('/admin/project/' . urlencode($project_id)));
    }

    /**
     * @return array
     */
    public function getProjects()
    {
        $projects = [];
        $xml = $this->_get('/admin/project/');
        foreach ($xml->children() as $project) {
            $projects[] = new item\Project(new \SimpleXMLElement($project->asXML()));

        }
        return $projects;
    }

    /**
     * @param $project_id
     * @return array
     */
    public function getProjectAssigneeGroups($project_id)
    {
        $xml = $this->_get('/admin/project/' . urlencode($project_id) . '/assignee/group');
        $groups = array();
        foreach ($xml->children() as $group) {
            $groups[] = new item\Group(new \SimpleXMLElement($group->asXML()));
        }
        return $groups;
    }

    /**
     * @param $name
     * @return Group
     */
    public function getGroup($name)
    {
        return new item\Group($this->_get('/admin/group/' . urlencode($name)));
    }

    /**
     * @param $login
     * @return array
     */
    public function getUserGroups($login)
    {
        $xml = $this->_get('/admin/user/' . urlencode($login) . '/group');
        $groups = array();
        foreach ($xml->children() as $group) {
            $groups[] = new item\Group(new \SimpleXMLElement($group->asXML()));
        }
        return $groups;
    }

    /**
     * @param $login
     * @param $group_name
     * @return mixed
     * @throws \Exception
     * @throws item\YouTrackException
     */
    public function setUserGroup($login, $group_name)
    {
        $r = $this->_request('POST', '/admin/user/' . urlencode($login) . '/group/' . urlencode($group_name));
        return $r['response'];
    }

    /**
     * @param Group $group
     * @return mixed
     */
    public function createGroup(Group $group)
    {
        $r = $this->_put('/admin/group/' . urlencode($group->name) . '?description=noDescription&autoJoin=false');
        return $r['response'];
    }

    /**
     * @param $url
     * @return \SimpleXMLElement
     */
    protected function _put($url)
    {
        return $this->_requestXml('PUT', $url, '<empty/>\n\n');
    }

    /**
     * @param $name
     * @return Role
     */
    public function getRole($name)
    {
        return new item\Role($this->_get('/admin/role/' . urlencode($name)));
    }

    /**
     * @param $project_id
     * @param $name
     * @return Subsystem
     */
    public function getSubsystem($project_id, $name)
    {
        return new item\Subsystem($this->_get('/admin/project/' . urlencode($project_id) . '/subsystem/' . urlencode($name)));
    }

    /**
     * @param $project_id
     * @return array
     */
    public function getSubsystems($project_id)
    {
        $xml = $this->_get('/admin/project/' . urlencode($project_id) . '/subsystem');
        $subsystems = array();
        foreach ($xml->children() as $subsystem) {
            $subsystems[] = new item\Subsystem(new \SimpleXMLElement($subsystem->asXML()));
        }
        return $subsystems;
    }

    /**
     * @param $project_id
     * @return array
     */
    public function getVersions($project_id)
    {
        $xml = $this->_get('/admin/project/' . urlencode($project_id) . '/version?showReleased=true');
        $versions = [];
        foreach ($xml->children() as $version) {
            $versions[] = new item\Version(new \SimpleXMLElement($version->asXML()));
        }
        return $versions;
    }

    /**
     * @param $project_id
     * @param $name
     * @return Version
     */
    public function getVersion($project_id, $name)
    {
        return new item\Version($this->_get('/admin/project/' . urlencode($project_id) . '/version/' . urlencode($name)));
    }

    public function getBuilds($project_id)
    {
        $xml = $this->_get('/admin/project/' . urlencode($project_id) . '/build');
        $builds = [];
        foreach ($xml->children() as $build) {
            $builds[] = new item\Build(new \SimpleXMLElement($build->asXML()));
        }
        return $builds;
    }

    public function getUsers($q = '')
    {
        $users = [];
        $q = trim((string)$q);
        $params = [
            'q' => $q,
        ];
        $this->_cleanUrlParameters($params);
        $xml = $this->_get('/admin/user/?' . http_build_query($params, '', '&'));
        if (!empty($xml) && is_object($xml)) {
            foreach ($xml->children() as $user) {
                $users[] = new item\User(new \SimpleXMLElement($user->asXML()));
            }
        }
        return $users;
    }

    /**
     * Loop through the given array and remove all entries
     * that have no value assigned.
     *
     * @param array &$params The array to inspect and clean up.
     */
    private function _cleanUrlParameters(&$params)
    {
        if (!empty($params) && is_array($params)) {
            foreach ($params as $key => $value) {
                if (empty($value)) {
                    unset($params["$key"]);
                }
            }
        }
    }

    /**
     * @throws NotImplementedException
     */
    public function createBuild()
    {
        throw new NotImplementedException("create_build()");
    }

    /**
     * @throws NotImplementedException
     */
    public function createBuilds()
    {
        throw new NotImplementedException("create_builds()");
    }

    /**
     * @param $project
     * @return \SimpleXMLElement
     */
    public function createProject($project)
    {
        return $this->createProjectDetailed($project->id, $project->name, $project->description, $project->leader);
    }

    /**
     * @param $project_id
     * @param $project_name
     * @param $project_description
     * @param $project_lead_login
     * @param int $starting_number
     * @return \SimpleXMLElement
     */
    public function createProjectDetailed($project_id, $project_name, $project_description, $project_lead_login, $starting_number = 1)
    {
        $params = [
            'projectName' => (string)$project_name,
            'description' => (string)$project_description,
            'projectLeadLogin' => (string)$project_lead_login,
            'lead' => (string)$project_lead_login,
            'startingNumber' => (string)$starting_number,
        ];
        return $this->_put('/admin/project/' . urlencode($project_id) . '?' . http_build_query($params, '', '&'));
    }

    /**
     * @param $project_id
     * @param $subsystems
     */
    public function createSubsystems($project_id, $subsystems)
    {
        foreach ($subsystems as $subsystem) {
            $this->create_subsystem($project_id, $subsystem);
        }
    }

    /**
     * @param $project_id
     * @param $subsystem
     * @return mixed
     */
    public function createSubsystem($project_id, $subsystem)
    {
        return $this->create_subsystem_detailed($project_id, $subsystem->name, $subsystem->isDefault, $subsystem->defaultAssignee);
    }

    /**
     * @param $project_id
     * @param $name
     * @param $is_default
     * @param $default_assignee_login
     * @return string
     */
    public function createSubsystemDetailed($project_id, $name, $is_default, $default_assignee_login)
    {
        $params = [
            'isDefault' => (string)$is_default,
            'defaultAssignee' => (string)$default_assignee_login,
        ];
        $this->_put('/admin/project/' . urlencode($project_id) . '/subsystem/' . urlencode($name) . '?' . http_build_query($params, '', '&'));
        return 'Created';
    }

    /**
     * @param $project_id
     * @param $name
     * @return mixed
     */
    public function deleteSubsystem($project_id, $name)
    {
        return $this->_request_xml('DELETE', '/admin/project/' . urlencode($project_id) . '/subsystem/' . urlencode($name));
    }

    /**
     * @param $project_id
     * @param $versions
     */
    public function createVersions($project_id, $versions)
    {
        foreach ($versions as $version) {
            $this->create_version($project_id, $version);
        }
    }

    /**
     * @param $project_id
     * @param $version
     * @return \SimpleXMLElement
     */
    public function createVersion($project_id, $version)
    {
        return $this->createVersionDetailed($project_id, $version->name, $version->isReleased, $version->isArchived, $version->releaseDate, $version->description);
    }

    /**
     * @param $project_id
     * @param $name
     * @param $is_released
     * @param $is_archived
     * @param null $release_date
     * @param string $description
     * @return \SimpleXMLElement
     */
    public function createVersionDetailed($project_id, $name, $is_released, $is_archived, $release_date = NULL, $description = '')
    {
        $params = array(
            'description' => (string)$description,
            'isReleased' => (string)$is_released,
            'isArchived' => (string)$is_archived,
        );
        if (!empty($release_date)) {
            $params['releaseDate'] = $release_date;
        }
        return $this->_put('/admin/project/' . urldecode($project_id) . '/version/' . urlencode($name) . '?' . http_build_query($params, '', '&'));
    }

    /**
     * @param $project_id
     * @param string $filter
     * @param string $after
     * @param string $max
     * @return array
     */
    public function getIssues($project_id, $filter = "", $after = "", $max = "")
    {
        $params = array(
            'after' => (string)$after,
            'max' => (string)$max,
            'filter' => (string)$filter,
        );
        $this->_cleanUrlParameters($params);
        $xml = $this->_get('/project/issues/' . urldecode($project_id) . '?' . http_build_query($params, '', '&'));
        $issues = array();
        foreach ($xml->children() as $issue) {
            $issues[] = new Issue(new \SimpleXMLElement($issue->asXML()));
        }
        return $issues;
    }

    /**
     * @param $project_id
     * @return array
     */
    public function getIssuesAll($project_id)
    {
        $xml = $this->_get('/project/issues/' . urldecode($project_id));
        $issues = array();
        foreach ($xml->children() as $issue) {
            $issues[] = new Issue(new \SimpleXMLElement($issue->asXML()));
        }
        return $issues;
    }

    /**
     * @param $issue_id
     * @param $command
     * @param null $comment
     * @param null $group
     * @return string
     * @throws \Exception
     * @throws item\YouTrackException
     */
    public function executeCommand($issue_id, $command, $comment = NULL, $group = NULL)
    {
        $params = [
            'command' => (string)$command,
        ];
        if (!empty($comment)) {
            $params['comment'] = (string)$comment;
        }
        if (!empty($group)) {
            $params['group'] = (string)$group;
        }
        $r = $this->_request('POST', '/issue/' . urlencode($issue_id) . '/execute?' . http_build_query($params, '', '&'));
        return 'Command executed';
    }

    /**
     * @param $name
     * @return CustomField
     */
    public function getCustomField($name)
    {
        return new item\CustomField($this->_get('/admin/customfield/field/' . urlencode($name)));
    }

    public function getCustomFields()
    {
        $xml = $this->_get('/admin/customfield/field');
        $fields = [];
        foreach ($xml->children() as $field) {
            $fields[] = new item\CustomField(new \SimpleXMLElement($field->asXML()));
        }
        return $fields;
    }

    /**
     * @param $fields
     */
    public function createCustomFields($fields)
    {
        foreach ($fields as $field) {
            $this->createCustomField($field);
        }
    }

    /**
     * @param $field
     * @return mixed
     */
    public function createCustomField($field)
    {
        return $this->create_custom_field_detailed($field->name, $field->type, $field->isPrivate, $field->visibleByDefault);
    }

    /**
     * @param $name
     * @param $type_name
     * @param $is_private
     * @param $default_visibility
     * @return string
     */
    public function createCustomFieldDetailed($name, $type_name, $is_private, $default_visibility)
    {
        $params = [
            'typeName' => (string)$type_name,
            'isPrivate' => (string)$is_private,
            'defaultVisibility' => (string)$default_visibility,
        ];
        $this->_put('/admin/customfield/field/' . urlencode($name) . '?' . http_build_query($params, '', '&'));
        return 'Created';
    }

    /**
     * @param $name
     * @return EnumBundle
     */
    public function getEnumBundle($name)
    {
        return new item\EnumBundle($this->_get('/admin/customfield/bundle/' . urlencode($name)));
    }

    /**
     * @param EnumBundle $bundle
     * @return \SimpleXMLElement
     */
    public function createEnumBundle(EnumBundle $bundle)
    {
        return $this->_requestXml('PUT', '/admin/customfield/bundle', $bundle->toXML(), 400);
    }

    /**
     * @param $name
     * @return mixed
     * @throws YouTrackException
     * @throws \Exception
     */
    public function deleteEnumBundle($name)
    {
        $r = $this->_request('DELETE', '/admin/customfield/bundle/' . urlencode($name), '');
        return $r['content'];
    }

    /**
     * @param $name
     * @param $values
     * @return string
     */
    public function addValuesToEnumBundle($name, $values)
    {
        foreach ($values as $value) {
            $this->addValueToEnumBundle($name, $value);
        }
        return implode(', ', $values);
    }

    /**
     * @param $name
     * @param $value
     * @return \SimpleXMLElement
     */
    public function addValueToEnumBundle($name, $value)
    {
        return $this->_put('/admin/customfield/bundle/' . urlencode($name) . '/' . urlencode($value));
    }

    /**
     * @param $project_id
     * @param $name
     * @return CustomField
     */
    public function getProjectCustomField($project_id, $name)
    {
        return new item\CustomField($this->_get('/admin/project/' . urlencode($project_id) . '/customfield/' . urlencode($name)));
    }

    /**
     * @param $project_id
     * @return array
     */
    public function getProjectCustomFields($project_id)
    {
        $xml = $this->_get('/admin/project/' . urlencode($project_id) . '/customfield');
        $fields = [];
        foreach ($xml->children() as $cfield) {
            $fields[] = new item\CustomField(new \SimpleXMLElement($cfield->asXML()));
        }
        return $fields;
    }

    /**
     * @param $project_id
     * @param CustomField $pcf
     * @return \SimpleXMLElement
     */
    public function createProjectCustomField($project_id, CustomField $pcf)
    {
        return $this->createProjectCustomFieldDetailed($project_id, $pcf->name, $pcf->emptyText, $pcf->params);
    }

    /**
     * @param $project_id
     * @param $name
     * @param $empty_field_text
     * @param array $params
     * @return \SimpleXMLElement
     */
    private function createProjectCustomFieldDetailed($project_id, $name, $empty_field_text, $params = array())
    {
        $_params = [
            'emptyFieldText' => (string)$empty_field_text,
        ];
        if (!empty($params)) {
            $_params = array_merge($_params, $params);
        }
        return $this->_put('/admin/project/' . urlencode($project_id) . '/customfield/' . urlencode($name) . '?' . http_build_query($_params, '', '&'));
    }

    /**
     * @return array
     */
    public function getIssueLinkTypes()
    {
        $xml = $this->_get('/admin/issueLinkType');
        $lts = [];
        foreach ($xml->children() as $node) {
            $lts[] = new item\IssueLinkType(new \SimpleXMLElement($node->asXML()));
        }
        return $lts;
    }

    /**
     * @param $lts
     */
    public function createIssueLinkTypes($lts)
    {
        foreach ($lts as $lt) {
            $this->createIssueLinkType($lt);
        }
    }

    /**
     * @param $ilt
     * @return \SimpleXMLElement
     */
    public function createIssueLinkType($ilt)
    {
        return $this->createIssueLinkTypeDetailed($ilt->name, $ilt->outwardName, $ilt->inwardName, $ilt->directed);
    }

    /**
     * @param $name
     * @param $outward_name
     * @param $inward_name
     * @param $directed
     * @return \SimpleXMLElement
     */
    public function createIssueLinkTypeDetailed($name, $outward_name, $inward_name, $directed)
    {
        $params = [
            'outwardName' => (string)$outward_name,
            'inwardName' => (string)$inward_name,
            'directed' => (string)$directed,
        ];
        return $this->_put('/admin/issueLinkType/' . urlencode($name) . '?' . http_build_query($params, '', '&'));
    }

    /**
     * @return bool
     */
    public function getVerifySsl()
    {
        return $this->verify_ssl;
    }

    /**
     * Use this method to enable or disable the ssl_verifypeer option of curl.
     * This is usefull if you use self-signed ssl certificates.
     *
     * @param bool $verify_ssl
     * @return void
     */
    public function setVerifySsl($verify_ssl)
    {
        $this->verify_ssl = $verify_ssl;
    }

}
