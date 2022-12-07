// html转义
htmlEncode = function (target) {
  return target.replace(/[<>&"]/g, function (c) {
    return { "<": "&lt;", ">": "&gt;", "&": "&amp;", '"': "&quot;" }[c];
  });
};
// 输出图表
printChart = function (target, title, data, avg = null) {
  let pv = [],
    uv = [],
    ip = [],
    time = [];
  for (let i = 0; i < data.length; i++)
    pv.push(data[i].pv),
      uv.push(data[i].uv),
      ip.push(data[i].ip),
      time.push(data[i].time);
  const chart = Highcharts.chart(target, {
    title: { text: title, x: -20 },
    subtitle: { text: "Generate By AccessPlugin", x: -20 },
    xAxis: { categories: time, title: { text: "时间", align: "high" } },
    yAxis: {
      title: { text: "数量" },
      plotLines:
        avg !== null
          ? [
              // 平均值
              {
                value: avg.pv,
                width: 1,
                color: "#F7A35C",
              },
              {
                value: avg.uv,
                width: 1,
                color: "#90ED7D",
              },
              {
                value: avg.ip,
                width: 1,
                color: "#7CB5ED",
              },
            ]
          : null,
    },
    tooltip: { valueSuffix: "" },
    plotOptions: { line: { dataLabels: { enabled: true } } },
    series: [
      {
        name: "PV（浏览）",
        data: pv,
        color: "#F7A35C",
      },
      {
        name: "UV（访客）",
        data: uv,
        color: "#90ED7D",
      },
      {
        name: "IP（地址）",
        data: ip,
        color: "#7CB5ED",
      },
    ],
  });
};
// 输出饼图
printPie = function (target, title, data) {
  let value = [];
  // 生成统计row
  for (let i = 0; i < data.length; i++)
    value.push({ name: data[i].title, y: data[i].count, cid: data[i].cid });
  // 计算最大比例并选中
  let maxKey = 0;
  for (let i = 0; i < value.length; i++) {
    if (value[i].y >= value[maxKey].y) maxKey = i;
  }
  if (value.length) value[maxKey].sliced = true;
  const chart = Highcharts.chart(target, {
    chart: { type: "pie" },
    title: { text: title },
    subtitle: { text: "Generate By AccessPlugin" },
    tooltip: {
      pointFormat:
        "<b>阅读数: {point.y}<br>占比: {point.percentage:.1f}%<br>cid={point.cid}</b>",
    },
    accessibility: { point: { valueSuffix: "%" } },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: "pointer",
        dataLabels: { enabled: true, format: "<b>{point.name}</b>: {point.y}" },
      },
    },
    series: [{ colorByPoint: true, data: value }],
  });
};
// 输出条形统计图
printBar = function (target, title, data) {
  let values = [],
    areas = [];
  for (let i = 0; i < data.length; i++)
    areas.push(data[i].area), values.push(data[i].count);
  return Highcharts.chart(target, {
    chart: { type: "bar" },
    title: { text: title, x: -20 },
    subtitle: { text: "Generate By AccessPlugin", x: -20 },
    xAxis: { categories: areas, title: { text: "地域" } },
    yAxis: { title: { text: "访问次数", align: "high" } },
    plotOptions: { bar: { dataLabels: { enabled: true }, colorByPoint: true } },
    legend: { enabled: false },
    series: [{ name: "访问量", data: values }],
  });
};

updateBar = function (target, title, data) {
  let values = [],
    areas = [];
  for (let i = 0; i < data.length; i++)
    areas.push(data[i].area), values.push(data[i].count);
  target.update(
    {
      title: { text: title },
      xAxis: { categories: areas, title: { text: "地域" } },
      series: [{ name: "访问量", data: values }],
    },
    true,
    false,
    { duration: 800 }
  );
};

// 显示来源统计表
printRefererTable = function (target, data) {
  let tbl = $("#" + target + " tbody");
  tbl.children().remove(); // 清空表格
  for (let i = 0; i < data.length; i++) {
    tbl.append(
      "<tr><td>" +
        (i + 1) +
        "</td><td>" +
        data[i].count +
        "</td><td>" +
        htmlEncode(data[i].value) +
        "</td><td></td></tr>"
    );
  }
};

// 拉取统计数据
getStatisticData = function (rpc, params = null, callback) {
  $.ajax({
    url: "/access/statistic",
    method: "get",
    dataType: "json",
    data: { rpc: rpc, ...params },
    async: true,
    success: function (data) {
      if (data.code === 0) callback(data.data);
      else
        console.log(
          "rpc=" +
            rpc +
            "数据获取错误 code=" +
            data.code +
            " msg=" +
            data.message
        );
    },
    error: function (xhr, status, error) {
      console.log("rpc=" + rpc + " API拉取错误 " + error.toString());
    },
  });
};

