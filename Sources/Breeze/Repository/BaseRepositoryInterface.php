<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Entity\BaseEntityInterface;
use Breeze\Util\Validate\DataNotFoundException;

interface BaseRepositoryInterface
{
	public const LIKE_TYPE_STATUS = 'breSta';
	public const LIKE_TYPE_COMMENT = 'breCom';
	public const TTL = 360;

	/**
	 * @throws DataNotFoundException
	 */
	public function getById(int $id): BaseEntityInterface;

	public function handleLikes($type, $content): array;

	public static function getAllTypes(): array;

	public function getUsersToLoad(array $userIds = []): array;

	public function loadUsersInfo(array $userIds = []): array;

	public function getCurrentUserInfo(): array;
}
