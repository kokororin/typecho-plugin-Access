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
        <select style="<?php if(!in_array($request->get('filter', 'all'), ['ip', 'path'])): ?>display: none<?php endif; ?>" name="fuzzy">
          <option <?php if($request->fuzzy != '1'): ?> selected="true"<?php endif; ?>value=""><?php _e('精确匹配'); ?></option>
          <option <?php if($request->fuzzy == '1'): ?> selected="true"<?php endif; ?>value="1"><?php _e('模糊匹配'); ?></option>
        </select>
        <input style="<?php if($request->get('filter', 'all') != 'ip'): ?>display: none<?php endif; ?>" type="text" class="text-s" placeholder="" value="<?= htmlspecialchars($request->ip ?: ''); ?>" name="ip" />
        <select style="<?php if($request->get('filter', 'all') != 'post'): ?>display: none<?php endif; ?>" name="cid">
          <?php foreach ($access->logs['cidList'] as $content):?>
          <option <?php if($request->cid == $content['cid']): ?> selected="true"<?php endif; ?>value="<?= $content['cid'];?>"><?= $content['title'];?> (<?= $content['count'];?>)</option>
          <?php endforeach;?>
        </select>
        <input style="<?php if($request->get('filter', 'all') != 'path'): ?>display: none<?php endif; ?>" type="text" class="text-s" placeholder="" value="<?= htmlspecialchars($request->path ?: ''); ?>" name="path" />
        <select name="type">
          <option <?php if($request->type == 1): ?> selected="true"<?php endif; ?>value="1"><?php _e('默认(仅人类)'); ?></option>
          <option <?php if($request->type == 2): ?> selected="true"<?php endif; ?>value="2"><?php _e('仅爬虫'); ?></option>
          <option <?php if($request->type == 3): ?> selected="true"<?php endif; ?>value="3"><?php _e('所有'); ?></option>
        </select>
          <input type="hidden" name="page" value="1">
          <button type="button" class="btn btn-s"><?php _e('筛选'); ?></button>
      </div>
    </form>
  </div>

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
          <td><a data-action="ip" data-ip="<?= $log['ip'] ?>" href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&filter=ip&ip=' . $log['ip'] . '&type='. $request->type); ?>"><?= $log['ip']; ?></td>
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
  </form>

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
  </div>
</div>

<script src="<?php $options->pluginUrl('Access/page/sweetalert.min.js')?>"></script>
<script type="text/javascript" defer src="<?php $options->pluginUrl('Access/page/routes/logs/index.js')?>"></script>
