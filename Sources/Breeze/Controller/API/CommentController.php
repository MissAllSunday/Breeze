<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Service\CommentServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateDataInterface;

class CommentController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_POST_COMMENT = 'postComment';
	public const SUB_ACTIONS = [
		self::ACTION_POST_COMMENT,
	];

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	/**
	 * @var CommentServiceInterface
	 */
	private $commentService;

	/**
	 * @var ValidateDataInterface
	 */
	private $validateData;

	public function __construct(
		CommentServiceInterface $statusService,
		UserServiceInterface $userService,
		ValidateDataInterface $validateData
	)
	{
		$this->commentService = $statusService;
		$this->userService = $userService;
		$this->validateData = $validateData;
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function dispatch(): void
	{
		$this->validateData->getRawData();

		$this->subActionCall();
	}

	public function postComment(): void
	{
		if (!$this->validateData->isValid())
			$this->print($this->validateData->response());

		$commentData = $this->validateData->getData();

		$this->print(array_merge(
			$this->validateData->response(),
			['content' => $this->commentService->saveAndGet($commentData),]
		));
	}

	public function render(string $subTemplate, array $params): void {}

	public function getMainAction(): string
	{
		return self::ACTION_POST_COMMENT;
	}
}
