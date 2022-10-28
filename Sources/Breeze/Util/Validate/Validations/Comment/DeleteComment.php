<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Entity\CommentEntity as CommentEntity;
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

class DeleteComment extends BaseActions implements ValidateDataInterface
{
	protected const PARAMS = [
		CommentEntity::ID => 0,
		CommentEntity::USER_ID => 0,
	];

	protected const SUCCESS_KEY = 'deleted_comment';

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
	 */
	public function checkAllow(): void
	{
		$permissionName = $this->repository->getCurrentUserInfo()['id'] === $this->data[CommentEntity::USER_ID] ?
			Permissions::DELETE_OWN_COMMENTS : Permissions::DELETE_COMMENTS;

		$this->validateAllow->permissions($permissionName, 'deleteStatus');
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[CommentEntity::USER_ID]]);
	}

	/**
	 * @throws DataNotFoundException
	 * @throws InvalidDataException
	 */
	public function checkData(): void
	{
		$this->validateData->compare(self::PARAMS, $this->data);
		$this->validateData->dataExists($this->data[CommentEntity::ID], $this->repository);
	}

	/**
	 * @throws InvalidDataException
	 * @throws NotAllowedException
	 * @throws DataNotFoundException
	 */
	public function isValid(): void
	{
		$this->checkData();
		$this->checkAllow();
		$this->checkUser();
	}
}
