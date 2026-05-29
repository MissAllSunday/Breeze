<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Enums\PermissionsEnum;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Validate\DataNotFoundException;
use Breeze\Util\Validate\InvalidDataException;
use Breeze\Util\Validate\NotAllowedException;
use Breeze\Util\Validate\Validations\BaseActions;
use Breeze\Util\Validate\Validations\ValidateDataInterface;
use Breeze\Validate\Types\Allow;
use Breeze\Validate\Types\Data;
use Breeze\Validate\Types\User;

class DeleteComment extends BaseActions implements ValidateDataInterface
{
	protected const array PARAMS = [
		CommentEntity::ID => 0,
		CommentEntity::USER_ID => 0,
	];

	protected const string SUCCESS_KEY = 'deleted_comment';

	public function __construct(
		Data $validateData,
		User $validateUser,
		Allow $validateAllow,
		CommentRepositoryInterface $repository,
		protected StatusRepositoryInterface $statusRepository
	) {
		parent::__construct($validateData, $validateUser, $validateAllow, $repository);
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
		$currentUserId = (int) $this->repository->getCurrentUserInfo()['id'];
		$commentUserId = $this->data[CommentEntity::USER_ID];

		if ($currentUserId === $commentUserId) {
			$this->validateAllow->permissions(PermissionsEnum::DELETE_OWN_COMMENTS, self::PERMISSION_MSG_DELETE_STATUS);

			return;
		}

		$comment = $this->repository->getById($this->data[CommentEntity::ID]);
		assert($comment instanceof CommentEntity);
		$status = $this->statusRepository->getBasicInfoById($comment->getStatusId());

		if ($status->getWallId() === $currentUserId) {
			$this->validateAllow->permissions(PermissionsEnum::DELETE_PROFILE_COMMENTS, self::PERMISSION_MSG_DELETE_STATUS);

			return;
		}

		$this->validateAllow->permissions(PermissionsEnum::DELETE_COMMENTS, self::PERMISSION_MSG_DELETE_STATUS);
	}

	/**
	 * @throws DataNotFoundException
	 */
	public function checkUser(): void
	{
		$this->validateUser->areValidUsers([$this->data[CommentEntity::USER_ID]]);
	}

	/**
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
