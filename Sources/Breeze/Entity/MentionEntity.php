<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MentionEntity extends BaseEntity implements EntityInterface
{
	const TABLE = 'mentions';
	const COLUMN_CONTENT_ID = 'id';
	const COLUMN_CONTENT_TYPE = 'owner_id';
	const COLUMN_ID_MENTIONED = 'poster_id';
	const COLUMN_ID_MEMBER= 'time';
	const COLUMN_TIME = 'body';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_CONTENT_ID => 'id',
			self::COLUMN_CONTENT_TYPE => 'owner_id',
			self::COLUMN_ID_MENTIONED => 'poster_id',
			self::COLUMN_ID_MEMBER => 'time',
			self::COLUMN_TIME => 'body',
		];
	}
}
