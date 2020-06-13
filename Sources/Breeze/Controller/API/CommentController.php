<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Service\CommentServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGatewayInterface;

class CommentController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_POST_COMMENT = 'postComment';
	public const ACTION_DELETE = 'delete';
	public const SUB_ACTIONS = [
		self::ACTION_POST_COMMENT,
		self::ACTION_DELETE,
	];

	/**
	 * @var UserServiceInterface
	 */
	private $userService;

	/**
	 * @var CommentServiceInterface
	 */
	private $commentService;

	public function __construct(
		CommentServiceInterface $statusService,
		UserServiceInterface $userService,
		ValidateGatewayInterface $gateway
	)
	{
		$this->commentService = $statusService;
		$this->userService = $userService;
		$this->gateway = $gateway;
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function dispatch(): void
	{
		$this->subActionCall();
	}

	public function postComment(): void
	{
		if (!$this->gateway->isValid())
			$this->print($this->gateway->response());

		$this->print(array_merge(
			$this->gateway->response(),
			['content' => $this->commentService->saveAndGet($this->gateway->getData())]
		));
	}

	public function delete(): void
	{
		if (!$this->gateway->isValid())
			$this->print($this->gateway->response());
	}

	public function render(string $subTemplate, array $params): void {}

	public function getMainAction(): string
	{
		return self::ACTION_POST_COMMENT;
	}
}
