<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Boostrap file not found');
}

class Access_Core
{
    protected $db;
    protected $request;
    protected $response;
    
    public $ua;
    public $config;
    public $action;
    public $title;
    public $logs = array();
    public $overview = array();
    public $referer = array();

    /**
     * 构造函数，根据不同类型的请求，计算不同的数据并渲染输出
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        # Load language pack
        if (Typecho_I18n::getLang() != 'zh_CN') {
            $file = __TYPECHO_ROOT_DIR__ . __TYPECHO_PLUGIN_DIR__ .
                    '/Access/lang/' . Typecho_I18n::getLang() . '.mo';
            file_exists($file) && Typecho_I18n::addLang($file);
        }
        # Init variables
        $this->db       = Typecho_Db::get();
        $this->config   = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        $this->request  = Typecho_Request::getInstance();
        $this->response = Typecho_Response::getInstance();
        if ($this->config->pageSize == null || $this->config->isDrop == null) {
            throw new Typecho_Plugin_Exception(_t('请先设置插件！'));
        }
        $this->ua = new Access_UA($this->request->getAgent());
        switch ($this->request->get('action')) {
            case 'overview':
                $this->action = 'overview';
                $this->title = _t('访问概览');
                $this->parseOverview();
                $this->parseReferer();
                break;
            case 'logs':
            default:
                $this->action = 'logs';
                $this->title = _t('访问日志');
                $this->parseLogs();
                break;
        }
    }

    /**
     * 生成详细访问日志数据，提供给页面渲染使用
     *
     * @access public
     * @return void
     */
    protected function parseLogs()
    {
        $type = $this->request->get('type', 1);
        $pagenum = $this->request->get('page', 1);
        $offset = (max(intval($pagenum), 1) - 1) * $this->config->pageSize;
        $query = $this->db->select()->from('table.access_log')
                    ->order('time', Typecho_Db::SORT_DESC)
                    ->offset($offset)->limit($this->config->pageSize);
        $qcount = $this->db->select('count(1) AS count')->from('table.access_log');
        switch ($type) {
            case 1:
                $query->where('robot = ?', 0);
                $qcount->where('robot = ?', 0);
                break;
            case 2:
                $query->where('robot = ?', 1);
                $qcount->where('robot = ?', 1);
                break;
            default:
                break;
        }
        $this->logs['list'] = $this->db->fetchAll($query);
        foreach ($this->logs['list'] as &$row) {
            $ua = new Access_UA($row['ua']);
            if ($ua->isRobot()) {
                $name = $ua->getRobotID();
                $version = $ua->getRobotVersion();
            } else {
                $name = $ua->getBrowserName();
                $version = $ua->getBrowserVersion();
            }
            if ($name == '') {
                $row['display_name'] = _t('未知');
            } elseif ($version == '') {
                $row['display_name'] = $name;
            } else {
                $row['display_name'] = $name . ' / ' . $version;
            }
        }

        $this->htmlEncode($this->logs['list']);

        $this->logs['rows'] = $this->db->fetchAll($qcount)[0]['count'];
        
        $page = new Access_Page($this->config->pageSize, $this->logs['rows'], $pagenum, 10, array(
            'panel' => Access_Plugin::$panel,
            'action' => 'logs',
            'type' => $type,
        ));
        $this->logs['page'] = $page->show();
    }

    /**
     * 生成来源统计数据，提供给页面渲染使用
     *
     * @access public
     * @return void
     */
    protected function parseReferer()
    {
        $this->referer['url'] = $this->db->fetchAll($this->db->select('DISTINCT entrypoint AS value, COUNT(1) as count')
            ->from('table.access_log')->where("entrypoint <> ''")->group('entrypoint')
            ->order('count', Typecho_Db::SORT_DESC)->limit($this->config->pageSize));
        $this->referer['domain'] = $this->db->fetchAll($this->db->select('DISTINCT entrypoint_domain AS value, COUNT(1) as count')
            ->from('table.access_log')->where("entrypoint_domain <> ''")->group('entrypoint_domain')
            ->order('count', Typecho_Db::SORT_DESC)->limit($this->config->pageSize));
        $this->htmlEncode($this->referer);
    }

