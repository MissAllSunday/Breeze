<?php

declare(strict_types=1);


namespace Breeze\Entity;

use DateTimeImmutable;

abstract class SharedEntity extends Entity implements SharedEntityInterface
{
	public const string ID = 'id';

	public const string CREATED_AT = 'created_at';

	public int $id = 0;

	protected int $user_id = 0;

	protected int $wall_id = 0;

	protected string $body = '';

	protected ?DateTimeImmutable $created_at = null;

	protected int $likes = 0;

	protected array $usersInfo = [];

	protected ?LikeInfoEntity $likesInfo = null;

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

	public function getCreatedAt(): ?DateTimeImmutable
	{
		return $this->created_at;
	}

	public function setCreatedAt(?DateTimeImmutable $created_at): void
	{
		$this->created_at = $created_at;
	}

	public function setBody(string $body): void
	{
		$this->body = $body;
	}

	public function getBody(): string
	{
		return $this->body;
	}

	public function setUserId(int $user_id): void
	{
		$this->user_id = $user_id;
	}

	public function getUserId(): int
	{
		return $this->user_id;
	}

	public function setWallId(int $wall_id): void
	{
		$this->wall_id = $wall_id;
	}

	public function getWallId(): int
	{
		return $this->wall_id;
	}

	public function setUsersInfo(array $usersInfo): void
	{
		$this->usersInfo = $usersInfo;
	}

	public function getUsersInfo(): array
	{
		return $this->usersInfo;
	}

	public function setLikesInfo(?LikeInfoEntity $likesInfo): void
	{
		$this->likesInfo = $likesInfo;
	}

	public function getLikesInfo(): ?LikeInfoEntity
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
		$this->setCreatedAt(new \DateTimeImmutable());
		$toInsert = $this->toArray();

		return array_intersect_key($toInsert, array_flip(static::getColumns()));
	}
}
