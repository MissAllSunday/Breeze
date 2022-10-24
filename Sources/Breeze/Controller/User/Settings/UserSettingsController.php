<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Service\Actions\UserSettingsServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Form\UserSettingsBuilderInterface;
use Breeze\Util\Validate\Validations\ValidateData;
use Breeze\Util\Validate\Validations\ValidateDataInterface;

class UserSettingsController extends BaseController implements ControllerInterface
{
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
		private UserSettingsServiceInterface $userSettingsService,
		private UserServiceInterface         $userService,
		private UserSettingsBuilderInterface $userSettingsBuilder
	) {
	}

	public function dispatch(): void
	{
		$this->subAction = $this->getRequest('sa', $this->getMainAction());
		$this->userSettingsService->init($this->getSubActions());

		// @TODO: validate if subaction exists and send an error page if it doesn't


		$this->subActionCall();
	}

	public function main(): void
	{
		$scriptUrl = $this->global('scripturl');
		$userId = $this->getRequest('u', 0);

		$this->userSettingsBuilder->setForm([
			'name' => UserSettingsEntity::IDENTIFIER,
			'url' => $scriptUrl . self::URL . ';u=' . $userId . ';sa=' . self::ACTION_SAVE,
		], $this->userService->getUserSettings($userId));

		$this->render(__FUNCTION__, [
			'form' => $this->userSettingsBuilder->display(),
			'msg' => $this->userSettingsService->getMessage(),
		]);
	}

	public function save(): void
	{
		$scriptUrl = $this->global('scripturl');
		$userId = $this->getRequest('u', 0);

//		$this->userSettingsService->save(
//			$userSettings,
//			$userId
//		);

		// @TODO: service shouldn't handle setting messages
		$this->userSettingsService->setMessage($this->getText('info_updated_settings'));

		// @TODO: Service should not do the redirection
		$this->userSettingsService->redirect($scriptUrl . self::URL . ';u=' .
			$userId . ';sa=' . self::ACTION_MAIN);
	}

	public function render(string $subTemplate, array $params = [], string $smfTemplate = ''): void
	{
		$this->userSettingsService->defaultSubActionContent($subTemplate, $params, $smfTemplate);
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

		return new $validatorName($this->userService);
	}
}
