<?php
define('__ACCESS_PLUGIN_ROOT__', __DIR__);

if (function_exists('spl_autoload_register')) {
    spl_autoload_register(function ($className) {
        $filePath = __ACCESS_PLUGIN_ROOT__ . '/' . $className . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
        } else {
            throw new Typecho_Plugin_Exception($filePath . ' is not existed');
        }
    });
} else {
    foreach (glob(__ACCESS_PLUGIN_ROOT__ . '/Access_*.php') as $filePath) {
        require_once $filePath;
    }
}
