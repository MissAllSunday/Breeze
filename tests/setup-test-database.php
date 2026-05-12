<?php

declare(strict_types=1);

/**
 * Test Database Setup Script
 *
 * This script creates and populates a test database for running
 * integration and performance tests.
 *
 * Can be run standalone:
 *   php tests/setup-test-database.php
 *
 * Or included by test files to use createTestTables() and truncateTestTables().
 */

require_once __DIR__ . '/database-config.php';

/**
 * Create all test tables based on install.php schema.
 *
 * @param PDO $pdo Active database connection
 * @param string $prefix Table prefix (e.g. 'smf_')
 */
function createTestTables(PDO $pdo, string $prefix): void
{
	$pdo->exec("
		CREATE TABLE IF NOT EXISTS `{$prefix}breeze_status` (
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
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS `{$prefix}breeze_comments` (
			`id` INT(4) NOT NULL AUTO_INCREMENT,
			`status_id` INT(4) NOT NULL,
			`user_id` INT(4) NOT NULL,
			`likes` INT(4) NOT NULL DEFAULT 0,
			`body` TEXT,
			`created_at` VARCHAR(255) DEFAULT '',
			PRIMARY KEY (`id`),
			KEY `status_id` (`status_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS `{$prefix}user_likes` (
			`id_member` INT(4) NOT NULL,
			`content_type` VARCHAR(6) NOT NULL DEFAULT '',
			`content_id` INT(4) NOT NULL,
			`like_time` INT(11) NOT NULL,
			PRIMARY KEY (`content_id`, `content_type`, `id_member`),
			KEY `content_id` (`content_id`),
			KEY `id_member` (`id_member`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS `{$prefix}user_alerts` (
			`id_alert` INT UNSIGNED AUTO_INCREMENT,
			`alert_time` INT UNSIGNED NOT NULL DEFAULT '0',
			`id_member` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
			`id_member_started` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
			`member_name` VARCHAR(255) NOT NULL DEFAULT '',
			`content_type` VARCHAR(255) NOT NULL DEFAULT '',
			`content_id` INT UNSIGNED NOT NULL DEFAULT '0',
			`content_action` VARCHAR(255) NOT NULL DEFAULT '',
			`is_read` INT UNSIGNED NOT NULL DEFAULT '0',
			`extra` TEXT NOT NULL,
			PRIMARY KEY (id_alert),
			INDEX idx_id_member (id_member),
			INDEX idx_alert_time (alert_time)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS `{$prefix}members` (
			`id_member` INT(4) NOT NULL AUTO_INCREMENT,
			`member_name` VARCHAR(80) NOT NULL DEFAULT '',
			`real_name` VARCHAR(255) NOT NULL DEFAULT '',
			`pm_ignore_list` VARCHAR(255) NOT NULL DEFAULT '',
			`buddy_list` TEXT,
			PRIMARY KEY (`id_member`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS `{$prefix}breeze_options` (
			`member_id` INT(4) NOT NULL,
			`variable` VARCHAR(255) NOT NULL DEFAULT '',
			`value` TEXT,
			PRIMARY KEY (`member_id`, `variable`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");

	$pdo->exec("
		CREATE TABLE IF NOT EXISTS `{$prefix}breeze_buddy_requests` (
			`id` INT(4) NOT NULL AUTO_INCREMENT,
			`sender_id` INT(4) NOT NULL,
			`receiver_id` INT(4) NOT NULL,
			`status` TINYINT(1) NOT NULL DEFAULT 0,
			`created_at` INT(11) NOT NULL DEFAULT 0,
			PRIMARY KEY (`id`),
			UNIQUE KEY `sender_receiver` (`sender_id`, `receiver_id`),
			KEY `idx_receiver_status` (`receiver_id`, `status`),
			KEY `idx_sender_status` (`sender_id`, `status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
	");
}

/**
 * Truncate all test tables to ensure a clean state.
 *
 * @param PDO $pdo Active database connection
 * @param string $prefix Table prefix (e.g. 'smf_')
 */
function truncateTestTables(PDO $pdo, string $prefix): void
{
	$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_status`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_comments`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}user_likes`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}user_alerts`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}members`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_options`");
}

// Only run the setup script when executed directly (not when included by tests)
if (php_sapi_name() === 'cli' && realpath($argv[0] ?? '') === realpath(__FILE__)) {
	echo "Setting up test database...\n\n";

	global $testDbConfig;

	// Connect without database to create it
	try {
		$dsn = sprintf(
			'mysql:host=%s;port=%s;charset=utf8mb4',
			$testDbConfig['host'],
			$testDbConfig['port']
		);

		$pdo = new PDO(
			$dsn,
			$testDbConfig['username'],
			$testDbConfig['password'],
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			]
		);

		echo "✓ Connected to MySQL server\n";
	} catch (PDOException $e) {
		die("✗ Failed to connect to MySQL: " . $e->getMessage() . "\n");
	}

	// Create database if it doesn't exist
	try {
		$dbName = $testDbConfig['database'];
		$pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
		echo "✓ Database '$dbName' created/verified\n";

		$pdo->exec("USE `$dbName`");
	} catch (PDOException $e) {
		die("✗ Failed to create database: " . $e->getMessage() . "\n");
	}

	$prefix = $testDbConfig['prefix'];

	// Create tables
	echo "\nCreating tables...\n";

	try {
		createTestTables($pdo, $prefix);
		echo "✓ All tables created\n";
	} catch (PDOException $e) {
		die("✗ Failed to create tables: " . $e->getMessage() . "\n");
	}

	// Clear existing data
	echo "\nPopulating test data...\n";

	try {
		truncateTestTables($pdo, $prefix);
		echo "✓ Cleared existing test data\n";
	} catch (PDOException $e) {
		die("✗ Failed to populate test data: " . $e->getMessage() . "\n");
	}

	// Verify setup
	echo "\nVerifying setup...\n";

	try {
		$result = $pdo->query("SELECT COUNT(*) as count FROM `{$prefix}breeze_status`");
		$count = $result->fetch(PDO::FETCH_ASSOC)['count'];
		echo "✓ Status count: $count\n";

		$result = $pdo->query("SELECT COUNT(*) as count FROM `{$prefix}breeze_comments`");
		$count = $result->fetch(PDO::FETCH_ASSOC)['count'];
		echo "✓ Comment count: $count\n";

		$result = $pdo->query("SHOW INDEX FROM `{$prefix}breeze_status` WHERE Key_name = 'idx_wall_created_id'");
		if ($result->rowCount() > 0) {
			echo "✓ Composite index idx_wall_created_id exists\n";
		} else {
			echo "⚠ Warning: Composite index idx_wall_created_id not found\n";
		}

		$result = $pdo->query("SHOW INDEX FROM `{$prefix}breeze_status` WHERE Key_name = 'idx_user_created_id'");
		if ($result->rowCount() > 0) {
			echo "✓ Composite index idx_user_created_id exists\n";
		} else {
			echo "⚠ Warning: Composite index idx_user_created_id not found\n";
		}

	} catch (PDOException $e) {
		die("✗ Failed to verify setup: " . $e->getMessage() . "\n");
	}

	echo "\n✅ Test database setup complete!\n\n";
	echo "Database: {$testDbConfig['database']}\n";
	echo "Prefix: {$testDbConfig['prefix']}\n";
	echo "\nYou can now run tests:\n";
	echo "  ./breezeVendor/bin/phpunit\n\n";
}
