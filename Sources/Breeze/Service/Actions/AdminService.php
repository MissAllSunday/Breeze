<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\PermissionsEnum;
use Breeze\Service\PermissionsService;
use Breeze\Util\Form\SettingsBuilderInterface;

class AdminService extends ActionsBaseService implements AdminServiceInterface
{
	protected array $configVars = [];

	public function __construct(protected SettingsBuilderInterface $settingsBuilder)
	{
	}

	public function init(array $subActions): void
	{
		$context = $this->global('context');
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);

		$this->requireOnce('ManageSettings');
		$this->requireOnce('ManageServer');

		$this->setLanguage(Breeze::NAME . self::IDENTIFIER);
		$this->setTemplate(Breeze::NAME . self::IDENTIFIER);

		loadGeneralSettingParameters(array_combine($subActions, $subActions), 'main');

		$tabs = [];

		foreach ($subActions as $subActionName) {
			$tabs[$subActionName] = [
				'url' => $scriptUrl . '?' . AdminServiceInterface::POST_URL . $subActionName,
				'description' => $this->getText('breezeAdmin_' . $subActionName . '_description'),
				'label' => $this->getText('breezeAdmin_' . $subActionName . '_title'),
			];
		}

		$context[$context['admin_menu_name']]['tab_data']['tabs'] = $tabs;

		$this->setGlobal('context', $context);
	}

	public function defaultSubActionContent(
		string $subActionName,
		array  $templateParams = [],
		string $smfTemplate = ''
	): void {
		if (empty($subActionName)) {
			return;
		}

		$context = $this->global('context');
		$scriptUrl = $this->global(Breeze::SCRIPT_URL);

		$context['post_url'] = $scriptUrl . '?' .
			AdminServiceInterface::POST_URL . $subActionName . ';' .
			$context['session_var'] . '=' . $context['session_id'] . ';save';

		if (!isset($context[Breeze::NAME])) {
			$context[Breeze::NAME] = [];
		}

		if (!empty($templateParams)) {
			$context = array_merge($context, $templateParams);
		}

		$context['page_title'] = $this->getText($this->getActionName() . '_' . $subActionName . '_title');
		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : ($subActionName);

		$context[$context['admin_menu_name']]['tab_data'] += [
			'title' => $context['page_title'],
			'description' => $this->getText($this->getActionName() . '_' . $subActionName . '_description'),
		];

		$this->setGlobal('context', $context);
	}

	public function configVars(bool $save = false): void
	{
		$this->requireOnce('ManageServer');

		$this->configVars = $this->settingsBuilder->getConfigVarsSettings();

		array_unshift($this->configVars, [
			'title',
			Breeze::PATTERN . self::AREA . '_settings_title',
		]);

		if ($save) {
			$this->saveConfigVars();
		}

		prepareDBSettingContext($this->configVars);
	}

	public function permissionsConfigVars(bool $save = false): void
	{
		$this->setLanguage(Breeze::NAME . PermissionsService::IDENTIFIER);

		$this->configVars = [
			['title', Breeze::PATTERN . self::AREA . '_permissions_title'],
		];

		foreach (PermissionsEnum::ALL_PERMISSIONS as $permission) {
			$this->configVars[] = [
				'permissions',
				'breeze_' . $permission,
				0,
				$this->getSmfText('permissionname_breeze_' . $permission),
			];
		}

		if ($save) {
			$this->saveConfigVars();
		}

		prepareDBSettingContext($this->configVars);
	}

	public function saveConfigVars(): void
	{
		checkSession();
		saveDBSettings($this->configVars);
	}

	public function isEnableFeature(string $featureName = '', string $redirectUrl = ''): bool
	{
		if (empty($featureName)) {
			return false;
		}

		$feature = $this->isEnable($featureName);

		if (!$feature && !empty($redirectUrl)) {
			$this->redirect($redirectUrl);
		}

		return $feature;
	}

	public function getActionName(): string
	{
		return self::AREA;
	}

	public function loadComponents(array $components = []): void
	{
	}

	public function redirect(string $urlName): void
	{
		// TODO: Implement redirect() method.
	}
}
