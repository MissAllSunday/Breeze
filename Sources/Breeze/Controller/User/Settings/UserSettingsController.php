<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Breeze;
use Breeze\Controller\BaseController;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\PermissionsTrait;
use Breeze\Util\Error;
use Breeze\Util\Form\UserSettingsBuilderInterface;
use Breeze\Util\Response;
use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class UserSettingsController extends BaseController
{
	use PermissionsTrait;

	public const ACTION = 'profile';
	public const AREA = 'breezeSettings';
	public const TEMPLATE = 'UserSettings';
	public const URL = '?action=profile;area=breezeSettings';
	public const ACTION_MAIN = 'main';
	public const ACTION_SAVE = 'save';

	public const SUB_ACTIONS = [
		self::ACTION_MAIN,
		self::ACTION_SAVE,
	];

	protected string $subAction;

	private array $validators = [
		'save' => [
			'validator' => 'UserSettings',
			'dataName' => UserSettingsEntity::IDENTIFIER,
		],
	];

	public function __construct(
		private UserRepositoryInterface $userRepository,
		private Response $response,
		private UserSettingsBuilderInterface $userSettingsBuilder
	) {
	}

	public function dispatch(): void
	{
		$this->isNotGuest($this->getText('error_no_access'));

		if (!$this->isEnable(SettingsEntity::MASTER)) {
			Error::show('no_valid_action');
		}

		$this->setLanguage(Breeze::NAME);
		$this->setTemplate(Breeze::NAME . self::TEMPLATE);

		$this->subActionCall();
	}

	public function main(): void
	{
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);
		$userId = $this->getRequest('u', 0);

		$this->userSettingsBuilder->setForm([
			'name' => UserSettingsEntity::IDENTIFIER,
			'url' => $scriptUrl . self::URL . ';u=' . $userId . ';sa=' . self::ACTION_SAVE,
		], $this->userRepository->getById($userId));

		$this->render(__FUNCTION__, [
			'form' => $this->userSettingsBuilder->display(),
			'msg' => $this->getPersistenceMessage(),
		]);
	}

	public function save(): void
	{
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);
		$userId = $this->getRequest('u', 0);
		$userSettings = $this->getRequest('user_settings');

		$this->userRepository->save(
			$userSettings,
			$userId
		);

		$this->setPersistenceMessage($this->getText('info_updated_settings'));
		$this->response->redirect($scriptUrl . self::URL . ';u=' .
			$userId . ';sa=' . self::ACTION_MAIN);
	}

	public function getSubActions(): array
	{
		return self::SUB_ACTIONS;
	}

	public function getMainAction(): string
	{
		return self::ACTION_MAIN;
	}

	protected function setValidator(): ValidateDataInterface
	{
		$validatorName = ValidateData::getNameSpace() . ucfirst($this->validators[$this->subAction]['validator']);

		return new $validatorName();
	}

	public function getActionName(): string
	{
		return self::ACTION_MAIN;
	}
}
