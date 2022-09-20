<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class PostMood extends ValidateMood implements ValidateDataInterface
{
	protected const SUCCESS_KEY = 'moodUpdated';

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
			self::STRING,
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
			throw new ValidateDataException('moodCreated');
		}
	}

	public function getInts(): array
	{
		return [
			MoodEntity::STATUS,
		];
	}

	public function getUserIdsNames(): array
	{
		return [];
	}

	public function getStrings(): array
	{
		return [
			MoodEntity::EMOJI,
			MoodEntity::DESC,
		];
	}

	public function getPosterId(): int
	{
		return 0;
	}

	public function getParams(): array
	{
		return array_merge(self::DEFAULT_PARAMS, $this->data);
	}
}
