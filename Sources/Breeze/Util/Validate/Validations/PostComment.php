<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Entity\CommentEntity;
use Breeze\Service\CommentServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class PostComment extends ValidateData implements ValidateDataInterface
{
	protected const PARAMS = [
		CommentEntity::COLUMN_STATUS_ID => 0,
		CommentEntity::COLUMN_STATUS_OWNER_ID => 0,
		CommentEntity::COLUMN_POSTER_ID => 0,
		CommentEntity::COLUMN_PROFILE_ID => 0,
		CommentEntity::COLUMN_BODY => '',
	];

	protected const SUCCESS_KEY = 'published_comment';

	/**
	 * @var CommentServiceInterface
	 */
	private $commentService;

	public function __construct(UserServiceInterface $userService, CommentServiceInterface $commentService)
	{
		$this->commentService = $commentService;

		parent::__construct($userService);
	}

	public function successKeyString(): string
	{
		return self::SUCCESS_KEY;
	}

	public function getSteps(): array
	{
		$this->steps = self::ALL_STEPS;
		$this->steps[] = 'permissions';

		return $this->steps;
	}

	public function setSteps(array $customSteps): void
	{
		$this->steps = $customSteps;
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		if (!Permissions::isAllowedTo(Permissions::POST_COMMENTS))
			throw new ValidateDataException('postComments');
	}

	public function getInts(): array
	{
		return [
			CommentEntity::COLUMN_STATUS_ID,
			CommentEntity::COLUMN_STATUS_OWNER_ID,
			CommentEntity::COLUMN_POSTER_ID,
			CommentEntity::COLUMN_PROFILE_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			CommentEntity::COLUMN_STATUS_OWNER_ID,
			CommentEntity::COLUMN_POSTER_ID,
			CommentEntity::COLUMN_PROFILE_ID,
		];
	}

	public function getStrings(): array
	{
		return [CommentEntity::COLUMN_BODY];
	}

	public function getPosterId(): int
	{
		return $this->data[CommentEntity::COLUMN_POSTER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
