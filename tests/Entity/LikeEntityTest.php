<?php

declare(strict_types=1);

namespace Breeze\Entity;

use Breeze\Enums\LikesEnum;
use Breeze\Fixtures\LikeFixtures;
use PHPUnit\Framework\TestCase;

class LikeEntityTest extends TestCase
{
	public function testFromArray(): void
	{
		$data = LikeFixtures::basic();

		$entity = LikeEntity::from($data);

		$this->assertEquals($data[LikeEntity::ID], $entity->getContentId());
		$this->assertEquals($data[LikeEntity::ID_MEMBER], $entity->getIdMember());
	}

	public function testToInsert(): void
	{
		$entity = LikeEntity::from(LikeFixtures::basic());

		$result = $entity->toInsert();

		$this->assertArrayHasKey('id_member', $result);
		$this->assertArrayHasKey('content_type', $result);
		$this->assertArrayHasKey('content_id', $result);
		$this->assertArrayHasKey('like_time', $result);
	}

	public function testSettersAndGetters(): void
	{
		$entity = LikeEntity::from();

		$entity->setIdMember(456);
		$this->assertEquals(456, $entity->getIdMember());

		$entity->setContentType(LikesEnum::Status);
		$this->assertEquals(LikesEnum::Status, $entity->getContentType());

		$entity->setContentId(789);
		$this->assertEquals(789, $entity->getContentId());
	}
}
