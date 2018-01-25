<?php
require_once __DIR__ . '/Access_Bootstrap.php';
/**
 * 获取访客信息，生成统计图表，由<a href="https://zhaiyiming.com/">@一名宅</a> 部分优化重构。
 *
 * @package Access
 * @author Kokororin
 * @version 2.0.2
 * @link https://kotori.love
 */
class Access_Plugin implements Typecho_Plugin_Interface
{
    public static $panel = 'Access/page/console.php';

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $msg = Access_Plugin::install();
        Helper::addPanel(1, self::$panel, _t('Access控制台'), _t('Access插件控制台'), 'subscriber');
        Helper::addRoute("access_track_gif", "/access/log/track.gif", "Access_Action", 'writeLogs');
        Helper::addRoute("access_ip", "/access/ip.json", "Access_Action", 'ip');
        Helper::addRoute("access_delete_logs", "/access/log/delete.json", "Access_Action", 'deleteLogs');
        Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('Access_Plugin', 'backend');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('Access_Plugin', 'frontend');
        Typecho_Plugin::factory('admin/footer.php')->end = array('Access_Plugin', 'adminFooter');
        return _t($msg);
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        if ($config->isDrop == 0) {
            $db = Typecho_Db::get();
            $db->query("DROP TABLE `{$db->getPrefix()}access_log`", Typecho_Db::WRITE);
        }
        Helper::removePanel(1, self::$panel);
        Helper::removeRoute("access_track_gif");
        Helper::removeRoute("access_ip");
        Helper::removeRoute("access_delete_logs");
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
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
            ), '0', '日志写入类型:', '请选择日志写入类型，如果写入速度较慢可选择前端写入日志。<br/>如果您使用了pjax，请在pjax相关事件中调用 window.Access.track() 方法。');
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

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    /**
     * 初始化以及升级插件数据库，如初始化失败,直接抛出异常
     *
     * @access public
     * @return string
     * @throws Typecho_Plugin_Exception
     */
    public static function install()
    {
        if (substr(trim(dirname(__FILE__), '/'), -6) != 'Access') {
            throw new Typecho_Plugin_Exception(_t('插件目录名必须为Access'));
        }
        $db = Typecho_Db::get();
        $adapterName = $db->getAdapterName();
        
        if (strpos($adapterName, 'Mysql') !== false) {
            $prefix  = $db->getPrefix();
            $scripts = file_get_contents('usr/plugins/Access/sql/Mysql.sql');
            $scripts = str_replace('typecho_', $prefix, $scripts);
            $scripts = str_replace('%charset%', 'utf8', $scripts);
            $scripts = explode(';', $scripts);
            try {
                $configLink = '<a href="' . Helper::options()->adminUrl . 'options-plugin.php?config=Access">' . _t('前往设置') . '</a>';
                # 初始化数据库如果不存在
                if (!$db->fetchRow($db->query("SHOW TABLES LIKE '{$prefix}access_log';", Typecho_Db::READ))) {
                    foreach ($scripts as $script) {
                        $script = trim($script);
                        if ($script) {
                            $db->query($script, Typecho_Db::WRITE);
                        }
                    }
                    $msg = _t('成功创建数据表，插件启用成功，') . $configLink;
                }
                # 处理旧版本数据
                if ($db->fetchRow($db->query("SHOW TABLES LIKE '{$prefix}access';", Typecho_Db::READ))) {
                    $rows = $db->fetchAll($db->select()->from('table.access'));
                    foreach ($rows as $row) {
                        $ua = new Access_UA($row['ua']);
                        $time = Helper::options()->gmtTime + (Helper::options()->timezone - Helper::options()->serverTimezone);
                        $row['browser_id'       ] = $ua->getBrowserID();
                        $row['browser_version'  ] = $ua->getBrowserVersion();
                        $row['os_id'            ] = $ua->getOSID();
                        $row['os_version'       ] = $ua->getOSVersion();
                        $row['path'             ] = parse_url($row['url'], PHP_URL_PATH);
                        $row['query_string'     ] = parse_url($row['url'], PHP_URL_QUERY);
                        $row['ip'               ] = bindec(decbin(ip2long($row['ip'])));
                        $row['entrypoint'       ] = $row['referer'];
                        $row['entrypoint_domain'] = $row['referer_domain'];
                        $row['time'             ] = $row['date'];
                        $row['robot'            ] = $ua->isRobot() ? 1 : 0;
                        $row['robot_id'         ] = $ua->getRobotID();
                        $row['robot_version'    ] = $ua->getRobotVersion();
                        unset($row['date']);
                        try {
                            $db->query($db->insert('table.access_log')->rows($row));
                        } catch (Typecho_Db_Exception $e) {
                            if ($e->getCode() != 23000)
                                throw new Typecho_Plugin_Exception(_t('导入旧版数据失败，插件启用失败，错误信息：%s。', $e->getMessage()));
                        }
                    }
                    $db->query("DROP TABLE `{$prefix}access`;", Typecho_Db::WRITE);
                    $msg = _t('成功创建数据表并更新数据，插件启用成功，') . $configLink;
                }
                return $msg;
            } catch (Typecho_Db_Exception $e) {
                throw new Typecho_Plugin_Exception(_t('数据表建立失败，插件启用失败，错误信息：%s。', $e->getMessage()));
            } catch (Exception $e) {
                throw new Typecho_Plugin_Exception($e->getMessage());
            }
        } else if (strpos($adapterName, 'SQLite') !== false) {
            $prefix  = $db->getPrefix();
            $scripts = file_get_contents('usr/plugins/Access/sql/SQLite.sql');
            $scripts = str_replace('typecho_', $prefix, $scripts);
            $scripts = explode(';', $scripts);
            try {
                $configLink = '<a href="' . Helper::options()->adminUrl . 'options-plugin.php?config=Access">' . _t('前往设置') . '</a>';
                # 初始化数据库如果不存在
                if (!$db->fetchRow($db->query("SELECT name FROM sqlite_master WHERE TYPE='table' AND name='{$prefix}access_log';", Typecho_Db::READ))) {
                    foreach ($scripts as $script) {
                        $script = trim($script);
                        if ($script) {
                            $db->query($script, Typecho_Db::WRITE);
                        }
                    }
                    $msg = _t('成功创建数据表，插件启用成功，') . $configLink;
                } else {
                    $msg = _t('数据表已经存在，插件启用成功，') . $configLink;
                }
                return $msg;
            } catch (Typecho_Db_Exception $e) {
                throw new Typecho_Plugin_Exception(_t('数据表建立失败，插件启用失败，错误信息：%s。', $e->getMessage()));
            } catch (Exception $e) {
                throw new Typecho_Plugin_Exception($e->getMessage());
            }
        } else {
            throw new Typecho_Plugin_Exception(_t('你的适配器为%s，目前只支持Mysql和SQLite', $adapterName));
        }
    }

    /**
     * 获取后端统计，该统计方法可以统计到一切访问
     *
     * @access public
     * @return void
     */
    public static function backend($archive)
    {
        $access = new Access_Core();
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');

        if ($config->writeType == 0) {
            $access->writeLogs($archive);
        }
    }

    /**
     * 获取前端统计，该方法要求客户端必须渲染网页，所以不能统计RSS等直接抓取PHP页面的方式
     *
     * @access public
     * @return void
     */
    public static function frontend($archive)
    {
        $config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        if ($config->writeType == 1) {
            $index = rtrim(Helper::options()->index, '/');
            $access = new Access_Core();
            $parsedArchive = $access->parseArchive($archive);
            echo "<script type=\"text/javascript\">(function(w){var t=function(){var i=new Image();i.src='{$index}/access/log/track.gif?u='+location.pathname+location.search+location.hash+'&cid={$parsedArchive['content_id']}&mid={$parsedArchive['meta_id']}&rand='+new Date().getTime()};t();var a={};a.track=t;w.Access=a})(this);</script>";
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
            Helper::options()->adminUrl('extending.php?panel=' . self::$panel);
            echo '\">' . _t('Access控制台') . '</a></li>");
});
</script>';
        }
    }
}
