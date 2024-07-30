<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Entity\LikeEntity;
use Breeze\LikesEnum;
use Breeze\PermissionsEnum;
use Breeze\Repository\InvalidDataException;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class Like extends BaseActions implements ValidateDataInterface
{
	protected const CHECK_TYPE = 'checkType';
	protected const VALIDATE = 'validate';

	protected const PARAMS = [
		LikeEntity::COLUMN_ID => 0,
		LikeEntity::COLUMN_TYPE => '',
		LikeEntity::COLUMN_ID_MEMBER => 0,
	];

	protected const SUCCESS_KEY = 'likeSuccess';

	/**
	 * @throws DataNotFoundException
	 */
	public function checkType(): void
	{
		$type = $this->data[LikeEntity::COLUMN_TYPE];

		if (!LikesEnum::isValid($type)) {
			throw new DataNotFoundException('likesTypeInvalid');
		}
	}

	/**
	 * @throws InvalidDataException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 */
	public function checkAllow(): void
	{
		$this->validateAllow->isFeatureEnable('enable_likes', 'likesNotEnabled');
		$this->validateAllow->permissions(PermissionsEnum::LIKES_LIKE, 'likesLike');
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[LikeEntity::COLUMN_ID_MEMBER]]);
	}

	/**
	 * @throws DataNotFoundException
	 * @throws NotAllowedException
	 * @throws InvalidDataException
	 */
	public function isValid(): void
	{
		$this->checkData();
		$this->checkAllow();
		$this->checkType();
		$this->checkUser();
	}
}
