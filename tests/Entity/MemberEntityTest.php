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
}
