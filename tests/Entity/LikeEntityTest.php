<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class LikeEntityTest extends TestCase
{
	public function testGetTypes(): void
	{
		$this->assertEquals([
			'br_sta',
			'br_com',
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
}
