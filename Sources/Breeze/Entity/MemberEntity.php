<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MemberEntity extends BaseEntity implements BaseEntityInterface
{
	const TABLE = 'members';
	const ID = 'id_member';
	const NAME = 'member_name';
	const REAL_NAME = 'real_name';
	const IGNORE_LIST = 'pm_ignore_list';
	const BUDDY_LIST = 'buddy_list';

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::NAME,
			self::REAL_NAME,
			self::IGNORE_LIST,
			self::BUDDY_LIST
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
