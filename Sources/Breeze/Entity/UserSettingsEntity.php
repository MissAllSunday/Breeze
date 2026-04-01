<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateTimeImmutable;

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
	public const string AUTO_FOLLOW_BACK = 'autoFollowBack';
	public const string USER_ID = 'userId';

	protected int $wall = 0;

	protected int $generalWall = 0;

	protected int $paginationNumber = 5;

	protected int $kickIgnored = 0;

	protected int $enableBuddiesTab = 0;

	protected string $aboutMe = '';

	protected int $autoFollowBack = 0;

	protected array $buddies = [];

	protected array $blockList = [];

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public static function getColumns(): array
	{
		return [
			self::WALL => SettingsEntity::TYPE_CHECK,
			self::GENERAL_WALL => SettingsEntity::TYPE_CHECK,
			self::PAGINATION_NUM => SettingsEntity::TYPE_TEXT,
			self::KICK_IGNORED => SettingsEntity::TYPE_CHECK,
			self::ENABLE_BUDDIES_TAB => SettingsEntity::TYPE_CHECK,
			self::AUTO_FOLLOW_BACK => SettingsEntity::TYPE_CHECK,
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
			self::AUTO_FOLLOW_BACK => 0,
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

	public function getAutoFollowBack(): int
	{
		return $this->autoFollowBack;
	}

	public function setAutoFollowBack(int $autoFollowBack): void
	{
		$this->autoFollowBack = $autoFollowBack;
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

	/**
	 * @return array [int]
	 */
	public function getBuddies(): array
	{
		return $this->buddies;
	}

	public function setBuddies(array $buddies): void
	{
		$this->buddies = array_map('intval', array_filter($buddies));
	}

	/**
	 * @return array [int]
	 */
	public function getBlockList(): array
	{
		return $this->blockList;
	}

	public function setBlockList(array $blockList): void
	{
		$this->blockList = array_map('intval', array_filter($blockList));
	}

	public function castValue(string $columnName, mixed $value): int|string|array|DateTimeImmutable
	{
		return match ($columnName) {
			self::WALL,
			self::GENERAL_WALL,
			self::KICK_IGNORED,
			self::ENABLE_BUDDIES_TAB,
			self::AUTO_FOLLOW_BACK,
			self::PAGINATION_NUM,
			MemberEntity::ID => (int) $value,
			self::BLOCK_LIST, self::BUDDIES => explode(',', $value),
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [
			'wall' => $this->getWall(),
			'generalWall' => $this->getGeneralWall(),
			'paginationNumber' => $this->getPaginationNumber(),
			'kickIgnored' => $this->getKickIgnored(),
			'aboutMe' => $this->getAboutMe(),
			'enableBuddiesTab' => $this->getEnableBuddiesTab(),
			'autoFollowBack' => $this->getAutoFollowBack(),
		];
	}
}
