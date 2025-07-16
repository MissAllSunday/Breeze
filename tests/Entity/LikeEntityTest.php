<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\LikesEnum;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class LikeEntityTest extends TestCase
{
	public function testGetTypes(): void
	{
		$this->assertEquals([
			LikesEnum::Status,
			LikesEnum::Comments,
		], LikeEntity::getTypes());
	}

	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id_member',
			'content_type',
			'content_id',
			'like_time',
		], LikeEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('user_likes', LikeEntity::getTableName());
	}

	public function testToInsert(): void
	{
		$entity = LikeEntity::from();
		$entity->setIdMember(123);
		$entity->setContentType(LikesEnum::Status);
		$entity->setContentId(456);
		$entity->setLikeTime(new DateTimeImmutable());

		$beforeTime = time();
		$result = $entity->toInsert();
		$afterTime = time();

		// Should contain all expected columns
		$expectedColumns = LikeEntity::getColumns();
		foreach ($expectedColumns as $column) {
			$this->assertArrayHasKey($column, $result);
		}

		// Should set like_time to current timestamp
		$this->assertArrayHasKey('like_time', $result);
		$this->assertIsInt($result['like_time']);
		$this->assertGreaterThanOrEqual($beforeTime, $result['like_time']);
		$this->assertLessThanOrEqual($afterTime, $result['like_time']);

		// Should preserve other values
		$this->assertEquals(123, $result['id_member']);
		$this->assertEquals(456, $result['content_id']);

		// Should convert enum to string value
		$this->assertEquals('br_sta', $result['content_type']);
		$this->assertIsString($result['content_type']);
	}

	public function testToInsertWithCommentsType(): void
	{
		$entity = LikeEntity::from();
		$entity->setIdMember(789);
		$entity->setContentType(LikesEnum::Comments);
		$entity->setContentId(101);
		$entity->setLikeTime(new DateTimeImmutable());

		$result = $entity->toInsert();

		// Should convert Comments enum to string value
		$this->assertEquals('br_com', $result['content_type']);
		$this->assertIsString($result['content_type']);
		$this->assertEquals(789, $result['id_member']);
		$this->assertEquals(101, $result['content_id']);
	}

	public function testToInsertOnlyReturnsDefinedColumns(): void
	{
		$entity = LikeEntity::from();
		$entity->setIdMember(123);
		$entity->setContentType(LikesEnum::Status);
		$entity->setContentId(456);
		$entity->setLikeTime(new DateTimeImmutable());

		$result = $entity->toInsert();

		// Should only contain the columns defined in getColumns()
		$expectedColumns = LikeEntity::getColumns();
		$this->assertCount(count($expectedColumns), $result);

		foreach (array_keys($result) as $key) {
			$this->assertContains($key, $expectedColumns);
		}
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function testCastValue(): void
	{
		$entity = LikeEntity::from();

		// Test integer casting for ID fields
		$this->assertEquals(123, $entity->castValue(LikeEntity::ID_MEMBER, '123'));
		$this->assertEquals(456, $entity->castValue(LikeEntity::ID, '456'));
		$this->assertEquals(5, $entity->castValue(LikeEntity::COUNT, '5'));

		// Test DateTime casting for time field
		$timestamp = time();
		$result = $entity->castValue(LikeEntity::TIME, $timestamp);
		$this->assertInstanceOf(\DateTimeImmutable::class, $result);
		$this->assertEquals($timestamp, $result->getTimestamp());

		// Test enum casting
		$this->assertEquals(LikesEnum::Status, $entity->castValue(LikeEntity::TYPE, 'br_sta'));
		$this->assertEquals(LikesEnum::Comments, $entity->castValue(LikeEntity::TYPE, 'br_com'));

		// Test default string casting
		$this->assertEquals('default', $entity->castValue('unknown_column', 'default'));
	}
}
