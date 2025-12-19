<?php

declare(strict_types=1);

namespace Breeze\Fixtures;

use Breeze\Entity\AlertEntity;

class AlertFixtures
{
	public static function basic(): array
	{
		return [
			AlertEntity::ID => 666,
			AlertEntity::TIME => time(),
			AlertEntity::ID_MEMBER => 2,
			AlertEntity::ID_MEMBER_STARTED => 3,
			AlertEntity::MEMBER_NAME => 'TestUser',
			AlertEntity::CONTENT_TYPE => 'br_sta',
			AlertEntity::CONTENT_ID => 1,
			AlertEntity::CONTENT_ACTION => 'like',
			AlertEntity::IS_READ => 0,
			AlertEntity::EXTRA => json_encode(['key' => 'value']),
		];
	}

	public static function withCustomData(array $overrides = []): array
	{
		return array_merge(self::basic(), $overrides);
	}

	public static function forInsertion(): array
	{
		$data = self::basic();
		unset($data[AlertEntity::ID]);

		return $data;
	}

	public static function readAlert(): array
	{
		return self::withCustomData([
			AlertEntity::IS_READ => 1,
		]);
	}

	public static function commentAlert(): array
	{
		return self::withCustomData([
			AlertEntity::CONTENT_TYPE => 'br_com',
			AlertEntity::CONTENT_ACTION => 'comment',
		]);
	}
}
