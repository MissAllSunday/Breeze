<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\InvalidDataException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class DeleteStatus extends BaseActions implements ValidateDataInterface
{
	protected const array PARAMS = [
		StatusEntity::ID => 0,
		StatusEntity::USER_ID => 0,
	];

	protected const string SUCCESS_KEY = 'deleted_status';

	public function __construct(
		Data $validateData,
		User $validateUser,
		Allow $validateAllow,
		protected StatusRepositoryInterface $statusRepository
	) {
		parent::__construct($validateData, $validateUser, $validateAllow, $statusRepository);
	}

	/**
	 * @throws NotAllowedException
	 */
	public function checkAllow(): void
	{
		$currentUserId = (int) $this->repository->getCurrentUserInfo()['id'];
		$statusUserId = $this->data[StatusEntity::USER_ID];

		if ($currentUserId === $statusUserId) {
			$this->validateAllow->permissions(PermissionsEnum::DELETE_OWN_STATUS, self::PERMISSION_MSG_DELETE_STATUS);

			return;
		}

		$status = $this->statusRepository->getById($this->data[StatusEntity::ID]);

		if ($status->getWallId() === $currentUserId) {
			$this->validateAllow->permissions(PermissionsEnum::DELETE_PROFILE_STATUS, self::PERMISSION_MSG_DELETE_STATUS);

			return;
		}

		$this->validateAllow->permissions(PermissionsEnum::DELETE_STATUS, self::PERMISSION_MSG_DELETE_STATUS);
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
