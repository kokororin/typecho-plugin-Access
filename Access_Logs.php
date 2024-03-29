<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Bootstrap file not found');
}

class Access_Logs {
    /**
     * 允许访问的业务函数名及其对应的允许访问方法
     * @var string[]
     */
    private static $rpcTypes = [
        'get' => 'GET',
        'delete' => 'POST',
        'cids' => 'GET',
    ];

    private static $presetWheres = [
        'robot' => '`ip` IN ( SELECT DISTINCT `ip` FROM `typecho_access_logs` WHERE `url` = "/robots.txt" )',
        'script' => '`ip` IN ( SELECT DISTINCT `ip` FROM `typecho_access_logs` WHERE `url` LIKE "%/wp-%" OR `url` LIKE "/.%" OR `url` LIKE "%../%" )',
    ];

    private $config;
    private $db;
    private $request;

    public function __construct($request) {
        $this->db = Typecho_Db::get();
        $this->request = $request;
        $this->config = Typecho_Widget::widget('Widget_Options')->plugin('Access');
    }

    /**
     * 创建过滤器对应的数据库查询语句
     *
     * @access private
     * @return string
     */
    private function filterQueryBuilder($query, $filters, $fuzzy)
    {
        $prefix = $this->db->getPrefix();
        $ids = array_key_exists('ids', $filters) ? $filters['ids'] : '';
        $ip = array_key_exists('ip', $filters) ? $filters['ip'] : '';
        $ua = array_key_exists('ua', $filters) ? $filters['ua'] : '';
        $cid = array_key_exists('cid', $filters) ? $filters['cid'] : '';
        $url = array_key_exists('url', $filters) ? $filters['url'] : '';
        $path = array_key_exists('path', $filters) ? $filters['path'] : '';
        $robot = array_key_exists('robot', $filters) ? $filters['robot'] : '';
        $preset = array_key_exists('preset', $filters) ? $filters['preset'] : '';
        $compare = $fuzzy === '1' ? ' LIKE ?' : ' = ?';
        $empty = $fuzzy ? '%' : '';
        if (!empty($ids) && count($ids) > 0) {
            $query->where(join(' OR ', array_fill(0, count($ids), 'id = ?')), ...$ids);
        }
        if ($ip !== $empty) {
            $query->where('ip' . $compare, $ip);
        }
        if ($ua !== $empty) {
            $query->where('ua' . $compare, $ua);
        }
        if ($cid !== '') {
            $query->where('content_id' . $compare, $cid);
        }
        if ($url !== $empty) {
            $query->where('url' . $compare, $url);
        }
        if ($path !== $empty) {
            $query->where('path' . $compare, $path);
        }
        if ($robot !== '') {
            $query->where('robot = ?', $robot);
        }
        $hasPreset = array_key_exists($preset, Access_Logs::$presetWheres);
        if ($hasPreset) {
            $query->where("\"STATIC_PLACEHOLDER_{$preset}\" = \"STATIC_PLACEHOLDER_{$preset}\"");
        }

        $sql = $query->prepare($query);
        if ($hasPreset) {
            foreach (Access_Logs::$presetWheres as $name => $where) {
                $placeholder = "\"STATIC_PLACEHOLDER_{$name}\" = \"STATIC_PLACEHOLDER_{$name}\"";
                $where = str_replace('typecho_', $prefix, Access_Logs::$presetWheres[$name]);
                $sql = str_replace($placeholder, $where, $sql);
            }
        }
        return $sql;
    }

