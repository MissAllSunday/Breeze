<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\BaseRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class PostComment extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		CommentEntity::STATUS_ID => 0,
		CommentEntity::USER_ID => 0,
		CommentEntity::BODY => '',
	];

	protected const SUCCESS_KEY = 'published_comment';

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

		$userId = $this->data[CommentEntity::USER_ID];

		$this->validateAllow->permissions(Permissions::POST_COMMENTS, 'postComments');
		$this->validateAllow->floodControl($userId);
		$this->validateUser->areValidUsers([$userId]);
	}
}
