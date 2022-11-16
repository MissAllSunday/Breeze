<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Likes;

use Breeze\Entity\LikeEntity;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class Like extends BaseActions implements ValidateDataInterface
{
	protected const CHECK_TYPE = 'checkType';
	protected const VALIDATE = 'validate';

	protected const PARAMS = [
		LikeEntity::ID => 0,
		LikeEntity::TYPE => '',
		LikeEntity::PARAM_SA => '',
		LikeEntity::ID_MEMBER => 0,
	];

	protected const SUCCESS_KEY = 'likeSuccess';

	/**
	 * @throws DataNotFoundException
	 */
	public function checkType(): void
	{
		$type = $this->data[LikeEntity::TYPE];

		if (!in_array($type, LikeEntity::getTypes())) {
			throw new DataNotFoundException('likesTypeInvalid');
		}
	}

	/**
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 */
	public function isValid(): void
	{
		$this->validateAllow->isFeatureEnable('enable_likes');
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->checkType();
		$this->validateAllow->permissions(Permissions::LIKES_LIKE, 'likesLike');
		$this->validateUser->areValidUsers([$this->data[LikeEntity::ID_MEMBER]]);
	}
}
