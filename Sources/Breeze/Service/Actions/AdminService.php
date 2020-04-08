<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Service\FormServiceInterface;
use Breeze\Service\PermissionsService;

class AdminService extends ActionsBaseService implements AdminServiceInterface
{
	/**
	 * @var array
	 */
	protected $configVars = [];

	/**
	 * @var FormServiceInterface
	 */
	private $formService;

	public function __construct(FormServiceInterface $formService)
	{
		$this->formService = $formService;
	}

	public function init(array $subActions): void
	{
		$context = $this->global('context');

		$this->requireOnce('ManageSettings');
		$this->requireOnce('ManageServer');

		$this->setLanguage(Breeze::NAME . self::IDENTIFIER);
		$this->setTemplate(Breeze::NAME . self::IDENTIFIER);

		loadGeneralSettingParameters(array_combine($subActions, $subActions), 'general');

		$context[$context['admin_menu_name']]['tab_data'] = [
			'tabs' => array_fill_keys($subActions, []),
		];

		$this->setGlobal('context', $context);
	}

	public function configVars(bool $save = false): void
	{
		$this->requireOnce('ManageServer');

		$this->configVars = $this->formService->getConfigVarsSettings();

		array_unshift($this->configVars, [
			'title',
			Breeze::PATTERN . 'page_settings_title'
		]);

		if ($save)
			$this->saveConfigVars();

		prepareDBSettingContext($this->configVars);
	}

	public function permissionsConfigVars(bool $save = false): void
	{
		$this->setLanguage(Breeze::NAME . PermissionsService::IDENTIFIER);

		$this->configVars = [
			['title', Breeze::PATTERN . 'page_permissions'],
		];

		foreach (PermissionsService::ALL_PERMISSIONS as $permission)
			$this->configVars[] = [
				'permissions',
				'breeze_' . $permission,
				0,
				$this->getSmfText('permissionname_breeze_' . $permission)
			];

		if ($save)
			$this->saveConfigVars();

		prepareDBSettingContext($this->configVars);
	}

	public function saveConfigVars(): void
	{
		checkSession();
		saveDBSettings($this->configVars);
	}

	public function isEnableFeature(string $featureName = '', string $redirectUrl = ''): bool
	{
		if (empty($featureName))
			return false;

		$feature = $this->getSetting($featureName);

		if (empty($feature) && !empty($redirectUrl))
			$this->redirect($redirectUrl);

		return (bool) $feature;
	}

	public function getActionName(): string
	{
		return self::AREA;
	}
}
