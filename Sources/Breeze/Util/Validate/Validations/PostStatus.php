<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\StatusEntity;
use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class PostStatus extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [
		StatusEntity::COLUMN_OWNER_ID => 0,
		StatusEntity::COLUMN_POSTER_ID => 0,
		StatusEntity::COLUMN_BODY => '',
	];

	protected const SUCCESS_KEY = 'published_status';

	private StatusServiceInterface $statusService;

	public function __construct(
		UserServiceInterface $userService,
		StatusServiceInterface $statusService
	) {
		$this->statusService = $statusService;

		parent::__construct($userService);
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		$this->steps = self::ALL_STEPS;
		$this->steps[] = 'validStatus';

		return $this->steps;
	}

	public function setSteps(array $customSteps): void
	{
		$this->steps = $customSteps;
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::POST_STATUS)) {
			throw new ValidateDataException('postStatus');
		}
	}

	public function getInts(): array
	{
		return [
			StatusEntity::COLUMN_OWNER_ID,
			StatusEntity::COLUMN_POSTER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			StatusEntity::COLUMN_OWNER_ID,
			StatusEntity::COLUMN_POSTER_ID,
		];
	}

	public function getStrings(): array
	{
		return [StatusEntity::COLUMN_BODY];
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
