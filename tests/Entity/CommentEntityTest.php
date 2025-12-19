<?php

declare(strict_types=1);

namespace Breeze\Entity;

use Breeze\Fixtures\CommentFixtures;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class CommentEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id',
			'statusId',
			'userId',
			'createdAt',
			'body',
			'likes',
		], CommentEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_comments', CommentEntity::getTableName());
	}

	public function testFromArray(): void
	{
		$data = CommentFixtures::basic();
		$entity = CommentEntity::from($data);

		$this->assertEquals($data[CommentEntity::ID], $entity->getId());
		$this->assertEquals($data[CommentEntity::STATUS_ID], $entity->getStatusId());
		$this->assertEquals($data[CommentEntity::USER_ID], $entity->getUserId());
		$this->assertEquals($data[CommentEntity::BODY], $entity->getBody());
	}

	public function testToInsert(): void
	{
		$entity = CommentEntity::from(CommentFixtures::basic());

		$result = $entity->toInsert();

		$this->assertArrayNotHasKey('id', $result);
		$this->assertArrayHasKey('statusId', $result);
		$this->assertArrayHasKey('userId', $result);
		$this->assertArrayHasKey('body', $result);
		$this->assertArrayHasKey('createdAt', $result);
	}

	public function testToInsertWithoutId(): void
	{
		$entity = CommentEntity::from();
		$entity->setStatusId(456);
		$entity->setUserId(789);
		$entity->setBody('Test comment');
		$entity->setCreatedAt(new DateTimeImmutable());

		$result = $entity->toInsert();

		// Should not contain ID field even if it wasn't set
		$this->assertArrayNotHasKey('id', $result);

		// Should still set createdAt
		$this->assertArrayHasKey('createdAt', $result);
		$this->assertIsInt($result['createdAt']);

		// Should preserve other values
		$this->assertEquals(456, $result['statusId']);
		$this->assertEquals(789, $result['userId']);
		$this->assertEquals('Test comment', $result['body']);
	}

	public function testToInsertOnlyReturnsDefinedColumns(): void
	{
		$entity = CommentEntity::from();
		$entity->setStatusId(123);
		$entity->setUserId(456);
		$entity->setBody('Test');
		$entity->setCreatedAt(new DateTimeImmutable());

		$result = $entity->toInsert();

		// Should only contain the columns defined in getColumns() (minus id)
		$expectedColumns = array_filter(CommentEntity::getColumns(), fn ($col) => $col !== 'id');
		$this->assertCount(count($expectedColumns), $result);

		foreach (array_keys($result) as $key) {
			$this->assertContains($key, CommentEntity::getColumns());
		}
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function testCastValue(): void
	{
		$entity = CommentEntity::from();

		// Test integer casting for ID fields
		$this->assertEquals(123, $entity->castValue(CommentEntity::ID, '123'));
		$this->assertEquals(456, $entity->castValue(CommentEntity::STATUS_ID, '456'));
		$this->assertEquals(789, $entity->castValue(CommentEntity::USER_ID, '789'));
		$this->assertEquals(5, $entity->castValue(CommentEntity::LIKES, '5'));

		// Test DateTime casting for createdAt
		$timestamp = time();
		$result = $entity->castValue(SharedEntity::CREATED_AT, $timestamp);
		$this->assertInstanceOf(\DateTimeImmutable::class, $result);
		$this->assertEquals($timestamp, $result->getTimestamp());

		// Test default string casting
		$this->assertEquals('Test comment body', $entity->castValue(CommentEntity::BODY, 'Test comment body'));
		$this->assertEquals('default', $entity->castValue('unknown_column', 'default'));
	}

	public function testToArray(): void
	{
		$data = CommentFixtures::basic();
		$entity = CommentEntity::from($data);

		$result = $entity->toArray();

		$this->assertEquals($data[CommentEntity::ID], $result[CommentEntity::ID]);
		$this->assertEquals($data[CommentEntity::BODY], $result[CommentEntity::BODY]);
	}

	public function testJsonSerialize(): void
	{
		$entity = CommentEntity::from(CommentFixtures::basic());

		$result = $entity->jsonSerialize();

		$this->assertIsArray($result);
		$this->assertArrayHasKey('id', $result);
		$this->assertArrayHasKey('body', $result);
	}

	public function testSettersAndGetters(): void
	{
		$entity = CommentEntity::from();

		$entity->setId(123);
		$this->assertEquals(123, $entity->getId());

		$entity->setStatusId(456);
		$this->assertEquals(456, $entity->getStatusId());

		$entity->setUserId(789);
		$this->assertEquals(789, $entity->getUserId());

		$entity->setBody('Test comment');
		$this->assertEquals('Test comment', $entity->getBody());

		$entity->setLikes(5);
		$this->assertEquals(5, $entity->getLikes());
	}
}
