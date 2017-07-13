<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Boostrap file not found');
}

class Access_Core
{
    protected $db;
    protected $prefix;
    protected $table;
    public $config;
    protected $request;
    protected $response;
    protected $pageSize;
    protected $isDrop;
    public $ua;
    public $action;
    public $title;
    public $logs = array();
    public $overview = array();
    public $referer = array();

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
        $this->table    = $this->db->getPrefix() . 'access_log';
        $this->config   = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        $this->request  = Typecho_Request::getInstance();
        $this->response = Typecho_Response::getInstance();
        $this->pageSize = $this->config->pageSize;
        $this->isDrop   = $this->config->isDrop;
        if ($this->pageSize == null || $this->isDrop == null) {
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

    protected function parseLogs()
    {
        $type = $this->request->get('type', 1);
        $pagenum = $this->request->get('page', 1);
        $offset = (max(intval($pagenum), 1) - 1) * $this->pageSize;
        $query = $this->db->select()->from('table.access_log')
                    ->order('time', Typecho_Db::SORT_DESC)
                    ->offset($offset)->limit($this->pageSize);
        $qcount = $this->db->select('count(1) AS count')->from('table.access_log');
        switch ($type) {
            case 1:
                $query->where('robot = ?', 0);
                $qcount->where('robot = ?', 0);
                break;
            case 2:
                $query->where('robot = ?', 1);
                $qcount->where('robot = ?', 0);
                break;
            default:
                break;
        }
        $this->logs['list'] = $this->db->fetchAll($query);

        $this->cleanArray($this->logs['list']);

        $this->logs['rows'] = $this->db->fetchAll($qcount)[0]['count'];
        
        $page = new Access_Page($this->pageSize, $this->logs['rows'], $pagenum, 10, array(
            'panel' => Access_Plugin::$panel,
            'action' => 'logs',
            'type' => $type,
        ));
        $this->logs['page'] = $page->show();
    }

    protected function parseReferer()
    {
        $this->referer['url'] = $this->db->fetchAll("SELECT DISTINCT referer, COUNT(*) as count FROM {$this->table} WHERE referer <> '' GROUP BY referer ORDER BY count DESC LIMIT {$this->pageSize}");
        $this->referer['domain'] = $this->db->fetchAll("SELECT DISTINCT referer_domain, COUNT(*) as count FROM {$this->table} WHERE referer_domain <> '' GROUP BY referer_domain ORDER BY count DESC LIMIT {$this->pageSize}");
        $this->cleanArray($this->referer);
    }

    protected function parseOverview()
    {

        $where = 'WHERE 1=1';

        $this->overview['ip']['today']['total'] = 0;
        $this->overview['uv']['today']['total'] = 0;
        $this->overview['pv']['today']['total'] = 0;
        $this->overview['ip']['yesterday']['total'] = 0;
        $this->overview['uv']['yesterday']['total'] = 0;
        $this->overview['pv']['yesterday']['total'] = 0;

        for ($i = 0; $i < 24; $i++) {
            $today = date("Y-m-d");
            $start = strtotime(date("{$today} {$i}:00:00"));
            $end = strtotime(date("{$today} {$i}:59:59"));
            $this->overview['ip']['today']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
            $this->overview['ip']['today']['total'] += $this->overview['ip']['today']['hours'][$i];
            $this->overview['uv']['today']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
            $this->overview['uv']['today']['total'] += $this->overview['uv']['today']['hours'][$i];
            $this->overview['pv']['today']['hours'][] = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
            $this->overview['pv']['today']['total'] += $this->overview['pv']['today']['hours'][$i];
        }

        for ($i = 0; $i < 24; $i++) {
            $yesterday = date("Y-m-d", time() - 24 * 60 * 60);
            $start = strtotime(date("{$yesterday} {$i}:00:00"));
            $end = strtotime(date("{$yesterday} {$i}:59:59"));
            $this->overview['ip']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
            $this->overview['ip']['yesterday']['total'] += $this->overview['ip']['yesterday']['hours'][$i];
            $this->overview['uv']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
            $this->overview['uv']['yesterday']['total'] += $this->overview['uv']['yesterday']['hours'][$i];
            $this->overview['pv']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where} AND `time` BETWEEN {$start} AND {$end}"));
            $this->overview['pv']['yesterday']['total'] += $this->overview['pv']['yesterday']['hours'][$i];
        }

        $this->overview['ip']['all']['total'] = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where}"));
        $this->overview['uv']['all']['total'] = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where}"));
        $this->overview['pv']['all']['total'] = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where}"));

        $this->overview['chart']['title']['text'] = date("Y-m-d 统计");
        $this->overview['chart']['xAxis']['categories'] = $this->buildObject(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23), true);
        $this->overview['chart']['series']['pv'] = $this->buildObject($this->overview['pv']['today']['hours'], false);
        $this->overview['chart']['series']['uv'] = $this->buildObject($this->overview['uv']['today']['hours'], false);
        $this->overview['chart']['series']['ip'] = $this->buildObject($this->overview['ip']['today']['hours'], false);

    }

    protected function cleanArray(&$array)
    {
        if (is_array($array)) {
            foreach ($array as &$value) {
                if (!is_array($value)) {
                    $value = htmlspecialchars(urldecode($value));
                } else {
                    $this->cleanArray($value);
                }
            }
        }
    }

    protected function buildObject($array, $quote)
    {
        $obj = Json::encode($array);
        $obj = str_replace("\"", "'", $obj);
        if ($quote) {
            return $obj;
        } else {
            return str_replace("'", '', $obj);
        }
    }

    public function isAdmin()
    {
        $hasLogin = Typecho_Widget::widget('Widget_User')->hasLogin();
        if (!$hasLogin) {
            return false;
        }
        $isAdmin = Typecho_Widget::widget('Widget_User')->pass('administrator', true);
        return $isAdmin;
    }

    public function deleteLogs($ids)
    {
        foreach ($ids as $id) {
            $this->db->query($this->db->delete($this->table)
                     ->where('id = ?', $id)
            );
        }
    }

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
