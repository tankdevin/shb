<?php

// +----------------------------------------------------------------------
// | ThinkAdmin

namespace think;

require __DIR__ . '/../thinkphp/base.php';

Container::get('app')->run()->send();


header("Access-Control-Allow-Origin: * ");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Connection, User-Agent, Cookie,token');


