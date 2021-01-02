<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;

class GetActiveMoods extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [];

	protected const SUCCESS_KEY = 'moodCreated';

	protected UserServiceInterface $userService;

	private MoodServiceInterface $moodService;

	protected array $steps = [];

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
