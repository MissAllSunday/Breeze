<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\LikeEntity;
use Breeze\Entity\LikeInfoEntity;
use Breeze\Enums\LikesEnum;

class LikeInfoFixtures
{
	public static function basic(): array
	{
		return [
			LikeEntity::ID => 666,
			LikeInfoEntity::TEXT => 'Test text',
			LikeInfoEntity::HREF => 'https://example.com',
			LikeEntity::TYPE => LikesEnum::Status->value,
			LikeInfoEntity::LIKES => [],
		];
	}

	public static function withCustomData(array $overrides = []): array
	{
		return array_merge(self::basic(), $overrides);
	}

	public static function withLikes(): array
	{
		return self::withCustomData([
			LikeInfoEntity::LIKES => [
				LikeFixtures::basic(),
				LikeFixtures::withCustomData(['id' => 2]),
			],
		]);
	}

	public static function commentLikeInfo(): array
	{
		return self::withCustomData([
			LikeEntity::TYPE => LikesEnum::Comments,
		]);
	}
}
