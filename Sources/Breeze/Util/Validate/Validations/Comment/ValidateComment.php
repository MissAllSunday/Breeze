<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateComment extends ValidateData
{
	protected StatusRepositoryInterface $statusRepository;

	protected CommentRepositoryInterface $commentRepository;

	/**
	 * @throws DataNotFoundException
	 */
	public function areValidUsers(): void
	{
		$usersIds = array_map(
			function ($intName) {
				return $this->data[$intName];
			},
			$this->getUserIdsNames()
		);

		$loadedUsers = $this->commentRepository->getUsersToLoad($usersIds);

		if (array_diff($usersIds, $loadedUsers)) {
			throw new DataNotFoundException('invalid_users');
		}
	}

	public function getCurrentUserInfo(): array
	{
		return $this->global('user_info');
	}
}
