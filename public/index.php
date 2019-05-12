<?php

    if(php_sapi_name() != 'cli-server')
        chdir('../');

    require_once('./controller.php');