<?php

declare(strict_types=1);


namespace Breeze\Entity;

class UserSettingsEntity extends BaseEntity implements BaseEntityInterface
{
	public const IDENTIFIER = 'user_settings';

	public const WALL =  'wall';

	public const GENERAL_WALL = 'generalWall';

	public const PAGINATION_NUM = 'paginationNumber';

	public const KICK_IGNORED = 'kickIgnored';

	public const BLOCK_LIST = 'blockList';

	public const BUDDIES = 'buddies';

	public const ABOUT_ME = 'aboutMe';

	public const MOOD = 'moodId';

	public const USER_ID = 'userId';

	public static function getColumns(): array
	{
		return [
			self::WALL => SettingsEntity::TYPE_CHECK,
			self::GENERAL_WALL => SettingsEntity::TYPE_CHECK,
			self::PAGINATION_NUM => SettingsEntity::TYPE_TEXT,
			self::KICK_IGNORED => SettingsEntity::TYPE_CHECK,
			self::ABOUT_ME => SettingsEntity::TYPE_TEXTAREA,
		];
	}

	public static function getDefaultValues(): array
	{
		return [
			self::WALL => 0,
			self::GENERAL_WALL => 0,
			self::PAGINATION_NUM => 5,
			self::KICK_IGNORED => 0,
			self::ABOUT_ME => '',
		];
	}

	public static function getInts(): array
	{
		return array_filter(self::getDefaultValues(), function (string $part): bool {
			return (bool) strlen($part);
		});
	}

	public static function getStrings(): array
	{
		return array_filter(self::getDefaultValues(), function ($value) {
			return is_string($value);
		});
	}

	public static function getTableName(): string
	{
		return '';
	}
}
