<?php
/**
 * Typecho Access Plugin
 *
 * @package Access
 * @author Kokororin
 * @version 1.0
 * @link https://kotori.love
 */
class Access_Plugin implements Typecho_Plugin_Interface
{
    public static $panel = 'Access/page/console.php';
    public static function activate()
    {
        $msg = Access_Plugin::install();
        Helper::addPanel(1, self::$panel, 'Access控制台', 'Access插件控制台', 'subscriber');
        Typecho_Plugin::factory('Widget_Archive')->header = array('Access_Plugin', 'start');
        return _t($msg);
    }

    public static function deactivate()
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        $isDrop = $config->isDrop;
        if ($isDrop == 0) {
            $db     = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $db->query("DROP TABLE `" . $prefix . "access`", Typecho_Db::WRITE);
        }
        Helper::removePanel(1, self::$panel);
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $pageSize = new Typecho_Widget_Helper_Form_Element_Text(
            'pageSize', null, '',
            '分页数量', '每页显示的日志数量');
        $isDrop = new Typecho_Widget_Helper_Form_Element_Radio(
            'isDrop', array(
                '0' => '删除',
                '1' => '不删除',
            ), '', '删除数据表:', '请选择是否在禁用插件时，删除日志数据表');
        $form->addInput($pageSize);
        $form->addInput($isDrop);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    public static function install()
    {
        if (substr(trim(dirname(__FILE__), '/'), -6) != 'Access') {
            throw new Typecho_Plugin_Exception('插件目录名必须为Access');
        }
        $installDb = Typecho_Db::get();
        $type      = explode('_', $installDb->getAdapterName());
        $type      = array_pop($type);
        $prefix    = $installDb->getPrefix();
        $scripts   = "CREATE TABLE `typecho_access` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ua` varchar(255) default NULL,
  `url` varchar(64) default NULL,
  `ip` varchar(16) default NULL,
  `date` int(10) unsigned default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MYISAM  DEFAULT CHARSET=%charset%;";
        $scripts = str_replace('typecho_', $prefix, $scripts);
        $scripts = str_replace('%charset%', 'utf8', $scripts);
        $scripts = explode(';', $scripts);
        try {
            foreach ($scripts as $script) {
                $script = trim($script);
                if ($script) {
                    $installDb->query($script, Typecho_Db::WRITE);
                }
            }
            return '成功创建数据表，插件启用成功';
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if (('Mysql' == $type && $code == (1050 || '42S01'))) {
                $script = 'SELECT * from `' . $prefix . 'access`';
                $installDb->query($script, Typecho_Db::READ);
                return '数据表已存在，插件启用成功';
            } else {
                throw new Typecho_Plugin_Exception('数据表建立失败，插件启用失败。错误号：' . $code);
            }
        }
    }

    public static function start()
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');

        $request = new Typecho_Request;
        $ip      = $request->getIp();
        $url     = $_SERVER['REQUEST_URI'];
        if ($ip == null) {
            $ip = 'UnKnow';
        }
        $options   = Typecho_Widget::widget('Widget_Options');
        $timeStamp = $options->gmtTime;
        $offset    = $options->timezone - $options->serverTimezone;
        $gtime     = $timeStamp + $offset;
        $db        = Typecho_Db::get();
        $rows      = array(
            'ua'   => $_SERVER['HTTP_USER_AGENT'],
            'url'  => $url,
            'ip'   => $ip,
            'date' => $gtime,
        );
        $db->query($db->insert('table.access')->rows($rows));

    }
}
