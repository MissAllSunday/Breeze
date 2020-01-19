<?php


namespace Breeze\Entity;


class Log extends Base
{
	const COLUMN_ID = 'id_log';
	const COLUMN_MEMBER = 'member';
	const COLUMN_CONTENT_TYPE = 'content_type';
	const COLUMN_CONTENT_ID = 'content_id';
	const COLUMN_TIME = 'time';
	const COLUMN_EXTRA = 'extra';

	function getColumns(): array
	{
		return [
			self::COLUMN_ID,
			self::COLUMN_MEMBER,
			self::COLUMN_CONTENT_TYPE,
			self::COLUMN_CONTENT_ID,
			self::COLUMN_TIME,
			self::COLUMN_EXTRA,
		];
	}
}