<?php

declare(strict_types=1);

namespace Breeze\Controller\User\Settings;

use Breeze\Controller\BaseController;
use Breeze\Controller\ControllerInterface;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Service\Actions\UserSettingsServiceInterface;
use Breeze\Service\UserServiceInterface;
use Breeze\Util\Form\UserSettingsBuilderInterface;
use Breeze\Util\Validate\ValidateGatewayInterface;

class UserSettingsController extends BaseController implements ControllerInterface
{
	public const URL = '?action=profile;area=breezeSettings';
	public const ACTION_MAIN = 'main';
	public const ACTION_SAVE = 'save';
	public const SUB_ACTIONS = [
		self::ACTION_MAIN,
		self::ACTION_SAVE,
	];

	private UserServiceInterface $userService;

	private UserSettingsServiceInterface $userSettingsService;

	private UserSettingsBuilderInterface $userSettingsBuilder;

	private ValidateGatewayInterface $gateway;

	public function __construct(
		UserSettingsServiceInterface $userSettingsService,
		UserServiceInterface $userService,
		UserSettingsBuilderInterface $userSettingsBuilder,
		ValidateGatewayInterface $gateway
	)
	{
		$this->userService = $userService;
		$this->userSettingsService = $userSettingsService;
		$this->userSettingsBuilder = $userSettingsBuilder;
		$this->gateway = $gateway;
	}

	public function dispatch(): void
	{
		$this->userSettingsService->init($this->getSubActions());
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
			'form' => $this->userSettingsBuilder->display()
		]);
	}

	public function save(): void
	{
		$userId = $this->getRequest('u', 0);
		$userSettings = $this->getRequest(UserSettingsEntity::IDENTIFIER, []);

		$this->userSettingsService->save(
			$userSettings,
			$userId
		);
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
}
