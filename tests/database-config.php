<?php

declare(strict_types=1);

/**
 * Database configuration for PHPUnit tests
 *
 * This file provides database connection configuration for running
 * integration and performance tests that require a real database.
 *
 * Configuration priority:
 * 1. Environment variables (for CI/CD)
 * 2. Local .env file (for development)
 * 3. Local configuration file (for development)
 * 4. Default values (for quick setup)
 */

// Check if we're in CI environment
$isCI = getenv('CI') === 'true' || getenv('GITHUB_ACTIONS') === 'true';

// Load .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
	$lines = file($envFile, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
	foreach ($lines as $line) {
		if (str_starts_with(trim($line), '#')) {
			continue;
		}
		if (str_contains($line, '=')) {
			[$name, $value] = explode('=', $line, 2);
			$name = trim($name);
			$value = trim($value);

			// Remove quotes if present
			if (preg_match('/^"(.*)"$/', $value, $matches)) {
				$value = $matches[1];
			} elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
				$value = $matches[1];
			}

			if (getenv($name) === false) {
				putenv(sprintf('%s=%s', $name, $value));
				$_ENV[$name] = $value;
				$_SERVER[$name] = $value;
			}
		}
	}
}

// Database configuration from environment variables or defaults
global $testDbConfig;
$testDbConfig = [
	'host' => getenv('DB_HOST') ?: '127.0.0.1',
	'port' => getenv('DB_PORT') ?: '3306',
	'database' => getenv('DB_DATABASE') ?: 'breeze_test',
	'username' => getenv('DB_USERNAME') ?: 'root',
	'password' => getenv('DB_PASSWORD') ?: '',
	'prefix' => getenv('DB_PREFIX') ?: 'smf_',
];

// Override with local config if it exists (for development)
$localConfigFile = __DIR__ . '/database-config.local.php';
if (file_exists($localConfigFile)) {
	$localConfig = require $localConfigFile;
	$testDbConfig = array_merge($testDbConfig, $localConfig);
}

/**
 * Initialize database connection for tests
 *
 * @return PDO|null Database connection or null if connection fails
 */
function getTestDatabaseConnection(): ?PDO
{
	global $testDbConfig;

	// Return null if config is not properly set
	if (!is_array($testDbConfig) || empty($testDbConfig['host']) || empty($testDbConfig['database'])) {
		return null;
	}

	try {
		$dsn = sprintf(
			'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
			$testDbConfig['host'],
			$testDbConfig['port'],
			$testDbConfig['database']
		);

		$pdo = new PDO(
			$dsn,
			$testDbConfig['username'],
			$testDbConfig['password'],
			[
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			]
		);

		return $pdo;
	} catch (PDOException $e) {
		// Connection failed - tests will be skipped
		return null;
	}
}

/**
 * Check if database connection is available
 *
 * @return bool True if database is available
 */
function isDatabaseAvailable(): bool
{
	return getTestDatabaseConnection() !== null;
}

/**
 * Initialize SMF database functions for tests
 *
 * This function sets up the global $smcFunc array with database
 * functions that tests can use.
 */
function initializeSmfDatabaseFunctions(): void
{
	global $smcFunc, $testDbConfig;

	$pdo = getTestDatabaseConnection();
	if ($pdo === null) {
		return;
	}

	// Mock SMF's database functions using PDO
	$smcFunc['db_query'] = function (string $identifier, string $query, array $params = []) use ($pdo, $testDbConfig) {
		// Replace SMF placeholders with PDO placeholders
		$query = str_replace('{db_prefix}', $testDbConfig['prefix'], $query);

		// Handle SMF's parameter format
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				// Handle array parameters (e.g., {array_int:ids})
				$placeholders = implode(',', array_fill(0, count($value), '?'));
				$query = preg_replace('/\{array_\w+:' . preg_quote($key, '/') . '\}/', $placeholders, $query);
			} else {
				// Handle single parameters
				$query = preg_replace('/\{\w+:' . preg_quote($key, '/') . '\}/', ':' . $key, $query);
			}
		}

		// Remove {raw:...} placeholders
		$query = preg_replace('/\{raw:(\w+)\}/', ':$1', $query);

		$stmt = $pdo->prepare($query);

		// Bind parameters
		$bindIndex = 1;
		foreach ($params as $key => $value) {
			if (is_array($value)) {
				foreach ($value as $arrayValue) {
					$stmt->bindValue($bindIndex++, $arrayValue);
				}
			} else {
				$stmt->bindValue(':' . $key, $value);
			}
		}

		$stmt->execute();

		return $stmt;
	};

	$smcFunc['db_fetch_assoc'] = function ($result) {
		if ($result instanceof PDOStatement) {
			return $result->fetch(PDO::FETCH_ASSOC);
		}

		return false;
	};

	$smcFunc['db_fetch_row'] = function ($result) {
		if ($result instanceof PDOStatement) {
			return $result->fetch(PDO::FETCH_NUM);
		}

		return false;
	};

	$smcFunc['db_num_rows'] = function ($result) {
		if ($result instanceof PDOStatement) {
			return $result->rowCount();
		}

		return 0;
	};

	$smcFunc['db_free_result'] = function ($result): void {
		if ($result instanceof PDOStatement) {
			$result->closeCursor();
		}
	};

	$smcFunc['db_insert_id'] = function (string $table) use ($pdo) {
		return (int) $pdo->lastInsertId();
	};
}

return $testDbConfig;
