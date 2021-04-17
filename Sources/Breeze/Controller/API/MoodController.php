<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\MoodEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Service\Actions\UserSettingsServiceInterface;
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
	public const ACTION_ACTIVE = 'getActiveMoods';
	public const ACTION_USER_SET = 'setUserMood';
	public const ACTION_ALL = 'getAllMoods';
	public const SUB_ACTIONS = [
		self::ACTION_POST,
		self::ACTION_DELETE,
		self::ACTION_PATCH,
		self::ACTION_ACTIVE,
		self::ACTION_ALL,
		self::ACTION_USER_SET,
	];

	private UserServiceInterface $userService;

	private MoodServiceInterface $moodService;
	private UserSettingsServiceInterface $userSettingsService;

	public function __construct(
		UserServiceInterface $userService,
		UserSettingsServiceInterface $userSettingsService,
		MoodServiceInterface $moodService,
		ValidateGatewayInterface $gateway
	) {
		parent::__construct($gateway);

		$this->userSettingsService = $userSettingsService;
		$this->userService = $userService;
		$this->moodService = $moodService;
		$this->gateway = $gateway;
	}

	public function createMood(): void
	{
	}

	public function deleteMood(): void
	{
		$data = $this->gateway->getData();

		$this->moodService->deleteMoods([$data[MoodEntity::ID]]);

		$this->print($this->gateway->response());
	}

	public function getAllMoods(): void
	{
		$this->print($this->moodService->getAll());
	}

	public function getActiveMoods(): void
	{
		$this->print($this->moodService->getActiveMoods());
	}

	public function setUserMood(): void
	{
		$data = $this->gateway->getData();
		$userId = array_pop($data);

		$this->userSettingsService->save($data, $userId);

		$this->print($this->gateway->response());
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_ACTIVE;
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
