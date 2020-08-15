<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class DeleteStatus extends ValidateData implements ValidateDataInterface
{
	public array $steps = [
		'clean',
		'isInt',
		'validStatus',
		'validUser',
		'permissions'
	];

	protected const PARAMS = [
		StatusEntity::COLUMN_ID => 0,
		StatusEntity::COLUMN_POSTER_ID => 0,
	];

	protected const SUCCESS_KEY = 'deleted_status';

	/**
	 */
	private StatusServiceInterface $statusService;
	
	/**
	 */
	private array $status;

	public function __construct(
		UserServiceInterface $userService,
		StatusServiceInterface $statusService
	)
	{
		$this->statusService = $statusService;

		parent::__construct($userService);
	}

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

		if ($currentUserInfo['id'] === $this->data[StatusEntity::COLUMN_POSTER_ID] &&
			!Permissions::isAllowedTo(Permissions::DELETE_OWN_STATUS))
			throw new ValidateDataException('deleteStatus');

		if (!Permissions::isAllowedTo(Permissions::DELETE_STATUS))
			throw new ValidateDataException('deleteStatus');
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function validComment(): void
	{
		$this->status = $this->statusService->getById($this->data[StatusEntity::COLUMN_ID]);
	}

	/**
	 * @throws ValidateDataException
	 * @throws InvalidStatusException
	 */
	public function validUser(): void
	{
		if (!$this->status)
			$this->status = $this->statusService->getById($this->data[StatusEntity::COLUMN_ID]);

		if (!isset($this->data[StatusEntity::COLUMN_POSTER_ID]) ||
			($this->status['data'][$this->data[StatusEntity::COLUMN_ID]][StatusEntity::COLUMN_POSTER_ID]
			!==
			$this->data[StatusEntity::COLUMN_POSTER_ID]))
			throw new ValidateDataException('wrong_values');
	}

	public function getInts(): array
	{
		return [
			StatusEntity::COLUMN_ID,
			StatusEntity::COLUMN_POSTER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			StatusEntity::COLUMN_POSTER_ID,
		];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getPosterId(): int
	{
		return $this->data[StatusEntity::COLUMN_POSTER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
