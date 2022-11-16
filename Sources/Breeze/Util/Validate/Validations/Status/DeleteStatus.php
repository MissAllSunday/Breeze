<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class DeleteStatus extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		StatusEntity::ID => 0,
		StatusEntity::USER_ID => 0,
	];

	protected const SUCCESS_KEY = 'deleted_status';

	/**
	 * @throws NotAllowedException
	 */
	public function checkAllow(): void
	{
		$permissionName = $this->repository->getCurrentUserInfo()['id'] === $this->data[StatusEntity::USER_ID] ?
			Permissions::DELETE_OWN_STATUS : Permissions::DELETE_STATUS;

		$this->validateAllow->permissions($permissionName, 'deleteStatus');
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
	 * @throws DataNotFoundException
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
