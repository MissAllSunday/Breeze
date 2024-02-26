<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class StatusEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id',
			'wallId',
			'userId',
			'createdAt',
			'body',
			'likes',
		], StatusEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_status', StatusEntity::getTableName());
	}
}
