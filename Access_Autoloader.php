<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Boostrap file not found');
}

class Access_Autoloader
{
    public static function autoloader($class)
    {
        if (strpos($class, 'Access') !== 0) {
            return;
        }

        $file = __ACCESS_PLUGIN_ROOT__ . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }

    public static function register()
    {
        if (function_exists('spl_autoload_register')) {
            spl_autoload_register(array('Access_Autoloader', 'autoloader'));
        } else {
            throw new Typecho_Plugin_Exception(_t('php版本过低，最低要求>=5.3.0'));
        }
    }
}
