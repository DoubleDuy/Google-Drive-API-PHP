CREATE TABLE `drive_files`
(
  `id` int
(11) NOT NULL AUTO_INCREMENT,
  `file_name` varchar
(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `google_drive_file_id` varchar
(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp
(),
  PRIMARY KEY
(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;