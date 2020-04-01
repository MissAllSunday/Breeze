<?php


namespace Breeze\Entity;


class SettingsEntity extends BaseEntity implements EntityInterface
{
	public const MASTER = 'master';
	public const FORCE_WALL = 'forceWall';
	public const MAX_BUDDIES_NUM = 'maxBuddiesNumber';
	public const ABOUT_ME_MAX_LENGTH = 'aboutMeMaxLength';
	public const ENABLE_MOOD = 'enableMood';
	public const MOOD_LABEL = 'moodLabel';
	public const MOOD_PLACEMENT = 'moodPlacement';
	public const MAX_FLOOD_NUM = 'maxFloodNum';
	public const MAX_FLOOD_MINUTES= 'maxFloodMinutes';

	public static function getColumns(): array
	{
		return [
			self::MASTER,
			self::FORCE_WALL,
			self::MAX_BUDDIES_NUM,
			self::ABOUT_ME_MAX_LENGTH,
			self::ENABLE_MOOD,
			self::MOOD_LABEL,
			self::MOOD_PLACEMENT,
			self::MAX_FLOOD_NUM,
			self::MAX_FLOOD_MINUTES,
		];
	}
}