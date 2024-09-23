<?php

declare(strict_types=1);

namespace Breeze\Validate\Types;

use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Traits\SettingsTrait;
use Breeze\Util\Validate\DataNotFoundException;

class User
{
	use SettingsTrait;

	public function __construct(protected BaseRepositoryInterface $repository)
	{
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function areValidUsers(array $usersIds): void
	{
		$loadedUsers = $this->repository->getUsersToLoad($usersIds);

		if (array_diff($usersIds, $loadedUsers)) {
			throw new DataNotFoundException('invalid_users');
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isSameUser(int $posterUserId): void
	{
		$sessionUser = $this->global('user_info');

		if ($posterUserId === 0 || $posterUserId !== (int)$sessionUser['id']) {
			throw new DataNotFoundException('invalid_users');
		}
	}
}
