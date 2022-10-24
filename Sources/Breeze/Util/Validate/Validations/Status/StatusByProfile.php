<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class StatusByProfile extends BaseActions implements ValidateDataInterface
{
	public function __construct(
		protected Data $validateData,
		protected User $validateUser,
		protected Allow $validateAllow,
		protected BaseRepositoryInterface $repository
	) {
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function isValid(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateUser->areValidUsers([$this->data[StatusEntity::WALL_ID]]);
	}
}
