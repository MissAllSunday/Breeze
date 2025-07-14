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

	public function testCastValue(): void
	{
		$entity = OptionsEntity::from();

		// Test integer casting for member_id
		$this->assertEquals(123, $entity->castValue(OptionsEntity::MEMBER_ID, '123'));
		$this->assertEquals(0, $entity->castValue(OptionsEntity::MEMBER_ID, '0'));

		// Test default string casting for other columns
		$this->assertEquals('test_variable', $entity->castValue(OptionsEntity::VARIABLE, 'test_variable'));
		$this->assertEquals('test_value', $entity->castValue(OptionsEntity::VALUE, 'test_value'));

		// Test default case with unknown column
		$this->assertEquals('default', $entity->castValue('unknown_column', 'default'));
	}
}
