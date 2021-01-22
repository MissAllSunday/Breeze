<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class UserSettingsEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'wall' => 'check',
			'generalWall' => 'check',
			'paginationNumber' => 'text',
			'kickIgnored' => 'check',
			'aboutMe' => 'textArea',
		], UserSettingsEntity::getColumns());
	}

	public function testGetDefaultValues(): void
	{
		$this->assertEquals([
			'wall' => 0,
			'generalWall' => 0,
			'paginationNumber' => 5,
			'kickIgnored' => 0,
			'aboutMe' => '',
		], UserSettingsEntity::getDefaultValues());
	}

	public function testGetInts(): void
	{
		$this->assertEquals([
			'wall' => 0,
			'generalWall' => 0,
			'paginationNumber' => 5,
			'kickIgnored' => 0,
		], UserSettingsEntity::getInts());
	}

	public function testGetStrings(): void
	{
		$this->assertEquals([
			'aboutMe' => '',
		], UserSettingsEntity::getStrings());
	}

	public function testGetTableName(): void
	{
		$this->assertEmpty(UserSettingsEntity::getTableName());
	}
}
