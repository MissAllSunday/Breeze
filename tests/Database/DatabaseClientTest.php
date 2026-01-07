<?php

declare(strict_types=1);

namespace Breeze\Database;

use PHPUnit\Framework\TestCase;

class DatabaseClientTest extends TestCase
{
	private DatabaseClient $client;

	private array $mockSmcFunc;

	protected function setUp(): void
	{
		// Create mock database functions
		$this->mockSmcFunc = [
			'db_query' => function ($identifier, $query, $params) {
				// Return bool for UPDATE/DELETE, resource-like for SELECT
				if (str_contains($query, 'UPDATE') || str_contains($query, 'DELETE')) {
					return true;
				}

				return 'query_result';
			},
			'db_quote' => function ($identifier, $query, $params) {
				return 'quoted_query';
			},
			'db_fetch_assoc' => function ($result) {
				return ['id' => 1, 'name' => 'test'];
			},
			'db_num_rows' => function ($result) {
				return 5;
			},
			'db_free_result' => function ($result) {
				return null;
			},
			'db_insert' => function ($method, $table, $columns, $data, $keys, $returnInsertId = 0) {
				return $returnInsertId ? 123 : null;
			},
			'db_insert_id' => function ($table, $column) {
				return 456;
			},
		];

		$GLOBALS['smcFunc'] = $this->mockSmcFunc;
		$this->client = new DatabaseClient();
	}

	protected function tearDown(): void
	{
		unset($GLOBALS['smcFunc']);
	}

	public function testConstructorInitializesDb(): void
	{
		$reflection = new \ReflectionClass($this->client);
		$property = $reflection->getProperty('db');
		$property->setAccessible(true);
		$value = $property->getValue($this->client);

		$this->assertIsArray($value);
		$this->assertArrayHasKey('db_query', $value);
	}

	public function testQuery(): void
	{
		$result = $this->client->query('SELECT * FROM test', []);

		$this->assertEquals('query_result', $result);
	}

	public function testQueryQuote(): void
	{
		$result = $this->client->queryQuote('SELECT * FROM test WHERE id = {int:id}', ['id' => 1]);

		$this->assertEquals('quoted_query', $result);
	}

	public function testFetchAssoc(): void
	{
		$result = $this->client->fetchAssoc('some_result');

		$this->assertIsArray($result);
		$this->assertArrayHasKey('id', $result);
		$this->assertArrayHasKey('name', $result);
		$this->assertEquals(1, $result['id']);
		$this->assertEquals('test', $result['name']);
	}

	public function testNumRows(): void
	{
		$result = $this->client->numRows('some_result');

		$this->assertEquals(5, $result);
	}

	public function testFreeResult(): void
	{
		// Should not throw any errors
		$this->client->freeResult('some_result');
		$this->assertTrue(true);
	}

	public function testInsert(): void
	{
		$columns = ['name' => 'string', 'age' => 'int'];
		$data = ['name' => 'John', 'age' => 30];

		// Should not throw any errors
		$this->client->insert('users', $columns, $data, 'id');
		$this->assertTrue(true);
	}

	public function testInsertSortsColumnsAndData(): void
	{
		$columns = ['z_field' => 'string', 'a_field' => 'int'];
		$data = ['z_field' => 'value', 'a_field' => 1];

		// Should not throw any errors - internally it sorts the arrays
		$this->client->insert('users', $columns, $data, 'id');
		$this->assertTrue(true);
	}

	public function testReplace(): void
	{
		$columns = ['name' => 'string', 'age' => 'int'];
		$data = ['name' => 'John', 'age' => 30];

		$result = $this->client->replace('users', $columns, $data, 'id');

		$this->assertEquals(123, $result);
	}

	public function testGetInsertedId(): void
	{
		$result = $this->client->getInsertedId('users', 'id');

		$this->assertEquals(456, $result);
	}

	public function testUpdate(): void
	{
		$result = $this->client->update(
			'users',
			'SET name = {string:name} WHERE id = {int:id}',
			['name' => 'Jane', 'id' => 1]
		);

		$this->assertTrue($result);
	}

	public function testDelete(): void
	{
		$result = $this->client->delete(
			'users',
			'WHERE id = {int:id}',
			['id' => 1]
		);

		$this->assertTrue($result);
	}

	public function testImplementsClientInterface(): void
	{
		$this->assertInstanceOf(ClientInterface::class, $this->client);
	}
}
