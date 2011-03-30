<?php

error_reporting( E_ALL | E_STRICT );

$root = realpath(dirname(__DIR__));
$library = $root . DIRECTORY_SEPARATOR . '/library';
$tests = $root . DIRECTORY_SEPARATOR . '/tests';

$path = array(
    $library,
    $tests,
    get_include_path(),
);
set_include_path(implode(PATH_SEPARATOR, $path));

spl_autoload_register(function($class) {
    $file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $class) . '.php';

    if (false !== ($file = stream_resolve_include_path($file))) {
        return include_once($file);
    }

    return false;
});

unset($root, $library, $tests, $path);
