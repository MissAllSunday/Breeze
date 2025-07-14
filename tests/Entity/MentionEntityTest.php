<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class MentionEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'content_id',
			'content_type',
			'id_mentioned',
			'id_member',
			'time',
		], MentionEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('mentions', MentionEntity::getTableName());
	}

	/**
	 * @throws \DateMalformedStringException
	 */
	public function testCastValue(): void
	{
		$entity = MentionEntity::from();

		// Test integer casting for ID fields
		$this->assertEquals(123, $entity->castValue(MentionEntity::CONTENT_ID, '123'));
		$this->assertEquals(456, $entity->castValue(MentionEntity::ID_MENTIONED, '456'));
		$this->assertEquals(789, $entity->castValue(MentionEntity::ID_MEMBER, '789'));

		// Test DateTime casting for time field
		$timestamp = time();
		$result = $entity->castValue(MentionEntity::TIME, $timestamp);
		$this->assertInstanceOf(\DateTimeImmutable::class, $result);
		$this->assertEquals($timestamp, $result->getTimestamp());

		// Test default string casting
		$this->assertEquals('br_sta', $entity->castValue(MentionEntity::CONTENT_TYPE, 'br_sta'));
		$this->assertEquals('default', $entity->castValue('unknown_column', 'default'));
	}
}
