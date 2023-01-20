<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MemberEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'members';
	public const ID = 'id_member';
	public const NAME = 'member_name';
	public const REAL_NAME = 'real_name';
	public const IGNORE_LIST = 'pm_ignore_list';
	public const BUDDY_LIST = 'buddy_list';

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::NAME,
			self::REAL_NAME,
			self::IGNORE_LIST,
			self::BUDDY_LIST,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
