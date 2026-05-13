<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\LikeEntity;
use Breeze\Enums\LikesEnum;

class LikeFixtures
{
	public static function basic(): array
	{
		return [
			LikeEntity::ID => 666,
			LikeEntity::ID_MEMBER => 2,
			LikeEntity::TYPE => LikesEnum::Status->value,
			LikeEntity::TIME => time(),
		];
	}

	public static function withCustomData(array $overrides = []): array
	{
		return array_merge(self::basic(), $overrides);
	}

	public static function forInsertion(): array
	{
		$data = self::basic();
		unset($data[LikeEntity::ID]);

		return $data;
	}

	public static function commentLike(): array
	{
		return self::withCustomData([
			LikeEntity::TYPE => LikesEnum::Comments->value,
			LikeEntity::CONTENT => 5,
		]);
	}

	public static function multipleLikes(): array
	{
		return [
			self::withCustomData([
				LikeEntity::ID => 1,
				LikeEntity::ID_MEMBER => 1,
			]),
			self::withCustomData([
				LikeEntity::ID => 2,
				LikeEntity::ID_MEMBER => 3,
				LikeEntity::TYPE => LikesEnum::Comments->value,
			]),
		];
	}
}
