<?php

declare(strict_types=1);

namespace Breeze\Entity;

use DateMalformedStringException;
use DateTimeImmutable;
use Exception;

class StatusEntity extends Entity
{
	public const string TABLE = 'breeze_status';
	public const string ID = 'id';
	public const string WALL_ID = 'wallId';
	public const string USER_ID = 'userId';
	public const string CREATED_AT = 'createdAt';
	public const string BODY = 'body';
	public const string LIKES = 'likes';

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::WALL_ID,
			self::USER_ID,
			self::CREATED_AT,
			self::BODY,
			self::LIKES,
		];
	}

	protected int $id = 0;

	protected int $wallId = 0;

	protected int $userId = 0;

	protected int $createdAt = 0;

	protected string $body = '';

	protected int $likes = 0;

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = (int) $id;
	}

	public function unsetId(): void
	{
		unset($this->id);
	}

	public function getWallId(): int
	{
		return $this->wallId;
	}

	public function setWallId(int $wallId): void
	{
		$this->wallId = $wallId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setUserId(int|string $userId): void
	{
		$this->userId = (int) $userId;
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function getCreatedAt(): DateTimeImmutable
	{
		return new DateTimeImmutable('@' . $this->createdAt);
	}

	public function setCreatedAt(int | DateTimeImmutable $createdAt): void
	{
		$this->createdAt = is_int($createdAt) ? $createdAt : $createdAt->getTimestamp();
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function setBody(string $body): void
	{
		$this->body = $body;
	}

	/**
	 * @deprecated use LikeRepository instead
	 */
	public function getLikes(): int
	{
		return $this->likes;
	}

	/**
	 * @deprecated use LikeRepository instead
	 */
	public function setLikes(int $likes): void
	{
		$this->likes = $likes;
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
