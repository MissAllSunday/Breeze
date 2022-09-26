<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateStatus extends ValidateData
{
	protected StatusRepositoryInterface $statusRepository;

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

		$loadedUsers = $this->statusRepository->getUsersToLoad($usersIds);

		if (array_diff($usersIds, $loadedUsers)) {
			throw new DataNotFoundException('invalid_users');
		}
	}

	public function getCurrentUserInfo(): array
	{
		return $this->global('user_info');
	}

	public function getParams(): array
	{
		return $this->getData();
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
