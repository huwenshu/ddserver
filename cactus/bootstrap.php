<?php

define('__PROD__', false); // production env.
define('__ROOT__', __DIR__);
define('__LOGP__', '/alidata/log/dudu'); // log path.
define('__CLI__', PHP_SAPI === 'cli');

if (!__PROD__) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', TRUE);
}

spl_autoload_register(function($name) {
    $folders = ['libraries'];
    $name = strtr($name, ['\\' => '/']);
    foreach ($folders as $folder) {
        if (file_exists(__ROOT__ . "/{$folder}/{$name}.php")) {
            require __ROOT__ . "/{$folder}/{$name}.php";
            break;
        }
    }
});

Foundation\Query::autoload();
Foundation\Filter::autoload();
Utility\Logger::autoload();

function config($name) {
    return new Foundation\Config($name);
}

