<?php

declare(strict_types=1);


namespace Breeze\Entity;

class UserSettingsEntity extends Entity implements EntityInterface
{
	public const string IDENTIFIER = 'user_settings';
	public const string WALL = 'wall';
	public const string GENERAL_WALL = 'generalWall';
	public const string PAGINATION_NUM = 'paginationNumber';
	public const string KICK_IGNORED = 'kickIgnored';
	public const string BLOCK_LIST = 'blockList';
	public const string ENABLE_BUDDIES_TAB = 'enableBuddiesTab';
	public const string BUDDIES = 'buddies';
	public const string ABOUT_ME = 'aboutMe';
	public const string USER_ID = 'userId';

	protected int $wall = 0;

	protected int $generalWall = 0;

	protected int $paginationNumber = 5;

	protected int $kickIgnored = 0;

	protected int $enableBuddiesTab = 0;

	protected string $aboutMe = '';

	public static function getColumns(): array
	{
		return [
			self::WALL => SettingsEntity::TYPE_CHECK,
			self::GENERAL_WALL => SettingsEntity::TYPE_CHECK,
			self::PAGINATION_NUM => SettingsEntity::TYPE_TEXT,
			self::KICK_IGNORED => SettingsEntity::TYPE_CHECK,
			self::ENABLE_BUDDIES_TAB => SettingsEntity::TYPE_CHECK,
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
			self::ENABLE_BUDDIES_TAB => 0,
			self::ABOUT_ME => '',
		];
	}

	public static function getInts(): array
	{
		return array_filter(self::getDefaultValues(), function (string $part): bool {
			return (bool)strlen($part);
		});
	}

	public static function getStrings(): array
	{
		return array_filter(self::getDefaultValues(), function ($value): bool {
			return is_string($value);
		});
	}

	public static function getTableName(): string
	{
		return '';
	}

	public function getWall(): int
	{
		return $this->wall;
	}

	public function setWall(int $wall): void
	{
		$this->wall = $wall;
	}

	public function getGeneralWall(): int
	{
		return $this->generalWall;
	}

	public function setGeneralWall(int $generalWall): void
	{
		$this->generalWall = $generalWall;
	}

	public function getKickIgnored(): int
	{
		return $this->kickIgnored;
	}

	public function setKickIgnored(int $kickIgnored): void
	{
		$this->kickIgnored = $kickIgnored;
	}

	public function getEnableBuddiesTab(): int
	{
		return $this->enableBuddiesTab;
	}

	public function setEnableBuddiesTab(int $enableBuddiesTab): void
	{
		$this->enableBuddiesTab = $enableBuddiesTab;
	}

	public function getAboutMe(): string
	{
		return $this->aboutMe;
	}

	public function setAboutMe(string $aboutMe): void
	{
		$this->aboutMe = $aboutMe;
	}

	public function getPaginationNumber(): int
	{
		return $this->paginationNumber;
	}

	public function setPaginationNumber(int $paginationNumber): void
	{
		$this->paginationNumber = $paginationNumber;
	}
}
