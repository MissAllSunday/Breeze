<?php

declare(strict_types=1);


namespace Breeze\Entity;

class Mood extends BaseEntity implements EntityInterface
{
	const TABLE = 'breeze_moods';
	const COLUMN_ID = 'moods_id';
	const COLUMN_NAME = 'name';
	const COLUMN_FILE = 'file';
	const COLUMN_EXT = 'ext';
	const COLUMN_DESC = 'description';
	const COLUMN_ENABLE = 'enable';

	public static function getColumns(): array
	{
		return [
		    self::COLUMN_ID,
		    self::COLUMN_NAME,
		    self::COLUMN_FILE,
		    self::COLUMN_EXT,
		    self::COLUMN_DESC,
		    self::COLUMN_ENABLE,
		];
	}
}
