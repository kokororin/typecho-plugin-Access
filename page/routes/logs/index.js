$(document).ready(function () {
  var pageNum = 1;

  function getPageNum() {
    return pageNum;
  }

  function setPageNum(n) {
    pageNum = Number.parseInt(n, 10) || 1;
  }

  function getFilters() {
    return {
      fuzzy: $('[name="filter-fuzzy"]').val(),
      ua: $('[name="filter-ua"]').val(),
      ip: $('[name="filter-ip"]').val(),
      cid: $('[name="filter-cid"]').val(),
      path: $('[name="filter-path"]').val(),
      robot: $('[name="filter-robot"]').val(),
    };
  }

  function setFilters(filters) {
    $('[name="filter-fuzzy"]').val('fuzzy' in filters ? filters.fuzzy : '');
    $('[name="filter-ua"]').val('ua' in filters ? filters.ua : '');
    $('[name="filter-ip"]').val('ip' in filters ? filters.ip : '');
    $('[name="filter-cid"]').val('cid' in filters ? filters.cid : '');
    $('[name="filter-path"]').val('path' in filters ? filters.path : '');
    $('[name="filter-robot"]').val('robot' in filters ? filters.robot : '');
  }

  function fetchLogs() {
    var startTime = new Date().valueOf();
    $('.typecho-list')
      .loadingModal({ text: '正在获取数据...', backgroundColor: '#292d33' })
      .loadingModal(
        'animation',
        [
          'doubleBounce',
          'rotatingPlane',
          // 'wave',
          // 'wanderingCubes',
          'foldingCube',
        ][Math.floor(Math.random() * 3)]
      )
      .loadingModal('show');

    $.ajax({
      url: "/access/logs",
      method: "get",
      dataType: "json",
      data: Object.assign(
        { },
        getFilters(),
        { rpc: 'get', page: getPageNum() }
      ),
      success: function (res) {
        // make sure loading animation visible for better experience
        var minDuring = 300;
        var during = new Date().valueOf() - startTime;
        if (during < minDuring) {
          setTimeout(function() { $('.typecho-list').loadingModal('hide'); }, minDuring - during);
        } else {
          $('.typecho-list').loadingModal('hide');
        }
        if (res.code === 0) {
          // logs list
          var $tbody, $tr, $td;
          $tbody = $('.typecho-list-table tbody');
          $tbody.html('');
          $.each(res.data.logs, function(index, item) {
            $tr = $('<tr />', { id: item.id, 'data-id': item.id });
            // id
            $td = $('<td />');
            $td.append($('<input />', {
              type: 'checkbox',
              value: item.id,
              name: 'id[]',
              'data-id': item.id,
            }));
            $tr.append($td);
            // url
            $td = $('<td />');
            $td.append($('<a />', {
              'data-action': 'search-anchor',
              'data-filter': JSON.stringify({ path: item.path }),
            }).text(item.url.replace(/%23/u, '#')));
            $tr.append($td);
            // ua
            $td = $('<td />');
            $td.append($('<a />', {
              title: item.ua,
              'data-action': 'search-anchor',
              'data-filter': JSON.stringify({ ua: item.ua }),
            }).text(item.display_name));
            $tr.append($td);
            // ip
            $td = $('<td />');
            $td.append($('<a />', {
              'data-action': 'search-anchor',
              'data-filter': JSON.stringify({ ip: item.ip }),
            }).text(item.ip));
            $tr.append($td);
            // ip_loc
            $td = $('<td />');
            $td.append($('<span />').text(item.ip_loc));
            $tr.append($td);
            // referer
            $td = $('<td />');
            $td.append($('<a />', {
              'data-action': 'search-anchor',
              'data-filter': JSON.stringify({ referer: item.referer }),
            }).text(item.referer));
            $tr.append($td);
            // time
            $td = $('<td />');
            $td.append($('<span />').text(dayjs(item.time * 1000).format('YYYY-MM-DD hh:mm:ss')));
            $tr.append($td);
            // append row to table body
            $tbody.append($tr);
          });
          // logs pagination
          $('a[data-action="search-anchor"]').click(onSearchAnchorClick);

          var $pagination;
          $pagination = $('.typecho-pager');
          $pagination.html('');

          var startPage, stopPage;
          if (res.data.pagination.total <= 10 || res.data.pagination.current <= 5) {
            startPage = 1;
            stopPage = Math.min(res.data.pagination.total, res.data.pagination.current + 5);
          } else if (res.data.pagination.total - res.data.pagination.current <= 5) {
            startPage = res.data.pagination.total - 10;
            stopPage = res.data.pagination.total;
          } else {
            startPage = res.data.pagination.current - 5;
            stopPage = res.data.pagination.current + 5;
          }

          if (startPage > 1) {
            $pagination.append(
              $('<li />')
                .append($('<a />', { class: 'typecho-access-logs-pagination-item', 'data-action': 'prev-page' })
                .text('«')
                .click(onPrevPage)
              )
            );
          }
          for (let index = startPage; index <= stopPage; index++) {
            $pagination.append(
              $('<li />', { class: index === res.data.pagination.current ? 'current' : '' })
                .append(
                  $('<a />', {
                    class: 'typecho-access-logs-pagination-item',
                    'data-action': 'goto-page',
                    'data-page': index,
                  })
                    .text(index)
                    .click(onGotoPage)
                )
            );
          }
          if (stopPage < res.data.pagination.total) {
            $pagination.append(
              $('<li />')
                .append($('<a />', { class: 'typecho-access-logs-pagination-item', 'data-action': 'next-page' })
                .text('»')
                .click(onNextPage)
              )
            );
          }
          $('input[name="page-jump"]').val(res.data.pagination.current);
          $('.typecho-access-logs-pagination-jump__total').text(res.data.pagination.total);
        } else {
          swal({
            icon: "error",
            title: "错误",
            text: "查询出错啦",
          });
        }
      },
      error: function (xhr, status, error) {
        $('body').loadingModal('hide');
        swal({
          icon: "error",
          title: "错误",
          text: "请求错误 code: " + xhr.status,
        });
      },
    });
  }

  function onSearchAnchorClick(e) {
    setPageNum(1);
    setFilters(JSON.parse(e.target.getAttribute('data-filter')));
    $('button[data-action="apply"]').first().click();
  }

  function onPrevPage() {
    setPageNum(getPageNum() - 1);
    fetchLogs();
  }

  function onGotoPage(e) {
    setPageNum(e.target.getAttribute('data-page'));
    fetchLogs();
  }

  function onNextPage() {
    setPageNum(getPageNum() + 1);
    fetchLogs();
  }

  $('button[data-action="apply"]').click(function() {
    fetchLogs();
    $('.typecho-access-logs-filter').removeClass('typecho-access-logs-filter--visible');
  });

  $('button[data-action="reset"]').click(function() {
    setPageNum(1);
    setFilters({ robot: '0' });
    fetchLogs();
    $('.typecho-access-logs-filter').removeClass('typecho-access-logs-filter--visible');
  });

  $('button[data-action="switch-filter"]').click(function() {
    $('.typecho-access-logs-filter').toggleClass('typecho-access-logs-filter--visible');
  });

  $('input[name="page-jump"]').on('keypress', function(e) {
    if (e.which == 13) {
      setPageNum(e.target.value);
      fetchLogs();
    }
  });

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
            url: "/access/logs",
            method: "post",
            dataType: "json",
            data: {
              rpc: 'delete',
              ids: JSON.stringify(ids),
            },
            success: function (res) {
              if (res.code == 0) {
                swal({
                  icon: "success",
                  title: "删除成功",
                  text: "成功删除" + res.data.count + "条记录",
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

  fetchLogs();
});
