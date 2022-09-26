<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class CreateMood extends ValidateMood implements ValidateDataInterface
{
	protected const PARAMS = [
		MoodEntity::EMOJI => '',
		MoodEntity::DESC => '',
		MoodEntity::STATUS => 0,
	];

	protected const DEFAULT_PARAMS = [
		MoodEntity::STATUS => 0,
	];

	protected const SUCCESS_KEY = 'moodCreated';

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
		]);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::ADMIN_FORUM)) {
			throw new DataNotFoundException('moodCreated');
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

	public function getData(): array
	{
		return $this->getParams();
	}
}
