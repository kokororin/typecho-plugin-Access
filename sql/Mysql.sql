CREATE TABLE `typecho_access_logs` (
  `id`                int(10) unsigned NOT NULL auto_increment,
  `ua`                varchar(512)     default ''  ,
  `browser_id`        varchar(32)      default ''  ,
  `browser_version`   varchar(32)      default ''  ,
  `os_id`             varchar(32)      default ''  ,
  `os_version`        varchar(32)      default ''  ,
  `url`               varchar(255)     default ''  ,
  `path`              varchar(255)     default ''  ,
  `query_string`      varchar(255)     default ''  ,
  `ip`                varchar(64)      default ''  ,
  `ip_country`        varchar(255)     default ''  ,
  `ip_province`       varchar(255)     default ''  ,
  `ip_city`           varchar(255)     default ''  ,
  `entrypoint`        varchar(255)     default ''  ,
  `entrypoint_domain` varchar(100)     default ''  ,
  `referer`           varchar(255)     default ''  ,
  `referer_domain`    varchar(100)     default ''  ,
  `time`              int(32) unsigned default '0' ,
  `content_id`        int(10) unsigned default NULL,
  `meta_id`           int(10) unsigned default NULL,
  `robot`             tinyint(1)       default '0' ,
  `robot_id`          varchar(32)      default ''  ,
  `robot_version`     varchar(32)      default ''  ,
  PRIMARY KEY (`id`),
  KEY `idx_time`              (`time`             ),
  KEY `idx_path`              (`path`             ),
  KEY `idx_ua`                (`ua`               ),
  KEY `idx_ip_ua`             (`ip`,`ua`          ),
  KEY `idx_ip_province`       (`ip_province`      ),
  KEY `idx_robot`             (`robot`, `time`    ),
  KEY `idx_os_id`             (`os_id`            ),
  KEY `idx_robot_id`          (`robot_id`         ),
  KEY `idx_browser_id`        (`browser_id`       ),
  KEY `idx_content_id`        (`content_id`       ),
  KEY `idx_meta_id`           (`meta_id`          ),
  KEY `idx_entrypoint`        (`entrypoint`       ),
  KEY `idx_entrypoint_domain` (`entrypoint_domain`),
  KEY `idx_referer`           (`referer`          ),
  KEY `idx_referer_domain`    (`referer_domain`   )
) ENGINE=InnoDB DEFAULT CHARSET=%charset%;
