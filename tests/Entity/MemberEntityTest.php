<?php

declare(strict_types=1);


namespace Breeze\Entity;

use PHPUnit\Framework\TestCase;

class MemberEntityTest extends TestCase
{
	public function testGetColumns(): void
	{
		$this->assertEquals([
			'id_member',
			'member_name',
			'real_name',
			'pm_ignore_list',
			'buddy_list',
		], MemberEntity::getColumns());
	}

	public function testGetTableName(): void
	{
		$this->assertEquals('members', MemberEntity::getTableName());
	}

	public function testCastValue(): void
	{
		$entity = MemberEntity::from();

		// Test integer casting for ID
		$this->assertEquals(123, $entity->castValue(MemberEntity::ID, '123'));
		$this->assertEquals(0, $entity->castValue(MemberEntity::ID, '0'));

		// Test default string casting for other columns
		$this->assertEquals('test_user', $entity->castValue(MemberEntity::NAME, 'test_user'));
		$this->assertEquals('Test User', $entity->castValue(MemberEntity::REAL_NAME, 'Test User'));
		$this->assertEquals('1,2,3', $entity->castValue(MemberEntity::IGNORE_LIST, '1,2,3'));
		$this->assertEquals('4,5,6', $entity->castValue(MemberEntity::BUDDY_LIST, '4,5,6'));

		// Test default case with unknown column
		$this->assertEquals('default', $entity->castValue('unknown_column', 'default'));
	}
}
