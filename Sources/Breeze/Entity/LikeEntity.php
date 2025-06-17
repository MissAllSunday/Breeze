<?php

declare(strict_types=1);


namespace Breeze\Entity;

use Breeze\LikesEnum;
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

	protected string|LikesEnum $content_type = '';

	protected int $content_id = 0;

	protected ?int $like_time = null;

	public function getIdMember(): int
	{
		return $this->id_member;
	}

	public function setIdMember(string|int $idMember): void
	{
		$this->id_member = (int) $idMember;
	}

	public function getContentType(): string
	{
		return $this->content_type;
	}

	public function setContentType(string|LikesEnum $type): void
	{
		$this->content_type = LikesEnum::isValid($type) ? LikesEnum::from($type)->value : '';
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
	 * @throws \DateMalformedStringException
	 */
	public function getLikeTime(): DateTimeImmutable | null
	{
		return $this->like_time === null ? null : new DateTimeImmutable('@' . $this->like_time);
	}

	public function setLikeTime(null | int | DateTimeImmutable $time): void
	{
		$this->like_time = (is_int($time) || $time === null) ? $time : $time->getTimestamp();;
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
}