    /**
     * 生成总览数据，提供给页面渲染使用
     *
     * @access public
     * @return void
     */
    protected function parseOverview()
    {
        # 初始化统计数组
        foreach (['ip', 'uv', 'pv'] as $type) {
            foreach (['today', 'yesterday'] as $day) {
                $this->overview[$type][$day]['total'] = 0;
            }
        }
        
        # 分类分时段统计数据
        foreach (['today' => date("Y-m-d"), 'yesterday'=> date("Y-m-d", time() - 24 * 60 * 60)] as $day => $time) {
            for ($i = 0; $i < 24; $i++) {
                $time = date("Y-m-d");
                $start = strtotime(date("{$time} {$i}:00:00"));
                $end   = strtotime(date("{$time} {$i}:59:59"));
                // "SELECT DISTINCT ip FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
                $this->overview['ip'][$day]['hours'][$i] = intval($this->db->fetchAll($this->db->select('COUNT(1) AS count')
                     ->from('(' . $this->db->select('DISTINCT ip')->from('table.access_log')
                     ->where('time >= ? AND time <= ?', $start, $end) . ') AS tmp'))[0]['count']);
                $this->overview['ip'][$day]['total'] += $this->overview['ip'][$day]['hours'][$i];
                // "SELECT DISTINCT ip,ua FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
                $this->overview['uv'][$day]['hours'][$i] = intval($this->db->fetchAll($this->db->select('COUNT(1) AS count')
                     ->from('(' . $this->db->select('DISTINCT ip,ua')->from('table.access_log')
                     ->where('time >= ? AND time <= ?', $start, $end) . ') AS tmp'))[0]['count']);
                $this->overview['uv'][$day]['total'] += $this->overview['uv'][$day]['hours'][$i];
                // "SELECT ip FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
                $this->overview['pv'][$day]['hours'][$i] = intval($this->db->fetchAll($this->db->select('COUNT(1) AS count')
                     ->from('table.access_log')->where('time >= ? AND time <= ?', $start, $end))[0]['count']);
                $this->overview['pv'][$day]['total'] += $this->overview['pv'][$day]['hours'][$i];
            }
        }

        # 总统计数据
        // "SELECT DISTINCT ip FROM {$this->table} {$where}"));
        $this->overview['ip']['all']['total'] = $this->db->fetchAll($this->db->select('COUNT(1) AS count')
             ->from('(' . $this->db->select('DISTINCT ip')->from('table.access_log') . ') AS tmp'))[0]['count'];
        // "SELECT DISTINCT ip,ua FROM {$this->table} {$where}"));
        $this->overview['uv']['all']['total'] = $this->db->fetchAll($this->db->select('COUNT(1) AS count')
             ->from('(' . $this->db->select('DISTINCT ip,ua')->from('table.access_log') . ') AS tmp'))[0]['count'];
        // "SELECT ip FROM {$this->table} {$where}"));
        $this->overview['pv']['all']['total'] = $this->db->fetchAll($this->db->select('COUNT(1) AS count')
             ->from('table.access_log'))[0]['count'];

        # 分类型绘制24小时访问图
        $this->overview['chart']['xAxis']['categories'] = json_encode([
            0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23
        ]);
        foreach (['ip', 'uv', 'pv'] as $type) {
            $this->overview['chart']['series'][$type] = json_encode($this->overview[$type]['today']['hours']);
        }
        $this->overview['chart']['title']['text'] = _t('%s 统计', date("Y-m-d"));
    }

    /**
     * 转义特殊字符，防止XSS等攻击
     *
     * @access public
     * @return void
     */
    protected function htmlEncode(&$variable)
    {
        if (is_array($variable)) {
            foreach ($variable as &$value) {
                $this->htmlEncode($value);
            }
        } elseif (is_string($variable)) {
            $variable = htmlspecialchars(urldecode($variable));
        }
    }

    /**
     * 判断是否是管理员登录状态
     *
     * @access public
     * @return bool
     */
    public function isAdmin()
    {
        $hasLogin = Typecho_Widget::widget('Widget_User')->hasLogin();
        if (!$hasLogin) {
            return false;
        }
        $isAdmin = Typecho_Widget::widget('Widget_User')->pass('administrator', true);
        return $isAdmin;
    }

    /**
     * 删除记录
     *
     * @access public
     * @return void
     */
    public function deleteLogs($ids)
    {
        foreach ($ids as $id) {
            $this->db->query($this->db->delete('table.access_log')
                     ->where('id = ?', $id)
            );
        }
    }

    /**
     * 获取首次进入网站时的来源
     *
     * @access public
     * @return string
     */
    public function getEntryPoint()
    {
        $entrypoint = Typecho_Cookie::get('__typecho_access_entrypoint');
        if ($entrypoint == null) {
            $entrypoint = $this->request->getReferer();
            if (strpos($entrypoint, rtrim(Helper::options()->siteUrl, '/')) !== false) {
                $entrypoint = null;
            }
            if ($entrypoint != null) {
                Typecho_Cookie::set('__typecho_access_entrypoint', $entrypoint);
            }
        }
        return $entrypoint;
    }

    /**
     * 记录当前访问（管理员登录不会记录）
     *
     * @access public
     * @return void
     */
    public function writeLogs($url = null)
    {
        if ($this->isAdmin()) {
            return;
        }
        if ($url == null) {
            $url = $this->request->getServer('REQUEST_URI');
        }
        $ip = $this->request->getIp();
        if ($ip == null) {
            $ip = '0.0.0.0';
        }
        $ip = bindec(decbin(ip2long($ip)));
        
        $entrypoint = $this->getEntryPoint();
        $referer    = $this->request->getReferer();
        $time       = Helper::options()->gmtTime + (Helper::options()->timezone - Helper::options()->serverTimezone);
        $rows = array(
            'ua'                => $this->ua->getUA(),
            'browser_id'        => $this->ua->getBrowserID(),
            'browser_version'   => $this->ua->getBrowserVersion(),
            'os_id'             => $this->ua->getOSID(),
            'os_version'        => $this->ua->getOSVersion(),
            'url'               => $url,
            'path'              => parse_url($url, PHP_URL_PATH),
            'query_string'      => parse_url($url, PHP_URL_QUERY),
            'ip'                => $ip,
            'referer'           => $referer,
            'referer_domain'    => parse_url($referer, PHP_URL_HOST),
            'entrypoint'        => $entrypoint,
            'entrypoint_domain' => parse_url($entrypoint, PHP_URL_HOST),
            'time'              => $time,
            // 'content_id'        => ,
            'robot'             => $this->ua->isRobot() ? 1 : 0,
            'robot_id'          => $this->ua->getRobotID(),
            'robot_version'     => $this->ua->getRobotVersion(),
        );

        try {
            $this->db->query($this->db->insert('table.access_log')->rows($rows));
        } catch (Exception $e) {} catch (Typecho_Db_Query_Exception $e) {}
    }

}
