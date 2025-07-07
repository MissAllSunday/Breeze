<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\LikesEnum;
use DateMalformedStringException;
use DateTimeImmutable;

class LikeEntity extends Entity
{
	public const string TABLE = 'user_likes';
	public const string COLUMN_ID_MEMBER = 'id_member';
	public const string COLUMN_TYPE = 'content_type';
	public const string COLUMN_ID = 'content_id';
	public const string COLUMN_TIME = 'like_time';
	public const string IDENTIFIER = 'likes_';

	protected int $id_member = 0;

	protected LikesEnum $content_type;

	protected int $content_id = 0;

	protected DateTimeImmutable | int $like_time = 0;

	public function getIdMember(): int
	{
		return $this->id_member;
	}

	public function setIdMember(string|int $idMember): void
	{
		$this->id_member = (int) $idMember;
	}

	public function getContentType(): LikesEnum
	{
		return $this->content_type;
	}

	public function setContentType(LikesEnum $type): void
	{
		$this->content_type = $type;
	}

	public function getContentId(): int
	{
		return $this->content_id;
	}

	public function setContentId(string|int $id): void
	{
		$this->content_id = (int) $id;
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function getLikeTime(): int | DateTimeImmutable
	{
		return is_int($this->like_time) ? new DateTimeImmutable('@' . $this->like_time) : $this->like_time;
	}

	public function setLikeTime(DateTimeImmutable | int $time): void
	{
		$this->like_time = $time;
	}

	public static function getTypes(): array
	{
		return LikesEnum::cases();
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

	/**
	 * @throws DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): mixed
	{
		return match ($columnName) {
			self::COLUMN_ID_MEMBER,
			self::COLUMN_ID, LikeHandledEntity::COUNT => (int) $value,
			self::COLUMN_TIME => $value === null ? null : new DateTimeImmutable('@' . $value),
			LikeHandledEntity::CAN_LIKE => (bool) $value,
			self::COLUMN_TYPE => is_string($value) ? LikesEnum::from($value) : $value,
			default => (string) $value,
		};
	}
}
