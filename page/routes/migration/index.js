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
            $('body').loadingModal('hide');
          } else {
            $('body').loadingModal('text', '迁移中，剩余' + res.data.remain + '条等待迁移...');
            start();
          }
          $('#ancient-logs-count').text(res.data.remain);
        }
      },
    });
  }

  $('[data-action="migrate"]').click(function() {
    start();
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
