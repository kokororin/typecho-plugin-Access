<?php
include 'common.php';
include 'header.php';
include 'menu.php';
require dirname(__FILE__) . '/../Access.php';
$extend = Access_Extend::getInstance();
?>

<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
           <h2><?php echo $extend->title;?></h2>
        </div>
        <div class="row typecho-page-main" role="main">
             <div class="col-mb-12">
                <ul class="typecho-option-tabs fix-tabs clearfix">
                    <li<?=($extend->action == 'overview' ? ' class="current"' : '')?>><a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=overview'); ?>"><?php _e('访问概览'); ?></a></li>
                    <li<?=($extend->action == 'logs' ? ' class="current"' : '')?>><a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=logs'); ?>"><?php _e('访问日志'); ?></a></li>
                    <li><a href="<?php $options->adminUrl('options-plugin.php?config=Access') ?>"><?php _e('插件设置'); ?></a></li>
                </ul>
            </div>

            <?php if($extend->action == 'logs'):?>

            <div class="col-mb-12 typecho-list">
                <div class="typecho-list-operate clearfix">
                    <form method="get">
                        
                        <div class="operate">
                            <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all" /></label>
                            <div class="btn-group btn-drop">
                                <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a lang="<?php _e('你确认要删除这些记录吗?'); ?>" href="javascript:alert('这个功能并没有开发呢')"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>  
                        </div>
                        
                        <div class="search" role="search">
                            <input type="hidden" value="<?php echo $request->get('panel'); ?>" name="panel" />
                            <?php if(isset($request->page)): ?>
                            <input type="hidden" value="<?php echo $request->get('page'); ?>" name="page" />
                            <?php endif; ?>
                            <select name="type">
                                <option <?php if($request->type == 1): ?> selected="true"<?php endif; ?>value="1"><?php _e('默认(仅人类)'); ?></option>
                                <option <?php if($request->type == 2): ?> selected="true"<?php endif; ?>value="2"><?php _e('仅爬虫'); ?></option>
                                <option <?php if($request->type == 3): ?> selected="true"<?php endif; ?>value="3"><?php _e('所有'); ?></option>
                            </select>
                            <button type="submit" class="btn btn-s"><?php _e('筛选'); ?></button>
                        </div>
                    </form>
                </div><!-- end .typecho-list-operate -->
            
                <form method="post" name="manage_posts" class="operate-form">
                <div class="typecho-table-wrap">
                    <table class="typecho-list-table">
                        <colgroup>
                            <col width="5"/>
                            <col width="30%"/>
                            <col width="25%"/>
                            <col width=""/>
                            <col width=""/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th> </th>
                                <th><?php _e('受访地址'); ?></th>
                                <th><?php _e('UA'); ?></th>
                                <th><?php _e('IP地址'); ?></th>
                                <th><?php _e('日期'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($extend->logs['list'])): ?>
                            <?php foreach ($extend->logs['list'] as $log): ?>
                            <tr id="<?php echo $log['id']; ?>">
                                <td><input type="checkbox" value="<?php echo $log['id']; ?>" name="id[]"/></td>
                                <td><a href="<?php echo str_replace("%23", "#", $log['url']); ?>"><?php echo urldecode(str_replace("%23", "#", $log['url'])); ?></a></td>
                                <td><a data-action="ua" href="#" title="<?php echo $log['ua'];?>"><?php echo $extend->parseUA($log['ua']); ?></a></td>
                                <td><a data-action="ip" data-address="<?php echo $extend->getAddress($log['ip']);?>" href="#"><?php echo $log['ip']; ?></a></td>
                                <td><?php echo date('Y-m-d H:i:s',$log['date']); ?></td>                   
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
                                    <li><a lang="<?php _e('你确认要删除这些记录吗?'); ?>" href="javascript:alert('这个功能并没有开发呢')"><?php _e('删除'); ?></a></li>
                                </ul>
                            </div>         
                        </div>
                        

                        <?php if($extend->logs['rows'] > 1): ?>
                        <ul class="typecho-pager">
                            <?php echo $extend->logs['page']; ?>
                        </ul>
                        <?php endif; ?>
                    </form>
                </div><!-- end .typecho-list-operate -->
            </div><!-- end .typecho-list -->

            <?php elseif($extend->action == 'overview'):?>

           
                
            <div class="col-mb-12 typecho-list">

               <h4 class="typecho-list-table-title">总记录表格</h4>
            
                <div class="typecho-table-wrap">
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
                            <tr>
                                <td>今日</td>
                                <td><?php echo $extend->overview['pv']['today']['total'];?></td>
                                <td><?php echo $extend->overview['uv']['today']['total'];?></td>
                                <td><?php echo $extend->overview['ip']['today']['total'];?></td>            
                            </tr>
                            <tr>
                                <td>昨日</td>
                                <td><?php echo $extend->overview['pv']['yesterday']['total'];?></td>
                                <td><?php echo $extend->overview['uv']['yesterday']['total'];?></td>
                                <td><?php echo $extend->overview['ip']['yesterday']['total'];?></td>         
                            </tr>
                            <tr>
                                <td>总计</td>
                                <td><?php echo $extend->overview['pv']['all']['total'];?></td>
                                <td><?php echo $extend->overview['uv']['all']['total'];?></td>
                                <td><?php echo $extend->overview['ip']['all']['total'];?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                 <h4 class="typecho-list-table-title">今日图表</h4>

                  <div class="typecho-table-wrap" id="chart">
                    
                </div>

            </div><!-- end .typecho-list -->


            <?php endif;?>

        </div><!-- end .typecho-page-main -->
    </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
?>
<script type="text/javascript">
$(document).ready(function() {
    $('a[data-action="ip"]').click(function() {
        alert($(this).data('address'));
        return false;
    });

    $('a[data-action="ua"]').click(function() {
        alert($.trim($(this).attr('title')));
        return false;
    });
});
</script>
<?php if($extend->action == 'overview'):?>
<script type="text/javascript">
$(document).ready(function() {
    $('#chart').highcharts({
        title: {
            text: '<?php echo $extend->overview['chart']['title']['text'];?>',
            x: -20 //center
        },
        subtitle: {
            text: 'Source: Typecho Access',
            x: -20
        },
        xAxis: {
            categories: <?php echo $extend->overview['chart']['xAxis']['categories'];?>
        },
        yAxis: {
            title: {
                text: '数量'
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: ''
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: [{
            name: 'PV',
            data: <?php echo $extend->overview['chart']['series']['pv'];?>
        }, {
            name: 'UV',
            data: <?php echo $extend->overview['chart']['series']['uv'];?>
        }, {
            name: 'IP',
            data: <?php echo $extend->overview['chart']['series']['ip'];?>
        }]
    });
});

</script>
<script src="<?php $options->pluginUrl('Access/lib/highcharts/js/highcharts.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/lib/highcharts/js/modules/exporting.js')?>"></script>
<?php endif;?>
<?php
include 'footer.php';
?>