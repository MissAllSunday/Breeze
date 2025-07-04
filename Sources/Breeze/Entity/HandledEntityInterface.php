<?php

declare(strict_types=1);

namespace Breeze\Entity;

interface HandledEntityInterface extends \JsonSerializable
{
	public function getUsersInfo(): array;

	public function setUsersInfo(array $usersInfo): void;

	public function setLikesInfo(?LikeHandledEntity $likesInfo): void;

	public function getLikesInfo(): ?LikeHandledEntity;

	public function getUserId(): int;

	public function getId(): int;

	public function getWallId(): int;

	public function setBody(string $body): void;

	public function getBody(): string;
}
