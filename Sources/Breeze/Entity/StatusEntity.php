<?php

declare(strict_types=1);

namespace Breeze\Entity;

use DateTimeImmutable;
use Exception;

class StatusEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_status';
	public const ID = 'id';
	public const WALL_ID = 'wallId';
	public const USER_ID = 'userId';
	public const CREATED_AT = 'createdAt';
	public const BODY = 'body';
	public const LIKES = 'likes';

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

	protected string|DateTimeImmutable $createdAt = '';

	protected string $body = '';

	protected array $likes = [];

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int|string $id): void
	{
		$this->id = (int) $id;
	}

	public function getWallId(): int
	{
		return $this->wallId;
	}

	public function setWallId(int|string $wallId): void
	{
		$this->wallId = (int) $wallId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setUserId(int|string $userId): void
	{
		$this->userId = (int) $userId;
	}

	public function getCreatedAt(): DateTimeImmutable|string
	{
		return $this->createdAt;
	}

	/**
	 * @throws Exception
	 */
	public function setCreatedAt(string $createdAt): void
	{
		$this->createdAt = new DateTimeImmutable($createdAt);
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
	 * @return  LikeEntity[]
	 */
	public function getLikes(): array
	{
		return $this->likes;
	}

	public function setLikes(array $likes): void
	{
		$this->likes = $likes;
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
