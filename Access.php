<?php
class Access_Extend
{
    protected $db;
    protected $prefix;
    protected $table;
    protected $config;
    protected $request;
    protected $pageSize;
    protected $isDrop;
    private static $_instance = null;
    public $action;
    public $title;
    public $logs     = array();
    public $overview = array();

    public function __construct()
    {
        $this->db       = Typecho_Db::get();
        $this->prefix   = $this->db->getPrefix();
        $this->table    = $this->prefix . 'access';
        $this->config   = Typecho_Widget::widget('Widget_Options')->plugin('Access');
        $this->request  = Typecho_Request::getInstance();
        $this->pageSize = $this->config->pageSize;
        $this->isDrop   = $this->config->isDrop;
        if ($this->pageSize == null || $this->isDrop == null) {
            throw new Typecho_Plugin_Exception('请先设置插件！');
        }
        switch ($this->request->get('action')) {
            case 'logs':
            default:
                $this->action = 'logs';
                $this->title  = '访问日志';
                $this->parseLogs();
                break;
            case 'overview':
                $this->action = 'overview';
                $this->title  = '访问概览';
                $this->parseOverview();
                break;
        }
    }

    public $spiderArray = array(
        "TencentTraveler",
        "Baiduspider",
        "BaiduGame",
        "Googlebot",
        "msnbot",
        "Sosospider+",
        "Sogou web spider",
        "ia_archiver",
        "Yahoo! Slurp",
        "YoudaoBot",
        "Yahoo Slurp",
        "MSNBot",
        "Java (Often spam bot)",
        "BaiDuSpider",
        "Voila",
        "Yandex bot",
        "BSpider",
        "twiceler",
        "Sogou Spider",
        "Speedy Spider",
        "Google AdSense",
        "Heritrix",
        "Python-urllib",
        "Alexa (IA Archiver)",
        "Ask",
        "Exabot",
        "Custo",
        "OutfoxBot/YodaoBot",
        "yacy",
        "SurveyBot",
        "legs",
        "lwp-trivial",
        "Nutch",
        "StackRambler",
        "The web archive (IA Archiver)",
        "Perl tool",
        "MJ12bot",
        "Netcraft",
        "MSIECrawler",
        "WGet tools",
        "larbin",
        "Fish search",
        "crawler",
        "bingbot",
    );

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function parseUA($ua)
    {
        $os = null;
        if ($this->isSpider($ua)) {
            $os = '爬虫';
        } elseif (preg_match('/Windows NT 6.0/i', $ua)) {
            $os = "Windows Vista";
        } elseif (preg_match('/Windows NT 6.1/i', $ua)) {
            $os = "Windows 7";
        } elseif (preg_match('/Windows NT 6.2/i', $ua)) {
            $os = "Windows 8";
        } elseif (preg_match('/Windows NT 6.3/i', $ua)) {
            $os = "Windows 8.1";
        } elseif (preg_match('/Windows NT 10.0/i', $ua)) {
            $os = "Windows 10";
        } elseif (preg_match('/Windows NT 5.1/i', $ua)) {
            $os = "Windows XP";
        } elseif (preg_match('/Windows NT 5.2/i', $ua) && preg_match('/Win64/i', $ua)) {
            $os = "Windows XP 64 bit";
        } elseif (preg_match('/Android ([0-9.]+)/i', $ua, $matches)) {
            $os = "Android " . $matches[1];
        } elseif (preg_match('/iPhone OS ([_0-9]+)/i', $ua, $matches)) {
            $os = 'iPhone ' . $matches[1];
        } elseif (preg_match('/Ubuntu/i', $ua, $matches)) {
            $os = 'Ubuntu ';
        } elseif (preg_match('/Mac OS X ([0-9_]+)/i', $ua, $matches)) {
            $os = 'Mac OS ' . $matches[1];
        } elseif (preg_match('/Linux/i', $ua, $matches)) {
            $os = 'Linux';
        } else {
            $os = '未知';
        }

        if ($this->isSpider($ua)) {
            $browser = '爬虫';
        } elseif (preg_match('#(Camino|Chimera)[ /]([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'Camino ' . $matches[2];
        } elseif (preg_match('#SE 2([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = '搜狗浏览器 2' . $matches[1];
        } elseif (preg_match('#360([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = '360浏览器 ' . $matches[1];
        } elseif (preg_match('#Maxthon( |\/)([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'Maxthon ' . $matches[2];
        } elseif (preg_match('#Chrome/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'Chrome ' . $matches[1];
        } elseif (preg_match('#XiaoMi/MiuiBrowser/([0-9.]+)#i', $ua, $matches)) {
            $browser = '小米浏览器 ' . $matches[1];
        } elseif (preg_match('#Safari/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'Safari ' . $matches[1];
        } elseif (preg_match('#opera mini#i', $ua)) {
            preg_match('#Opera/([a-zA-Z0-9.]+)#i', $ua, $matches);
            $browser = 'Opera Mini ' . $matches[1];
        } elseif (preg_match('#Opera.([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'Opera ' . $matches[1];
        } elseif (preg_match('#TencentTraveler ([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = '腾讯TT浏览器 ' . $matches[1];
        } elseif (preg_match('#UCWEB([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'UCWEB ' . $matches[1];
        } elseif (preg_match('#MSIE ([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'Internet Explorer ' . $matches[1];
        } elseif (preg_match('#Trident#', $ua, $matches)) {
            $browser = 'Internet Explorer 11';
        } elseif (preg_match('#Edge/12.0#i', $ua, $matches)) {
            //win10中spartan浏览器
            $browser = 'Spartan';
        } elseif (preg_match('#(Firefox|Phoenix|Firebird|BonEcho|GranParadiso|Minefield|Iceweasel)/([a-zA-Z0-9.]+)#i', $ua, $matches)) {
            $browser = 'Firefox ' . $matches[2];
        } else {
            $browser = '未知';
        }
        return $os . " | " . $browser;
    }

    public function isSpider($ua)
    {
        $ua = strtolower($ua);
        if (!empty($ua)) {
            foreach ($this->spiderArray as $val) {
                $str = strtolower($val);
                if (strpos($ua, $str) !== false) {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    protected function getWhere($type)
    {
        $where_str = '';
        foreach ($this->spiderArray as $value) {
            $where_str .= "ua {1} LIKE " . "'%{$value}%' {2} ";
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
        $type   = $this->request->get('type', 1);
        $p      = $this->request->get('page', 1);
        $offset = (max(intval($p), 1) - 1) * $this->pageSize;
        $where  = $this->getWhere($type);

        $this->logs['list'] = $this->db->fetchAll("SELECT * FROM {$this->table} {$where} ORDER BY id DESC LIMIT {$this->pageSize} OFFSET {$offset}");

        $this->logs['rows'] = count($this->db->fetchAll("SELECT * FROM {$this->table} {$where}"));
        include_once dirname(__FILE__) . '/lib/Page.php';
        $pager              = new Page($this->pageSize, $this->logs['rows'], $p, 10);
        $this->logs['page'] = $pager->show();
    }

    protected function parseOverview()
    {
        //$where = $this->getWhere(1);

        $where = 'WHERE 1=1';

        $this->overview['ip']['today']['total']     = 0;
        $this->overview['uv']['today']['total']     = 0;
        $this->overview['pv']['today']['total']     = 0;
        $this->overview['ip']['yesterday']['total'] = 0;
        $this->overview['uv']['yesterday']['total'] = 0;
        $this->overview['pv']['yesterday']['total'] = 0;

        for ($i = 0; $i < 24; $i++) {
            $today                                    = date("Y-m-d");
            $start                                    = strtotime(date("{$today} {$i}:00:00"));
            $end                                      = strtotime(date("{$today} {$i}:59:59"));
            $this->overview['ip']['today']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['ip']['today']['total'] += $this->overview['ip']['today']['hours'][$i];
            $this->overview['uv']['today']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['uv']['today']['total'] += $this->overview['uv']['today']['hours'][$i];
            $this->overview['pv']['today']['hours'][] = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['pv']['today']['total'] += $this->overview['pv']['today']['hours'][$i];
        }

        for ($i = 0; $i < 24; $i++) {
            $yesterday                                    = date("Y-m-d", time() - 24 * 60 * 60);
            $start                                        = strtotime(date("{$yesterday} {$i}:00:00"));
            $end                                          = strtotime(date("{$yesterday} {$i}:59:59"));
            $this->overview['ip']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['ip']['yesterday']['total'] += $this->overview['ip']['yesterday']['hours'][$i];
            $this->overview['uv']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['uv']['yesterday']['total'] += $this->overview['uv']['yesterday']['hours'][$i];
            $this->overview['pv']['yesterday']['hours'][] = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where} AND date BETWEEN {$start} AND {$end}"));
            $this->overview['pv']['yesterday']['total'] += $this->overview['pv']['yesterday']['hours'][$i];
        }

        $this->overview['ip']['all']['total']           = count($this->db->fetchAll("SELECT DISTINCT ip FROM {$this->table} {$where}"));
        $this->overview['uv']['all']['total']           = count($this->db->fetchAll("SELECT DISTINCT ip,ua FROM {$this->table} {$where}"));
        $this->overview['pv']['all']['total']           = count($this->db->fetchAll("SELECT ip FROM {$this->table} {$where}"));

        $this->overview['chart']['title']['text']       = date("Y-m-d 统计");
        $this->overview['chart']['xAxis']['categories'] = $this->buildObject(array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23), true);
        $this->overview['chart']['series']['pv']        = $this->buildObject($this->overview['pv']['today']['hours'], false);
        $this->overview['chart']['series']['uv']        = $this->buildObject($this->overview['uv']['today']['hours'], false);
        $this->overview['chart']['series']['ip']        = $this->buildObject($this->overview['ip']['today']['hours'], false);

    }

    protected function buildObject($array, $quote)
    {
        $obj = json_encode($array);
        $obj = str_replace("\"", "'", $obj);
        if ($quote) {
            return $obj;
        } else {
            return str_replace("'", '', $obj);
        }
    }

    public function getAddress($ip)
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return 'Invalid IP address';
        }

        $ipdatafile = dirname(__FILE__) . '/lib/qqwry.dat';
        if (!$fd = @fopen($ipdatafile, 'rb')) {
            return 'Invalid IP data file';
        }

        $ip    = explode('.', $ip);
        $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

        if (!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4))) {
            return;
        }

        @$ipbegin = implode('', unpack('L', $DataBegin));
        if ($ipbegin < 0) {
            $ipbegin += pow(2, 32);
        }

        @$ipend = implode('', unpack('L', $DataEnd));
        if ($ipend < 0) {
            $ipend += pow(2, 32);
        }

        $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

        $BeginNum = $ip2num = $ip1num = 0;
        $ipAddr1  = $ipAddr2  = '';
        $EndNum   = $ipAllNum;

        while ($ip1num > $ipNum || $ip2num < $ipNum) {
            $Middle = intval(($EndNum + $BeginNum) / 2);

            fseek($fd, $ipbegin + 7 * $Middle);
            $ipData1 = fread($fd, 4);
            if (strlen($ipData1) < 4) {
                fclose($fd);
                return 'System Error';
            }
            $ip1num = implode('', unpack('L', $ipData1));
            if ($ip1num < 0) {
                $ip1num += pow(2, 32);
            }

            if ($ip1num > $ipNum) {
                $EndNum = $Middle;
                continue;
            }

            $DataSeek = fread($fd, 3);
            if (strlen($DataSeek) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
            fseek($fd, $DataSeek);
            $ipData2 = fread($fd, 4);
            if (strlen($ipData2) < 4) {
                fclose($fd);
                return 'System Error';
            }
            $ip2num = implode('', unpack('L', $ipData2));
            if ($ip2num < 0) {
                $ip2num += pow(2, 32);
            }

            if ($ip2num < $ipNum) {
                if ($Middle == $BeginNum) {
                    fclose($fd);
                    return 'Unknown';
                }
                $BeginNum = $Middle;
            }
        }

        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(1)) {
            $ipSeek = fread($fd, 3);
            if (strlen($ipSeek) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
            fseek($fd, $ipSeek);
            $ipFlag = fread($fd, 1);
        }

        if ($ipFlag == chr(2)) {
            $AddrSeek = fread($fd, 3);
            if (strlen($AddrSeek) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return 'System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }

            $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
            fseek($fd, $AddrSeek);

            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }

        } else {
            fseek($fd, -1, SEEK_CUR);
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr1 .= $char;
            }

            $ipFlag = fread($fd, 1);
            if ($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if (strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return 'System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while (($char = fread($fd, 1)) != chr(0)) {
                $ipAddr2 .= $char;
            }

        }
        fclose($fd);

        if (preg_match('/http/i', $ipAddr2)) {
            $ipAddr2 = '';
        }
        $ipaddr = "$ipAddr1 $ipAddr2";
        $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
        $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
        if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
            $ipaddr = 'Unknown';
        }

        $charset = mb_detect_encoding($ipaddr, array('UTF-8', 'GBK', 'GB2312'));
        $charset = strtolower($charset);
        if ('cp936' == $charset) {
            $charset = 'GBK';
        }
        if ("utf-8" != $charset) {
            $ipaddr = iconv($charset, "UTF-8//IGNORE", $ipaddr);
        }
        return $ipaddr;

    }

}
