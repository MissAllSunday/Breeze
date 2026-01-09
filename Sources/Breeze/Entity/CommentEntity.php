<?php

declare(strict_types=1);

namespace Breeze\Entity;

use Breeze\Util\Time;
use DateMalformedStringException;
use DateTimeImmutable;

class CommentEntity extends SharedEntity implements SharedEntityInterface
{
	public const string TABLE = 'breeze_comments';
	public const string ID = 'id';
	public const string STATUS_ID = 'status_id';
	public const string USER_ID = 'user_id';
	public const string BODY = 'body';
	public const string LIKES = 'likes';

	protected int $statusId = 0;

	protected int $userId = 0;

	protected string $body = '';

	protected int $likes = 0;

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public function getStatusId(): int
	{
		return $this->statusId;
	}

	public function setStatusId(int $statusId): void
	{
		$this->statusId = $statusId;
	}

	/**
	 * @return int 0 as wallId is not used in comments
	 */
	public function getWallId(): int
	{
		return 0;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setUserId(int $userId): void
	{
		$this->userId = $userId;
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function setBody(string $body): void
	{
		$this->body = $body;
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

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'status_id' => $this->getStatusId(),
			'user_id' => $this->getUserId(),
			'created_at' => Time::from($this->getCreatedAt()),
			'body' => $this->getBody(),
			'likes' => 0,  // @deprecated use likesInfo.count instead
			'likesInfo' => $this->getLikesInfo(),
			'userData' => $this->getUsersInfo()[$this->getUserId()] ?? [],
		];
	}
}
