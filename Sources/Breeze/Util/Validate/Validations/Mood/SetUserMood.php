<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\UserSettingsEntity;
use Breeze\Exceptions\InvalidMoodException;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class SetUserMood extends ValidateMood implements ValidateDataInterface
{
	protected const PARAMS = [
		UserSettingsEntity::MOOD => 0,
		UserSettingsEntity::USER_ID => 0,
	];

	protected const SUCCESS_KEY = 'moodChanged';

	public function __construct(protected MoodRepositoryInterface $moodRepository)
	{
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		return array_merge($this->steps, [
			self::INT,
			self::PERMISSIONS,
			self::DATA_EXISTS,
			self::VALID_USERS,
			self::SAME_USER,
		]);
	}

	/**
	 * @throws InvalidMoodException
	 */
	public function dataExists(): void
	{
		$this->moodRepository->getById($this->data[UserSettingsEntity::MOOD]);
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::USE_MOOD)) {
			throw new ValidateDataException('moodChanged');
		}
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
			UserSettingsEntity::USER_ID,
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
		return self::PARAMS;
	}
}
