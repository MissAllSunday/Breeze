<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Service\LikesServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class LikesController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_LIKE = 'like';

	public const ACTION_UNLIKE = 'unlike';

	public const SUB_ACTIONS = [
		self::ACTION_LIKE,
		self::ACTION_UNLIKE,
	];

	private LikesServiceInterface $likesService;

	private UserServiceInterface $userService;

	public function __construct(
		LikesServiceInterface $likesService,
		UserServiceInterface $userService,
		ValidateGatewayInterface $gateway
	) {
		parent::__construct($gateway);

		$this->userService = $userService;
		$this->likesService = $likesService;
		$this->gateway = $gateway;
	}

	public function like(): void
	{
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_LIKE;
	}

	public function setValidator(): ValidateDataInterface
	{
		$validatorName = ValidateData::getNameSpace() . ucfirst($this->subAction);

		return new $validatorName(
			$this->userService,
			$this->likesService
		);
	}

	public function render(string $subTemplate, array $params): void
	{
	}
}
