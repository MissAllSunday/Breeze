<?php

declare(strict_types=1);

namespace Breeze\Entity;

use Breeze\LikesEnum;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;

class LikeHandledEntityTest extends TestCase
{
	public function testSettersAndGetters(): void
	{
		$entity = new LikeHandledEntity();

		// Test count
		$entity->setCount(5);
		$this->assertEquals(5, $entity->getCount());

		// Test already liked
		$entity->setAlreadyLiked(true);
		$this->assertTrue($entity->isAlreadyLiked());

		$entity->setAlreadyLiked(false);
		$this->assertFalse($entity->isAlreadyLiked());

		// Test can like
		$entity->setCanLike(true);
		$this->assertTrue($entity->canLike());

		$entity->setCanLike(false);
		$this->assertFalse($entity->canLike());

		// Test additional info
		$additionalInfo = ['text' => 'test', 'href' => 'https://example.com'];
		$entity->setAdditionalInfo($additionalInfo);
		$this->assertEquals($additionalInfo, $entity->getAdditionalInfo());
	}

	public function testConstructorWithData(): void
	{
		$data = [
			'count' => 10,
			'already_liked' => true,
			'can_like' => false,
			'additional_info' => ['key' => 'value'],
			'content_type' => LikesEnum::Status,
			'content_id' => 123,
			'id_member' => 456,
			'like_time' => time(),
		];

		$entity = new LikeHandledEntity($data);

		$this->assertEquals(10, $entity->getCount());
		$this->assertTrue($entity->isAlreadyLiked());
		$this->assertFalse($entity->canLike());
		$this->assertEquals(['key' => 'value'], $entity->getAdditionalInfo());
		$this->assertEquals(LikesEnum::Status, $entity->getContentType());
		$this->assertEquals(123, $entity->getContentId());
		$this->assertEquals(456, $entity->getIdMember());
	}

	public function testJsonSerialize(): void
	{
		$entity = new LikeHandledEntity();
		$entity->setContentId(123);
		$entity->setCount(5);
		$entity->setAlreadyLiked(true);
		$entity->setCanLike(false);
		$entity->setContentType(LikesEnum::Comments);
		$entity->setAdditionalInfo(['test' => 'data']);

		$likeTime = new DateTimeImmutable();
		$entity->setLikeTime($likeTime);

		$result = $entity->jsonSerialize();

		$this->assertIsArray($result);
		$this->assertEquals(123, $result['contentId']);
		$this->assertEquals(5, $result['count']);
		$this->assertTrue($result['alreadyLiked']);
		$this->assertFalse($result['canLike']);
		$this->assertEquals('br_com', $result['type']);
		$this->assertEquals(['test' => 'data'], $result['additionalInfo']);
		$this->assertEquals($likeTime->format(DateTimeInterface::ATOM), $result['likeTime']);
	}

	public function testInheritsFromLikeEntity(): void
	{
		$entity = new LikeHandledEntity();
		$this->assertInstanceOf(LikeEntity::class, $entity);
	}

	public function testImplementsJsonSerializable(): void
	{
		$entity = new LikeHandledEntity();
		$this->assertInstanceOf(\JsonSerializable::class, $entity);
	}

	public function testDefaultValues(): void
	{
		$entity = new LikeHandledEntity();

		$this->assertEquals(0, $entity->getCount());
		$this->assertFalse($entity->isAlreadyLiked());
		$this->assertFalse($entity->canLike());
		$this->assertEquals([], $entity->getAdditionalInfo());
	}

	public function testConstants(): void
	{
		$this->assertEquals('can_like', LikeHandledEntity::CAN_LIKE);
		$this->assertEquals('count', LikeHandledEntity::COUNT);
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function testCastValue(): void
	{
		$entity = new LikeHandledEntity();

		// Test count casting
		$this->assertEquals(5, $entity->castValue(LikeHandledEntity::COUNT, '5'));
		$this->assertEquals(0, $entity->castValue(LikeHandledEntity::COUNT, '0'));

		// Test can_like casting
		$this->assertTrue($entity->castValue(LikeHandledEntity::CAN_LIKE, '1'));
		$this->assertFalse($entity->castValue(LikeHandledEntity::CAN_LIKE, '0'));
		$this->assertTrue($entity->castValue(LikeHandledEntity::CAN_LIKE, true));
		$this->assertFalse($entity->castValue(LikeHandledEntity::CAN_LIKE, false));

		// Test content_type casting
		$this->assertEquals(LikesEnum::Status, $entity->castValue('content_type', 'br_sta'));
		$this->assertEquals(LikesEnum::Comments, $entity->castValue('content_type', 'br_com'));

		// Test like_time casting
		$timestamp = time();
		$result = $entity->castValue('like_time', $timestamp);
		$this->assertInstanceOf(DateTimeImmutable::class, $result);
		$this->assertEquals($timestamp, $result->getTimestamp());

		// Test null like_time
		$this->assertNull($entity->castValue('like_time', null));

		// Test default casting
		$this->assertEquals('test', $entity->castValue('unknown_column', 'test'));
	}
}
