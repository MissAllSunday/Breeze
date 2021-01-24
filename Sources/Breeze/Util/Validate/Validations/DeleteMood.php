<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\InvalidMoodException;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class DeleteMood extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [
		MoodEntity::ID => 0,
	];

	protected const SUCCESS_KEY = 'moodDeleted';

	protected UserServiceInterface $userService;

	private MoodServiceInterface $moodService;

	public function __construct(
		UserServiceInterface $userService,
		MoodServiceInterface $moodService
	) {
		$this->moodService = $moodService;

		parent::__construct($userService);
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		return array_merge($this->steps, [
			self::PERMISSIONS,
			self::DATA_EXISTS,
		]);
	}

	/**
	 * @throws InvalidMoodException
	 */
	public function dataExists(): void
	{
		$this->moodService->getMoodById($this->data[MoodEntity::ID]);
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::ADMIN_FORUM)) {
			throw new ValidateDataException('moodDelete');
		}
	}

	public function getInts(): array
	{
		return [
			MoodEntity::ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getPosterId(): int
	{
		return 0;
	}

	public function getParams(): array
	{
		return [];
	}

	public function getData(): array
	{
		return $this->data;
	}
}
