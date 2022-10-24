<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Mood;

use Breeze\Entity\MoodEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class PostMood extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		MoodEntity::EMOJI => '',
		MoodEntity::DESC => '',
		MoodEntity::STATUS => 0,
	];
	protected const SUCCESS_KEY = 'moodUpdated';

	public function __construct(
		protected Data $validateData,
		protected User $validateUser,
		protected Allow $validateAllow,
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
	 */
	public function isValid(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateAllow->permissions(Permissions::ADMIN_FORUM, 'moodCreated');
	}
}
