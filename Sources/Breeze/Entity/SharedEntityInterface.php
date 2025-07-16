<?php

declare(strict_types=1);


namespace Breeze\Entity;

interface SharedEntityInterface extends EntityInterface
{
	public function getUsersInfo(): array;

	public function setUsersInfo(array $usersInfo): void;

	public function setLikesInfo(?LikeInfoEntity $likesInfo): void;

	public function getLikesInfo(): ?LikeInfoEntity;

	public function getUserId(): int;

	public function getId(): int;

	public function getWallId(): int;

	public function setBody(string $body): void;

	public function getBody(): string;
}
