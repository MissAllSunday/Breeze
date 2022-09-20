<?php

declare(strict_types=1);


namespace Breeze\Repository;

use Breeze\Traits\CacheTrait;
use Breeze\Traits\TextTrait;

abstract class BaseRepository implements BaseRepositoryInterface
{
	use CacheTrait;
	use TextTrait;

	public function handleLikes($type, $content): array
	{
		return [];
	}

	public static function getAllTypes(): array
	{
		return [
			self::LIKE_TYPE_STATUS,
			self::LIKE_TYPE_COMMENT,
		];
	}

	public function getUsersToLoad(array $userIds = []): array
	{
		return loadMemberData($userIds);
	}

	public function loadUsersInfo(array $userIds = []): array
	{
		$loadedUsers = [];

		$modSettings = $this->global('modSettings');
		$loadedIDs = $this->getUsersToLoad($userIds);

		foreach ($userIds as $userId) {
			if (!in_array($userId, $loadedIDs)) {
				$loadedUsers[$userId] = [
					'link' => $this->getSmfText('guest_title'),
					'name' => $this->getSmfText('guest_title'),
					'avatar' => ['href' => $modSettings['avatar_url'] . '/default.png'],
				];

				continue;
			}

			$loadedUsers[$userId] = loadMemberContext($userId, true);
		}

		return $loadedUsers;
	}
}
