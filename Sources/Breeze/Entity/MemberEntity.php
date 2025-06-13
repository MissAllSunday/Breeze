<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MemberEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'members';
	public const string ID = 'id_member';
	public const string NAME = 'member_name';
	public const string REAL_NAME = 'real_name';
	public const string IGNORE_LIST = 'pm_ignore_list';
	public const string BUDDY_LIST = 'buddy_list';

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

	public static function getUserDataColumns(): array
	{
		return [
			'avatar',
			'buddies',
			'custom_fields',
			'email',
			'group',
			'group_color',
			'group_icons',
			'group_id',
			'href',
			'id',
			'is_activated',
			'is_banned',
			'is_buddy',
			'is_guest',
			'is_reverse_buddy',
			'last_login_timestamp',
			'link',
			'link_color',
			'name',
			'name_color',
			'online',
			'signature',
			'title',
			'username',
			'username_color',
		];
	}
}
