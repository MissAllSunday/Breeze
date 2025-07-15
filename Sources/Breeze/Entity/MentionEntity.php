<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateMalformedStringException;
use DateTimeImmutable;

// @todo convert to object when working with mentions
class MentionEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'mentions';
	public const string CONTENT_ID = 'content_id';
	public const string CONTENT_TYPE = 'content_type';
	public const string ID_MENTIONED = 'id_mentioned';
	public const string ID_MEMBER = 'id_member';
	public const string TIME = 'time';

	public const string PROPERTY_CONTENT_ID = 'id';
	public const string PROPERTY_CONTENT_TYPE = 'type';
	public const string PROPERTY_ID_MENTIONED = 'idMentioned';
	public const string PROPERTY_ID_MEMBER = 'idMember';
	public const string PROPERTY_TIME = 'time';

	public const array KEY_MAP = [
		self::CONTENT_ID => self::PROPERTY_CONTENT_ID,
		self::CONTENT_TYPE => self::PROPERTY_CONTENT_TYPE,
		self::ID_MENTIONED => self::PROPERTY_ID_MENTIONED,
		self::ID_MEMBER => self::PROPERTY_ID_MEMBER,
		self::TIME => self::PROPERTY_TIME,
	];

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public static function getColumns(): array
	{
		return [
			self::CONTENT_ID,
			self::CONTENT_TYPE,
			self::ID_MENTIONED,
			self::ID_MEMBER,
			self::TIME,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): string|int|DateTimeImmutable
	{
		return match ($columnName) {
			self::CONTENT_ID,
			self::ID_MENTIONED,
			self::ID_MEMBER => (int) $value,
			self::TIME => new DateTimeImmutable('@' . $value),
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [];
	}
}
