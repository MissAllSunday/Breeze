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
		MoodEntity::COLUMN_EMOJI => '',
		MoodEntity::COLUMN_DESC => '',
		MoodEntity::COLUMN_STATUS => 0,
	];

	protected const DEFAULT_PARAMS = [
		MoodEntity::COLUMN_STATUS => 0,
	];

	protected const SUCCESS_KEY = 'moodCreated';

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
		return array_merge($this->steps, [
			self::INT,
			self::STRING,
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
			MoodEntity::COLUMN_EMOJI,
			MoodEntity::COLUMN_DESC,
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
