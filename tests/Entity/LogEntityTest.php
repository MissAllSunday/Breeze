<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class LogEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id_log',
			'member',
			'content_type',
			'content_id',
			'time',
			'extra',
		], LogEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_logs', LogEntity::getTableName());
	}
}
