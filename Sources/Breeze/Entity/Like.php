<?php

declare(strict_types=1);


namespace Breeze\Entity;

class Like extends Base
{
	const COLUMN_ID_MEMBER = 'id_member';
	const COLUMN_CONTENT_TYPE = 'content_type';
	const COLUMN_CONTENT_ID = 'content_id';
	const COLUMN_LIKE_TIME = 'like_time';

	function getColumns(): array
	{
		return [
		    self::COLUMN_ID_MEMBER,
		    self::COLUMN_CONTENT_TYPE,
		    self::COLUMN_CONTENT_ID,
		    self::COLUMN_LIKE_TIME,
		];
	}
}