<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateTimeImmutable;

abstract class SharedEntity extends Entity implements SharedEntityInterface
{
	public const string ID = 'id';

	public const string CREATED_AT = 'createdAt';

	public int $id = 0;

	protected int $userId = 0;

	protected int $wallId = 0;

	protected string $body = '';

	protected DateTimeImmutable $createdAt;

	protected int $likes = 0;

	protected array $usersInfo = [];

	protected ?LikeEntity $likesInfo = null;

	public function setId(int $id): void
	{
		$this->id = $id;
	}

	public function unsetId(): void
	{
		unset($this->id);
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getCreatedAt(): DateTimeImmutable
	{
		return $this->createdAt;
	}

	public function setCreatedAt(DateTimeImmutable $createdAt): void
	{
		$this->createdAt = $createdAt;
	}

	public function setBody(string $body): void
	{
		$this->body = $body;
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function setUserId(int $userId): void
	{
		$this->userId = $userId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setWallId(int $wallId): void
	{
		$this->wallId = $wallId;
	}

	public function getWallId(): int
	{
		return $this->wallId;
	}

	public function setUsersInfo(array $usersInfo): void
	{
		$this->usersInfo = $usersInfo;
	}

	public function getUsersInfo(): array
	{
		return $this->usersInfo;
	}

	public function setLikesInfo(?LikeEntity $likesInfo): void
	{
		$this->likesInfo = $likesInfo;
	}

	public function getLikesInfo(): ?LikeEntity
	{
		return $this->likesInfo;
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

	public function toInsert(): array
	{
		$this->unsetId();
		$toInsert = $this->toArray();
		$toInsert[self::CREATED_AT] = $this->getCreatedAt()->getTimestamp();

		return array_intersect_key($toInsert, array_flip(static::getColumns()));
	}

	public function toArray(): array
	{
		return get_object_vars($this);
	}
}
