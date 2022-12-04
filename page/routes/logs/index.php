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

    <div class="search typecho-access-logs-search" role="search">
      <button data-action="apply" type="button" class="btn btn-s"><?php _e('刷新'); ?></button>
      <button data-action="switch-filter" type="button" class="btn btn-s"><?php _e('筛选'); ?></button>
      <div class="typecho-access-logs-filter">
        <div class="typecho-access-logs-filter-item">
          <label class="typecho-access-logs-filter-item__label">匹配方式</label>
          <select class="typecho-access-logs-filter-item__content" name="filter-fuzzy">
            <option value=""><?php _e('精确匹配'); ?></option>
            <option value="1"><?php _e('模糊匹配'); ?></option>
          </select>
        </div>
        <div class="typecho-access-logs-filter-item">
          <label class="typecho-access-logs-filter-item__label">UA</label>
          <input class="typecho-access-logs-filter-item__content" type="text" class="text-s" name="filter-ua" autocomplete="off" />
        </div>
        <div class="typecho-access-logs-filter-item">
          <label class="typecho-access-logs-filter-item__label">IP</label>
          <input class="typecho-access-logs-filter-item__content" type="text" class="text-s" name="filter-ip" autocomplete="off" />
        </div>
        <div class="typecho-access-logs-filter-item">
          <label class="typecho-access-logs-filter-item__label">文章ID</label>
          <select class="typecho-access-logs-filter-item__content" name="filter-cid">
            <option value=""><?php _e('不限'); ?></option>
          </select>
        </div>
        <div class="typecho-access-logs-filter-item">
          <label class="typecho-access-logs-filter-item__label">受访地址</label>
          <input class="typecho-access-logs-filter-item__content" type="text" class="text-s" name="filter-path" autocomplete="off" />
        </div>
        <div class="typecho-access-logs-filter-item">
          <label class="typecho-access-logs-filter-item__label">访客类型</label>
          <select class="typecho-access-logs-filter-item__content" name="filter-robot">
            <option value="0"><?php _e('默认(仅人类)'); ?></option>
            <option value="1"><?php _e('仅爬虫'); ?></option>
            <option value=""><?php _e('所有'); ?></option>
          </select>
        </div>
        <div class="typecho-access-logs-filter-apply">
          <button class="btn btn-m typecho-access-logs-filter-apply__btn" data-action="reset" type="button"><?php _e('重 置'); ?></button>
          <button class="btn btn-m typecho-access-logs-filter-apply__btn" data-action="apply" type="button"><?php _e('应 用'); ?></button>
        </div>
      </div>
    </div>
  </div>

  <div class="typecho-table-wrap">
    <table class="typecho-list-table">
      <colgroup>
        <col width="5%"/>
        <col width="28%"/>
        <col width="25%"/>
        <col width="18%"/>
        <col width="14%"/>
        <col width="20%"/>
        <col width="20%"/>
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
        <tr>
          <td colspan="7"><h6 class="typecho-list-table-title">loading</h6></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="typecho-list-operate clearfix">
    <div class="operate">
      <label>
        <i class="sr-only"><?php _e('全选'); ?></i>
        <input type="checkbox" class="typecho-table-select-all" />
      </label>
      <div class="btn-group btn-drop">
        <button class="btn dropdown-toggle btn-s" type="button">
          <i class="sr-only"><?php _e('操作'); ?></i>
          <span><?php _e('选中项'); ?></span>
          <i class="i-caret-down"></i>
        </button>
        <ul class="dropdown-menu">
          <li>
            <a data-action="delete" href="javascript:;"><?php _e('删除'); ?></a>
          </li>
        </ul>
      </div>
    </div>

    <div class="typecho-access-logs-pagination-jump">
      <input class="text-s typecho-access-logs-pagination-jump__number" type="text" name="page-jump" autocomplete="off" />
      <span class="typecho-access-logs-pagination-jump__text">/</span>
      <span class="typecho-access-logs-pagination-jump__total">loading</span>
    </div>
    <ul class="typecho-pager"></ul>
  </div>
</div>

<script src="<?php $options->pluginUrl('Access/page/components/object.assign/index.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/components/sweetalert/index.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/components/dayjs/index.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.css')?>">
<script defer src="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/routes/logs/index.css')?>">
<script defer src="<?php $options->pluginUrl('Access/page/routes/logs/index.js')?>"></script>
