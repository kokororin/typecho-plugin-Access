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
     * @return void
     */
    private function filterQueryBuilder($query, $filters, $fuzzy)
    {
        $ids = array_key_exists('ids', $filters) ? $filters['ids'] : '';
        $ip = array_key_exists('ip', $filters) ? $filters['ip'] : '';
        $ua = array_key_exists('ua', $filters) ? $filters['ua'] : '';
        $cid = array_key_exists('cid', $filters) ? $filters['cid'] : '';
        $path = array_key_exists('path', $filters) ? $filters['path'] : '';
        $robot = array_key_exists('robot', $filters) ? $filters['robot'] : '';
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
        if ($cid !== $empty) {
            $query->where('content_id' . $compare, $cid);
        }
        if ($path !== $empty) {
            $query->where('path' . $compare, $path);
        }
        if ($robot !== $empty) {
            $query->where('robot = ?', $robot);
        }
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
            'path' => $this->request->get('path', ''),
            'robot' => $this->request->get('robot', ''),
        );
        $fuzzy = $this->request->get('fuzzy', '');
        $pageSize = intval($this->config->pageSize);
        $pageNum = intval($this->request->get('page', 1));

        $counterQuery = $this->db->select('count(1) AS count')->from('table.access_logs');
        $dataQuery = $this->db->select()->from('table.access_logs')
            ->order('time', Typecho_Db::SORT_DESC)
            ->offset((max(intval($pageNum), 1) - 1) * $pageSize)
            ->limit($pageSize);

        $this->filterQueryBuilder($dataQuery, $filters, $fuzzy);
        $this->filterQueryBuilder($counterQuery, $filters, $fuzzy);

        $resp['count'] = $this->db->fetchAll($counterQuery)[0]['count'];
        $resp['pagination'] = [
            'size' => $pageSize,
            'current' => $pageNum,
            'total' => floor($resp['count'] / $pageSize),
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
        $path = $this->request->get('path', '');
        $robot = $this->request->get('robot', '');
        if ($ids) {
            $ids = Json::decode($ids, true);
            if (!is_array($ids)) {
                throw new Exception('Bad Request', 400);
            }
            $this->filterQueryBuilder($counterQuery, ['ids' => $ids], false);
            $this->filterQueryBuilder($operatorQuery, ['ids' => $ids], false);
        } else if ($ip || $ua || $cid || $path || $robot) {
            $filters = array(
                'ip' => $ip,
                'ua' => $ua,
                'cid' => $cid,
                'path' => $path,
                'robot' => $robot,
            );
            $fuzzy = $this->request->get('fuzzy', '');
            $this->filterQueryBuilder($counterQuery, $filters, $fuzzy);
            $this->filterQueryBuilder($operatorQuery, $filters, $fuzzy);
        } else {
            throw new Exception('Bad Request', 400);
        }

        $resp['count'] = $this->db->fetchAll($counterQuery)[0]['count'];
        $this->db->query($operatorQuery);

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
