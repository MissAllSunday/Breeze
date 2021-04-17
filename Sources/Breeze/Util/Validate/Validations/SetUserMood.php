<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\MoodEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\InvalidMoodException;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class SetUserMood extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [
		UserSettingsEntity::MOOD => 0,
		UserSettingsEntity::USER_ID => 0,
	];

	protected const DEFAULT_PARAMS = [];

	protected const SUCCESS_KEY = 'moodChanged';

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
			self::INT,
			self::DATA_EXISTS,
			self::VALID_USERS,
		]);
	}

	/**
	 * @throws InvalidMoodException
	 */
	public function dataExists(): void
	{
		$this->moodService->getMoodById($this->data[UserSettingsEntity::MOOD]);
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		// @TODO Implement "use mood" permission
	}

	public function getInts(): array
	{
		return [
			UserSettingsEntity::MOOD,
			UserSettingsEntity::USER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			UserSettingsEntity::USER_ID
		];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getPosterId(): int
	{
		return $this->data[UserSettingsEntity::USER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return array_merge(self::DEFAULT_PARAMS, $this->data);
	}

	public function getData(): array
	{
		return $this->getParams();
	}
}
