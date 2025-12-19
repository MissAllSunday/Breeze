<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\MemberEntity;

class MemberFixtures
{
	public static function basic(): array
	{
		return [
			MemberEntity::ID => 666,
			MemberEntity::NAME => 'testuser',
			MemberEntity::REAL_NAME => 'Test User',
			MemberEntity::IGNORE_LIST => '1,2,3',
			MemberEntity::BUDDY_LIST => '4,5,6',
		];
	}

	public static function withCustomData(array $overrides = []): array
	{
		return array_merge(self::basic(), $overrides);
	}

	public static function guest(): array
	{
		return self::withCustomData([
			MemberEntity::ID => 0,
			MemberEntity::NAME => 'Guest',
			MemberEntity::REAL_NAME => 'Guest',
		]);
	}

	public static function multipleMembers(): array
	{
		return [
			self::withCustomData([
				MemberEntity::ID => 1,
				MemberEntity::NAME => 'user1',
				MemberEntity::REAL_NAME => 'User One',
			]),
			self::withCustomData([
				MemberEntity::ID => 2,
				MemberEntity::NAME => 'user2',
				MemberEntity::REAL_NAME => 'User Two',
			]),
		];
	}
}
