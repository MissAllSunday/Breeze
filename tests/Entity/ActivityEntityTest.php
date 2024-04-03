<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class ActivityEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id',
			'name',
			'userId',
			'contentId',
			'created_at',
			'extra',
		], ActivityEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_activities', ActivityEntity::getTableName());
	}
}
