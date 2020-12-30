<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class MoodController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_POST = 'createMood';
	public const ACTION_DELETE = 'deleteMood';
	public const ACTION_PATCH = 'editMood';
	public const SUB_ACTIONS = [
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_PATCH,
	];

	private UserServiceInterface $userService;

	private MoodServiceInterface $moodService;

	public function __construct(
		UserServiceInterface $userService,
		MoodServiceInterface $moodService,
		ValidateGatewayInterface $gateway
	) {
		parent::__construct($gateway);
		$this->userService = $userService;
		$this->moodService = $moodService;
		$this->gateway = $gateway;
	}

	public function createMood(): void
	{
		var_dump($this->gateway->getData());
		die;
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_POST;
	}

	public function setValidator(): ValidateDataInterface
	{
		$validatorName = ValidateData::getNameSpace() . ucfirst($this->subAction);

		return new $validatorName(
			$this->userService,
			$this->moodService
		);
	}

	public function render(string $subTemplate, array $params): void
	{
	}
}
