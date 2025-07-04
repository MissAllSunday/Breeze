<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateMalformedStringException;
use DateTimeImmutable;

class MentionEntity extends NormalizedEntity implements EntityInterface
{
	public const string TABLE = 'mentions';
	public const string COLUMN_CONTENT_ID = 'content_id';
	public const string COLUMN_CONTENT_TYPE = 'content_type';
	public const string COLUMN_ID_MENTIONED = 'id_mentioned';
	public const string COLUMN_ID_MEMBER = 'id_member';
	public const string COLUMN_TIME = 'time';

	public const string PROPERTY_CONTENT_ID = 'id';
	public const string PROPERTY_CONTENT_TYPE = 'type';
	public const string PROPERTY_ID_MENTIONED = 'idMentioned';
	public const string PROPERTY_ID_MEMBER = 'idMember';
	public const string PROPERTY_TIME = 'time';

	public const array KEY_MAP = [
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

	/**
	 * @throws DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): string|int
	{
		return match ($columnName) {
			self::COLUMN_CONTENT_ID,
			self::COLUMN_ID_MENTIONED,
			self::COLUMN_ID_MEMBER => (int) $value,
			self::COLUMN_TIME => new DateTimeImmutable('@' . $value),
			default => (string) $value,
		};
	}
}
