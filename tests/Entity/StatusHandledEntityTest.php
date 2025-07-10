<?php

declare(strict_types=1);

namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class StatusHandledEntityTest extends TestCase
{
	public function testSettersAndGetters(): void
	{
		$entity = new StatusHandledEntity();

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

		// Test comments
		$comment1 = new CommentHandledEntity();
		$comment2 = new CommentHandledEntity();
		$comments = [$comment1, $comment2];
		$entity->setComments($comments);
		$this->assertEquals($comments, $entity->getComments());

		// Test isNew
		$entity->setIsNew(true);
		$this->assertTrue($entity->isNew());

		$entity->setIsNew(false);
		$this->assertFalse($entity->isNew());
	}

	public function testConstructorWithData(): void
	{
		$data = [
			'id' => 123,
			'wallId' => 456,
			'userId' => 789,
			'body' => 'Test status body',
			'createdAt' => time(),
		];

		$entity = new StatusHandledEntity($data);

		$this->assertEquals(123, $entity->getId());
		$this->assertEquals(456, $entity->getWallId());
		$this->assertEquals(789, $entity->getUserId());
		$this->assertEquals('Test status body', $entity->getBody());
	}

	public function testJsonSerialize(): void
	{
		$entity = new StatusHandledEntity();
		$entity->setId(123);
		$entity->setWallId(456);
		$entity->setUserId(789);
		$entity->setBody('Test status');
		$entity->setCreatedAt(666);
		$entity->setIsNew(true);

		// Set users info
		$usersInfo = [789 => ['name' => 'TestUser', 'avatar' => 'avatar.jpg']];
		$entity->setUsersInfo($usersInfo);

		// Set likes info
		$likesInfo = new LikeHandledEntity();
		$likesInfo->setCount(5);
		$entity->setLikesInfo($likesInfo);

		// Set comments
		$comment = new CommentHandledEntity();
		$comment->setId(1);
		$entity->setComments([$comment]);

		$result = $entity->jsonSerialize();

		$this->assertIsArray($result);
		$this->assertEquals(123, $result['id']);
		$this->assertEquals(456, $result['wallId']);
		$this->assertEquals(789, $result['userId']);
		$this->assertEquals('Test status', $result['body']);
		$this->assertEquals('time formatted', $result['createdAt']);
		$this->assertEquals(0, $result['likes']); // deprecated field
		$this->assertSame($likesInfo, $result['likesInfo']);
		$this->assertEquals([$comment], $result['comments']);
		$this->assertEquals(['name' => 'TestUser', 'avatar' => 'avatar.jpg'], $result['userData']);
		$this->assertTrue($result['isNew']);
	}

	public function testJsonSerializeWithMissingUserData(): void
	{
		$entity = new StatusHandledEntity();
		$entity->setId(123);
		$entity->setUserId(789);
		$entity->setCreatedAt(time());

		// Don't set users info for this user
		$entity->setUsersInfo([456 => ['name' => 'OtherUser']]);

		$result = $entity->jsonSerialize();

		$this->assertEquals([], $result['userData']);
	}

	public function testInheritsFromStatusEntity(): void
	{
		$entity = new StatusHandledEntity();
		$this->assertInstanceOf(StatusEntity::class, $entity);
	}

	public function testImplementsHandledEntityInterface(): void
	{
		$entity = new StatusHandledEntity();
		$this->assertInstanceOf(HandledEntityInterface::class, $entity);
	}

	public function testImplementsJsonSerializable(): void
	{
		$entity = new StatusHandledEntity();
		$this->assertInstanceOf(\JsonSerializable::class, $entity);
	}

	public function testDefaultValues(): void
	{
		$entity = new StatusHandledEntity();

		$this->assertEquals([], $entity->getUsersInfo());
		$this->assertNull($entity->getLikesInfo());
		$this->assertEquals([], $entity->getComments());
		$this->assertFalse($entity->isNew());
	}

	public function testHandledEntityInterfaceMethods(): void
	{
		$entity = new StatusHandledEntity();

		// Test getUserId (inherited from StatusEntity)
		$entity->setUserId(123);
		$this->assertEquals(123, $entity->getUserId());

		// Test getId (inherited from StatusEntity)
		$entity->setId(456);
		$this->assertEquals(456, $entity->getId());

		// Test getWallId (inherited from StatusEntity)
		$entity->setWallId(789);
		$this->assertEquals(789, $entity->getWallId());

		// Test setBody/getBody (inherited from StatusEntity)
		$entity->setBody('Test body');
		$this->assertEquals('Test body', $entity->getBody());
	}

	public function testCommentsArrayTyping(): void
	{
		$entity = new StatusHandledEntity();

		$comment1 = new CommentHandledEntity();
		$comment1->setId(1);
		$comment1->setBody('Comment 1');

		$comment2 = new CommentHandledEntity();
		$comment2->setId(2);
		$comment2->setBody('Comment 2');

		$comments = [$comment1, $comment2];
		$entity->setComments($comments);

		$retrievedComments = $entity->getComments();
		$this->assertCount(2, $retrievedComments);
		$this->assertInstanceOf(CommentHandledEntity::class, $retrievedComments[0]);
		$this->assertInstanceOf(CommentHandledEntity::class, $retrievedComments[1]);
		$this->assertEquals(1, $retrievedComments[0]->getId());
		$this->assertEquals(2, $retrievedComments[1]->getId());
	}
}
