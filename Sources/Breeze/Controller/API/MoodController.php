<?php

declare(strict_types=1);


namespace Breeze\Controller\API;

use Breeze\Entity\MoodEntity;
use Breeze\Service\Actions\UserSettingsServiceInterface;
use Breeze\Service\MoodServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Validate\ValidateGatewayInterface;
use Breeze\Util\Validate\Validations\Mood\ValidateMood;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class MoodController extends ApiBaseController implements ApiBaseInterface
{
	public const ACTION_POST = 'postMood';
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

	protected ValidateDataInterface $validator;

	public function __construct(
		private UserServiceInterface         $userService,
		private UserSettingsServiceInterface $userSettingsService,
		private MoodServiceInterface         $moodService,
		protected ValidateGatewayInterface   $gateway
	) {
		parent::__construct($gateway);
	}

	public function postMood(): void
	{
		$data = $this->validator->getData();

		$mood = $this->moodService->saveMood($data);

		$this->print(array_merge(
			$this->gateway->response(),
			['content' => $mood]
		));
	}

	public function deleteMood(): void
	{
		$data = $this->validator->getData();

		$this->moodService->deleteMoods([$data[MoodEntity::ID]]);

		$this->print($this->gateway->response(), 204);
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
		$data = $this->validator->getData();
		$userId = array_pop($data);

		$this->userSettingsService->save($data, $userId);

		$this->print($this->gateway->response(), 202);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_ACTIVE;
	}

	public function setValidator(): void
	{
		$validatorName = ValidateMood::getNameSpace() . ucfirst($this->subAction);

		$this->validator = new $validatorName(
			$this->userService,
			$this->moodService
		);
	}

	public function getValidator(): ValidateDataInterface
	{
		return $this->validator;
	}

	public function render(string $subTemplate, array $params): void
	{
	}
}
