<?php

// Set appropriate error reporting
error_reporting(E_ALL | E_STRICT);

// Find the root
define('TESTS_VO_ROOT', realpath(__DIR__));

// Override our include path
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            TESTS_VO_ROOT,
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'library',
            get_include_path()
        )
    )
);

spl_autoload_register(function($class) {
    if (class_exists($class, false) || interface_exists($class, false)) {
        return;
    }

    $className = ltrim($class, '\\');
    $file = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $file      = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $file .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    include $file;
});
