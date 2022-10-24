<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class SetUserMood extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		UserSettingsEntity::MOOD => 0,
		UserSettingsEntity::USER_ID => 0,
	];

	protected const SUCCESS_KEY = 'moodChanged';

	public function __construct(
		protected Data $validateData,
		protected Allow $validateAllow,
		protected User $validateUser,
		protected BaseRepositoryInterface $repository
	) {
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 * @throws InvalidDataException
	 */
	public function isValid(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateData->dataExists($this->data[MoodEntity::ID], $this->repository);
		$this->validateAllow->permissions(Permissions::USE_MOOD, 'moodChanged');
		$this->validateUser->areValidUsers([$this->data[UserSettingsEntity::USER_ID]]);
		$this->validateUser->isSameUser($this->data[UserSettingsEntity::USER_ID]);
	}
}
