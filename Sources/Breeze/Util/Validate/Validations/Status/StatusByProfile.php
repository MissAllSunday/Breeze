<?php

declare(strict_types=1);


namespace Breeze\Util\Validate\Validations\Status;

use Breeze\Entity\StatusEntity;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class StatusByProfile extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [StatusEntity::WALL_ID => 0];

	/**
	 * @throws InvalidDataException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[StatusEntity::WALL_ID]]);
	}

	/**
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 */
	public function isValid(): void
	{
		$this->checkData();
		$this->checkUser();
	}
}
