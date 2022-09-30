<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\CommentEntity;
use Breeze\Repository\CommentRepositoryInterface;
use Breeze\Repository\InvalidCommentException;
use Breeze\Util\Validate\ValidateGateway;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\Comment\ValidateComment;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class CommentController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_POST_COMMENT = 'postComment';
	public const ACTION_DELETE = 'deleteComment';

	public const SUB_ACTIONS = [
		self::ACTION_POST_COMMENT,
		self::ACTION_DELETE,
	];

	protected ValidateDataInterface $validator;

	public function __construct(
		private CommentRepositoryInterface $commentRepository,
		protected ValidateGatewayInterface $gateway
	) {
		parent::__construct($gateway);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function setValidator(): void
	{
		$validatorName = ValidateComment::getNameSpace() . ucfirst($this->subAction);

		$this->validator = new $validatorName($this->commentRepository);
	}

	public function getValidator(): ValidateDataInterface
	{
		return $this->validator;
	}

	public function postComment(): void
	{
		try {
			$commentId = $this->commentRepository->save($this->validator->getData());

			$this->print(array_merge(
				$this->gateway->response(),
				['content' => $this->commentRepository->getById($commentId)]
			));
		} catch (InvalidCommentException $invalidCommentException) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $invalidCommentException->getMessage(),
			]);
		}
	}

	public function deleteComment(): void
	{
		$data = $this->validator->getData();

		try {
			$this->commentRepository->deleteById($data[CommentEntity::ID]);

			$this->print($this->gateway->response());
		} catch (InvalidCommentException $invalidCommentException) {
			$this->print([
				'type' => ValidateGateway::ERROR_TYPE,
				'message' => $invalidCommentException->getMessage(),
			]);
		}
	}

	public function getMainAction(): string
	{
		return self::ACTION_POST_COMMENT;
	}
}
