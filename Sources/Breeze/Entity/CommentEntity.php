<?php

declare(strict_types=1);

namespace Breeze\Entity;

class CommentEntity extends BaseEntity
{
	const TABLE = 'breeze_comments';
	const COLUMN_ID ='comments_id';
	const COLUMN_STATUS_ID = 'comments_status_id';
	const COLUMN_STATUS_OWNER_ID = 'comments_status_owner_id';
	const COLUMN_POSTER_ID = 'comments_poster_id';
	const COLUMN_PROFILE_ID = 'comments_profile_id';
	const COLUMN_TIME = 'comments_time';
	const COLUMN_BODY = 'comments_body';
	const COLUMN_LIKES = 'likes';

	public static function getColumns(): array
	{
		return [
		    self::COLUMN_ID,
		    self::COLUMN_STATUS_ID,
		    self::COLUMN_STATUS_OWNER_ID,
		    self::COLUMN_POSTER_ID,
		    self::COLUMN_PROFILE_ID,
		    self::COLUMN_TIME,
		    self::COLUMN_BODY,
		    self::COLUMN_LIKES,
		];
	}
}
