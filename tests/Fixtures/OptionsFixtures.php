<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\OptionsEntity;

class OptionsFixtures
{
	public static function basic(): array
	{
		return [
			OptionsEntity::MEMBER_ID => 666,
			OptionsEntity::VARIABLE => 'test_option',
			OptionsEntity::VALUE => 'test_value',
		];
	}

	public static function withCustomData(array $overrides = []): array
	{
		return array_merge(self::basic(), $overrides);
	}

	public static function generalWallOption(): array
	{
		return self::withCustomData([
			OptionsEntity::VARIABLE => 'generalWall',
			OptionsEntity::VALUE => '1',
		]);
	}

	public static function multipleOptions(): array
	{
		return [
			self::basic(),
			self::withCustomData([
				OptionsEntity::VARIABLE => 'another_option',
				OptionsEntity::VALUE => 'another_value',
			]),
		];
	}
}
