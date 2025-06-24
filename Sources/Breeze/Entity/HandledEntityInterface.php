<?php

declare(strict_types=1);

namespace Breeze\Entity;

interface HandledEntityInterface extends \JsonSerializable
{
	public function getUsersInfo(): array;

	public function setUsersInfo(array $usersInfo): void;

	/**
	 * @param array $likesInfo [LikeHandledEntity]
	 */
	public function setLikesInfo(array $likesInfo): void;

	/**
	 * @return array [LikeHandledEntity]
	 */
	public function getLikesInfo(): array;

	public function getUserId(): int;

	public function getId(): int;

	public function getWallId(): int;

	public function setBody(string $body): void;

	public function getBody(): string;
}
