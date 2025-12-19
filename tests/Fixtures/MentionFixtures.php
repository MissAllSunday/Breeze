<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\MentionEntity;

class MentionFixtures
{
	public static function basic(): array
	{
		return [
			MentionEntity::CONTENT_ID => 666,
			MentionEntity::ID_MENTIONED => 2,
			MentionEntity::ID_MEMBER => 3,
			MentionEntity::TIME => time(),
			MentionEntity::TYPE => 'br_sta',
		];
	}

	public static function withCustomData(array $overrides = []): array
	{
		return array_merge(self::basic(), $overrides);
	}

	public static function commentMention(): array
	{
		return self::withCustomData([
			MentionEntity::TYPE => 'br_com',
		]);
	}

	public static function multipleMentions(): array
	{
		return [
			self::basic(),
			self::withCustomData([
				MentionEntity::CONTENT_ID => 2,
				MentionEntity::ID_MENTIONED => 4,
			]),
		];
	}
}
