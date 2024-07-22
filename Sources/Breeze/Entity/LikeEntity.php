<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\LikesEnum;

class LikeEntity extends NormalizeEntity implements BaseEntityInterface
{
	public const TABLE = 'user_likes';
	public const COLUMN_ID_MEMBER = 'id_member';
	public const COLUMN_TYPE = 'content_type';
	public const COLUMN_ID = 'content_id';
	public const COLUMN_TIME = 'like_time';
	public const PROPERTY_ID_MEMBER = 'idMember';
	public const PROPERTY_TYPE = 'type';
	public const PROPERTY_ID = 'id';
	public const PROPERTY_TIME = 'time';
	public const PARAM_LIKE = 'like';
	public const PARAM_SA = 'sa';
	public const TYPE_STATUS = 'br_sta';
	public const TYPE_COMMENT = 'br_com';
	public const IDENTIFIER = 'likes_';
	public const KEY_MAP = [
		self::COLUMN_ID_MEMBER => self::PROPERTY_ID_MEMBER,
		self::COLUMN_TYPE => self::PROPERTY_TYPE,
		self::COLUMN_ID => self::PROPERTY_ID,
		self::COLUMN_TIME => self::PROPERTY_TIME,
	];

	protected int $idMember = 0;
	protected string|LikesEnum $type = '';
	protected int $id = 0;
	protected string $time = '';

	public function getIdMember(): int
	{
		return $this->idMember;
	}

	public function setIdMember(int $idMember): void
	{
		$this->idMember = $idMember;
	}

	public function getType(): string|LikesEnum
	{
		return $this->type;
	}

	public function setType(string|LikesEnum $type): void
	{
		$this->type = $type;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getTime(): string
	{
		return $this->time;
	}

	public function setTime(string $time): void
	{
		$this->time = $time;
	}

	public static function getTypes(): array
	{
		return [
			self::TYPE_STATUS,
			self::TYPE_COMMENT,
		];
	}

	public static function getColumns(): array
	{
		return [
			self::COLUMN_ID_MEMBER,
			self::COLUMN_TYPE,
			self::COLUMN_ID,
			self::COLUMN_TIME,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}

	public function getColumnMap(): array
	{
		return self::KEY_MAP;
	}
}
