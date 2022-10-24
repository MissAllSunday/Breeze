<?php

declare(strict_types=1);


namespace Breeze\Repository;

interface BaseRepositoryInterface
{
	public const LIKE_TYPE_STATUS = 'breSta';
	public const LIKE_TYPE_COMMENT = 'breCom';
	public const TTL = 360;

	/**
	 * @throws InvalidDataException
	 */
	public function getById(int $id);

	public function handleLikes($type, $content): array;

	public static function getAllTypes(): array;

	public function getUsersToLoad(array $userIds = []): array;

	public function loadUsersInfo(array $userIds = []): array;

	public function getCurrentUserInfo(): array;
}
