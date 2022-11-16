<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class DeleteMood extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		MoodEntity::ID => 0,
	];

	protected const SUCCESS_KEY = 'moodDeleted';

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::ADMIN_FORUM)) {
			throw new DataNotFoundException('moodDelete');
		}
	}

	/**
	 * @throws DataNotFoundException
	 * @throws NotAllowedException
	 * @throws InvalidDataException
	 */
	public function isValid(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateAllow->permissions(Permissions::ADMIN_FORUM, 'moodDelete');
		$this->validateData->dataExists($this->data[MoodEntity::ID], $this->repository);
	}
}
