<?php
/**
 * 获取访客信息
 *
 * @package Access
 * @author Kokororin
 * @version 1.6
 * @link https://kotori.love
 */
class Access_Plugin implements Typecho_Plugin_Interface
{
    public static $panel = 'Access/page/console.php';
    public static function activate()
    {
        $msg = Access_Plugin::install();
        Helper::addPanel(1, self::$panel, 'Access控制台', 'Access插件控制台', 'subscriber');
        Helper::addRoute("access_write_logs", "/access/log/write.json", "Access_Action", 'writeLogs');
        Helper::addRoute("access_ip", "/access/ip.json", "Access_Action", 'ip');
        Helper::addRoute("access_delete_logs", "/access/log/delete.json", "Access_Action", 'deleteLogs');
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('Access_Plugin', 'backend');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('Access_Plugin', 'frontend');
        Typecho_Plugin::factory('admin/footer.php')->end = array('Access_Plugin', 'adminFooter');
        return _t($msg);
    }

    public static function deactivate()
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        $isDrop = $config->isDrop;
        if ($isDrop == 0) {
            $db = Typecho_Db::get();
            $prefix = $db->getPrefix();
            $db->query("DROP TABLE `" . $prefix . "access`", Typecho_Db::WRITE);
        }
        Helper::removePanel(1, self::$panel);
        Helper::removeRoute("access_write_logs");
        Helper::removeRoute("access_ip");
        Helper::removeRoute("access_delete_logs");
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $pageSize = new Typecho_Widget_Helper_Form_Element_Text(
            'pageSize', null, '10',
            '分页数量', '每页显示的日志数量');
        $isDrop = new Typecho_Widget_Helper_Form_Element_Radio(
            'isDrop', array(
                '0' => '删除',
                '1' => '不删除',
            ), '1', '删除数据表:', '请选择是否在禁用插件时，删除日志数据表');
        $writeType = new Typecho_Widget_Helper_Form_Element_Radio(
            'writeType', array(
                '0' => '后端',
                '1' => '前端',
            ), '0', '日志写入类型:', '请选择日志写入类型，数据量大时（几万以上），后端写入可能会拖慢博客访问速度');
        $canAnalytize = new Typecho_Widget_Helper_Form_Element_Radio(
            'canAnalytize', array(
                '0' => '不允许',
                '1' => '允许',
            ), '1', '允许统计使用情况:', '请选择是否允许插件作者统计使用情况');
        $form->addInput($pageSize);
        $form->addInput($isDrop);
        $form->addInput($writeType);
        $form->addInput($canAnalytize);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    public static function install()
    {
        $configLink = '<a href="' . Helper::options()->adminUrl . 'options-plugin.php?config=Access' . '">请设置</a>';
        if (substr(trim(dirname(__FILE__), '/'), -6) != 'Access') {
            throw new Typecho_Plugin_Exception('插件目录名必须为Access');
        }
        $installDb = Typecho_Db::get();
        $type = explode('_', $installDb->getAdapterName());
        $type = array_pop($type);
        $prefix = $installDb->getPrefix();
        $scripts = "CREATE TABLE `typecho_access` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ua` varchar(255) default NULL,
  `url` varchar(64) default NULL,
  `ip` varchar(16) default NULL,
  `referer` varchar(255) default NULL,
  `referer_domain` varchar(100) default NULL,
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
            return '成功创建数据表，插件启用成功，' . $configLink;
        } catch (Typecho_Db_Exception $e) {
            $code = $e->getCode();
            if ($type != 'Mysql') {
                throw new Typecho_Plugin_Exception('你的适配器为' . $type . '，目前只支持Mysql');
            }
            if ($code == (1050 || '42S01')) {
                $script = 'SELECT * from `' . $prefix . 'access`';
                $installDb->query($script, Typecho_Db::READ);
                if (!array_key_exists('referer', $installDb->fetchRow($installDb->select()->from('table.access')))) {
                    $installDb->query('ALTER TABLE `' . $prefix . 'access` ADD `referer` varchar(255) NULL AFTER `ip`, ADD `referer_domain` varchar(100) NULL AFTER `referer`;');
                    return '数据表结构已更新，插件启用成功，' . $configLink;
                }
                return '数据表已存在，插件启用成功，' . $configLink;
            } else {
                throw new Typecho_Plugin_Exception('数据表建立失败，插件启用失败。错误号：' . $code);
            }
        } catch (Exception $e) {
            throw new Typecho_Plugin_Exception($e->getMessage());
        }
    }

    public static function backend($archive)
    {
        require_once __DIR__ . '/Access_Bootstrap.php';
        $access = new Access_Core();
        $access->getReferer();
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');

        if ($config->writeType == 0) {
            $access->writeLogs();
        }
    }

    public static function frontend()
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        if ($config->writeType == 1) {
            echo '<script type="text/javascript">(function(){var xhr=new XMLHttpRequest();xhr.open("GET","' . rtrim(Helper::options()->index, '/') . '/access/log/write.json?u="+location.pathname+location.search+location.hash' . ',true);xhr.send();})();</script>';
        }
    }

    public static function adminFooter()
    {
        $url = $_SERVER['PHP_SELF'];
        $filename = substr($url, strrpos($url, '/') + 1);
        if ($filename == 'index.php') {
            echo '<script>
$(document).ready(function() {
  $("#start-link").append("<li><a href=\"';
            Helper::options()->adminUrl('extending.php?panel=' . Access_Plugin::$panel);
            echo '\">Access控制台</a></li>");
});
</script>';
        }
    }
}
