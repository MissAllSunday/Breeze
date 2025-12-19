<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\SharedEntity;
use Breeze\Entity\StatusEntity;

class StatusFixtures
{
	public static function basic(): array
	{
		return [
			StatusEntity::ID => 666,
			StatusEntity::WALL_ID => 1,
			StatusEntity::USER_ID => 2,
			StatusEntity::BODY => 'This is a basic test status',
			StatusEntity::LIKES => 0,
			SharedEntity::CREATED_AT => time(),
		];
	}

	public static function withCustomData(array $overrides = []): array
	{
		return array_merge(self::basic(), $overrides);
	}

	public static function forInsertion(): array
	{
		$data = self::basic();
		unset($data[StatusEntity::ID]);

		return $data;
	}

	public static function multipleStatuses(): array
	{
		return [
			self::withCustomData([
				StatusEntity::ID => 1,
				StatusEntity::BODY => 'First status',
			]),
			self::withCustomData([
				StatusEntity::ID => 2,
				StatusEntity::BODY => 'Second status',
				StatusEntity::WALL_ID => 2,
			]),
			self::withCustomData([
				StatusEntity::ID => 3,
				StatusEntity::BODY => 'Third status',
				StatusEntity::USER_ID => 3,
			]),
		];
	}

	public static function invalidStatus(): array
	{
		return [
			StatusEntity::WALL_ID => 0,
			StatusEntity::USER_ID => 0,
			StatusEntity::BODY => '',
		];
	}
}
