<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class GetActiveMoods extends ValidateMood implements ValidateDataInterface
{
	protected const PARAMS = [];

	protected const SUCCESS_KEY = 'moodCreated';

	protected array $steps = [];

	public function __construct(protected MoodRepositoryInterface $moodRepository)
	{
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
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
