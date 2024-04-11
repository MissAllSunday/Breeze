<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\PermissionsEnum;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class PostStatus extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		StatusEntity::WALL_ID => 0,
		StatusEntity::USER_ID => 0,
		StatusEntity::BODY => '',
	];

	protected const SUCCESS_KEY = 'published_status';

	/**
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateData->isInt([StatusEntity::WALL_ID, StatusEntity::USER_ID], $this->data);
		$this->validateData->isString([StatusEntity::BODY], $this->data);
	}

	/**
	 * @throws NotAllowedException
	 */
	public function checkAllow(): void
	{
		$this->validateAllow->permissions(PermissionsEnum::POST_STATUS, PermissionsEnum::POST_STATUS);
		$this->validateAllow->floodControl($this->data[StatusEntity::WALL_ID]);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[StatusEntity::USER_ID], $this->data[StatusEntity::WALL_ID]]);
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 * @throws InvalidDataException
	 */
	public function isValid(): void
	{
		$this->checkData();
		$this->checkAllow();
		$this->checkUser();
	}
}
