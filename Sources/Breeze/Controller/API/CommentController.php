<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\InvalidCommentException;
use Breeze\Service\CommentServiceInterface;
use Breeze\Service\StatusServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGateway;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class CommentController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_POST_COMMENT = 'postComment';
	public const ACTION_DELETE = 'deleteComment';
	public const SUB_ACTIONS = [
		self::ACTION_POST_COMMENT,
		self::ACTION_DELETE,
	];

	/**
	 */
	private UserServiceInterface $userService;

	/**
	 */
	private CommentServiceInterface $commentService;

	/**
	 */
	private StatusServiceInterface $statusService;

	public function __construct(
		CommentServiceInterface $commentService,
		StatusServiceInterface $statusService,
		UserServiceInterface $userService,
		ValidateGatewayInterface $gateway
	)
	{
		$this->commentService = $commentService;
		$this->userService = $userService;
		$this->statusService = $statusService;

		parent::__construct($gateway);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}
	
	public function setValidator(): ValidateDataInterface
	{
		$validatorName = ValidateData::getNameSpace() . ucfirst($this->subAction);
		
		return new $validatorName(
			$this->userService,
			$this->statusService,
			$this->commentService
		);
	}

	public function postComment(): void
	{
		$this->print(array_merge(
			$this->gateway->response(),
			['content' => $this->commentService->saveAndGet($this->gateway->getData())]
		));
	}

	public function deleteComment(): void
	{
		$data = $this->gateway->getData();

		try {
			$this->commentService->deleteById($data[CommentEntity::COLUMN_ID]);

			$this->print($this->gateway->response());
		} catch (InvalidCommentException $e) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $e->getMessage(),
			]);
		}
	}

	public function render(string $subTemplate, array $params): void {}

	public function getMainAction(): string
	{
		return self::ACTION_POST_COMMENT;
	}
}
