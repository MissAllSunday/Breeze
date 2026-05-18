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
			'enableBuddiesTab' => 'check',
			'blockBuddyRequests' => 'check',
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
			'enableBuddiesTab' => 0,
			'blockBuddyRequests' => 0,
		], UserSettingsEntity::getDefaultValues());
	}

	public function testGetInts(): void
	{
		$this->assertEquals([
			'wall' => 0,
			'generalWall' => 0,
			'paginationNumber' => 5,
			'kickIgnored' => 0,
			'enableBuddiesTab' => 0,
			'blockBuddyRequests' => 0,
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

	public function testCastValue(): void
	{
		$entity = UserSettingsEntity::from();

		// Test integer casting for wall settings
		$this->assertEquals(1, $entity->castValue(UserSettingsEntity::WALL, '1'));
		$this->assertEquals(0, $entity->castValue(UserSettingsEntity::WALL, '0'));
		$this->assertEquals(1, $entity->castValue(UserSettingsEntity::GENERAL_WALL, '1'));
		$this->assertEquals(0, $entity->castValue(UserSettingsEntity::KICK_IGNORED, '0'));
		$this->assertEquals(1, $entity->castValue(UserSettingsEntity::ENABLE_BUDDIES_TAB, '1'));
		$this->assertEquals(10, $entity->castValue(UserSettingsEntity::PAGINATION_NUM, '10'));

		// Test integer casting for member ID
		$this->assertEquals(123, $entity->castValue(MemberEntity::ID, '123'));

		// Test array casting for comma-separated values
		$this->assertEquals(['1', '2', '3'], $entity->castValue(UserSettingsEntity::BLOCK_LIST, '1,2,3'));
		$this->assertEquals(['4', '5'], $entity->castValue(UserSettingsEntity::BUDDIES, '4,5'));
		$this->assertEquals([''], $entity->castValue(UserSettingsEntity::BLOCK_LIST, ''));

		// Test default string casting
		$this->assertEquals('test', $entity->castValue(UserSettingsEntity::ABOUT_ME, 'test'));
		$this->assertEquals('default', $entity->castValue('unknown_column', 'default'));
	}
}
