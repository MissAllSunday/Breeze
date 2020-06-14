<?php

declare(strict_types=1);

namespace Breeze\Util\Validate\Validations;

use Breeze\Service\CommentServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Permissions;
use Breeze\Util\Validate\ValidateDataException;

class DeleteComment extends ValidateData implements ValidateDataInterface
{
	public const PARAM_COMMENT_ID = 'commentId';
	public const PARAM_POSTER_ID = 'posterId';

	public $steps = [
		'clean',
		'isInt',
		'validUser',
		'permissions'
	];

	protected const PARAMS = [
		self::PARAM_COMMENT_ID => 0,
		self::PARAM_POSTER_ID => 0,
	];

	protected const SUCCESS_KEY = 'deleted_comment';

	/**
	 * @var array
	 */
	private $comment;

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
		return $this->steps;
	}

	/**
	 * @throws ValidateDataException
	 */
	public function permissions(): void
	{
		$currentUserInfo = $this->userService->getCurrentUserInfo();

		if ($currentUserInfo['id'] === $this->data[self::PARAM_POSTER_ID] &&
			!Permissions::isAllowedTo(Permissions::DELETE_OWN_COMMENTS))
			throw new ValidateDataException('deleteComments');

		if (!Permissions::isAllowedTo(Permissions::DELETE_COMMENTS))
			throw new ValidateDataException('deleteComments');
	}

	public function validUser(): void
	{
		$this->comment = $this->commentService->getById()
	}

	public function getInts(): array
	{
		return [
			self::PARAM_COMMENT_ID,
			self::PARAM_POSTER_ID,
		];
	}

	public function getUserIdsNames(): array
	{
		return [
			self::PARAM_POSTER_ID,
		];
	}

	public function getStrings(): array
	{
		return [];
	}

	public function getPosterId(): int
	{
		return $this->data[self::PARAM_POSTER_ID] ?? 0;
	}

	public function getParams(): array
	{
		return self::PARAMS;
	}
}
