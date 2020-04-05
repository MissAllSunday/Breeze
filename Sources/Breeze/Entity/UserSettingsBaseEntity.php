<?php

declare(strict_types=1);


namespace Breeze\Entity;

class UserSettingsBaseEntity extends BaseEntity implements BaseEntityInterface
{
	public const WALL =  'wall';
	public const GENERAL_WALL = 'generalWall';
	public const PAGINATION_NUM = 'paginationNumber';
	public const ALERT_NUM = 'alertNumber';
	public const AUTO_LOADING = 'autoLoading';
	public const ACTIVITY_LOG = 'activityLog';
	public const KICK_IGNORED = 'kickIgnored';
	public const BLOCK_LIST = 'blockList';
	public const BUDDIES = 'buddies';
	public const ABOUT_ME = 'aboutMe';

	public static function getColumns(): array
	{
		return [
			self::WALL,
			self::GENERAL_WALL,
			self::PAGINATION_NUM,
			self::ALERT_NUM,
			self::AUTO_LOADING,
			self::ACTIVITY_LOG,
			self::KICK_IGNORED,
			self::BLOCK_LIST,
			self::BUDDIES,
			self::ABOUT_ME,
		];
	}

	public static function getTableName(): string
	{
		return '';
	}
}
