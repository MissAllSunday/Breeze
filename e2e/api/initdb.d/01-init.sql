-- E2E Mock API Database Schema and Fixtures
-- Automatically executed by MySQL container on first startup

-- Ensure the init-script connection uses utf8mb4 so emoji literals in the
-- INSERT statements below are treated as 4-byte UTF-8, not Latin-1.
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE DATABASE IF NOT EXISTS breeze_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE breeze_test;

CREATE TABLE IF NOT EXISTS `smf_breeze_status` (
  `id` INT(4) NOT NULL AUTO_INCREMENT,
  `wall_id` INT(4) NOT NULL,
  `user_id` INT(4) NOT NULL,
  `created_at` INT(11) NOT NULL,
  `body` TEXT,
  `likes` INT(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`, `wall_id`),
  KEY `user_id` (`user_id`),
  KEY `wall_id` (`wall_id`),
  KEY `idx_wall_created_id` (`wall_id`, `created_at`, `id`),
  KEY `idx_user_created_id` (`user_id`, `created_at`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smf_breeze_comments` (
  `id` INT(4) NOT NULL AUTO_INCREMENT,
  `status_id` INT(4) NOT NULL,
  `user_id` INT(4) NOT NULL,
  `likes` INT(4) NOT NULL DEFAULT 0,
  `body` TEXT,
  `created_at` VARCHAR(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smf_user_likes` (
  `id_member` INT(4) NOT NULL,
  `content_type` VARCHAR(20) NOT NULL DEFAULT '',
  `content_id` INT(4) NOT NULL,
  `like_time` INT(11) NOT NULL,
  PRIMARY KEY (`content_id`, `content_type`, `id_member`),
  KEY `content_id` (`content_id`),
  KEY `id_member` (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smf_members` (
  `id_member` INT(4) NOT NULL AUTO_INCREMENT,
  `member_name` VARCHAR(80) NOT NULL DEFAULT '',
  `real_name` VARCHAR(255) NOT NULL DEFAULT '',
  `pm_ignore_list` VARCHAR(255) NOT NULL DEFAULT '',
  `buddy_list` TEXT,
  PRIMARY KEY (`id_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smf_breeze_options` (
  `member_id` INT(4) NOT NULL,
  `variable` VARCHAR(255) NOT NULL DEFAULT '',
  `value` TEXT,
  PRIMARY KEY (`member_id`, `variable`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert fixture member
INSERT INTO `smf_members` (`id_member`, `member_name`, `real_name`) VALUES
  (1, 'testuser', 'Test User');

-- Insert 3 statuses matching current mock API data
INSERT INTO `smf_breeze_status` (`id`, `wall_id`, `user_id`, `created_at`, `body`, `likes`) VALUES
  (1, 1, 1, UNIX_TIMESTAMP() - 3600, 'This is mock status #1 for E2E testing.', 0),
  (2, 1, 1, UNIX_TIMESTAMP() - 7200, 'This is mock status #2 for E2E testing.', 0),
  (3, 1, 1, UNIX_TIMESTAMP() - 10800, 'This is mock status #3 for E2E testing.', 0);

-- Insert 1 comment per status matching current mock API data
INSERT INTO `smf_breeze_comments` (`id`, `status_id`, `user_id`, `likes`, `body`, `created_at`) VALUES
  (100, 1, 1, 0, 'A comment on status #1', 'Apr 30, 2026 04:14 PM'),
  (200, 2, 1, 0, 'A comment on status #2', 'Apr 30, 2026 04:14 PM'),
  (300, 3, 1, 0, 'A comment on status #3', 'Apr 30, 2026 04:14 PM');

-- ── Reactions tables ─────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS `smf_breeze_reaction_collections` (
  `id`          INT(4)         NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(255)   NOT NULL DEFAULT '',
  `description` TEXT,
  `is_active`   TINYINT(1)     NOT NULL DEFAULT 1,
  `sort_order`  INT(4)         NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smf_breeze_reactions` (
  `id`            INT(4)       NOT NULL AUTO_INCREMENT,
  `collection_id` INT(4)       NOT NULL,
  `emoji`         VARCHAR(20)  NOT NULL DEFAULT '',
  `label`         VARCHAR(255) NOT NULL DEFAULT '',
  `sort_order`    INT(4)       NOT NULL DEFAULT 0,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `collection_id` (`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smf_breeze_content_reactions` (
  `id_member`    INT(4)       NOT NULL,
  `content_type` VARCHAR(20)  NOT NULL DEFAULT '',
  `content_id`   INT(4)       NOT NULL,
  `reaction_id`  INT(4)       NOT NULL,
  `reacted_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_member`, `content_type`, `content_id`),
  KEY `content_id` (`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed one active collection with 3 reactions
INSERT INTO `smf_breeze_reaction_collections` (`id`, `name`, `description`, `is_active`, `sort_order`) VALUES
  (1, 'Default', 'Default reaction set', 1, 0);

-- Emoji literals use the _utf8mb4 charset introducer with explicit hex bytes so
-- they are stored correctly regardless of the MySQL session charset at init time.
--   👍  U+1F44D → UTF-8: F0 9F 91 8D
--   ❤️  U+2764 U+FE0F → UTF-8: E2 9D A4 EF B8 8F
--   😂  U+1F602 → UTF-8: F0 9F 98 82
INSERT INTO `smf_breeze_reactions` (`id`, `collection_id`, `emoji`, `label`, `sort_order`, `is_active`) VALUES
  (1, 1, _utf8mb4 X'F09F918D',     'Thumbs Up', 1, 1),
  (2, 1, _utf8mb4 X'E29DA4EFB88F', 'Heart',     2, 1),
  (3, 1, _utf8mb4 X'F09F9882',     'Haha',      3, 1);
