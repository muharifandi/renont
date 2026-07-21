-- Perbaikan audit keamanan REST API (RentOn-Audit-Keamanan-REST-API.md)
-- 1) Tabel `limits`, dipakai REST_Controller::_check_limit() untuk rate limiting
--    per-IP pada endpoint login (belum ada di skema manapun sebelumnya).
CREATE TABLE IF NOT EXISTS `limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) NOT NULL,
  `api_key` varchar(40) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 0,
  `hour_started` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `limits_uri_api_key` (`uri`,`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 2) Kolom kedaluwarsa untuk API key (temuan: key tidak pernah expire).
ALTER TABLE `keys` ADD COLUMN `date_expires` int(11) NULL DEFAULT NULL AFTER `date_created`;
