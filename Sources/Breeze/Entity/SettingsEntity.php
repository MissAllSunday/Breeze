<?php

declare(strict_types=1);


namespace Breeze\Entity;

class SettingsEntity
{
	public const MASTER = 'master';
	public const FORCE_WALL = 'forceWall';
	public const MAX_BUDDIES_NUM = 'maxBuddiesNumber';
	public const ABOUT_ME_MAX_LENGTH = 'aboutMeMaxLength';
	public const ENABLE_MOOD = 'enableMood';
	public const MOOD_LABEL = 'moodLabel';
	public const MOOD_DEFAULT = 'moodDefault';
	public const MOOD_PLACEMENT = 'moodPlacement';
	public const MAX_FLOOD_NUM = 'maxFloodNum';
	public const MAX_FLOOD_MINUTES = 'maxFloodMinutes';
	public const TYPE_CHECK = 'check';
	public const TYPE_INT = 'int';
	public const TYPE_TEXT = 'text';
	public const TYPE_TEXTAREA = 'textArea';
	public const TYPE_SELECT = 'select';
	public const PF_TEXT_KEY = 'custom_profile_placement_';

	public static function getColumns(): array
	{
		return [
			self::MASTER => self::TYPE_CHECK,
			self::FORCE_WALL => self::TYPE_CHECK,
			self::MAX_BUDDIES_NUM => self::TYPE_INT,
			self::ABOUT_ME_MAX_LENGTH => self::TYPE_INT,
			self::ENABLE_MOOD => self::TYPE_CHECK,
			self::MOOD_LABEL => self::TYPE_TEXT,
			self::MOOD_PLACEMENT => self::TYPE_SELECT,
			self::MAX_FLOOD_NUM => self::TYPE_INT,
			self::MAX_FLOOD_MINUTES => self::TYPE_INT,
		];
	}

	public static function defaultValues(): array
	{
		return [
			self::TYPE_CHECK => false,
			self::TYPE_INT => 0,
			self::TYPE_TEXT => '',
			self::TYPE_TEXTAREA => '',
			self::TYPE_SELECT => [],
		];
	}
}
