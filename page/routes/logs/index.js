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
      preset: $('[name="filter-preset"]').val(),
    };
  }

  function setFilters(filters) {
    $('[name="filter-fuzzy"]').val('fuzzy' in filters ? filters.fuzzy : '');
    $('[name="filter-ua"]').val('ua' in filters ? filters.ua : '');
    $('[name="filter-ip"]').val('ip' in filters ? filters.ip : '');
    $('[name="filter-cid"]').val('cid' in filters ? filters.cid : '');
    $('[name="filter-path"]').val('path' in filters ? filters.path : '');
    $('[name="filter-robot"]').val('robot' in filters ? filters.robot : '');
    $('[name="filter-preset"]').val('preset' in filters ? filters.preset : '');
  }

  function hideFilters() {
    $('.typecho-access-logs-filter').removeClass('typecho-access-logs-filter--visible');
  }

  function toggleFilters() {
    $('.typecho-access-logs-filter').toggleClass('typecho-access-logs-filter--visible');
  }

  function fetchLogs(silent) {
    var startTime = new Date().valueOf();
    if (!silent) {
      $('.typecho-access-logs-main')
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
    }

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
        if (!silent) {
          // make sure loading animation visible for better experience
          var minDuring = 300;
          var during = new Date().valueOf() - startTime;
          if (during < minDuring) {
            setTimeout(function() { $('.typecho-access-logs-main').loadingModal('hide'); }, minDuring - during);
          } else {
            $('.typecho-access-logs-main').loadingModal('hide');
          }
        }
        if (res.code === 0) {
          // logs list
          var $tbody, $tr, $td;
          $tbody = $('.typecho-access-logs-list-table tbody');
          $tbody.html('');
          $.each(res.data.logs, function(index, item) {
            $tr = $('<tr />', { id: item.id, 'data-id': item.id });
            // id
            $td = $('<td />', { style: 'text-align: center' });
            $td.append($('<input />', {
              type: 'checkbox',
              value: item.id,
              name: 'id[]',
              class: 'typecho-access-logs-list-checkbox form-check-input',
              'data-id': item.id,
            }));
            $tr.append($td);
            // url
            $td = $('<td />');
            $td.append($('<a />', {
              class: '',
              'data-action': 'search-anchor',
              'data-filter': JSON.stringify({ path: item.path }),
            }).text(item.url.replace(/%23/u, '#')));
            $tr.append($td);
            // ua
            $td = $('<td />');
            $td.append($('<a />', {
              title: item.ua,
              class: '',
              'data-action': 'search-anchor',
              'data-filter': JSON.stringify({ ua: item.ua }),
            }).text(item.display_name));
            $tr.append($td);
            // ip
            $td = $('<td />');
            $td.append($('<a />', {
              class: '',
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
              class: '',
              'data-action': 'search-anchor',
              'data-filter': JSON.stringify({ referer: item.referer }),
            }).text(item.referer));
            $tr.append($td);
            // time
            $td = $('<td />');
            $td.append($('<span />').text(dayjs(item.time * 1000).format('YYYY-MM-DD HH:mm:ss')));
            $tr.append($td);
            // append row to table body
            $tbody.append($tr);
          });
          if ($tbody.html() === '') {
            $tbody.html('<tr><td colspan="7"><h6 class="typecho-access-logs-list-table-title typecho-access-logs-table-placeholder">暂无数据</h6></td></tr>');
          }
          $('.typecho-access-logs-list-checkbox').change(updateSelectAll);
          $('a[data-action="search-anchor"]').click(onSearchAnchorClick);
          updateSelectAll();

          // logs pagination
          setPageNum(res.data.pagination.current);
          var $pagination;
          $pagination = $('.typecho-access-logs-pagination');
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
          for (var index = startPage; index <= stopPage; index++) {
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

  function updateSelectAll() {
    var checked = true;
    $('.typecho-access-logs-list-checkbox').each(function(_, el) {
      checked = checked && $(el).prop('checked');
    });
    if ($('.typecho-access-logs-list-table-select-all').prop('checked') == checked) {
      return;
    }
    $('.typecho-access-logs-list-table-select-all').prop('checked', checked);
  }

  function onSearchAnchorClick(e) {
    setPageNum(1);
    setFilters(JSON.parse(e.target.getAttribute('data-filter')));
    $('button[data-action="filter-apply"]').first().click();
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

  $('button[data-action="filter-apply"]').click(function() {
    setPageNum(1);
    fetchLogs();
    hideFilters();
  });

  $('button[data-action="filter-reset"]').click(function() {
    setPageNum(1);
    setFilters({ robot: '0' });
    fetchLogs();
    hideFilters();
  });

  $('button[data-action="switch-filter"]').click(function() {
    toggleFilters();
  });

  $('[name="filter-fuzzy"]').change(function(e) {
    var filters = getFilters();
    var inputKeys = ['ua', 'ip', 'path'];
    if (e.target.value === '1') {
      $(inputKeys).each(function(_, k) {
        if (filters[k] === '') {
          filters[k] = '%';
        } else {
          // MySQL escape: https://dev.mysql.com/doc/refman/8.0/en/string-comparison-functions.html
          filters[k] = filters[k].replace(/[%_]/gu, function(s) { return '\\' + s; });
        }
      });
    } else {
      $(inputKeys).each(function(_, k) {
        if (filters[k] === '%') {
          filters[k] = '';
        } else {
          // MySQL escape: https://dev.mysql.com/doc/refman/8.0/en/string-comparison-functions.html
          filters[k] = filters[k].replace(/\\[%_]/gu, function(s) { return s.substring(1); });
        }
      });
    }
    setFilters(filters);
  });

  $('.typecho-access-logs-list-table-select-all').click(function() {
    $(this).parent().parent().parent().parent().parent()
      .find('input[name="id[]"]')
      .prop('checked', $(this).prop('checked'));
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

  function deleteLogs(filters) {
    function action(force) {
      $.ajax({
        url: "/access/logs",
        method: "post",
        dataType: "json",
        data: Object.assign(
          {},
          filters,
          {
            rpc: 'delete',
            force: force ? 'force' : '',
          }
        ),
        success: function (res) {
          if (res.code === 0) {
            if (res.data.requireForce) {
              swal({
                title: "你确定?",
                text: "本次操作将删除" + res.data.count + "条记录，你确认要删除这些记录吗?",
                icon: "warning",
                buttons: {
                  cancel: "算啦",
                  confirm: "是的",
                },
              }).then((value) => {
                if (value === true) {
                  action(true);
                }
              });
            } else {
              swal({
                icon: "success",
                title: "删除成功",
                text: "成功删除" + res.data.count + "条记录",
              });
              if (filters.ids) {
                $.each(JSON.parse(filters.ids), function (index, elem) {
                  $('.typecho-access-logs-list-table tbody tr[data-id="' + elem + '"]')
                    .fadeOut(500)
                    .remove();
                });
                setTimeout(function() { fetchLogs(true); }, 480);
              } else {
                fetchLogs(false);
              }
              hideFilters();
            }
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
    }
    action(false);
  }

  $('.typecho-access-logs-dropdown-toggle').click(function() {
    $(this).parent().toggleClass('typecho-access-logs-dropdown--active');
    $(this).next().css({ 'min-width': $(this).parent().width() + 'px' });
  })

  $('[data-action="select-delete"]').click(function () {
    $('.typecho-access-logs-dropdown')
      .removeClass('typecho-access-logs-dropdown--active');
    var ids = [];
    $('.typecho-access-logs-list-checkbox').each(function (index, elem) {
      if (elem.checked) {
        ids.push($(elem).data("id"));
      }
    });
    if (ids.length === 0) {
      swal({
        icon: "warning",
        title: "错误",
        text: "你并没有勾选任何内容",
      });
    } else {
      swal({
        title: "你确定?",
        text: "你确认要删除选中的" + ids.length + "条记录吗?",
        icon: "warning",
        buttons: {
          cancel: "算啦",
          confirm: "是的",
        },
      }).then((value) => {
        if (value === true) {
          deleteLogs({ ids: JSON.stringify(ids) });
        }
      });
    }
    $(this).parents(".dropdown-menu").hide().prev().removeClass("active");
  });

  $('[data-action="filter-delete"]').click(function() {
    swal({
      title: "你确定?",
      text: "将按照过滤器批量删除符合条件的记录，你确认要删除吗?",
      icon: "warning",
      buttons: {
        cancel: "算啦",
        confirm: "是的",
      },
    }).then((value) => {
      if (value === true) {
        deleteLogs(getFilters());
      }
    });
  });

  $.ajax({
    url: "/access/logs",
    method: "get",
    dataType: "json",
    data: { rpc: 'cids' },
    success: function (res) {
      if (res.code === 0) {
        var $select = $('select[name="filter-cid"]');
        $.each(res.data.list, function(_, item) {
          $select.append(
            $('<option />', { value: item.cid })
              .text(item.title + ' (' + item.count + ')')
          );
        });
      }
    },
  });

  fetchLogs();
});
