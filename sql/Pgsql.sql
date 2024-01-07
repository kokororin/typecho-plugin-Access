CREATE TABLE typecho_access_logs (
  id                serial PRIMARY KEY,
  ua                varchar(512)     DEFAULT ''  ,
  browser_id        varchar(32)      DEFAULT ''  ,
  browser_version   varchar(32)      DEFAULT ''  ,
  os_id             varchar(32)      DEFAULT ''  ,
  os_version        varchar(32)      DEFAULT ''  ,
  url               varchar(255)     DEFAULT ''  ,
  path              varchar(255)     DEFAULT ''  ,
  query_string      varchar(255)     DEFAULT ''  ,
  ip                varchar(64)      DEFAULT ''  ,
  ip_country        varchar(255)     DEFAULT ''  ,
  ip_province       varchar(255)     DEFAULT ''  ,
  ip_city           varchar(255)     DEFAULT ''  ,
  entrypoint        varchar(255)     DEFAULT ''  ,
  entrypoint_domain varchar(100)     DEFAULT ''  ,
  referer           varchar(255)     DEFAULT ''  ,
  referer_domain    varchar(100)     DEFAULT ''  ,
  time              int              DEFAULT '0' ,
  content_id        int              DEFAULT NULL,
  meta_id           int              DEFAULT NULL,
  robot             boolean          DEFAULT '0' ,
  robot_id          varchar(32)      DEFAULT ''  ,
  robot_version     varchar(32)      DEFAULT ''  
);

CREATE INDEX idx_time ON typecho_access_logs (time);
CREATE INDEX idx_path ON typecho_access_logs (path);
CREATE INDEX idx_ua ON typecho_access_logs (ua);
CREATE INDEX idx_ip_ua ON typecho_access_logs (ip, ua);
CREATE INDEX idx_ip_province ON typecho_access_logs (ip_province);
CREATE INDEX idx_robot ON typecho_access_logs (robot, time);
CREATE INDEX idx_os_id ON typecho_access_logs (os_id);
CREATE INDEX idx_robot_id ON typecho_access_logs (robot_id);
CREATE INDEX idx_browser_id ON typecho_access_logs (browser_id);
CREATE INDEX idx_content_id ON typecho_access_logs (content_id);
CREATE INDEX idx_meta_id ON typecho_access_logs (meta_id);
CREATE INDEX idx_entrypoint ON typecho_access_logs (entrypoint);
CREATE INDEX idx_entrypoint_domain ON typecho_access_logs (entrypoint_domain);
CREATE INDEX idx_referer ON typecho_access_logs (referer);
CREATE INDEX idx_referer_domain ON typecho_access_logs (referer_domain);
