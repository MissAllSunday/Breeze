<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class GetAllMoods extends ValidateMood implements ValidateDataInterface
{
	protected const PARAMS = [];

	protected const SUCCESS_KEY = 'moodCreated';

	protected array $steps = [self::PERMISSIONS];

	public function __construct(protected MoodRepositoryInterface $moodRepository)
	{
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
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
		return [];
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
}
