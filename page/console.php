<?php
include_once 'common.php';
include 'header.php';
include 'menu.php';
require_once __DIR__ . '/../Access_Bootstrap.php';
$access = new Access_Core();
?>
<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
           <h2><?= $access->title;?></h2>
        </div>
        <div class="row typecho-page-main" role="main">
             <div class="col-mb-12">
                <ul class="typecho-option-tabs fix-tabs clearfix">
                    <li<?=($access->action == 'overview' ? ' class="current"' : '')?>><a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=overview'); ?>"><?php _e('访问概览'); ?></a></li>
                    <li<?=($access->action == 'logs' ? ' class="current"' : '')?>><a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=logs'); ?>"><?php _e('访问日志'); ?></a></li>
                    <li><a href="<?php $options->adminUrl('options-plugin.php?config=Access') ?>"><?php _e('插件设置'); ?></a></li>
                </ul>
            </div>

            <?php if($access->action == 'logs'):?>

            <div class="col-mb-12 typecho-list">
                <div class="typecho-list-operate clearfix">

                    <div class="operate">
                        <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                        <div class="btn-group btn-drop">
                            <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                            <ul class="dropdown-menu">
                                <li><a data-action="delete" href="javascript:;"><?php _e('删除'); ?></a></li>
                            </ul>
                        </div>
                    </div>

                    <form method="get" class="search-form">
                        <div class="search" role="search">
                            <?php if ($request->get('filter', 'all') != 'all'): ?>
                            <a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=logs'); ?>"><?php _e('&laquo; 取消筛选'); ?></a>
                            <?php endif; ?>
                            <input type="hidden" value="<?= $request->get('panel'); ?>" name="panel" />
                            <?php if(isset($request->page)): ?>
                            <input type="hidden" value="<?= $request->get('page'); ?>" name="page" />
                            <?php endif; ?>
                            <select name="filter">
                                <option <?php if($request->filter == 'all'): ?> selected="true"<?php endif; ?>value="all"><?php _e('所有'); ?></option>
                                <option <?php if($request->filter == 'ip'): ?> selected="true"<?php endif; ?>value="ip"><?php _e('按IP'); ?></option>
                                <option <?php if($request->filter == 'post'): ?> selected="true"<?php endif; ?>value="post"><?php _e('按文章'); ?></option>
                                <option <?php if($request->filter == 'path'): ?> selected="true"<?php endif; ?>value="path"><?php _e('按路由'); ?></option>
                            </select>
                            <input style="<?php if($request->get('filter', 'all') != 'ip'): ?>display: none<?php endif; ?>" type="text" class="text-s" placeholder="" value="<?= htmlspecialchars($request->ip); ?>" name="ip" />
                            <select style="<?php if($request->get('filter', 'all') != 'post'): ?>display: none<?php endif; ?>" name="cid">
                                <?php foreach ($access->logs['cidList'] as $content):?>
                                <option <?php if($request->cid == $content['cid']): ?> selected="true"<?php endif; ?>value="<?= $content['cid'];?>"><?= $content['title'];?> (<?= $content['count'];?>)</option>
                                <?php endforeach;?>
                            </select>
                            <input style="<?php if($request->get('filter', 'all') != 'path'): ?>display: none<?php endif; ?>" type="text" class="text-s" placeholder="" value="<?= htmlspecialchars($request->path); ?>" name="path" />
                            <select name="type">
                                <option <?php if($request->type == 1): ?> selected="true"<?php endif; ?>value="1"><?php _e('默认(仅人类)'); ?></option>
                                <option <?php if($request->type == 2): ?> selected="true"<?php endif; ?>value="2"><?php _e('仅爬虫'); ?></option>
                                <option <?php if($request->type == 3): ?> selected="true"<?php endif; ?>value="3"><?php _e('所有'); ?></option>
                            </select>
                                <input type="hidden" name="page" value="1">
                                <button type="button" class="btn btn-s"><?php _e('筛选'); ?></button>
                        </div>
                    </form>
                </div><!-- end .typecho-list-operate -->

                <form method="post" class="operate-form">
                <div class="typecho-table-wrap">
                    <table class="typecho-list-table">
                        <colgroup>
                            <col width="5%"/>
                            <col width="28%"/>
                            <col width="25%"/>
                            <col width="18%"/>
                            <col width="16%"/>
                            <col width="20%"/>
                            <col width="18%"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th> </th>
                                <th><?php _e('受访地址'); ?></th>
                                <th><?php _e('UA'); ?></th>
                                <th><?php _e('IP地址'); ?></th>
                                <th><?php _e('IP属地'); ?></th>
                                <th><?php _e('Referer'); ?></th>
                                <th><?php _e('日期'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($access->logs['list'])): ?>
                            <?php foreach ($access->logs['list'] as $log): ?>
                            <tr id="<?= $log['id']; ?>" data-id="<?= $log['id']; ?>">
                                <td><input type="checkbox" data-id="<?= $log['id']; ?>" value="<?= $log['id']; ?>" name="id[]"/></td>
                                <td><a target="_self" href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&filter=path&path=' . $log['path'] . '&type='. $request->type); ?>"><?= urldecode(str_replace("%23", "#", $log['url'])); ?></a></td>
                                <td><a data-action="ua" href="#" title="<?= $log['ua'];?>"><?= $log['display_name']; ?></a></td>
                                <td><a data-action="ip" data-ip="<?= $access->long2ip($log['ip']); ?>" href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&filter=ip&ip=' . $access->long2ip($log['ip']) . '&type='. $request->type); ?>"><?= $access->long2ip($log['ip']); ?></td>
                                <td><?= $log['ip_loc'] ?></td>
                                <td><a target="_blank" data-action="referer" href="<?= $log['referer']; ?>"><?= $log['referer']; ?></a></td>
                                <td><?= date('Y-m-d H:i:s', $log['time']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6"><h6 class="typecho-list-table-title"><?php _e('当前无日志'); ?></h6></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                </form><!-- end .operate-form -->

                <div class="typecho-list-operate clearfix">
                    <form method="get">

                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a data-action="delete" href="javascript:;"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>
                        </div>


                        <?php if($access->logs['rows'] > 1): ?>
                        <ul class="typecho-pager">
                            <?= $access->logs['page']; ?>
                        </ul>
                        <?php endif; ?>
                    </form>
                </div><!-- end .typecho-list-operate -->
            </div><!-- end .typecho-list -->

            <?php elseif($access->action == 'overview'):?>



            <div class="col-mb-12 typecho-list">

               <h4 class="typecho-list-table-title">访问数表格</h4>

                <div class="typecho-table-wrap" id="tbl-count">
                    <table class="typecho-list-table">
                        <colgroup>
                            <col width="10%"/>
                            <col width="30%"/>
                            <col width="25%"/>
                            <col width=""/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th> </th>
                                <th><?php _e('浏览量(PV)'); ?></th>
                                <th><?php _e('访客数(UV)'); ?></th>
                                <th><?php _e('IP数'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr name="count-today">
                                <td>今日</td>
                                <td>loaging...</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr name="count-yesterday">
                                <td>昨日</td>
                                <td>loaging...</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr name="count-total">
                                <td>总计</td>
                                <td>loaging...</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-mb-12 col-4">
                    <h4 class="typecho-list-table-title">来源域名</h4>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table" id="tbl-referer-domain">
                            <colgroup>
                                <col width="15%"/>
                                <col width="15%"/>
                                <col width="70%"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>次数</th>
                                    <th>来源域名</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>loaging...</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-mb-12 col-8">
                    <h4 class="typecho-list-table-title">来源页</h4>

                    <div class="typecho-table-wrap">
                        <table class="typecho-list-table" id="tbl-referer-url">
                            <colgroup>
                                <col width="15%"/>
                                <col width="15%"/>
                                <col width="70%"/>
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>排名</th>
                                    <th>次数</th>
                                    <th>来源URL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>loaging...</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-mb-12">
                    <h4 class="typecho-list-table-title">文章浏览分析</h4>
                    <div class="typecho-table-wrap" id="pie-article">loading...</div>
                </div>

                <div class="col-mb-12">
                    <h4 class="typecho-list-table-title">访客地域分析</h4>
                    <div class="typecho-table-wrap">
                        <ul class="typecho-option-tabs clearfix">
                            <li><button id="btn-china" class="btn btn-s primary">国内</button></li>
                            <li><button id="btn-inter" class="btn btn-s">国际</button><li>
                        </ul>
                        <div class="typecho-table-wrap" id="bar-location">loading...</div>
                    </div>
                </div>

                <div class="col-mb-12">
                    <h4 class="typecho-list-table-title">今日图表</h4>
                    <div class="typecho-table-wrap" id="chart-today"></div>
                </div>
                
                <div class="col-mb-12">
                    <h4 class="typecho-list-table-title">昨日图表</h4>
                    <div class="typecho-table-wrap" id="chart-yesterday"></div>
                </div>
                
                <div class="col-mb-12">
                    <h4 class="typecho-list-table-title">当月图表</h4>
                    <div class="typecho-table-wrap" id="chart-month"></div>
                </div>
            </div>
            <!-- end .typecho-list -->


            <?php endif;?>

        </div>
        <!-- end .typecho-page-main -->
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
?>
<script type="text/javascript">
$(document).ready(function() {
    $('a[data-action="ua"]').click(function() {
        swal({
            icon: 'info',
            title: 'User-Agent', 
            text: $(this).attr('title').trim()
        });
    });

    $('.dropdown-menu a[data-action="delete"]').click(function() {
        swal({
            title: '你确定?',
            text: '你确认要删除这些记录吗?',
            icon: 'warning',
            buttons: {
                cancel: '算啦',
                confirm: '是的'
            }
        }).then((value) => {
            if(value === true) {
                let ids = [];
                $('.typecho-list-table input[type="checkbox"]').each(function(index, elem) {
                    if (elem.checked) {
                        ids.push($(elem).data('id'));
                    }
                });
                if(ids.length != 0) {
                    $.ajax({
                        url: '/access/log/delete',
                        method: 'post',
                        dataType: 'json',
                        contentType: 'application/json',
                        data: JSON.stringify(ids),
                        success: function(data) {
                            if (data.code == 0) {
                                swal({
                                    icon: 'success',
                                    title: '删除成功',
                                    text: '所选记录已删除'
                                });
                                $.each(ids, function(index, elem) {
                                    $('.typecho-list-table tbody tr[data-id="' + elem + '"]').fadeOut(500).remove();
                                });
                            } else {
                                swal({
                                    icon: 'error',
                                    title: '错误',
                                    text: '删除出错啦'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            swal({
                                icon: 'error',
                                title: '错误',
                                text: '请求错误 code: '+xhr.status
                            });
                        }
                    });
                } else {
                    return swal({
                        icon: 'warning',
                        title: '错误',
                        text: '你并没有勾选任何内容'
                    });
                }
            }
        });
        var $this = $(this);
        $this.parents('.dropdown-menu').hide().prev().removeClass('active');
    });

    var $form = $('form.search-form');
    var $ipInput = $form.find('input[name="ip"]');
    var $cidSelect = $form.find('select[name="cid"]');
    var $pathInput = $form.find('input[name="path"]');
    var $filterSelect = $form.find('select[name="filter"]');

    $filterSelect.on('change', function() {
        $ipInput.removeAttr('placeholder').val('').hide();
        $cidSelect.hide();
        $pathInput.removeAttr('placeholder').val('').hide();

        switch ($filterSelect.val()) {
            case 'ip':
                $ipInput.attr('placeholder', '输入ip').show();
                break;
            case 'post':
                $cidSelect.show();
                break;
            case 'path':
                $pathInput.attr('placeholder', '输入路由').show();
                break;
        }
    });

    $form.find('button[type="button"]').on('click', function() {
        var ipRegex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;

        if ($filterSelect.val() == 'ip' && !ipRegex.test($ipInput.val())) {
            return swal({
                icon: 'error',
                title: '筛选条件错误',
                text: 'IP地址不合法'
            });
        }

        $form.submit();
    });
});
</script>
<script src="<?php $options->pluginUrl('Access/page/sweetalert.min.js')?>"></script>
<?php if($access->action == 'overview'):?>
<script src="<?php $options->pluginUrl('Access/page/highcharts/js/highcharts.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/highcharts/js/modules/exporting.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/highcharts/js/modules/accessibility.js')?>"></script>
<script type="text/javascript">
// html转义
htmlEncode = function(target) {
    return target.replace(/[<>&"]/g, function(c){ 
        return {'<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;'}[c];
    });
}
// 输出图表
printChart = function(target, title, data, avg=null) {
    let pv = [], uv = [], ip = [], time = [];
    for(let i = 0;i < data.length;i++)
        pv.push(data[i].pv), uv.push(data[i].uv), ip.push(data[i].ip), time.push(data[i].time);
    const chart = Highcharts.chart(target, {
        title: {text: title, x: -20},
        subtitle: {text: 'Generate By AccessPlugin', x: -20},
        xAxis: {categories: time, title: {text: '时间', align: 'high'}},
        yAxis: {
            title: {text: '数量'},
            plotLines: (avg !== null) ? [
                // 平均值
                {
                    value: avg.pv,
                    width: 1,
                    color: '#F7A35C'
                }, {
                    value: avg.uv,
                    width: 1,
                    color: '#90ED7D'
                }, {
                    value: avg.ip,
                    width: 1,
                    color: '#7CB5ED'
                }
            ] : null
        },
        tooltip: {valueSuffix: ''},
        plotOptions: {line: {dataLabels: {enabled: true}}},
        series: [
            {
                name: 'PV（浏览）',
                data: pv,
                color: '#F7A35C'
            }, {
                name: 'UV（访客）',
                data: uv,
                color: '#90ED7D'
            }, {
                name: 'IP（地址）',
                data: ip,
                color: '#7CB5ED'
            }
        ]
    });
}
// 输出饼图
printPie = function(target, title, data) {
    let value = [];
    // 生成统计row
    for(let i = 0;i < data.length;i++)
        value.push({name: data[i].title, y: data[i].count, cid: data[i].cid})
    // 计算最大比例并选中
    let maxKey = 0;
    for(let i = 0;i < value.length;i++) {
        if(value[i].y >= value[maxKey].y)
            maxKey = i;
    }
    if(value.length)
        value[maxKey].sliced = true;
    const chart = Highcharts.chart(target, {
        chart: {type: 'pie'},
        title: {text: title},
        subtitle: {text: 'Generate By AccessPlugin'},
        tooltip: {pointFormat: '<b>阅读数: {point.y}<br>占比: {point.percentage:.1f}%<br>cid={point.cid}</b>'},
        accessibility: {point: {valueSuffix: '%'}},
        plotOptions: {pie: {allowPointSelect: true, cursor: 'pointer', dataLabels: {enabled: true, format: '<b>{point.name}</b>: {point.y}'}}},
        series: [{colorByPoint: true, data: value}]
    })
}
// 输出条形统计图
printBar = function(target, title, data) {
    let values = [],
        areas = [];
    for(let i = 0;i < data.length;i++)
        areas.push(data[i].area), values.push(data[i].count);
    return Highcharts.chart(target, {
        chart: {type: 'bar'},
        title: {text: title, x: -20},
        subtitle: {text: 'Generate By AccessPlugin', x: -20},
        xAxis: {categories: areas, title: {text: '地域'}},
        yAxis: {title: {text: '访问次数', align: 'high'}},
        plotOptions: {bar: {dataLabels: {enabled: true}, colorByPoint: true}},
        legend: {enabled: false},
        series: [{name: '访问量', data: values}]
    });
}

updateBar = function(target, title, data) {
    let values = [],
        areas = [];
    for(let i = 0;i < data.length;i++)
        areas.push(data[i].area), values.push(data[i].count);
    target.update({
        title: {text: title},
        xAxis: {categories: areas, title: {text: '地域'}},
        series: [{name: '访问量', data: values}]
    }, true, false, {duration: 800});
}

// 显示来源统计表
printRefererTable = function(target, data) {
    let tbl = $('#'+target+' tbody');
    tbl.children().remove(); // 清空表格
    for(let i = 0;i < data.length;i++) {
        tbl.append('<tr><td>'+(i+1)+'</td><td>'+data[i].count+'</td><td>'+htmlEncode(data[i].value)+'</td><td></td></tr>');
    }
}

// 拉取统计数据
getStatisticData = function(rpc, params=null, callback) {
    $.ajax({
        url: '/access/statistic/view',
        method: 'get',
        dataType: 'json',
        data: {rpc: rpc, ...params},
        async: true,
        success: function(data) {
            if(data.code === 0)
                callback(data.data);
            else
                console.log('rpc='+rpc+'数据获取错误 code='+data.code+' msg='+data.message);
        },
        error: function(xhr, status, error) {
            console.log('rpc='+rpc+' API拉取错误 '+error.toString());
        }
    })
}

// 挂接format方法
Date.prototype.format = function(fmt) {
    var o = {
        '%Y+': this.getFullYear(), // 年
        '%m+': this.getMonth() + 1, //月份
        '%d+': this.getDate(), //日
        '%H+': this.getHours(), //小时
        '%M+': this.getMinutes(), //分
        '%S+': this.getSeconds(), //秒
        '%Q+': Math.floor((this.getMonth() + 3) / 3), //季度
        '%s+': this.getMilliseconds() //毫秒
    };
    for(var k in o)
        if(new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, 
                (RegExp.$1.length == 2) ? 
                    o[k] : (('00' + o[k]).substr(('' + o[k]).length))
            );
    return fmt;
}

$().ready(function() {
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    // 今日计数统计数据
    getStatisticData('count', {type: 'day', time: today.format('%Y-%mm-%dd')} ,function(data) {
        let row = $('#tbl-count tr[name="count-today"] td');
        row.eq(1).text(data.count.pv), row.eq(2).text(data.count.uv), row.eq(3).text(data.count.ip);
    });
    // 昨日计数统计数据
    getStatisticData('count', {type: 'day', time: yesterday.format('%Y-%mm-%dd')} ,function(data) {
        let row = $('#tbl-count tr[name="count-yesterday"] td');
        row.eq(1).text(data.count.pv), row.eq(2).text(data.count.uv), row.eq(3).text(data.count.ip);
    });
    // 总计数统计数据
    getStatisticData('count', {type: 'total'} ,function(data) {
        let row = $('#tbl-count tr[name="count-total"] td');
        row.eq(1).text(data.count.pv), row.eq(2).text(data.count.uv), row.eq(3).text(data.count.ip);
    });

    // 来源域名统计
    getStatisticData('referer', {type: 'domain', pn: 1, ps: 10} ,function(data) {
        printRefererTable('tbl-referer-domain', data);
    });
    // 来源页统计
    getStatisticData('referer', {type: 'url', pn: 1, ps: 10} ,function(data) {
        printRefererTable('tbl-referer-url', data);
    });

    // 文章浏览比例统计
    getStatisticData('article', {ps: 10} ,function(data) {
        printPie('pie-article', '最受欢迎的文章', data);
    });

    // 浏览地域分析图
    getStatisticData('location', {cate: 'china', ps: 10} ,function(data) {
        const chartBar = printBar('bar-location', '国内访问地域分析', data);
        // 国际按钮
        $('#btn-inter').click(function(){
            $('#btn-inter').addClass('primary');
            $('#btn-china').removeClass('primary');
            getStatisticData('location', {cate: 'inter', ps: 10} ,function(data) {
                updateBar(chartBar, '国际访问地域分析', data)
            });
        });
        // 国内按钮
        $('#btn-china').click(function(){
            $('#btn-china').addClass('primary');
            $('#btn-inter').removeClass('primary');
            getStatisticData('location', {cate: 'china', ps: 10} ,function(data) {
                updateBar(chartBar, '国内访问地域分析', data)
            });
        });
    });

    // 当天访问图表
    getStatisticData('chart', {type: 'day', time: today.format('%Y-%mm-%dd')} ,function(data) {
        printChart('chart-today', data.dst+' 统计', data.chart, data.avg);
    });
    // 昨天访问图表
    getStatisticData('chart', {type: 'day', time: yesterday.format('%Y-%mm-%dd')} ,function(data) {
        printChart('chart-yesterday', data.dst+' 统计', data.chart, data.avg);
    });
    // 当月访问图表
    getStatisticData('chart', {type: 'month', time: today.format('%Y-%mm')} ,function(data) {
        printChart('chart-month', data.dst+' 统计', data.chart, data.avg);
    });
});

</script>
<?php endif;?>
<?php
include 'footer.php';
?>
