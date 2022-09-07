<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\LikeEntity;
use Breeze\Service\LikeServiceInterface;
use Breeze\Service\UserServiceInterface;
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

	private LikeServiceInterface $likesService;

	private UserServiceInterface $userService;

	public function __construct(
		LikeServiceInterface $likesService,
		UserServiceInterface $userService,
		ValidateGatewayInterface $gateway
	) {
		parent::__construct($gateway);

		$this->userService = $userService;
		$this->likesService = $likesService;
	}

	public function like(): void
	{
		$data = $this->gateway->getData();

		$this->print(array_merge(
			$this->gateway->response(),
			['content' => $this->likesService->likeContent(
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

	public function getValidator(): ValidateDataInterface
	{
		$validatorName = ValidateLikes::getNameSpace() . ucfirst($this->subAction);

		return new $validatorName(
			$this->userService,
			$this->likesService
		);
	}

	public function render(string $subTemplate, array $params): void
	{
	}
}