// 挂接format方法
Date.prototype.format = function (fmt) {
  var o = {
    "%Y+": this.getFullYear(), // 年
    "%m+": this.getMonth() + 1, //月份
    "%d+": this.getDate(), //日
    "%H+": this.getHours(), //小时
    "%M+": this.getMinutes(), //分
    "%S+": this.getSeconds(), //秒
    "%Q+": Math.floor((this.getMonth() + 3) / 3), //季度
    "%s+": this.getMilliseconds(), //毫秒
  };
  for (var k in o)
    if (new RegExp("(" + k + ")").test(fmt))
      fmt = fmt.replace(
        RegExp.$1,
        RegExp.$1.length == 2 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length)
      );
  return fmt;
};

$().ready(function () {
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);

  var queue = [];
  function processQueue() {
    var fn = queue.shift();
    if (fn) {
      fn(processQueue);
    }
  }

  function addQueue(fn) {
    queue.push(fn);
  }

  // 今日计数统计数据
  addQueue(function(cb) {
    getStatisticData(
      "count",
      { type: "day", time: today.format("%Y-%mm-%dd") },
      function (data) {
        let row = $('#tbl-count tr[name="count-today"] td');
        row.eq(1).text(data.count.pv),
          row.eq(2).text(data.count.uv),
          row.eq(3).text(data.count.ip);
        cb();
      }
    );
  });

  // 昨日计数统计数据
  addQueue(function(cb) {
    getStatisticData(
      "count",
      { type: "day", time: yesterday.format("%Y-%mm-%dd") },
      function (data) {
        let row = $('#tbl-count tr[name="count-yesterday"] td');
        row.eq(1).text(data.count.pv),
          row.eq(2).text(data.count.uv),
          row.eq(3).text(data.count.ip);
        cb();
      }
    );
  });

  // 总计数统计数据
  addQueue(function(cb) {
    getStatisticData("count", { type: "total" }, function (data) {
      let row = $('#tbl-count tr[name="count-total"] td');
      row.eq(1).text(data.count.pv),
        row.eq(2).text(data.count.uv),
        row.eq(3).text(data.count.ip);
        cb();
    });
  });

  // 来源域名统计
  addQueue(function(cb) {
    getStatisticData(
      "referer",
      { type: "domain", pn: 1, ps: 10 },
      function (data) {
        printRefererTable("tbl-referer-domain", data);
        cb();
      }
    );
  });

  // 来源页统计
  addQueue(function(cb) {
    getStatisticData("referer", { type: "url", pn: 1, ps: 10 }, function (data) {
      printRefererTable("tbl-referer-url", data);
      cb();
    });
  });

  // 文章浏览比例统计
  addQueue(function(cb) {
    getStatisticData("article", { ps: 10 }, function (data) {
      printPie("pie-article", "最受欢迎的文章", data);
      cb();
    });
  });

  // 浏览地域分析图
  addQueue(function(cb) {
    getStatisticData("location", { cate: "china", ps: 10 }, function (data) {
      const chartBar = printBar("bar-location", "国内访问地域分析", data);
      // 国际按钮
      $("#btn-inter").click(function () {
        $("#btn-inter").addClass("primary");
        $("#btn-china").removeClass("primary");
        getStatisticData("location", { cate: "inter", ps: 10 }, function (data) {
          updateBar(chartBar, "国际访问地域分析", data);
        });
      });
      // 国内按钮
      $("#btn-china").click(function () {
        $("#btn-china").addClass("primary");
        $("#btn-inter").removeClass("primary");
        getStatisticData("location", { cate: "china", ps: 10 }, function (data) {
          updateBar(chartBar, "国内访问地域分析", data);
        });
      });
      cb();
    });
  });

  // 当天访问图表
  addQueue(function(cb) {
    getStatisticData(
      "chart",
      { type: "day", time: today.format("%Y-%mm-%dd") },
      function (data) {
        printChart("chart-today", data.dst + " 统计", data.chart, data.avg);
        cb();
      }
    );
  });

  // 昨天访问图表
  addQueue(function(cb) {
    getStatisticData(
      "chart",
      { type: "day", time: yesterday.format("%Y-%mm-%dd") },
      function (data) {
        printChart("chart-yesterday", data.dst + " 统计", data.chart, data.avg);
        cb();
      }
    );
  });

  // 当月访问图表
  addQueue(function(cb) {
    getStatisticData(
      "chart",
      { type: "month", time: today.format("%Y-%mm") },
      function (data) {
        printChart("chart-month", data.dst + " 统计", data.chart, data.avg);
        cb();
      }
    );
  });

  processQueue();
});
