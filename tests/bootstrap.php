<?php

error_reporting(E_ALL | E_STRICT);

if (is_readable(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} else {
    set_include_path(
        dirname(__DIR__) . '/library' . PATH_SEPARATOR . get_include_path()
    );
}
