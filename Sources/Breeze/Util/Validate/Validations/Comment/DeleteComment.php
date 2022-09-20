<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Entity\CommentEntity as CommentEntity;
use Breeze\Exceptions\InvalidCommentException;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class DeleteComment extends ValidateComment implements ValidateDataInterface
{
	public array $steps = [
		self::COMPARE,
		self::INT,
		self::VALID_COMMENT,
		self::VALID_USER,
		self::PERMISSIONS,
	];

	protected const PARAMS = [
		CommentEntity::ID => 0,
		CommentEntity::USER_ID => 0,
	];

	protected const SUCCESS_KEY = 'deleted_comment';

	private array $comment;

	public function __construct(
		protected CommentRepositoryInterface $commentRepository,
		protected StatusRepositoryInterface $statusRepository
	) {
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		return $this->steps;
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		$currentUserInfo = $this->getCurrentUserInfo();

		if ($currentUserInfo['id'] === $this->data[CommentEntity::USER_ID] &&
			!Permissions::isAllowedTo(Permissions::DELETE_OWN_COMMENTS)) {
			throw new ValidateDataException('deleteComments');
		}

		if (!Permissions::isAllowedTo(Permissions::DELETE_COMMENTS)) {
			throw new ValidateDataException('deleteComments');
		}
	}

	/**
	 * @throws InvalidCommentException
	 */
	public function validComment(): void
	{
		$this->comment = $this->commentService->getById($this->data[CommentEntity::ID]);
	}

	/**
	 * @throws InvalidCommentException
	 * @throws ValidateDataException
	 */
	public function validUser(): void
	{
		if (!$this->comment) {
			$this->comment = $this->commentService->getById($this->data[CommentEntity::ID]);
		}

		if (!isset($this->data[CommentEntity::USER_ID]) ||
			($this->data[CommentEntity::USER_ID]
			!==
			$this->comment['data'][$this->data[CommentEntity::ID]][CommentEntity::USER_ID])) {
			throw new ValidateDataException('wrong_values');
		}
	}

	public function getInts(): array
	{
		return [
			CommentEntity::ID,
			CommentEntity::USER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			CommentEntity::USER_ID,
		];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getPosterId(): int
	{
		return $this->data[CommentEntity::USER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
