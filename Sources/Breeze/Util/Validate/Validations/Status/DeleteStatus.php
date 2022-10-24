<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class DeleteStatus extends BaseActions implements ValidateDataInterface
{

	protected const PARAMS = [
		StatusEntity::ID => 0,
		StatusEntity::USER_ID => 0,
	];

	protected const SUCCESS_KEY = 'deleted_status';

	public function __construct(
		protected Data $validateData,
		protected User $validateUser,
		protected Allow $validateAllow,
		protected BaseRepositoryInterface $repository
	) {
	}

	/**
	 * @throws NotAllowedException
	 */
	public function permissions(): void
	{
		$permissionName = $this->repository->getCurrentUserInfo()['id'] === $this->data[StatusEntity::USER_ID] ?
			Permissions::DELETE_OWN_STATUS : Permissions::DELETE_STATUS;

		$this->validateAllow->permissions($permissionName, 'deleteStatus');
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 * @throws InvalidDataException
	 */
	public function isValid(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);

		$userId = $this->data[StatusEntity::USER_ID];
		$statusId = $this->data[StatusEntity::ID];

		$this->permissions();
		$this->validateUser->areValidUsers([$userId]);
		$this->validateData->dataExists($statusId, $this->repository);
	}
}
