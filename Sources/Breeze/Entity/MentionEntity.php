<?php

declare(strict_types=1);


namespace Breeze\Entity;

class MentionEntity extends NormalizeEntity implements BaseEntityInterface
{
	public const TABLE = 'mentions';
	public const COLUMN_CONTENT_ID = 'content_id';
	public const COLUMN_CONTENT_TYPE = 'content_type';
	public const COLUMN_ID_MENTIONED = 'id_mentioned';
	public const COLUMN_ID_MEMBER = 'id_member';
	public const COLUMN_TIME = 'time';

	public const PROPERTY_CONTENT_ID = 'id';
	public const PROPERTY_CONTENT_TYPE = 'type';
	public const PROPERTY_ID_MENTIONED = 'idMentioned';
	public const PROPERTY_ID_MEMBER = 'idMember';
	public const PROPERTY_TIME = 'time';

	public const KEY_MAP = [
		self::COLUMN_CONTENT_ID => self::PROPERTY_CONTENT_ID,
		self::COLUMN_CONTENT_TYPE => self::PROPERTY_CONTENT_TYPE,
		self::COLUMN_ID_MENTIONED => self::PROPERTY_ID_MENTIONED,
		self::COLUMN_ID_MEMBER => self::PROPERTY_ID_MEMBER,
		self::COLUMN_TIME => self::PROPERTY_TIME,
	];

	public static function getColumns(): array
	{
		return [
			self::COLUMN_CONTENT_ID,
			self::COLUMN_CONTENT_TYPE,
			self::COLUMN_ID_MENTIONED,
			self::COLUMN_ID_MEMBER,
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
