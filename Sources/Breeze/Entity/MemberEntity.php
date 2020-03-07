<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MemberEntity extends BaseEntity
{
	const TABLE = 'members';
	const COLUMN_ID = 'id_member';
	const COLUMN_MEMBER_NAME = 'member_name';
	const COLUMN_REAL_NAME = 'real_name';
	const COLUMN_PROFILE_VIEWS = 'breeze_profile_views';
	const COLUMN_IGNORE_LIST = 'pm_ignore_list';
	const COLUMN_BUDDY_LIST = 'buddy_list';

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID,
			self::COLUMN_MEMBER_NAME,
			self::COLUMN_REAL_NAME,
			self::COLUMN_PROFILE_VIEWS,
			self::COLUMN_IGNORE_LIST,
			self::COLUMN_BUDDY_LIST
		];

	}
}
