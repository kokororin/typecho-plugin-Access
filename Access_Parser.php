<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Boostrap file not found');
}

class Access_Parser
{
    public $bots = array(
        'TencentTraveler',
        'Baiduspider',
        'BaiduGame',
        'Googlebot',
        'msnbot',
        'Sosospider+',
        'Sogou web spider',
        'ia_archiver',
        'Yahoo! Slurp',
        'YoudaoBot',
        'Yahoo Slurp',
        'MSNBot',
        'Java (Often spam bot)',
        'BaiDuSpider',
        'Voila',
        'Yandex bot',
        'BSpider',
        'twiceler',
        'Sogou Spider',
        'Speedy Spider',
        'Google AdSense',
        'Heritrix',
        'Python-urllib',
        'Alexa (IA Archiver)',
        'Ask',
        'Exabot',
        'Custo',
        'OutfoxBot/YodaoBot',
        'yacy',
        'SurveyBot',
        'legs',
        'lwp-trivial',
        'Nutch',
        'StackRambler',
        'The web archive (IA Archiver)',
        'Perl tool',
        'MJ12bot',
        'Netcraft',
        'MSIECrawler',
        'WGet tools',
        'larbin',
        'Fish search',
        'crawler',
        'bingbot',
    );

    protected $currentBot = null;

    public function getBrowser($ua)
    {
        $os = null;
        if ($this->isBot($ua)) {
            return $this->currentBot;
        } elseif (preg_match('/Windows NT 6.0/i', $ua)) {
            $os = 'Windows Vista';
        } elseif (preg_match('/Windows NT 6.1/i', $ua)) {
            $os = 'Windows 7';
        } elseif (preg_match('/Windows NT 6.2/i', $ua)) {
            $os = 'Windows 8';
        } elseif (preg_match('/Windows NT 6.3/i', $ua)) {
            $os = 'Windows 8.1';
        } elseif (preg_match('/Windows NT 10.0/i', $ua)) {
            $os = 'Windows 10';
        } elseif (preg_match('/Windows NT 5.1/i', $ua)) {
            $os = 'Windows XP';
        } elseif (preg_match('/Windows NT 5.2/i', $ua) && preg_match('/Win64/i', $ua)) {
            $os = 'Windows XP 64 bit';
        } elseif (preg_match('/Android ([0-9.]+)/i', $ua, $matches)) {
            $os = 'Android ' . $matches[1];
        } elseif (preg_match('/iPhone OS ([_0-9]+)/i', $ua, $matches)) {
            $os = 'iPhone ' . $matches[1];
        } elseif (preg_match('/Ubuntu/i', $ua, $matches)) {
            $os = 'Ubuntu ';
        } elseif (preg_match('/Mac OS X ([0-9_]+)/i', $ua, $matches)) {
            $os = 'Mac OS X ' . $matches[1];
        } elseif (preg_match('/Linux/i', $ua, $matches)) {
            $os = 'Linux';
        } else {
            $os = '未知';
        }

        if ($this->isBot($ua)) {
            return $this->currentBot;
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
        return $os . ' / ' . $browser;
    }

    public function isBot($ua)
    {
        $ua = $this->filter($ua);
        if (!empty($ua)) {
            foreach ($this->bots as $val) {
                $str = $this->filter($val);
                if (strpos($ua, $str) !== false) {
                    $this->currentBot = $str;
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    public function filter($str)
    {
        return $this->removeSpace(strtolower($str));
    }

    protected function removeSpace($str)
    {
        return preg_replace('/\s+/', '', $str);
    }
}
