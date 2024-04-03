<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class OptionsEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'member_id',
			'variable',
			'value',
		], OptionsEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('breeze_options', OptionsEntity::getTableName());
	}
}
