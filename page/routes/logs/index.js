$(document).ready(function () {
  $('a[data-action="ua"]').click(function () {
    swal({
      icon: "info",
      title: "User-Agent",
      text: $(this).attr("title").trim(),
    });
  });

  $('.dropdown-menu a[data-action="delete"]').click(function () {
    swal({
      title: "你确定?",
      text: "你确认要删除这些记录吗?",
      icon: "warning",
      buttons: {
        cancel: "算啦",
        confirm: "是的",
      },
    }).then((value) => {
      if (value === true) {
        let ids = [];
        $('.typecho-list-table input[type="checkbox"]').each(function (
          index,
          elem
        ) {
          if (elem.checked) {
            ids.push($(elem).data("id"));
          }
        });
        if (ids.length != 0) {
          $.ajax({
            url: "/access/log/delete",
            method: "post",
            dataType: "json",
            contentType: "application/json",
            data: JSON.stringify(ids),
            success: function (data) {
              if (data.code == 0) {
                swal({
                  icon: "success",
                  title: "删除成功",
                  text: "所选记录已删除",
                });
                $.each(ids, function (index, elem) {
                  $('.typecho-list-table tbody tr[data-id="' + elem + '"]')
                    .fadeOut(500)
                    .remove();
                });
              } else {
                swal({
                  icon: "error",
                  title: "错误",
                  text: "删除出错啦",
                });
              }
            },
            error: function (xhr, status, error) {
              swal({
                icon: "error",
                title: "错误",
                text: "请求错误 code: " + xhr.status,
              });
            },
          });
        } else {
          return swal({
            icon: "warning",
            title: "错误",
            text: "你并没有勾选任何内容",
          });
        }
      }
    });
    var $this = $(this);
    $this.parents(".dropdown-menu").hide().prev().removeClass("active");
  });

  var $form = $("form.search-form");
  var $ipInput = $form.find('input[name="ip"]');
  var $cidSelect = $form.find('select[name="cid"]');
  var $pathInput = $form.find('input[name="path"]');
  var $filterSelect = $form.find('select[name="filter"]');
  var $fuzzySelect = $form.find('select[name="fuzzy"]');

  $filterSelect.on("change", function () {
    $ipInput.removeAttr("placeholder").val("").hide();
    $cidSelect.hide();
    $pathInput.removeAttr("placeholder").val("").hide();
    $fuzzySelect.hide();

    switch ($filterSelect.val()) {
      case "ip":
        $ipInput.attr("placeholder", "输入ip").show();
        $fuzzySelect.show();
        break;
      case "post":
        $cidSelect.show();
        break;
      case "path":
        $pathInput.attr("placeholder", "输入路由").show();
        $fuzzySelect.show();
        break;
    }
  });

  $fuzzySelect.on("change", function (e) {
    if (e.target.value === "1") {
      $ipInput.val($ipInput.val().replace(/%/u, "\\%"));
      $pathInput.val($ipInput.val().replace(/%/u, "\\%"));
    } else {
      $ipInput.val($ipInput.val().replace(/\\%/u, "%"));
      $pathInput.val($ipInput.val().replace(/\\%/u, "%"));
    }
  });

  $form.find('button[type="button"]').on("click", function () {
    $form.submit();
  });
});
