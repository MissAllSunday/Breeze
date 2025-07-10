<?php

declare(strict_types=1);

namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class CommentHandledEntityTest extends TestCase
{
	public function testSettersAndGetters(): void
	{
		$entity = new CommentHandledEntity();

		// Test users info
		$usersInfo = [1 => ['name' => 'User1'], 2 => ['name' => 'User2']];
		$entity->setUsersInfo($usersInfo);
		$this->assertEquals($usersInfo, $entity->getUsersInfo());

		// Test likes info
		$likesInfo = new LikeHandledEntity();
		$entity->setLikesInfo($likesInfo);
		$this->assertSame($likesInfo, $entity->getLikesInfo());

		// Test null likes info
		$entity->setLikesInfo(null);
		$this->assertNull($entity->getLikesInfo());
	}

	public function testConstructorWithData(): void
	{
		$data = [
			'id' => 123,
			'statusId' => 456,
			'userId' => 789,
			'body' => 'Test comment body',
			'createdAt' => time(),
			'likes' => 3,
		];

		$entity = new CommentHandledEntity($data);

		$this->assertEquals(123, $entity->getId());
		$this->assertEquals(456, $entity->getStatusId());
		$this->assertEquals(789, $entity->getUserId());
		$this->assertEquals('Test comment body', $entity->getBody());
		$this->assertEquals(3, $entity->getLikes());
	}

	public function testGetWallId(): void
	{
		$entity = new CommentHandledEntity();
		
		// CommentHandledEntity always returns 0 for wallId
		$this->assertEquals(0, $entity->getWallId());
	}

	public function testJsonSerialize(): void
	{
		$entity = new CommentHandledEntity();
		$entity->setId(123);
		$entity->setStatusId(456);
		$entity->setUserId(789);
		$entity->setBody('Test comment');
		$entity->setCreatedAt(1640995200); // 2022-01-01 00:00:00 UTC

		// Set users info
		$usersInfo = [789 => ['name' => 'TestUser', 'avatar' => 'avatar.jpg']];
		$entity->setUsersInfo($usersInfo);

		// Set likes info
		$likesInfo = new LikeHandledEntity();
		$likesInfo->setCount(3);
		$entity->setLikesInfo($likesInfo);

		$result = $entity->jsonSerialize();

		$this->assertIsArray($result);
		$this->assertEquals(123, $result['id']);
		$this->assertEquals(456, $result['statusId']);
		$this->assertEquals(789, $result['userId']);
		$this->assertEquals('Test comment', $result['body']);
		$this->assertEquals('2022-01-01T00:00:00+00:00', $result['createdAt']);
		$this->assertEquals(0, $result['likes']); // deprecated field
		$this->assertSame($likesInfo, $result['likesInfo']);
		$this->assertEquals(['name' => 'TestUser', 'avatar' => 'avatar.jpg'], $result['userData']);
	}

	public function testJsonSerializeWithMissingUserData(): void
	{
		$entity = new CommentHandledEntity();
		$entity->setId(123);
		$entity->setUserId(789);
		$entity->setCreatedAt(time());

		// Don't set users info for this user
		$entity->setUsersInfo([456 => ['name' => 'OtherUser']]);

		$result = $entity->jsonSerialize();

		$this->assertEquals([], $result['userData']);
	}

	public function testInheritsFromCommentEntity(): void
	{
		$entity = new CommentHandledEntity();
		$this->assertInstanceOf(CommentEntity::class, $entity);
	}

	public function testImplementsHandledEntityInterface(): void
	{
		$entity = new CommentHandledEntity();
		$this->assertInstanceOf(HandledEntityInterface::class, $entity);
	}

	public function testImplementsJsonSerializable(): void
	{
		$entity = new CommentHandledEntity();
		$this->assertInstanceOf(\JsonSerializable::class, $entity);
	}

	public function testDefaultValues(): void
	{
		$entity = new CommentHandledEntity();

		$this->assertEquals([], $entity->getUsersInfo());
		$this->assertNull($entity->getLikesInfo());
	}

	public function testHandledEntityInterfaceMethods(): void
	{
		$entity = new CommentHandledEntity();

		// Test getUserId (inherited from CommentEntity)
		$entity->setUserId(123);
		$this->assertEquals(123, $entity->getUserId());

		// Test getId (inherited from CommentEntity)
		$entity->setId(456);
		$this->assertEquals(456, $entity->getId());

		// Test getWallId (overridden in CommentHandledEntity)
		$this->assertEquals(0, $entity->getWallId());

		// Test setBody/getBody (inherited from CommentEntity)
		$entity->setBody('Test body');
		$this->assertEquals('Test body', $entity->getBody());
	}

	public function testInheritedCommentEntityMethods(): void
	{
		$entity = new CommentHandledEntity();

		// Test statusId
		$entity->setStatusId(999);
		$this->assertEquals(999, $entity->getStatusId());

		// Test createdAt
		$timestamp = time();
		$entity->setCreatedAt($timestamp);
		$this->assertEquals($timestamp, $entity->getCreatedAt()->getTimestamp());

		// Test likes (deprecated)
		$entity->setLikes(5);
		$this->assertEquals(5, $entity->getLikes());
	}

	public function testUsersInfoHandling(): void
	{
		$entity = new CommentHandledEntity();
		
		// Test empty users info
		$this->assertEquals([], $entity->getUsersInfo());
		
		// Test setting complex users info
		$usersInfo = [
			123 => [
				'name' => 'John Doe',
				'avatar' => 'john.jpg',
				'link' => '/profile/john',
				'online' => true,
			],
			456 => [
				'name' => 'Jane Smith',
				'avatar' => 'jane.jpg',
				'link' => '/profile/jane',
				'online' => false,
			],
		];
		
		$entity->setUsersInfo($usersInfo);
		$this->assertEquals($usersInfo, $entity->getUsersInfo());
		
		// Test that the specific user data is correctly retrieved in jsonSerialize
		$entity->setUserId(123);
		$entity->setCreatedAt(time());
		
		$result = $entity->jsonSerialize();
		$this->assertEquals($usersInfo[123], $result['userData']);
	}
}
