<?php

declare(strict_types=1);


namespace Breeze\Entity;

class UserSettingsEntity
{
	public const IDENTIFIER = 'user_settings';
	public const WALL =  'wall';
	public const GENERAL_WALL = 'generalWall';
	public const PAGINATION_NUM = 'paginationNumber';
	public const KICK_IGNORED = 'kickIgnored';
	public const BLOCK_LIST = 'blockList';
	public const BUDDIES = 'buddies';
	public const ABOUT_ME = 'aboutMe';
	public const MOOD = 'mood';

	public static function getColumns(): array
	{
		return [
			self::WALL => SettingsEntity::TYPE_CHECK,
			self::GENERAL_WALL => SettingsEntity::TYPE_CHECK,
			self::PAGINATION_NUM => SettingsEntity::TYPE_INT,
			self::KICK_IGNORED => SettingsEntity::TYPE_CHECK,
			self::ABOUT_ME => SettingsEntity::TYPE_TEXT,
		];
	}
}
