<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class StatusEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id',
			'wallId',
			'userId',
			'createdAt',
			'body',
			'likes',
		], StatusEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_status', StatusEntity::getTableName());
	}

	public function testToInsert(): void
	{
		$entity = StatusEntity::from();
		$entity->setId(123);
		$entity->setWallId(456);
		$entity->setUserId(789);
		$entity->setBody('Test status body');
		$entity->setCreatedAt(new DateTimeImmutable());

		$beforeTime = time();
		$result = $entity->toInsert();
		$afterTime = time();

		// Should not contain the ID field
		$this->assertArrayNotHasKey('id', $result);

		// Should contain all expected columns except id
		$expectedColumns = StatusEntity::getColumns();
		foreach ($expectedColumns as $column) {
			if ($column !== 'id') {
				$this->assertArrayHasKey($column, $result);
			}
		}

		// Should set createdAt to current timestamp
		$this->assertArrayHasKey('createdAt', $result);
		$this->assertIsInt($result['createdAt']);
		$this->assertGreaterThanOrEqual($beforeTime, $result['createdAt']);
		$this->assertLessThanOrEqual($afterTime, $result['createdAt']);

		// Should preserve other values
		$this->assertEquals(456, $result['wallId']);
		$this->assertEquals(789, $result['userId']);
		$this->assertEquals('Test status body', $result['body']);
		$this->assertEquals(0, $result['likes']);
	}

	public function testToInsertWithoutId(): void
	{
		$entity = StatusEntity::from();
		$entity->setWallId(456);
		$entity->setUserId(789);
		$entity->setBody('Test status');
		$entity->setCreatedAt(new DateTimeImmutable());

		$result = $entity->toInsert();

		// Should not contain ID field even if it wasn't set
		$this->assertArrayNotHasKey('id', $result);

		// Should still set createdAt
		$this->assertArrayHasKey('createdAt', $result);
		$this->assertIsInt($result['createdAt']);

		// Should preserve other values
		$this->assertEquals(456, $result['wallId']);
		$this->assertEquals(789, $result['userId']);
		$this->assertEquals('Test status', $result['body']);
	}

	public function testToInsertOnlyReturnsDefinedColumns(): void
	{
		$entity = StatusEntity::from();
		$entity->setWallId(123);
		$entity->setUserId(456);
		$entity->setBody('Test');
		$entity->setCreatedAt(new DateTimeImmutable());

		$result = $entity->toInsert();

		// Should only contain the columns defined in getColumns() (minus id)
		$expectedColumns = array_filter(StatusEntity::getColumns(), fn ($col) => $col !== 'id');
		$this->assertCount(count($expectedColumns), $result);

		foreach (array_keys($result) as $key) {
			$this->assertContains($key, StatusEntity::getColumns());
		}
	}

	/**
	 * @throws \DateMalformedStringException
	 */
	public function testCastValue(): void
	{
		$entity = StatusEntity::from();

		// Test integer casting for ID fields
		$this->assertEquals(123, $entity->castValue(StatusEntity::ID, '123'));
		$this->assertEquals(456, $entity->castValue(StatusEntity::WALL_ID, '456'));
		$this->assertEquals(789, $entity->castValue(StatusEntity::USER_ID, '789'));
		$this->assertEquals(10, $entity->castValue(StatusEntity::LIKES, '10'));

		// Test DateTime casting for createdAt
		$timestamp = time();
		$result = $entity->castValue(SharedEntity::CREATED_AT, $timestamp);
		$this->assertInstanceOf(\DateTimeImmutable::class, $result);
		$this->assertEquals($timestamp, $result->getTimestamp());

		// Test default string casting
		$this->assertEquals('Test status body', $entity->castValue(StatusEntity::BODY, 'Test status body'));
		$this->assertEquals('default', $entity->castValue('unknown_column', 'default'));
	}
}
