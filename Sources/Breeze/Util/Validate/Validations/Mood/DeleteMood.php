<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class DeleteMood extends ValidateMood implements ValidateDataInterface
{
	protected const PARAMS = [
		MoodEntity::ID => 0,
	];

	protected const SUCCESS_KEY = 'moodDeleted';

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
			self::PERMISSIONS,
			self::DATA_EXISTS,
		]);
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
