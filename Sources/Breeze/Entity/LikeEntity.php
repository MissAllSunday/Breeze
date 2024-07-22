<?php

declare(strict_types=1);


namespace Breeze\Entity;

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
	const KEY_MAP = [
		self::COLUMN_ID_MEMBER => self::PROPERTY_ID_MEMBER,
		self::COLUMN_TYPE => self::PROPERTY_TYPE,
		self::COLUMN_ID => self::PROPERTY_ID,
		self::COLUMN_TIME => self::PROPERTY_TIME,
	];

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
