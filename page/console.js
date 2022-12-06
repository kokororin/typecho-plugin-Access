$().ready(function () {
  $.ajax({
    url: "/access/migration",
    method: "get",
    dataType: "json",
    data: { rpc: 'overview' },
    success: function (res) {
      if (res.data.total > 0) {
        $('#migration-tab-li').show();
      }
    },
  });
});
