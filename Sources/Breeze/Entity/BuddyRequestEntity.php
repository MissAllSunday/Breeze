<?php

declare(strict_types=1);

namespace Breeze\Entity;

class BuddyRequestEntity extends Entity implements EntityInterface
{
	public const string TABLE = 'breeze_buddy_requests';
	public const string ID = 'id';
	public const string SENDER_ID = 'sender_id';
	public const string RECEIVER_ID = 'receiver_id';
	public const string STATUS = 'status';
	public const string CREATED_AT = 'created_at';

	public const int PENDING = 0;
	public const int CONFIRMED = 1;

	protected int $id = 0;

	protected int $sender_id = 0;

	protected int $receiver_id = 0;

	protected int $status = self::PENDING;

	protected int $created_at = 0;

	public static function from(array $data = []): self
	{
		$class = self::class;

		return new $class($data);
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function getSenderId(): int
	{
		return $this->sender_id;
	}

	public function setSenderId(int $senderId): void
	{
		$this->sender_id = $senderId;
	}

	public function getReceiverId(): int
	{
		return $this->receiver_id;
	}

	public function setReceiverId(int $receiverId): void
	{
		$this->receiver_id = $receiverId;
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function setStatus(int $status): void
	{
		$this->status = $status;
	}

	public function getCreatedAt(): int
	{
		return $this->created_at;
	}

	public function setCreatedAt(int $createdAt): void
	{
		$this->created_at = $createdAt;
	}

	public static function getColumns(): array
	{
		return [
			self::ID => 'int',
			self::SENDER_ID => 'int',
			self::RECEIVER_ID => 'int',
			self::STATUS => 'int',
			self::CREATED_AT => 'int',
		];
	}

	public static function getTableName(): string
	{
		return self::TABLE;
	}

	public function castValue(string $columnName, mixed $value): string|int
	{
		return match ($columnName) {
			self::ID,
			self::SENDER_ID,
			self::RECEIVER_ID,
			self::STATUS,
			self::CREATED_AT => (int) $value,
			default => (string) $value,
		};
	}

	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'senderId' => $this->getSenderId(),
			'receiverId' => $this->getReceiverId(),
			'status' => $this->getStatus(),
			'createdAt' => $this->getCreatedAt(),
		];
	}
}
