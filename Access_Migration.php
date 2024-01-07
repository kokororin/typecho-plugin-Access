<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Bootstrap file not found');
}

class Access_Migration {
    /**
     * 允许访问的业务函数名及其对应的允许访问方法
     * @var string[]
     */
    private static $rpcTypes = [
        'overview' => 'GET',
        'migrate' => 'POST',
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
     * 显示是否存在旧版数据
     *
     * @access public
     * @return bool
     * @throws Exception
     */
    public function exists()
    {
        $prefix = $this->db->getPrefix();
        $adapterName = $this->db->getAdapterName();
        if (strpos($adapterName, 'Mysql') !== false || strpos($adapterName, 'SQLite') !== false) {
            if ($this->db->fetchRow($this->db->query("SHOW TABLES LIKE '{$prefix}access';", Typecho_Db::READ))) {
                return true;
            }
            if ($this->db->fetchRow($this->db->query("SHOW TABLES LIKE '{$prefix}access_log';", Typecho_Db::READ))) {
                return true;
            }
        } else if (strpos($adapterName, 'Pgsql') !== false) {
            if ($this->db->fetchRow($this->db->query("SELECT to_regclass('public.{$prefix}access');", Typecho_Db::READ))) {
                return true;
            }
            if ($this->db->fetchRow($this->db->query("SELECT to_regclass('public.{$prefix}access_log');", Typecho_Db::READ))) {
                return true;
            }
        }
        return false;
    }

    /**
     * 显示旧版本数据数量
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    private function overview()
    {
        $resp = [ 'total' => 0 ];
        $prefix = $this->db->getPrefix();
        $adapterName = $this->db->getAdapterName();
    
        // 统计 v1 版本数据
        if (strpos($adapterName, 'Mysql') !== false || strpos($adapterName, 'SQLite') !== false) {
            $checkTableV1 = $this->db->fetchRow($this->db->query("SHOW TABLES LIKE '{$prefix}access';", Typecho_Db::READ));
        } else if (strpos($adapterName, 'Pgsql') !== false) {
            $checkTableV1 = $this->db->fetchRow($this->db->query("SELECT tablename FROM pg_catalog.pg_tables WHERE tablename = '{$prefix}access';", Typecho_Db::READ));
        }
        if ($checkTableV1) {
            $resp['v1'] = $this->db->fetchRow($this->db->select('COUNT(1) AS cnt')->from('table.access'))['cnt'];
            $resp['total'] += $resp['v1'];
        }
    
        // 统计 v2 版本数据
        if (strpos($adapterName, 'Mysql') !== false || strpos($adapterName, 'SQLite') !== false) {
            $checkTableV2 = $this->db->fetchRow($this->db->query("SHOW TABLES LIKE '{$prefix}access_log';", Typecho_Db::READ));
        } else if (strpos($adapterName, 'Pgsql') !== false) {
            $checkTableV2 = $this->db->fetchRow($this->db->query("SELECT tablename FROM pg_catalog.pg_tables WHERE tablename = '{$prefix}access_log';", Typecho_Db::READ));
        }
        if ($checkTableV2) {
            $resp['v2'] = intval($this->db->fetchRow($this->db->select('COUNT(1) AS cnt')->from('table.access_log'))['cnt']);
            $resp['total'] += $resp['v2'];
        }
    
        return $resp;
    }
    

    /**
     * 迁移旧版本数据，单次1000条
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    private function migrate()
    {
        $resp = [ 'count' => 0, 'remain' => 0 ];
        $step = 1000;
        $prefix = $this->db->getPrefix();
        $adapterName = $this->db->getAdapterName();
    
        $showTableQuery = strpos($adapterName, 'Pgsql') !== false ? 
            "SELECT * FROM information_schema.tables WHERE table_name LIKE '{$prefix}access';" :
            "SHOW TABLES LIKE '{$prefix}access';";
        // 迁移 v1 版本数据
        if ($this->db->fetchRow($this->db->query($showTableQuery, Typecho_Db::READ))) {
            $remain = intval($this->db->fetchRow($this->db->select('COUNT(1) AS cnt')->from('table.access'))['cnt']);
            if ($resp['count'] === 0) {
                $rows = $this->db->fetchAll($this->db->select()->from('table.access')->limit($step));
                foreach ($rows as $row) {
                    $id = $row['id'];
                    unset($row['id']);
                    $ua = new Access_UA($row['ua']);
                    $row['browser_id'       ] = $ua->getBrowserID();
                    $row['browser_version'  ] = $ua->getBrowserVersion();
                    $row['os_id'            ] = $ua->getOSID();
                    $row['os_version'       ] = $ua->getOSVersion();
                    $row['path'             ] = parse_url($row['url'], PHP_URL_PATH);
                    $row['query_string'     ] = parse_url($row['url'], PHP_URL_QUERY);
                    $row['ip'               ] = $row['ip'];
                    $row['entrypoint'       ] = $row['referer'];
                    $row['entrypoint_domain'] = $row['referer_domain'];
                    $row['time'             ] = $row['date'];
                    $row['robot'            ] = $ua->isRobot() ? 1 : 0;
                    $row['robot_id'         ] = $ua->getRobotID();
                    $row['robot_version'    ] = $ua->getRobotVersion();
                    unset($row['date']);
                    $this->db->query($this->db->insert('table.access_logs')->rows($row));
                    $resp['count'] += 1;
                    $this->db->query($this->db->delete('table.access')->where('id = ?', $id));
                    $remain -= 1;
                }
            }
            if ($remain === 0) {
                $dropTableQuery = strpos($adapterName, 'Pgsql') !== false ? 
                    "DROP TABLE IF EXISTS {$prefix}access;" :
                    "DROP TABLE `{$prefix}access`;";
                $this->db->query($dropTableQuery, Typecho_Db::WRITE);
            }
            $resp['remain'] += $remain;
        }
        
        $showTableQuery = strpos($adapterName, 'Pgsql') !== false ? 
            "SELECT * FROM information_schema.tables WHERE table_name LIKE '{$prefix}access_log';" :
            "SHOW TABLES LIKE '{$prefix}access_log';";
        // 迁移 v2 版本数据
        if ($this->db->fetchRow($this->db->query($showTableQuery, Typecho_Db::READ))) {
            $remain = intval($this->db->fetchRow($this->db->select('COUNT(1) AS cnt')->from('table.access_log'))['cnt']);
            if ($resp['count'] === 0) {
                $rows = $this->db->fetchAll($this->db->select()->from('table.access_log')->limit($step));
                foreach ($rows as $row) {
                    $id = $row['id'];
                    unset($row['id']);
                    $row['ip'] = long2ip($row['ip']);
                    $this->db->query($this->db->insert('table.access_logs')->rows($row));
                    $resp['count'] += 1;
                    $this->db->query($this->db->delete('table.access_log')->where('id = ?', $id));
                    $remain -= 1;
                }
            }
            if ($remain === 0) {
                $dropTableQuery = strpos($adapterName, 'Pgsql') !== false ? 
                    "DROP TABLE IF EXISTS {$prefix}access_log;" :
                    "DROP TABLE `{$prefix}access_log`;";
                $this->db->query($dropTableQuery, Typecho_Db::WRITE);
            }
            $resp['remain'] += $remain;
        }
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
        if(!method_exists($this, $rpcType) || !array_key_exists($rpcType, Access_Migration::$rpcTypes))
            throw new Exception('Bad Request', 400);
        $method = Access_Migration::$rpcTypes[$rpcType];
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
