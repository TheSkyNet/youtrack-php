# YouTrack Client PHP Library


Basically this is a port of the fork of `jan0sch/YouTrack-Client-PHP-Library` php api from Jetbrains.

The source of this library is released under the BSD license (see LICENSE for details).


## Requirements

* PHP 5.3.x (Any version above 5 might work but I can't guarantee that.)
* curl
* simplexml
* YouTrack 3.0 with REST-API enabled

## Usage

    {
        "require": {
           "TheSkyNet/youtrack-php": "1.0.*"
        }
    }

    <?php
    $youtrack = new \youTrack\Connection("http://example.com", "login", "password");
    $issue = $youtrack->get_issue("TEST-1");

## Tests

The unit tests are incomplete but you can run them using `phpunit` like this:

    % phpunit test

## Contributors
* [@TheSkyNet](https://github.com/TheSkyNet)
* [@jan0sch](https://github.com/jan0sch)
* [@jkswoods](https://github.com/jkswoods)
* [@Shyru](https://github.com/Shyru)
* [@nepda](https://github.com/nepda)
* [@richardhinkamp](https://github.com/richardhinkamp)

