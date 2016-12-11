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
    protected $pageSize;
    protected $isDrop;
    public $parser;
    public $action;
    public $title;
    public $logs = array();
    public $overview = array();
    public $referer = array();

    public function __construct()
    {
        $this->db = Typecho_Db::get();
        $this->prefix = $this->db->getPrefix();
        $this->table = $this->prefix . 'access';
        $this->config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        $this->request = Typecho_Request::getInstance();
        $this->pageSize = $this->config->pageSize;
        $this->isDrop = $this->config->isDrop;
        if ($this->pageSize == null || $this->isDrop == null) {
            throw new Typecho_Plugin_Exception('请先设置插件！');
        }
        $this->parser = new Access_Parser();
        switch ($this->request->get('action')) {
            case 'logs':
            default:
                $this->action = 'logs';
                $this->title = '访问日志';
                $this->parseLogs();
                break;
            case 'overview':
                $this->action = 'overview';
                $this->title = '访问概览';
                $this->parseOverview();
                $this->parseReferer();
                break;
        }
    }

    protected function getWhere($type)
    {
        $where_str = '';
        foreach ($this->parser->bots as $value) {
            $where_str .= "replace(LOWER(`ua`), ' ', '') {1} LIKE " . "'%{$this->parser->filter($value)}%' {2} ";
        }
        $where_str = rtrim($where_str, '{2} ');
        switch ($type) {
            case 1:
                $where = str_replace('{1}', 'NOT', $where_str);
                $where = str_replace('{2}', 'and', $where);
                break;
            case 2:
                $where = str_replace('{1}', '', $where_str);
                $where = str_replace('{2}', 'or', $where);
                break;
            case 3:
                $where = '1=1';
                break;
            default:
                throw new Typecho_Plugin_Exception('参数不正确！');
        }
        return 'WHERE ' . $where;
    }

    protected function parseLogs()
    {
        $type = $this->request->get('type', 1);
        $p = $this->request->get('page', 1);
        $offset = (max(intval($p), 1) - 1) * $this->pageSize;
        $where = $this->getWhere($type);

        $this->logs['list'] = $this->db->fetchAll("SELECT * FROM {$this->table} {$where} ORDER BY id DESC LIMIT {$this->pageSize} OFFSET {$offset}");

        $this->logs['rows'] = count($this->db->fetchAll("SELECT * FROM {$this->table} {$where}"));

        $page = new Access_Page($this->pageSize, $this->logs['rows'], $p, 10, array(
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
            $this->overview['ip']['today']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['ip']['today']['total'] += $this->overview['ip']['today']['hours'][$i];
            $this->overview['uv']['today']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['uv']['today']['total'] += $this->overview['uv']['today']['hours'][$i];
            $this->overview['pv']['today']['hours'][] = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['pv']['today']['total'] += $this->overview['pv']['today']['hours'][$i];
        }

        for ($i = 0; $i < 24; $i++) {
            $yesterday = date("Y-m-d", time() - 24 * 60 * 60);
            $start = strtotime(date("{$yesterday} {$i}:00:00"));
            $end = strtotime(date("{$yesterday} {$i}:59:59"));
            $this->overview['ip']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['ip']['yesterday']['total'] += $this->overview['ip']['yesterday']['hours'][$i];
            $this->overview['uv']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['uv']['yesterday']['total'] += $this->overview['uv']['yesterday']['hours'][$i];
            $this->overview['pv']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
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

}
