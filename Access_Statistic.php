<?php
if (!defined('__ACCESS_PLUGIN_ROOT__')) {
    throw new Exception('Boostrap file not found');
}

class Access_Statistic {
    private $db;
    private $request;

    public function __construct($request) {
        $this->db = Typecho_Db::get();
        $this->request = $request;
    }

    /**
     * 获取计数分析
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    private function count(): ?array {
        $resp = [];
        $type = $this->request->get('type', 'total'); # 统计类型
        $dstTime = $this->request->get('time'); # 目标时间
        switch($type) {
            # 总计数据
            case 'total':
                $startTime = 0;
                $endTime = 0;
                break;
            # 按天统计
            case 'day':
                if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dstTime))
                    throw new Exception('Bad Request', 400);
                $startTime = strtotime(date("{$dstTime} 00:00:00"));
                $endTime = strtotime(date("{$dstTime} 23:59:59"));
                break;
            # 按月统计
            case 'month':
                if(!preg_match('/^\d{4}-\d{2}$/', $dstTime))
                    throw new Exception('Bad Request', 400);
                [$year, $month] = explode('-', $dstTime);
                $monthDays = date('t', mktime(0, 0, 0, intval($month), 1, intval($year))); # 计算当月天数
                $startTime = strtotime(date("{$dstTime}-01 00:00:00"));
                $endTime = strtotime(date("{$dstTime}-{$monthDays} 23:59:59"));
                break;
            default:
                throw new Exception('Bad Request', 400);
        }
        $resp['type'] = $type;
        $resp['dst'] = $dstTime;
        # ip数
        $subQuery = $this->db
            ->select('DISTINCT ip')
            ->from('table.access_log');
        if ($endTime > 0) {
            $subQuery->where("time >= ? AND time <= ?", $startTime, $endTime);
        }
        if(method_exists($subQuery, 'prepare'))
            $subQuery = $subQuery->prepare($subQuery);
        $resp['count']['ip'] = intval($this->db->fetchRow(
            $this->db
            ->select('COUNT(1) AS cnt')
            ->from('(' . $subQuery . ') AS tmp')
        )['cnt']);
        # 访客数
        $subQuery = $this->db
            ->select('DISTINCT ip, ua')
            ->from('table.access_log');
        if ($endTime > 0) {
            $subQuery->where("time >= ? AND time <= ?", $startTime, $endTime);
        }
        if(method_exists($subQuery, 'prepare'))
            $subQuery = $subQuery->prepare($subQuery);
        $resp['count']['uv'] = intval($this->db->fetchRow(
            $this->db
            ->select('COUNT(1) AS cnt')
            ->from('(' . $subQuery . ') AS tmp')
        )['cnt']);
        # 浏览数
        $subQuery = $this->db
            ->select('COUNT(1) AS cnt')
            ->from('table.access_log');
        if ($endTime > 0) {
            $subQuery->where("time >= ? AND time <= ?", $startTime, $endTime);
        }
        $resp['count']['pv'] = intval($this->db->fetchRow($subQuery)['cnt']);
        return $resp;
    }

    /**
     * 获取文章访问统计
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    private function article(): ?array {
        $resp = [];
        $ps = $this->request->get('ps', 10); # 页大小
        # 统计文章浏览比例
        foreach(
            $this->db->fetchAll(
                $this->db
                ->select('content_id AS cid, table.contents.title AS title, COUNT(1) AS cnt')
                ->from('table.access_log')
                ->join('table.contents', 'content_id = table.contents.cid', Typecho_Db::INNER_JOIN)
                ->where('IFNULL(content_id, 0)')
                ->group('content_id')
                ->order('cnt', Typecho_Db::SORT_DESC)
                ->limit($ps)
            ) as $i
        ) {
            $resp[] = [
                'cid' => intval($i['cid']),
                'title' => $i['title'],
                'count' => intval($i['cnt'])
            ];
        }
        return $resp;
    }

    /**
     * 获取访问地域统计
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    private function location(): ?array {
        $resp = [];
        $ps = $this->request->get('ps', 10); # 页大小
        $cate = $this->request->get('cate'); # 类型
        switch($cate) {
            case 'china':
                # 国内
                $fetchData = $this->db->fetchAll(
                    $this->db
                    ->select("IF(ip_province = '中国', '国内未明确', ip_province) AS area, COUNT(1) AS cnt")
                    ->from('table.access_log')
                    ->where("ip_country = '中国'")
                    ->group('area')
                    ->order('cnt', Typecho_Db::SORT_DESC)
                    ->limit($ps)
                );
                break;
            case 'inter':
                # 国际
                $fetchData = $this->db->fetchAll(
                    $this->db
                    ->select("ip_country AS area, COUNT(1) AS cnt")
                    ->from('table.access_log')
                    ->group('area')
                    ->order('cnt', Typecho_Db::SORT_DESC)
                    ->limit(15)
                );
                break;
            default:
                throw new Exception('Bad Request', 400);
        }
        foreach($fetchData as $row) {
            $resp[] = [
                'area' => $row['area'],
                'count' => intval($row['cnt'])
            ];
        }
        return $resp;
    }

    /**
     * 获取访问量图表数据
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    private function chart(): ?array {
        $resp = [];
        $type = $this->request->get('type'); # 统计类型
        $dstTime = $this->request->get('time'); # 目标时间
        switch($type) {
            # 按天统计
            case 'day':
                if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dstTime))
                    throw new Exception('Bad Request', 400);
                $loopStart = 0;
                $loopEnd = 23;
                break;
            # 按月统计
            case 'month':
                if(!preg_match('/^\d{4}-\d{2}$/', $dstTime))
                    throw new Exception('Bad Request', 400);
                [$year, $month] = explode('-', $dstTime);
                $loopStart = 1;
                $loopEnd = date('t', mktime(0, 0, 0, intval($month), 1, intval($year))); # 计算当月天数
                break;
            default:
                throw new Exception('Bad Request', 400);
        }
        $resp['type'] = $type;
        $resp['dst'] = $dstTime;

        foreach(range($loopStart, $loopEnd) as $i) {
            $chart = [];
            switch($type) {
                case 'day':
                    $startTime = strtotime(date("{$dstTime} {$i}:00:00"));
                    $endTime = strtotime(date("{$dstTime} {$i}:59:59"));
                    break;
                case 'month':
                    $startTime = strtotime(date("{$dstTime}-{$i} 00:00:00"));
                    $endTime = strtotime(date("{$dstTime}-{$i} 23:59:59"));
                    break;
            }
            $chart['time'] = $i;
            # ip数
            $subQuery = $this->db
                ->select('DISTINCT ip')
                ->from('table.access_log')
                ->where('time >= ? AND time <= ?', $startTime, $endTime);
            if(method_exists($subQuery, 'prepare'))
                $subQuery = $subQuery->prepare($subQuery);
            $chart['ip'] = intval($this->db->fetchRow(
                $this->db
                ->select('COUNT(1) AS count')
                ->from('(' . $subQuery . ') AS tmp')
            )['count']);
            # 访客数
            $subQuery = $this->db
                ->select('DISTINCT ip,ua')
                ->from('table.access_log')
                ->where('time >= ? AND time <= ?', $startTime, $endTime);
            if(method_exists($subQuery, 'prepare'))
                $subQuery = $subQuery->prepare($subQuery);
            $chart['uv'] = intval($this->db->fetchRow(
                $this->db
                ->select('COUNT(1) AS count')
                ->from('(' . $subQuery . ') AS tmp')
            )['count']);
            # 浏览数
            $chart['pv'] = intval($this->db->fetchRow(
                $this->db
                ->select('COUNT(1) AS count')
                ->from('table.access_log')
                ->where('time >= ? AND time <= ?', $startTime, $endTime)
            )['count']);
            $resp['chart'][] = $chart;
        }

        # 计算各平均值
        $pvSum = 0;
        $uvSum = 0;
        $ipSum = 0;
        $cnt = count($resp['chart']);
        foreach($resp['chart'] as $i) {
            $pvSum += $i['pv'];
            $uvSum += $i['uv'];
            $ipSum += $i['ip'];
        }
        $resp['avg']['pv'] = round($pvSum / $cnt, 2);
        $resp['avg']['uv'] = round($uvSum / $cnt, 2);
        $resp['avg']['ip'] = round($ipSum / $cnt, 2);

        return $resp;
    }

    /**
     * 获取来源统计
     *
     * @access private
     * @return ?array
     * @throws Exception
     */
    private function referer(): ?array {
        $resp = [];
        $type = $this->request->get('type', 'url'); # 统计类型
        $ps = $this->request->get('ps', 10);
        $pn = $this->request->get('pn', 1);
        switch($type) {
            case 'url':
                $fetchData = $this->db->fetchAll(
                    $this->db
                    ->select('DISTINCT entrypoint AS value, COUNT(1) as cnt')
                    ->from('table.access_log')
                    ->where("entrypoint != ''")
                    ->group('entrypoint')
                    ->order('cnt', Typecho_Db::SORT_DESC)
                    ->page($pn, $ps)
                );
                break;
            case 'domain':
                $fetchData = $this->db->fetchAll($this->db
                    ->select('DISTINCT entrypoint_domain AS value, COUNT(1) as cnt')
                    ->from('table.access_log')
                    ->where("entrypoint_domain != ''")
                    ->group('entrypoint_domain')
                    ->order('cnt', Typecho_Db::SORT_DESC)
                    ->page($pn, $ps)
                );
                break;
            default:
                throw new Exception('Bad Request', 400);
        }
        foreach($fetchData as $i) {
            $resp[] = [
                'value' => ($type == 'url') ? urldecode($i['value']) : $i['value'],
                'count' => $i['cnt']
            ];
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
        if(!method_exists($this, $rpcType) || in_array($rpcType, ['__construct', 'invoke']))
            throw new Exception('Bad Request', 400);
        return $this->$rpcType(); # 方法指针
    }
}
