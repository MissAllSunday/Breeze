<?php

declare(strict_types=1);

namespace Breeze\Entity;

use Breeze\Fixtures\StatusFixtures;
use PHPUnit\Framework\TestCase;

class StatusEntityTest extends TestCase
{
	public function testFromArray(): void
	{
		$data = StatusFixtures::basic();
		$entity = StatusEntity::from($data);

		$this->assertEquals($data[StatusEntity::ID], $entity->getId());
		$this->assertEquals($data[StatusEntity::WALL_ID], $entity->getWallId());
		$this->assertEquals($data[StatusEntity::USER_ID], $entity->getUserId());
		$this->assertEquals($data[StatusEntity::BODY], $entity->getBody());
	}

	public function testToInsert(): void
	{
		$entity = StatusEntity::from(StatusFixtures::basic());

		$result = $entity->toInsert();

		$this->assertArrayNotHasKey('id', $result);
		$this->assertArrayHasKey('wallId', $result);
		$this->assertArrayHasKey('userId', $result);
		$this->assertArrayHasKey('body', $result);
		$this->assertArrayHasKey('createdAt', $result);
	}

	public function testSettersAndGetters(): void
	{
		$entity = StatusEntity::from();

		$entity->setId(123);
		$this->assertEquals(123, $entity->getId());

		$entity->setWallId(456);
		$this->assertEquals(456, $entity->getWallId());

		$entity->setUserId(789);
		$this->assertEquals(789, $entity->getUserId());

		$entity->setBody('Test status');
		$this->assertEquals('Test status', $entity->getBody());

		$entity->setLikes(10);
		$this->assertEquals(10, $entity->getLikes());
	}
}
