<script src="<?php $options->pluginUrl('Access/page/components/sweetalert/index.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.css')?>">
<script defer src="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/routes/migration/index.css')?>">
<script defer src="<?php $options->pluginUrl('Access/page/routes/migration/index.js')?>"></script>

<div class="typecho-access-migration-main">
  <div class="typecho-access-migration-summary">
    <span>存在历史数据：</span>
    <span id="ancient-logs-count">loading</span>
    <span>条</span>
  </div>

  <button data-action="migrate" type="button" class="typecho-access-migration-btn"><?php _e('开始迁移'); ?></button>
</div>
