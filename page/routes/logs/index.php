<script src="<?php $options->pluginUrl('Access/page/components/object.assign/index.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/components/sweetalert/index.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/components/dayjs/index.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.css')?>">
<script defer src="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/routes/logs/index.css')?>">
<script defer src="<?php $options->pluginUrl('Access/page/routes/logs/index.js')?>"></script>

<div class="bento-col-mb-12 typecho-access-logs-main">
  <div class="typecho-access-logs-controls">
    <div class="typecho-access-logs-controls__left">
      <div class="typecho-access-logs-dropdown">
        <button class="typecho-access-logs-btn typecho-access-logs-btn-s typecho-access-logs-dropdown-toggle" type="button">
          <span><?php _e('选中项'); ?></span>
          <div class="typecho-access-logs-dropdown-btn__icon"><i class="i-caret-down"></i></div>
        </button>
        <ul class="typecho-access-logs-dropdown-content">
          <li><button class="typecho-access-logs-btn typecho-access-logs-btn--s typecho-access-logs-btn--warn" data-action="select-delete"><?php _e('删 除'); ?></button></li>
        </ul>
      </div>
    </div>

    <div class="typecho-access-logs-controls__right typecho-access-logs-search" role="search">
      <button data-action="filter-apply" type="button" class="typecho-access-logs-btn typecho-access-logs-btn--s"><?php _e('刷 新'); ?></button>
      <button data-action="switch-filter" type="button" class="typecho-access-logs-btn typecho-access-logs-btn--s"><?php _e('筛 选'); ?></button>
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
            <option value="0"><?php _e('仅人类'); ?></option>
            <option value="1"><?php _e('仅爬虫'); ?></option>
            <option value=""><?php _e('所有'); ?></option>
          </select>
        </div>
        <div class="typecho-access-logs-filter-item">
          <label class="typecho-access-logs-filter-item__label">智能判断</label>
          <select class="typecho-access-logs-filter-item__content" name="filter-preset">
            <option value=""><?php _e('无'); ?></option>
            <option value="robot"><?php _e('疑似爬虫'); ?></option>
            <option value="script"><?php _e('疑似脚本'); ?></option>
          </select>
        </div>
        <div class="typecho-access-logs-filter-apply">
          <div>
            <button class="typecho-access-logs-btn typecho-access-logs-btn--m typecho-access-logs-btn--warn typecho-access-logs-filter-apply__btn" data-action="filter-delete" type="button"><?php _e('批量删除'); ?></button>
          </div>
          <div>
            <button class="typecho-access-logs-btn typecho-access-logs-btn--m typecho-access-logs-filter-apply__btn" data-action="filter-reset" type="button"><?php _e('重 置'); ?></button>
            <button class="typecho-access-logs-btn typecho-access-logs-btn--m typecho-access-logs-btn--primary typecho-access-logs-filter-apply__btn" data-action="filter-apply" type="button"><?php _e('应 用'); ?></button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="typecho-access-logs-table-wrap">
    <table class="typecho-access-logs-list-table">
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
          <th style="text-align: center">
            <label class="typecho-access-logs-select-all">
              <i class="sr-only"><?php _e('全选'); ?></i>
              <input type="checkbox" class="typecho-access-logs-list-table-select-all form-check-input" />
            </label>
          </th>
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
          <td colspan="7"><h6 class="typecho-access-logs-list-table-title">loading</h6></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="typecho-access-logs-controls">
    <div class="typecho-access-logs-controls__left">
      <div class="typecho-access-logs-dropdown">
        <button class="typecho-access-logs-btn typecho-access-logs-btn-s typecho-access-logs-dropdown-toggle" type="button">
          <span><?php _e('选中项'); ?></span>
          <div class="typecho-access-logs-dropdown-btn__icon"><i class="i-caret-down"></i></div>
        </button>
        <ul class="typecho-access-logs-dropdown-content">
          <li><button class="typecho-access-logs-btn typecho-access-logs-btn--s typecho-access-logs-btn--warn" data-action="select-delete"><?php _e('删 除'); ?></button></li>
        </ul>
      </div>
    </div>

    <div class="typecho-access-logs-controls__right">
      <ul class="typecho-access-logs-pagination"></ul>
      <div class="typecho-access-logs-pagination-jump">
        <input class="text-s typecho-access-logs-pagination-jump__number" type="text" name="page-jump" autocomplete="off" />
        <span class="typecho-access-logs-pagination-jump__text">/</span>
        <span class="typecho-access-logs-pagination-jump__total">loading</span>
      </div>
    </div>
  </div>
</div>
