<?php
include_once 'common.php';
include 'header.php';
include 'menu.php';
require_once __DIR__ . '/../Access_Bootstrap.php';
$access = new Access_Core();
?>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/components/bentogrid/index.css')?>">
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/console.css')?>">
<div class="main">
  <div class="body container">
    <div>
      <h2 class="typecho-access-console-title"><?= $access->title;?></h2>
    </div>
    <div class="bento-row" role="main">
      <div class="bento-col-mb-12">
        <ul class="typecho-option-tabs fix-tabs clearfix">
          <li<?=($access->action == 'overview' ? ' class="current"' : '')?>><a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=overview'); ?>"><?php _e('访问概览'); ?></a></li>
          <li<?=($access->action == 'logs' ? ' class="current"' : '')?>><a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=logs'); ?>"><?php _e('访问日志'); ?></a></li>
          <?php if ($access->hasMigration): ?>
          <li<?=($access->action == 'migration' ? ' class="current"' : '')?>><a href="<?php $options->adminUrl('extending.php?panel=' . Access_Plugin::$panel . '&action=migration'); ?>"><?php _e('数据迁移'); ?></a></li>
          <?php endif ?>
          <li><a href="<?php $options->adminUrl('options-plugin.php?config=Access') ?>"><?php _e('插件设置'); ?></a></li>
        </ul>
      </div>
      <div class="bento-clearfix"></div>
      <?php include("routes/{$access->action}/index.php") ?>
    </div>
  </div>
</div>

<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>
