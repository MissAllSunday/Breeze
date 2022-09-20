<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Exceptions\InvalidStatusException;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\StatusRepositoryInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class PostComment extends ValidateComment implements ValidateDataInterface
{
	protected const PARAMS = [
		CommentEntity::STATUS_ID => 0,
		CommentEntity::USER_ID => 0,
		CommentEntity::BODY => '',
	];

	protected const SUCCESS_KEY = 'published_comment';

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
		return array_merge(self::DEFAULT_STEPS, [
			self::VALID_USERS,
			self::FLOOD_CONTROL,
			self::VALID_STATUS,
		]);
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::POST_COMMENTS)) {
			throw new ValidateDataException('postComments');
		}
	}

	/**
	 * @throws InvalidStatusException
	 */
	public function validStatus(): void
	{
		$this->statusRepository->getById($this->data[CommentEntity::STATUS_ID]);
	}

	public function getInts(): array
	{
		return [
			CommentEntity::STATUS_ID,
			CommentEntity::USER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [CommentEntity::USER_ID];
	}

	public function getStrings(): array
	{
		return [CommentEntity::BODY];
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
