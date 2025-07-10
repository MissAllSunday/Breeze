<?php

declare(strict_types=1);

namespace Breeze\Entity;

use DateMalformedStringException;
use DateTimeImmutable;

class CommentEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'breeze_comments';
	public const string ID = 'id';
	public const string STATUS_ID = 'statusId';
	public const string USER_ID = 'userId';
	public const string CREATED_AT = 'createdAt';
	public const string BODY = 'body';
	public const string LIKES = 'likes';

	public int $id = 0;

	protected int $statusId = 0;

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
		$this->id = $id;
	}

	public function unsetId(): void
	{
		unset($this->id);
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

	/**
	 * @throws DateMalformedStringException
	 */
	public function getCreatedAt(): DateTimeImmutable
	{
		return new DateTimeImmutable('@' . $this->createdAt);
	}

	public function setCreatedAt(string | int | DateTimeImmutable $createdAt): void
	{
		$this->createdAt = $createdAt instanceof DateTimeImmutable ? $createdAt->getTimestamp() : (int) $createdAt;
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

	/**
	 * @throws DateMalformedStringException
	 */
	public function castValue(string $columnName, mixed $value): string|int|DateTimeImmutable
	{
		return match ($columnName) {
			self::ID,
			self::STATUS_ID,
			self::USER_ID,
			self::LIKES => (int) $value,
			self::CREATED_AT => new DateTimeImmutable('@' . $value),
			default => (string) $value,
		};
	}
}
