<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 03/02/2016
 * Time: 15:48
 * A class extending the standard php exception.
 */
namespace app\services\youTrack\exception;

class YouTrackException extends \Exception
{
    /**
     * Constructor
     *
     * @param string $url The url that triggered the error.
     * @param array $response The output of <code>curl_getinfo($resource)</code>.
     * @param array $content The content returned from the url.
     */
    public function __construct($url, $response, $content)
    {
        $code = (int)$response['http_code'];
        $previous = NULL;
        $message = "Error for '" . $url . "': " . $response['http_code'];
        if (!empty($response['content_type']) && !preg_match('/text\/html/', $response['content_type'])) {
            $xml = simplexml_load_string($content);
            $error = new YouTrackError($xml);
            $message .= ": " . $error->__get("error");
        }
        parent::__construct($message, $code, $previous);
    }
}

