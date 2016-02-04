<?php
/**
 * Created by PhpStorm.
 * User: Kevin
 * Date: 04/02/2016
 * Time: 20:41
 * A simple exception that should be raised if a function is not yet implemented.
 */
namespace app\services\youTrack\item;

class NotImplementedException extends \Exception
{
    /**
     * Constructor
     *
     * @param string $function_name The name of the function.
     */
    public function __construct($function_name)
    {
        $code = 0;
        $previous = NULL;
        $message = 'This function is not yet implemented: "' . $function_name . '"!';
        parent::__construct($message, $code, $previous);
    }
}
