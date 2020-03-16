--
-- Trojan
--
CREATE TABLE IF NOT EXISTS `users` (
  `id`                 int    unsigned  NOT NULL AUTO_INCREMENT,
  `user_id`            int    unsigned  NOT NULL,
  `password`           char(56)         NOT NULL,
  `quota`              bigint           NOT NULL DEFAULT '0',
  `upload`             bigint unsigned  NOT NULL DEFAULT '0',
  `download`           bigint unsigned  NOT NULL DEFAULT '0',
  `upload_old`         bigint unsigned  NOT NULL DEFAULT '0',
  `download_old`       bigint unsigned  NOT NULL DEFAULT '0',
  `total`              bigint           NOT NULL DEFAULT '0' COMMENT '总使用流量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Trojan';
