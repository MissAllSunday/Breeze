<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\Enums\LikesEnum;
use PHPUnit\Framework\TestCase;

class LikeInfoEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([], LikeInfoEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('user_likes', LikeInfoEntity::getTableName());
	}

	public function testToInsert(): void
	{
		$entity = LikeInfoEntity::from();
		$this->assertEquals([], $entity->toInsert());
	}

	public function testFrom(): void
	{

		$data = [
			'content_id' => 123,
			'text' => 'Test text',
			'href' => 'https://example.com',
			'can_like' => true,
			'already_liked' => false,
		];
		$entityWithData = LikeInfoEntity::from($data);
		$this->assertInstanceOf(LikeInfoEntity::class, $entityWithData);
		$this->assertEquals(123, $entityWithData->getContentId());
		$this->assertEquals('Test text', $entityWithData->getText());
		$this->assertEquals('https://example.com', $entityWithData->getHref());
		$this->assertTrue($entityWithData->canLike());
		$this->assertFalse($entityWithData->isAlreadyLiked());
	}

	public function testContentIdGetterAndSetter(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertEquals(0, $entity->getContentId());

		$entity->setContentId(123);
		$this->assertEquals(123, $entity->getContentId());

		$entity->setContentId(0);
		$this->assertEquals(0, $entity->getContentId());
	}

	public function testTextGetterAndSetter(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertEquals('', $entity->getText());

		$entity->setText('Test text');
		$this->assertEquals('Test text', $entity->getText());

		$entity->setText('');
		$this->assertEquals('', $entity->getText());

		$entity->setText('Special chars: àáâãäåæçèéêë');
		$this->assertEquals('Special chars: àáâãäåæçèéêë', $entity->getText());
	}

	public function testHrefGetterAndSetter(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertEquals('', $entity->getHref());

		$entity->setHref('https://example.com');
		$this->assertEquals('https://example.com', $entity->getHref());

		$entity->setHref('/relative/path');
		$this->assertEquals('/relative/path', $entity->getHref());

		$entity->setHref('');
		$this->assertEquals('', $entity->getHref());
	}

	public function testCanLikeGetterAndSetter(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertFalse($entity->canLike());

		$entity->setCanLike(true);
		$this->assertTrue($entity->canLike());

		$entity->setCanLike(false);
		$this->assertFalse($entity->canLike());
	}

	public function testAlreadyLikedGetterAndSetter(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertFalse($entity->isAlreadyLiked());

		$entity->setAlreadyLiked(true);
		$this->assertTrue($entity->isAlreadyLiked());

		$entity->setAlreadyLiked(false);
		$this->assertFalse($entity->isAlreadyLiked());
	}

	public function testLikesGetterAndSetter(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertEquals([], $entity->getLikes());

		$likes = ['like1', 'like2'];
		$entity->setLikes($likes);
		$this->assertEquals($likes, $entity->getLikes());

		$entity->setLikes([]);
		$this->assertEquals([], $entity->getLikes());
	}

	public function testPushLike(): void
	{
		$entity = LikeInfoEntity::from();

		$like1 = LikeEntity::from([
			LikeEntity::ID_MEMBER => 1,
			LikeEntity::TYPE => LikesEnum::Status->value, // Use string value
			LikeEntity::ID => 123,
			LikeEntity::TIME => time(),
		]);

		$like2 = LikeEntity::from([
			LikeEntity::ID_MEMBER => 2,
			LikeEntity::TYPE => LikesEnum::Comments->value, // Use string value
			LikeEntity::ID => 456,
			LikeEntity::TIME => time(),
		]);

		$entity->pushLike($like1);
		$this->assertCount(1, $entity->getLikes());
		$this->assertEquals($like1, $entity->getLikes()[0]);

		$entity->pushLike($like2);
		$this->assertCount(2, $entity->getLikes());
		$this->assertEquals($like1, $entity->getLikes()[0]);
		$this->assertEquals($like2, $entity->getLikes()[1]);
	}

	public function testPushLikeWithExistingLikes(): void
	{
		$entity = LikeInfoEntity::from();

		$existingLikes = ['existing1', 'existing2'];
		$entity->setLikes($existingLikes);

		$newLike = LikeEntity::from([
			LikeEntity::ID_MEMBER => 1,
			LikeEntity::TYPE => LikesEnum::Status->value, // Use string value
			LikeEntity::ID => 123,
			LikeEntity::TIME => time(),
		]);

		$entity->pushLike($newLike);

		$allLikes = $entity->getLikes();
		$this->assertCount(3, $allLikes);
		$this->assertEquals('existing1', $allLikes[0]);
		$this->assertEquals('existing2', $allLikes[1]);
		$this->assertEquals($newLike, $allLikes[2]);
	}

	public function testCastValueWithLikes(): void
	{
		$entity = LikeInfoEntity::from();

		// Test with array of arrays (should convert to LikeEntity objects)
		$likesData = [
			[
				LikeEntity::ID_MEMBER => 1,
				LikeEntity::TYPE => LikesEnum::Status->value,
				LikeEntity::ID => 123,
				LikeEntity::TIME => time(),
			],
			[
				LikeEntity::ID_MEMBER => 2,
				LikeEntity::TYPE => LikesEnum::Comments->value,
				LikeEntity::ID => 456,
				LikeEntity::TIME => time(),
			],
		];

		$result = $entity->castValue(LikeInfoEntity::LIKES, $likesData);

		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertInstanceOf(LikeEntity::class, $result[0]);
		$this->assertInstanceOf(LikeEntity::class, $result[1]);
	}

	public function testCastValueWithExistingLikeEntities(): void
	{
		$entity = LikeInfoEntity::from();

		$like1 = LikeEntity::from([
			LikeEntity::ID_MEMBER => 1,
			LikeEntity::TYPE => LikesEnum::Status->value, // Use string value
			LikeEntity::ID => 123,
			LikeEntity::TIME => time(),
		]);

		$like2 = LikeEntity::from([
			LikeEntity::ID_MEMBER => 2,
			LikeEntity::TYPE => LikesEnum::Comments->value, // Use string value
			LikeEntity::ID => 456,
			LikeEntity::TIME => time(),
		]);

		$likes = [$like1, $like2];

		$result = $entity->castValue(LikeInfoEntity::LIKES, $likes);

		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertEquals($like1, $result[0]);
		$this->assertEquals($like2, $result[1]);
	}

	public function testCastValueWithIntegerFields(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertEquals(123, $entity->castValue(LikeEntity::ID, '123'));
		$this->assertEquals(456, $entity->castValue(LikeEntity::ID, 456));
		$this->assertEquals(0, $entity->castValue(LikeEntity::ID, '0'));

		$this->assertEquals(5, $entity->castValue(LikeEntity::COUNT, '5'));
		$this->assertEquals(10, $entity->castValue(LikeEntity::COUNT, 10));
	}

	public function testCastValueWithBooleanFields(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertTrue($entity->castValue(LikeEntity::CAN_LIKE, 1));
		$this->assertFalse($entity->castValue(LikeEntity::ALREADY_LIKED, 0));
	}

	public function testCastValueWithDefaultStringCasting(): void
	{
		$entity = LikeInfoEntity::from();

		$this->assertEquals('test', $entity->castValue('unknown_field', 'test'));
		$this->assertEquals('123', $entity->castValue('unknown_field', 123));
		$this->assertEquals('1', $entity->castValue('unknown_field', true));
		$this->assertEquals('', $entity->castValue('unknown_field', false));
	}

	public function testJsonSerialize(): void
	{
		$entity = LikeInfoEntity::from();
		$entity->setContentId(123);
		$entity->setText('Test text');
		$entity->setHref('https://example.com');
		$entity->setContentType(LikesEnum::Status);

		$like1 = LikeEntity::from([
			LikeEntity::ID_MEMBER => 1,
			LikeEntity::TYPE => LikesEnum::Status->value, // Use string value
			LikeEntity::ID => 123,
			LikeEntity::TIME => time(),
		]);

		$like2 = LikeEntity::from([
			LikeEntity::ID_MEMBER => 2,
			LikeEntity::TYPE => LikesEnum::Comments->value, // Use string value
			LikeEntity::ID => 456,
			LikeEntity::TIME => time(),
		]);

		$entity->pushLike($like1);
		$entity->pushLike($like2);

		$result = $entity->jsonSerialize();

		$this->assertIsArray($result);
		$this->assertArrayHasKey('likes', $result);
		$this->assertArrayHasKey('count', $result);
		$this->assertArrayHasKey('contentId', $result);
		$this->assertArrayHasKey('text', $result);
		$this->assertArrayHasKey('href', $result);

		$this->assertEquals([$like1, $like2], $result['likes']);
		$this->assertEquals(2, $result['count']);
		$this->assertEquals(123, $result['contentId']);
		$this->assertEquals('Test text', $result['text']);
		$this->assertEquals('https://example.com', $result['href']);
	}

	public function testJsonSerializeWithEmptyLikes(): void
	{
		$entity = LikeInfoEntity::from();
		$entity->setContentId(456);
		$entity->setText('Empty likes test');
		$entity->setHref('/test/path');
		$entity->setContentType(LikesEnum::Status);

		$result = $entity->jsonSerialize();

		$this->assertEquals([], $result['likes']);
		$this->assertEquals(0, $result['count']);
		$this->assertEquals(456, $result['contentId']);
		$this->assertEquals('Empty likes test', $result['text']);
		$this->assertEquals('/test/path', $result['href']);
	}
}
