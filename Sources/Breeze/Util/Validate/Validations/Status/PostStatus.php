<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class PostStatus extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		StatusEntity::WALL_ID => 0,
		StatusEntity::USER_ID => 0,
		StatusEntity::BODY => '',
	];

	protected const SUCCESS_KEY = 'published_status';

	public function __construct(
		protected Data $validateData,
		protected User $validateUser,
		protected Allow $validateAllow,
		protected BaseRepositoryInterface $repository
	) {
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 */
	public function isValid(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);

		$userId = $this->data[StatusEntity::USER_ID];
		$wallId = $this->data[StatusEntity::WALL_ID];

		$this->validateAllow->permissions(Permissions::POST_STATUS, 'postStatus');
		$this->validateAllow->floodControl($userId);
		$this->validateUser->areValidUsers([$userId, $wallId]);
	}
}
