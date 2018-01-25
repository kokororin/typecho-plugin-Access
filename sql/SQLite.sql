CREATE TABLE `typecho_access_log` (
  `id`                INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
  `ua`                varchar(255)     default ''  ,
  `browser_id`        varchar(32)      default ''  ,
  `browser_version`   varchar(32)      default ''  ,
  `os_id`             varchar(32)      default ''  ,
  `os_version`        varchar(32)      default ''  ,
  `url`               varchar(255)     default ''  ,
  `path`              varchar(255)     default ''  ,
  `query_string`      varchar(255)     default ''  ,
  `ip`                int(32)          default '0' ,
  `entrypoint`        varchar(255)     default ''  ,
  `entrypoint_domain` varchar(100)     default ''  ,
  `referer`           varchar(255)     default ''  ,
  `referer_domain`    varchar(100)     default ''  ,
  `time`              int(32)          default '0' ,
  `content_id`        int(10)          default NULL,
  `meta_id`           int(10)          default NULL,
  `robot`             tinyint(1)       default '0' ,
  `robot_id`          varchar(32)      default ''  ,
  `robot_version`     varchar(32)      default ''
);
CREATE INDEX `typecho_access_log_time`              ON `typecho_access_log` (`time`             );
CREATE INDEX `typecho_access_log_path`              ON `typecho_access_log` (`path`             );
CREATE INDEX `typecho_access_log_ip_ua`             ON `typecho_access_log` (`ip`, `ua`         );
CREATE INDEX `typecho_access_log_robot`             ON `typecho_access_log` (`robot`, `time`    );
CREATE INDEX `typecho_access_log_os_id`             ON `typecho_access_log` (`os_id`            );
CREATE INDEX `typecho_access_log_robot_id`          ON `typecho_access_log` (`robot_id`         );
CREATE INDEX `typecho_access_log_browser_id`        ON `typecho_access_log` (`browser_id`       );
CREATE INDEX `typecho_access_log_content_id`        ON `typecho_access_log` (`content_id`       );
CREATE INDEX `typecho_access_log_meta_id`           ON `typecho_access_log` (`meta_id`          );
CREATE INDEX `typecho_access_log_entrypoint`        ON `typecho_access_log` (`entrypoint`       );
CREATE INDEX `typecho_access_log_entrypoint_domain` ON `typecho_access_log` (`entrypoint_domain`);
CREATE INDEX `typecho_access_log_referer`           ON `typecho_access_log` (`referer`          );
CREATE INDEX `typecho_access_log_referer_domain`    ON `typecho_access_log` (`referer_domain`   );
COMMIT;
