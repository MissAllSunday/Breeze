<?php

declare(strict_types=1);

/**
 * Test Database Setup Script
 *
 * This script creates and populates a test database for running
 * integration and performance tests.
 *
 * Usage:
 *   php tests/setup-test-database.php
 */

require_once __DIR__ . '/database-config.php';

echo "Setting up test database...\n\n";

// Get database configuration
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

	// Select the database
	$pdo->exec("USE `$dbName`");
} catch (PDOException $e) {
	die("✗ Failed to create database: " . $e->getMessage() . "\n");
}

// Create tables based on install.php schema
$prefix = $testDbConfig['prefix'];

echo "\nCreating tables...\n";

// Create breeze_status table
try {
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
	echo "✓ Created table: {$prefix}breeze_status\n";
} catch (PDOException $e) {
	die("✗ Failed to create breeze_status table: " . $e->getMessage() . "\n");
}

// Create breeze_comments table
try {
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
	echo "✓ Created table: {$prefix}breeze_comments\n";
} catch (PDOException $e) {
	die("✗ Failed to create breeze_comments table: " . $e->getMessage() . "\n");
}

// Create user_likes table (SMF table for likes)
try {
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
	echo "✓ Created table: {$prefix}user_likes\n";
} catch (PDOException $e) {
	die("✗ Failed to create user_likes table: " . $e->getMessage() . "\n");
}

// Create members table (mock for testing)
try {
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
	echo "✓ Created table: {$prefix}members\n";
} catch (PDOException $e) {
	die("✗ Failed to create members table: " . $e->getMessage() . "\n");
}

// Create breeze_options table
try {
	$pdo->exec("
        CREATE TABLE IF NOT EXISTS `{$prefix}breeze_options` (
            `member_id` INT(4) NOT NULL,
            `variable` VARCHAR(255) NOT NULL DEFAULT '',
            `value` TEXT,
            PRIMARY KEY (`member_id`, `variable`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
	echo "✓ Created table: {$prefix}breeze_options\n";
} catch (PDOException $e) {
	die("✗ Failed to create breeze_options table: " . $e->getMessage() . "\n");
}

// Populate with test data
echo "\nPopulating test data...\n";

try {
	// Clear existing data
	$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_status`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_comments`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}user_likes`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}members`");
	$pdo->exec("TRUNCATE TABLE `{$prefix}breeze_options`");

	// Insert test statuses for performance testing
	$baseTime = time();
	$statusCount = 2000; // Create 2000 statuses for performance testing

	echo "Inserting $statusCount test statuses...\n";

	$stmt = $pdo->prepare("
        INSERT INTO `{$prefix}breeze_status`
        (wall_id, user_id, created_at, body, likes)
        VALUES (?, ?, ?, ?, ?)
    ");

	for ($i = 1; $i <= $statusCount; $i++) {
		$wallId = ($i % 10) + 1; // Distribute across 10 walls
		$userId = ($i % 5) + 1; // 5 different users
		$createdAt = $baseTime - ($i * 60); // 1 minute apart
		$body = "Test status #$i for performance testing";
		$likes = rand(0, 50);

		$stmt->execute([$wallId, $userId, $createdAt, $body, $likes]);

		if ($i % 100 == 0) {
			echo "  Inserted $i/$statusCount statuses...\n";
		}
	}

	echo "✓ Inserted $statusCount test statuses\n";

	// Insert some comments
	echo "Inserting test comments...\n";
	$stmt = $pdo->prepare("
        INSERT INTO `{$prefix}breeze_comments`
        (status_id, user_id, created_at, body, likes)
        VALUES (?, ?, ?, ?, ?)
    ");

	for ($i = 1; $i <= 100; $i++) {
		$statusId = rand(1, min(100, $statusCount));
		$userId = rand(1, 5);
		$createdAt = (string)($baseTime - ($i * 30));
		$body = "Test comment #$i";
		$likes = rand(0, 10);

		$stmt->execute([$statusId, $userId, $createdAt, $body, $likes]);
	}

	echo "✓ Inserted 100 test comments\n";

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

	// Verify indexes
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
echo "\nYou can now run performance tests:\n";
echo "  ./breezeVendor/bin/phpunit tests/Performance/PaginationPerformanceTest.php\n\n";
