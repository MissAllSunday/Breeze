<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Exceptions\InvalidMoodException;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Validate\Validations\ValidateData;

abstract class ValidateMood extends ValidateData
{
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

	protected MoodRepositoryInterface $moodRepository;

	/**
	 * @throws InvalidMoodException
	 */
	public function dataExists(): void
	{
		if (isset($this->data[MoodEntity::ID])) {
			$this->moodRepository->getById($this->data[MoodEntity::ID]);
		}
	}

	public static function getNameSpace(): string
	{
		return __NAMESPACE__ . '\\';
	}
}
