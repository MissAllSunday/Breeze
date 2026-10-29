<?php

declare(strict_types=1);

namespace Breeze\Integration;

use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Schema drift guard for the Breeze content tables.
 *
 * The test database is created by tests/setup-test-database.php, which must
 * mirror install.php. These assertions fail whenever the two drift apart, so
 * a schema regression cannot slip through the integration suite.
 *
 * When the shipped schema changes, update EXPECTED_SCHEMA (and only
 * EXPECTED_SCHEMA) plus install.php and tests/setup-test-database.php.
 */
class SchemaIntegrationTest extends TestCase
{
	/**
	 * The expected shape of both content tables, as defined by install.php.
	 */
	private const array EXPECTED_SCHEMA = [
		'breeze_status' => [
			'columns' => [
				'id' => 'bigint',
				'wall_id' => 'int',
				'user_id' => 'int',
				'body' => 'text',
				'created_at' => 'int',
			],
			'primary_key' => ['id'],
			'indexes' => [
				'idx_wall_created_id' => ['wall_id', 'created_at', 'id'],
				'idx_user_created_id' => ['user_id', 'created_at', 'id'],
			],
		],
		'breeze_comments' => [
			'columns' => [
				'id' => 'bigint',
				'status_id' => 'bigint',
				'user_id' => 'int',
				'body' => 'text',
				'created_at' => 'int',
			],
			'primary_key' => ['id'],
			'indexes' => [
				'idx_status_created_id' => ['status_id', 'created_at', 'id'],
				'user_id' => ['user_id'],
			],
		],
	];

	private static ?PDO $pdo = null;

	public static function setUpBeforeClass(): void
	{
		require_once __DIR__ . '/../setup-test-database.php';

		if (!isDatabaseAvailable()) {
			self::markTestSkipped('Database is not available. Run tests/setup-test-database.php first.');
		}

		self::$pdo = getTestDatabaseConnection();
	}

	public static function tableProvider(): array
	{
		return array_map(
			static fn (string $table): array => [$table],
			array_keys(self::EXPECTED_SCHEMA)
		);
	}

	#[DataProvider('tableProvider')]
	public function testColumnsExistWithExpectedTypes(string $table): void
	{
		$actualColumns = $this->getColumns($table);

		foreach (self::EXPECTED_SCHEMA[$table]['columns'] as $column => $expectedType) {
			$this->assertArrayHasKey(
				$column,
				$actualColumns,
				"Column {$table}.{$column} is missing from the test schema"
			);
			$this->assertSame(
				$expectedType,
				$actualColumns[$column],
				"Column {$table}.{$column} has an unexpected data type"
			);
		}
	}

	#[DataProvider('tableProvider')]
	public function testPrimaryKeyMatchesExpected(string $table): void
	{
		$this->assertSame(
			self::EXPECTED_SCHEMA[$table]['primary_key'],
			$this->getIndexColumns($table, 'PRIMARY'),
			"Primary key of {$table} does not match install.php"
		);
	}

	#[DataProvider('tableProvider')]
	public function testSecondaryIndexesMatchExpected(string $table): void
	{
		foreach (self::EXPECTED_SCHEMA[$table]['indexes'] as $indexName => $expectedColumns) {
			$actualColumns = $this->getIndexColumns($table, $indexName);

			$this->assertNotSame(
				[],
				$actualColumns,
				"Index {$table}.{$indexName} is missing from the test schema"
			);
			$this->assertSame(
				$expectedColumns,
				$actualColumns,
				"Index {$table}.{$indexName} does not cover the expected columns in order"
			);
		}
	}

	/**
	 * @return array<string, string> column name => data type
	 */
	private function getColumns(string $table): array
	{
		global $testDbConfig;

		$statement = self::$pdo->prepare(
			'SELECT COLUMN_NAME, DATA_TYPE
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table'
		);
		$statement->execute([
			':schema' => $testDbConfig['database'],
			':table' => $testDbConfig['prefix'] . $table,
		]);

		$columns = [];

		foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
			$columns[$row['COLUMN_NAME']] = $row['DATA_TYPE'];
		}

		return $columns;
	}

	/**
	 * @return list<string> the index columns, in sequence order
	 */
	private function getIndexColumns(string $table, string $indexName): array
	{
		global $testDbConfig;

		$statement = self::$pdo->prepare(
			'SELECT COLUMN_NAME
			FROM information_schema.STATISTICS
			WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table AND INDEX_NAME = :index
			ORDER BY SEQ_IN_INDEX ASC'
		);
		$statement->execute([
			':schema' => $testDbConfig['database'],
			':table' => $testDbConfig['prefix'] . $table,
			':index' => $indexName,
		]);

		return array_values($statement->fetchAll(PDO::FETCH_COLUMN));
	}
}
