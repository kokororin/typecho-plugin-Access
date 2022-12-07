<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Boostrap file not found');
}

class Access_Core
{
    protected $db;
    protected $request;
    protected $response;
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
        $this->db = Typecho_Db::get();
        $this->config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        $this->request = Typecho_Request::getInstance();
        $this->response = Typecho_Response::getInstance();
        if ($this->config->pageSize == null || $this->config->isDrop == null) {
            throw new Typecho_Plugin_Exception(_t('请先设置插件！'));
        }
        switch ($this->request->get('action')) {
            case 'migration':
                $this->action = 'migration';
                $this->title = _t('数据迁移');
                break;
            case 'logs':
                $this->action = 'logs';
                $this->title = _t('访问日志');
                break;
            case 'overview':
            default:
                $this->action = 'overview';
                $this->title = _t('访问概览');
                break;
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
     * 获取首次进入网站时的来源
     *
     * @access private
     * @return string
     */
    private function getEntryPoint()
    {
        $entrypoint = $this->request->getReferer();
        if ($entrypoint == null) {
            $entrypoint = Typecho_Cookie::get('__typecho_access_entrypoint')?:'';
        }
        if (parse_url($entrypoint, PHP_URL_HOST) == parse_url(Helper::options()->siteUrl, PHP_URL_HOST)) {
            $entrypoint = '';
        }
        if ($entrypoint != null) {
            Typecho_Cookie::set('__typecho_access_entrypoint', $entrypoint);
        }
        return $entrypoint;
    }

    /**
     * 判断当前 IP 是否在屏蔽 IP(段) 中
     *
     * @access private
     * @return bool
     */
    private function isBlockIp($ip): ?bool
    {
        $version = Access_Ip::matchIPVersion($ip);
        if ($version === null) {
            return false;
        }

        $lines = explode('\n', $this->config->blockIps);
        foreach ($lines as $line) {
            $cidr = explode('#', $line)[0];
            $cidr = trim($cidr);

            if (!empty($cidr)) {
                if (Access_Ip::matchCIDR($cidr, $ip)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 记录当前访问（管理员登录不会记录）
     *
     * @access public
     * @return void
     */
    public function writeLogs($archive = null, $url = null, $content_id = null, $meta_id = null)
    {
        if ($this->isAdmin()) {
            return;
        }
        if ($url == null) {
            $url = $this->request->getServer('REQUEST_URI');
        }
        $ip = $this->request->getIp();
        if ($this->isBlockIp($ip)) {
            return;
        }
        if(!empty($ip)) {
            # 解析ip归属地
            try {
                $ipdb = new Access_IpDb(dirname(__file__).'/lib/ipipfree.ipdb');
                $city = $ipdb->findInfo($ip, 'CN');
                $ip_country = $city->country_name;
                if($ip_country == '中国') {
                    $ip_province = $city->region_name;
                    $ip_city = $city->city_name;
                } else {
                    $ip_province = $ip_city = NULL;
                }
            } catch(Excpetion $e) {
                $ip_country = $ip_province = $ip_city = '未知';
            }
        } else {
            $ip = '';
        }

        $entrypoint = $this->getEntryPoint();
        $referer = $this->request->getReferer();
        if (empty($referer)){
            $referer = '';
        }
        $time = Helper::options()->gmtTime + (Helper::options()->timezone - Helper::options()->serverTimezone);

        if ($archive != null) {
            $parsedArchive = $this->parseArchive($archive);
            $content_id = $parsedArchive['content_id'];
            $meta_id = $parsedArchive['meta_id'];
        } else {
            $content_id = is_numeric($content_id) ? $content_id : null;
            $meta_id = is_numeric($meta_id) ? $meta_id : null;
        }

        $ua = new Access_UA($this->request->getAgent());
        $rows = array(
            'ua' => $ua->getUA(),
            'browser_id' => $ua->getBrowserID(),
            'browser_version' => $ua->getBrowserVersion(),
            'os_id' => $ua->getOSID(),
            'os_version' => $ua->getOSVersion(),
            'url' => $url,
            'path' => parse_url($url, PHP_URL_PATH),
            'query_string' => parse_url($url, PHP_URL_QUERY),
            'ip' => $ip,
            'ip_country' => $ip_country,
            'ip_province' => $ip_province,
            'ip_city' => $ip_city,
            'referer' => $referer,
            'referer_domain' => parse_url($referer, PHP_URL_HOST),
            'entrypoint' => $entrypoint,
            'entrypoint_domain' => parse_url($entrypoint, PHP_URL_HOST),
            'time' => $time,
            'content_id' => $content_id,
            'meta_id' => $meta_id,
            'robot' => $ua->isRobot() ? 1 : 0,
            'robot_id' => $ua->getRobotID(),
            'robot_version' => $ua->getRobotVersion(),
        );

        try {
            $this->db->query($this->db->insert('table.access_logs')->rows($rows));
        } catch (Exception $e) {} catch (Typecho_Db_Query_Exception $e) {}
    }

    /**
     * 重新刷数据库，当遇到一些算法变更时可能需要用到
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function rewriteLogs()
    {
        $db = Typecho_Db::get();
        $rows = $db->fetchAll($db->select()->from('table.access_logs'));
        foreach ($rows as $row) {
            $ua = new Access_UA($row['ua']);
            $row['browser_id'] = $ua->getBrowserID();
            $row['browser_version'] = $ua->getBrowserVersion();
            $row['os_id'] = $ua->getOSID();
            $row['os_version'] = $ua->getOSVersion();
            $row['robot'] = $ua->isRobot() ? 1 : 0;
            $row['robot_id'] = $ua->getRobotID();
            $row['robot_version'] = $ua->getRobotVersion();
            try {
                $db->query($db->update('table.access_logs')->rows($row)->where('id = ?', $row['id']));
            } catch (Typecho_Db_Exception $e) {
                throw new Typecho_Plugin_Exception(_t('刷新数据库失败：%s。', $e->getMessage()));
            }
        }
    }

    /**
     * 解析archive对象
     *
     * @access public
     * @return array
     */
    public function parseArchive($archive)
    {
        // 暂定首页的meta_id为0
        $content_id = null;
        $meta_id = null;
        if ($archive->is('index')) {
            $meta_id = 0;
        } elseif ($archive->is('post') || $archive->is('page')) {
            $content_id = $archive->cid;
        } elseif ($archive->is('tag')) {
            $meta_id = $archive->tags[0]['mid'];
        } elseif ($archive->is('category')) {
            $meta_id = $archive->categories[0]['mid'];
        } elseif ($archive->is('archive', 404)) {}

        return array(
            'content_id' => $content_id,
            'meta_id' => $meta_id,
        );
    }
}
