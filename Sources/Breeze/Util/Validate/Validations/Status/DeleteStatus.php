<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class DeleteStatus extends ValidateStatus implements ValidateDataInterface
{
	public array $steps = [
		self::CLEAN,
		self::INT,
		self::VALID_STATUS,
		self::VALID_USER,
		self::PERMISSIONS,
	];

	protected const PARAMS = [
		StatusEntity::ID => 0,
		StatusEntity::USER_ID => 0,
	];

	protected const SUCCESS_KEY = 'deleted_status';

	private array $status;

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		return $this->steps;
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		$currentUserInfo = $this->userService->getCurrentUserInfo();

		if ($currentUserInfo['id'] === $this->data[StatusEntity::USER_ID] &&
			!Permissions::isAllowedTo(Permissions::DELETE_OWN_STATUS)) {
			throw new ValidateDataException('deleteStatus');
		}

		if (!Permissions::isAllowedTo(Permissions::DELETE_STATUS)) {
			throw new ValidateDataException('deleteStatus');
		}
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function validStatus(): void
	{
		$this->status = $this->statusService->getById($this->data[StatusEntity::ID]);
	}

	/**
	 * @throws ValidateDataException
	 * @throws InvalidStatusException
	 */
	public function validUser(): void
	{
		if (!$this->status) {
			$this->validStatus();
		}

		if (!isset($this->data[StatusEntity::USER_ID]) ||
			($this->status['data'][$this->data[StatusEntity::ID]][StatusEntity::USER_ID]
			!== $this->data[StatusEntity::USER_ID])) {
			throw new ValidateDataException('wrong_values');
		}
	}

	public function getInts(): array
	{
		return [
			StatusEntity::ID,
			StatusEntity::USER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			StatusEntity::USER_ID,
		];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getPosterId(): int
	{
		return $this->data[StatusEntity::USER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
