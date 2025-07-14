<?php

declare(strict_types=1);

namespace Breeze\Entity;

use Breeze\Util\Time;
use DateMalformedStringException;
use DateTimeImmutable;

class StatusEntity extends SharedEntity implements SharedEntityInterface
{
	public const string TABLE = 'breeze_status';
	public const string ID = 'id';
	public const string WALL_ID = 'wallId';
	public const string USER_ID = 'userId';
	public const string BODY = 'body';
	public const string LIKES = 'likes';

	protected int $wallId = 0;

	protected int $userId = 0;

	/** @var CommentEntity[] */
	protected array $comments = [];

	protected bool $isNew = false;

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

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

	public function getComments(): array
	{
		return $this->comments;
	}

	public function setComments(array $comments): void
	{
		$this->comments = $comments;
	}

	public function isNew(): bool
	{
		return $this->isNew;
	}

	public function setIsNew(bool $isNew): void
	{
		$this->isNew = $isNew;
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
			self::ID, self::WALL_ID, self::USER_ID, self::LIKES => (int) $value,
			self::CREATED_AT => new DateTimeImmutable('@' . $value),
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'wallId' => $this->getWallId(),
			'userId' => $this->getUserId(),
			'likes' => 0,  // @deprecated use likesInfo.count instead
			'body' => $this->getBody(),
			'createdAt' => Time::from($this->getCreatedAt()),
			'likesInfo' => $this->getLikesInfo(),
			'comments' => $this->getComments(),
			'userData' => $this->getUsersInfo()[$this->getUserId()] ?? [],
			'isNew' => $this->isNew(),
		];
	}
}
