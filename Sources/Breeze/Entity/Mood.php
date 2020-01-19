<?php


namespace Breeze\Entity;


class Mood extends Base
{
	const COLUMN_ID = 'moods_id';
	const COLUMN_NAME = 'name';
	const COLUMN_FILE = 'file';
	const COLUMN_EXT = 'ext';
	const COLUMN_DESC = 'description';
	const COLUMN_ENABLE = 'enable';

	function getColumns(): array
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