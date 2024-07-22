<?php

declare(strict_types=1);

namespace Breeze\Entity;

class CommentEntity extends BaseEntity implements BaseEntityInterface
{
	public const TABLE = 'breeze_comments';
	public const ID = 'id';
	public const STATUS_ID = 'statusId';
	public const USER_ID = 'userId';
	public const CREATED_AT = 'createdAt';
	public const BODY = 'body';
	public const LIKES = 'likes';

	protected int $id = 0;
	protected int $statusId = 0;
	protected int $userId = 0;
	protected string $createdAt = '';
	protected string $body = '';
	protected array $likes = [];

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getStatusId(): int
	{
		return $this->statusId;
	}

	public function setStatusId(int $statusId): void
	{
		$this->statusId = $statusId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setUserId(int $userId): void
	{
		$this->userId = $userId;
	}

	public function getCreatedAt(): string
	{
		return $this->createdAt;
	}

	public function setCreatedAt(string $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function setBody(string $body): void
	{
		$this->body = $body;
	}

	public function getLikes(): array
	{
		return $this->likes;
	}

	public function setLikes(array $likes): void
	{
		$this->likes = $likes;
	}

	public static function getColumns(): array
	{
		return [
			self::ID,
			self::STATUS_ID,
			self::USER_ID,
			self::CREATED_AT,
			self::BODY,
			self::LIKES,
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}
}
