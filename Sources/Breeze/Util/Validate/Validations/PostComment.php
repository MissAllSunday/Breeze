<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\InvalidStatusException;
use Breeze\Service\CommentServiceInterface;
use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class PostComment extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [
		CommentEntity::STATUS_ID => 0,
		CommentEntity::COLUMN_STATUS_OWNER_ID => 0,
		CommentEntity::USER_ID => 0,
		CommentEntity::COLUMN_PROFILE_ID => 0,
		CommentEntity::BODY => '',
	];

	protected const SUCCESS_KEY = 'published_comment';

	private CommentServiceInterface $commentService;

	private StatusServiceInterface $statusService;

	public function __construct(
		UserServiceInterface $userService,
		StatusServiceInterface $statusService,
		CommentServiceInterface $commentService
	) {
		$this->commentService = $commentService;
		$this->statusService = $statusService;

		parent::__construct($userService);
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
		$this->statusService->getById($this->data[CommentEntity::STATUS_ID]);
	}

	public function getInts(): array
	{
		return [
			CommentEntity::STATUS_ID,
			CommentEntity::COLUMN_STATUS_OWNER_ID,
			CommentEntity::USER_ID,
			CommentEntity::COLUMN_PROFILE_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			CommentEntity::COLUMN_STATUS_OWNER_ID,
			CommentEntity::USER_ID,
			CommentEntity::COLUMN_PROFILE_ID,
		];
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
