<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MoodEntity extends BaseEntity implements EntityInterface
{
	const TABLE = 'breeze_moods';
	const COLUMN_ID = 'moods_id';
	const COLUMN_EMOJI = 'emoji';
	const COLUMN_DESC = 'description';
	const COLUMN_ENABLE = 'enable';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID,
			self::COLUMN_EMOJI,
			self::COLUMN_DESC,
			self::COLUMN_ENABLE,
		];
	}
}
