<?php
define('__ACCESS_PLUGIN_ROOT__', __DIR__);

foreach (glob(__ACCESS_PLUGIN_ROOT__ . '/Access_*.php') as $filePath) {
    require_once $filePath;
}
