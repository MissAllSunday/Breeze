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

	protected int $status_id = 0;

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public function getStatusId(): int
	{
		return $this->status_id;
	}

	public function setStatusId(int $status_id): void
	{
		$this->status_id = $status_id;
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
