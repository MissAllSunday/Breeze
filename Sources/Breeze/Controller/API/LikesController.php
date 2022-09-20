<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\LikeEntity;
use Breeze\Repository\LikeRepositoryInterface;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\Likes\ValidateLikes;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class LikesController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_LIKE = 'like';
	public const ACTION_UNLIKE = 'unlike';

	public const SUB_ACTIONS = [
		self::ACTION_LIKE,
		self::ACTION_UNLIKE,
	];

	protected ValidateDataInterface $validator;

	public function __construct(
		private LikeRepositoryInterface $likeRepository,
		protected ValidateGatewayInterface $gateway
	) {
		parent::__construct($gateway);
	}

	public function like(): void
	{
		$data = $this->validator->getData();

		$this->print(array_merge(
			$this->gateway->response(),
			['content' => $this->likeRepository->likeContent(
				$data[LikeEntity::TYPE],
				$data[LikeEntity::ID],
				$data[LikeEntity::ID_MEMBER]
			)]
		));
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_LIKE;
	}

	public function setValidator(): void
	{
		$validatorName = ValidateLikes::getNameSpace() . ucfirst($this->subAction);

		$this->validator = $validatorName($this->likeRepository);
	}

	public function getValidator(): ValidateDataInterface
	{
		return $this->validator;
	}

	public function render(string $subTemplate, array $params): void
	{
	}
}
