## Typecho Access 插件

提供简易的访客记录查看。获取访客信息，生成统计图表。

### 功能简介/更新说明

#### 数据分析与统计

* IP / UV / PV总量统计
* 来源页 / 域名排名
* 最受欢迎的文章
* 国内 / 国际访问地域分析
* 每日IP / UV / PV统计图表(带均线)
* 前后端分离，使用CSR方式展示数据

#### 访问日志

* ~~ip归属地使用淘宝API~~
* 管理员登录时不记录日志
* 修复Referer记录错误的bug
* 添加删除日志的功能
* 修复旧版本升级错误的提示，自动更新数据表结构
* 使用ipip.net离线数据库
* 日志写入支持前端写入或后端写入
* 日志筛选支持按ip、文章标题、路由进行过滤
* ip归属地使用SSR方式解析，并加入日志显示

### 使用须知

* 插件更新升级时，请先禁用插件后再上传
* 插件目录名请修改为Access
* PHP需拥有Calendar扩展
* PHP --version >= 7

### 作者/贡献者
<a href="https://github.com/kokororin/typecho-plugin-Access/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=kokororin/typecho-plugin-Access" />
</a>

### 图示
![](https://static-files.kotori.love/blog/2015/12/4187563925.jpg)

![A75B8F39-C8B6-4CD2-AFFC-784B3E27B8A4.png](https://static-files.kotori.love/blog/2015/12/2019049143.png)

![](https://static-files.kotori.love/blog/2016/08/1564663056.png)

![](https://static-files.kotori.love/blog/2016/08/1121750290.png)

![BDEF004E-157E-4ADF-99C0-5EE65BDA61A6.png](https://static-files.kotori.love/blog/2016/11/3973345673.png)