    /**
     * 根据过滤器，获取详细访问日志数据
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    public function get(): ?array
    {
        $resp = [];
        $filters = array(
            'ip' => $this->request->get('ip', ''),
            'ua' => $this->request->get('ua', ''),
            'cid' => $this->request->get('cid', ''),
            'url' => $this->request->get('url', ''),
            'path' => $this->request->get('path', ''),
            'robot' => $this->request->get('robot', ''),
            'preset' => $this->request->get('preset', ''),
        );
        $fuzzy = $this->request->get('fuzzy', '');
        $pageSize = intval($this->config->pageSize);
        $pageNum = intval($this->request->get('page', 1));

        $counterQuery = $this->db->select('count(1) AS count')->from('table.access_logs');
        $dataQuery = $this->db->select()->from('table.access_logs')
            ->order('time', Typecho_Db::SORT_DESC)
            ->offset((max(intval($pageNum), 1) - 1) * $pageSize)
            ->limit($pageSize);

        $dataQuery = $this->filterQueryBuilder($dataQuery, $filters, $fuzzy);
        $counterQuery = $this->filterQueryBuilder($counterQuery, $filters, $fuzzy);

        $resp['count'] = $this->db->fetchAll($counterQuery)[0]['count'];
        $resp['pagination'] = [
            'size' => $pageSize,
            'current' => $pageNum,
            'total' => max(floor($resp['count'] / $pageSize), 1),
        ];
        $resp['logs'] = $this->db->fetchAll($dataQuery);
        foreach ($resp['logs'] as &$row) {
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
            if($row['ip_country'] == '中国') {
                $row['ip_loc'] = "{$row['ip_province']} {$row['ip_city']}";
            } else {
                $row['ip_loc'] = $row['ip_country'];
            }
        }

        return $resp;
    }

    /**
     * 根据过滤器，删除详细访问日志数据
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    public function delete(): ?array
    {
        $resp = [];

        $counterQuery = $this->db->select('count(1) AS count')->from('table.access_logs');
        $operatorQuery = $this->db->delete('table.access_logs');

        $ids = $this->request->get('ids', '');
        $ip = $this->request->get('ip', '');
        $ua = $this->request->get('ua', '');
        $cid = $this->request->get('cid', '');
        $url = $this->request->get('url', '');
        $path = $this->request->get('path', '');
        $robot = $this->request->get('robot', '');
        $preset = $this->request->get('preset', '');
        $force = $this->request->get('force', '');
        if ($ids) {
            $ids = Json::decode($ids, true);
            if (!is_array($ids)) {
                throw new Exception('Bad Request', 400);
            }
            $counterQuery = $this->filterQueryBuilder($counterQuery, ['ids' => $ids], false);
            $operatorQuery = $this->filterQueryBuilder($operatorQuery, ['ids' => $ids], false);
        } else if ($ip || $ua || $cid || $url || $path || $robot || $preset) {
            $filters = array(
                'ip' => $ip,
                'ua' => $ua,
                'cid' => $cid,
                'url' => $url,
                'path' => $path,
                'robot' => $robot,
                'preset' => $preset,
            );
            $fuzzy = $this->request->get('fuzzy', '');
            $counterQuery = $this->filterQueryBuilder($counterQuery, $filters, $fuzzy);
            $operatorQuery = $this->filterQueryBuilder($operatorQuery, $filters, $fuzzy);
        } else {
            throw new Exception('Bad Request', 400);
        }

        $resp['count'] = $this->db->fetchAll($counterQuery)[0]['count'];
        // delete more than one page requires 2nd time confirmation
        if ($resp['count'] <= $this->config->pageSize || $force === 'force') {
            $this->db->query($operatorQuery);
        } else {
            $resp['requireForce'] = true;
        }

        return $resp;
    }

    /**
     * 获取日志 id 列表
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    public function cids(): ?array
    {
        $resp = [];

        $list = $this->db->fetchAll($this->db->select('DISTINCT content_id as cid, COUNT(1) as count, table.contents.title')
            ->from('table.access_logs')
            ->join('table.contents', 'table.access_logs.content_id = table.contents.cid')
            ->group('table.access_logs.content_id')
            ->order('count', Typecho_Db::SORT_DESC));
        $resp['list'] = $list;

        return $resp;
    }

    /**
     * 业务调度入口
     *
     * @access public
     * @param string rpcType 调用过程类型
     * @return ?array
     * @throws Exception
     */
    public function invoke(string $rpcType): ?array {
        if(!method_exists($this, $rpcType) || !array_key_exists($rpcType, Access_Logs::$rpcTypes))
            throw new Exception('Bad Request', 400);
        $method = Access_Logs::$rpcTypes[$rpcType];
        if (
            ($method === 'GET' && !$this->request->isGet())
            || ($method === 'POST' && !$this->request->isPost())
            || ($method === 'PUT' && !$this->request->isPut())
        ) {
            throw new Exception('Method Not Allowed', 405);
        }
        return $this->$rpcType();
    }
}
