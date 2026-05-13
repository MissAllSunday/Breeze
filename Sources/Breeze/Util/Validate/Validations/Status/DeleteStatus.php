<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\InvalidDataException;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class DeleteStatus extends BaseActions implements ValidateDataInterface
{
	protected const array PARAMS = [
		StatusEntity::ID => 0,
		StatusEntity::USER_ID => 0,
	];

	protected const string SUCCESS_KEY = 'deleted_status';

	/**
	 * @throws NotAllowedException
	 */
	public function checkAllow(): void
	{
		$currentUserId = (int) $this->repository->getCurrentUserInfo()['id'];
		$statusUserId = $this->data[StatusEntity::USER_ID];

		if ($currentUserId === $statusUserId) {
			$this->validateAllow->permissions(PermissionsEnum::DELETE_OWN_STATUS, 'deleteStatus');

			return;
		}

		assert($this->repository instanceof StatusRepositoryInterface);
		$status = $this->repository->getById($this->data[StatusEntity::ID]);

		if ($status->getWallId() === $currentUserId) {
			$this->validateAllow->permissions(PermissionsEnum::DELETE_PROFILE_STATUS, 'deleteStatus');

			return;
		}

		$this->validateAllow->permissions(PermissionsEnum::DELETE_STATUS, 'deleteStatus');
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[StatusEntity::USER_ID]]);
	}

	/**
	 * @throws InvalidDataException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateData->dataExists($this->data[StatusEntity::ID], $this->repository);
	}

	/**
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 * @throws NotAllowedException
	 */
	public function isValid(): void
	{
		$this->checkData();
		$this->checkAllow();
		$this->checkUser();
	}
}
