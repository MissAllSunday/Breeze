<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Entity\CommentEntity;
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
	 * @throws InvalidDataException
	 * @throws DataNotFoundException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateData->isInt([CommentEntity::STATUS_ID, CommentEntity::USER_ID], $this->data);
		$this->validateData->isString([CommentEntity::BODY], $this->data);
	}

	/**
	 * @throws NotAllowedException
	 */
	public function checkAllow(): void
	{
		$this->validateAllow->permissions(Permissions::POST_COMMENTS, 'postComments');
		$this->validateAllow->floodControl($this->data[CommentEntity::USER_ID]);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[CommentEntity::USER_ID]]);
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
