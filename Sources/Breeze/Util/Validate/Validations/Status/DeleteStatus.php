<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Service\PermissionsServiceInterface;
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
	];

	protected const string SUCCESS_KEY = 'deleted_status';

	public function __construct(
		Data $validateData,
		User $validateUser,
		Allow $validateAllow,
		protected StatusRepositoryInterface $statusRepository,
		protected PermissionsServiceInterface $permissionsService
	) {
		parent::__construct($validateData, $validateUser, $validateAllow, $statusRepository);
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 */
	public function checkAllow(): void
	{
		$status = $this->statusRepository->getById($this->data[StatusEntity::ID]);

		if (!$this->permissionsService->canDelete(
			PermissionsEnum::TYPE_STATUS,
			$status->getUserId(),
			$status->getWallId()
		)) {
			throw new NotAllowedException(self::PERMISSION_MSG_DELETE_STATUS);
		}
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$status = $this->statusRepository->getById($this->data[StatusEntity::ID]);

		$this->validateUser->areValidUsers([$status->getUserId()]);
	}

	/**
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateData->isInt([StatusEntity::ID], $this->data);
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
