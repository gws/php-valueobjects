<?php

error_reporting(E_ALL | E_STRICT);

define('VO_ROOT', realpath(__DIR__));

// Override our include path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            VO_ROOT,
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library',
            get_include_path()
        )
    )
);

// Use small PSR-0 compatible autoloader
spl_autoload_register(function($className) {
    $className = ltrim($className, '\\');
    $fileName  = '';
    $namespace = '';

    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    @include_once $fileName;
});
