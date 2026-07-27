CREATE TABLE IF NOT EXISTS `portfolio_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `video_format` enum('landscape','vertical') NOT NULL DEFAULT 'landscape',
  `media_type` enum('file','url') NOT NULL DEFAULT 'url',
  `media_url` text NOT NULL,
  `thumbnail_url` text DEFAULT NULL,
  `category_tag` enum('Residential','Commercial','Maintenance','Showcase') NOT NULL DEFAULT 'Showcase',
  `status` enum('Published','Draft') NOT NULL DEFAULT 'Draft',
  `view_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_project_id` (`project_id`),
  KEY `idx_status_format` (`status`, `video_format`),
  KEY `idx_category_tag` (`category_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
