$(document).ready(function () {
  function start() {
    $('body').loadingModal({ text: '正在准备迁移...', backgroundColor: '#292d33' }).show();
    $.ajax({
      url: "/access/migration",
      method: "post",
      dataType: "json",
      data: { rpc: 'migrate' },
      success: function (res) {
        if (res.code === 0) {
          if (res.data.remain === 0) {
            swal({
              icon: "success",
              title: "完成",
              text: "旧版数据迁移完成！",
            });
            $('body').loadingModal('hide');
          } else {
            $('body').loadingModal('text', '迁移中，剩余' + res.data.remain + '条等待迁移...');
            start();
          }
          $('#ancient-logs-count').text(res.data.remain);
        }
      },
      error: function (xhr, status, error) {
        if (!silent) {
          $('.typecho-access-logs-main').loadingModal('hide');
        }
        swal({
          icon: "error",
          title: "错误",
          text: "请求错误 code: " + xhr.status,
        });
      },
    });
  }

  $('[data-action="migrate"]').click(function() {
    swal({
      title: "确定进行旧版数据迁移?",
      text: "\n1. 每次迁移1000条，自动重复直到完全迁移完成。\n2. 可随时通过关闭页面中断迁移。\n3. 迁移中断后下次将从中断处继续迁移。\n4. 请勿同时打开多个页面点击迁移，否则会导致数据重复。",
      icon: "warning",
      buttons: {
        cancel: "算啦",
        confirm: "是的",
      },
    }).then((value) => {
      if (value === true) {
        start();
      }
    });
  });

  $.ajax({
    url: "/access/migration",
    method: "get",
    dataType: "json",
    data: { rpc: 'overview' },
    success: function (res) {
      $('#ancient-logs-count').text(res.data.total);
    },
  });
});
