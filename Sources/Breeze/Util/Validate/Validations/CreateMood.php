<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\MoodEntity;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class CreateMood extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [
		MoodEntity::COLUMN_ID => 0,
		MoodEntity::COLUMN_EMOJI => '',
		MoodEntity::COLUMN_DESC => '',
		MoodEntity::COLUMN_STATUS => 0,
	];

	protected const SUCCESS_KEY = 'mood_created';

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
		return self::DEFAULT_STEPS;
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::POST_COMMENTS)) {
			throw new ValidateDataException('postComments');
		}
	}

	public function getInts(): array
	{
		return [
			MoodEntity::COLUMN_ID,
			MoodEntity::COLUMN_STATUS,
		];
	}

	public function getUserIdsNames(): array
	{
		return [];
	}

	public function getStrings(): array
	{
		return [
			MoodEntity::COLUMN_EMOJI => '',
			MoodEntity::COLUMN_DESC => '',
		];
	}

	public function getPosterId(): int
	{
		return 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
