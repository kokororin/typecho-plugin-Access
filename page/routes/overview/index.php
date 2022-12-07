
<script src="<?php $options->pluginUrl('Access/page/highcharts/js/highcharts.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/highcharts/js/modules/exporting.js')?>"></script>
<script src="<?php $options->pluginUrl('Access/page/highcharts/js/modules/accessibility.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.css')?>">
<script defer src="<?php $options->pluginUrl('Access/page/components/loadingmodal/index.js')?>"></script>
<link rel="stylesheet" href="<?php $options->pluginUrl('Access/page/routes/overview/index.css')?>">
<script type="text/javascript" defer src="<?php $options->pluginUrl('Access/page/routes/overview/index.js')?>"></script>
<div class="typecho-access-overview-main bento-col-mb-12">
  <h4 class="typecho-access-overview-table-title">访问数表格</h4>
  <div class="typecho-access-overview-table-wrap" id="tbl-count">
    <table class="typecho-access-overview-list-table">
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
          <td>loading...</td>
          <td></td>
          <td></td>
        </tr>
        <tr name="count-yesterday">
          <td>昨日</td>
          <td>loading...</td>
          <td></td>
          <td></td>
        </tr>
        <tr name="count-total">
          <td>总计</td>
          <td>loading...</td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="bento-col-mb-12 bento-col-4 typecho-access-overview-block">
    <h4 class="typecho-access-overview-table-title">来源域名</h4>

    <div class="typecho-access-overview-table-wrap">
      <table class="typecho-access-overview-list-table" id="tbl-referer-domain">
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
            <td>loading...</td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="bento-col-mb-12 bento-col-8 typecho-access-overview-block">
    <h4 class="typecho-access-overview-table-title">来源页</h4>

    <div class="typecho-access-overview-table-wrap">
      <table class="typecho-access-overview-list-table" id="tbl-referer-url">
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
            <td>loading...</td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="bento-col-mb-12">
    <h4 class="typecho-access-overview-table-title">文章浏览分析</h4>
    <div class="typecho-access-overview-table-wrap" id="pie-article">loading...</div>
  </div>

  <div class="bento-col-mb-12">
    <h4 class="typecho-access-overview-table-title">访客地域分析</h4>
    <div class="typecho-access-overview-table-wrap">
      <ul class="typecho-option-tabs clearfix">
        <li><button id="btn-china" class="btn btn-s primary">国内</button></li>
        <li><button id="btn-inter" class="btn btn-s">国际</button><li>
      </ul>
      <div class="typecho-access-overview-table-wrap" id="bar-location">loading...</div>
    </div>
  </div>

  <div class="bento-col-mb-12">
    <h4 class="typecho-access-overview-table-title">今日图表</h4>
    <div class="typecho-access-overview-table-wrap" id="chart-today"></div>
  </div>

  <div class="bento-col-mb-12">
    <h4 class="typecho-access-overview-table-title">昨日图表</h4>
    <div class="typecho-access-overview-table-wrap" id="chart-yesterday"></div>
  </div>

  <div class="bento-col-mb-12">
    <h4 class="typecho-access-overview-table-title">当月图表</h4>
    <div class="typecho-access-overview-table-wrap" id="chart-month"></div>
  </div>
</div>
