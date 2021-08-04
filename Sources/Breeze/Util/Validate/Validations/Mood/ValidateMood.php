<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\InvalidMoodException;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateMood extends ValidateData
{
	protected UserServiceInterface $userService;

	protected MoodServiceInterface $moodService;

	protected const PARAMS = [
		MoodEntity::EMOJI => '',
		MoodEntity::DESC => '',
		MoodEntity::STATUS => 0,
		MoodEntity::ID,
	];

	protected const DEFAULT_PARAMS = [
		MoodEntity::EMOJI => '',
		MoodEntity::DESC => '',
		MoodEntity::STATUS => 0,
	];

	public function __construct(
		UserServiceInterface $userService,
		MoodServiceInterface $moodService
	) {
		$this->moodService = $moodService;

		parent::__construct($userService);
	}

	/**
	 * @throws InvalidMoodException
	 */
	public function dataExists(): void
	{
		$this->moodService->getMoodById($this->data[MoodEntity::ID]);
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
