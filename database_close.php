<?php

    if (!defined('ACCESS') || !defined('PHPMYADMIN') || !defined('REALPATH') || !defined('PATH_DATABASE') || !defined('LINK_IDENTIFIER'))
        die('Not access');

    if (is_resource(LINK_IDENTIFIER))
        @mysql_close(LINK_IDENTIFIER);

?>