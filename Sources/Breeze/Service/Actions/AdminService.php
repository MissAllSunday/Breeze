<?php

declare(strict_types=1);


namespace Breeze\Service\Actions;

use Breeze\Breeze;
use Breeze\Entity\SettingsEntity;
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

		if (!$this->isEnable(SettingsEntity::ENABLE_MOOD))
			$subActions = array_diff($subActions, ['moodList']);

		loadGeneralSettingParameters(array_combine($subActions, $subActions), 'general');

		$context[$context['admin_menu_name']]['tab_data']['tabs'] = array_fill_keys($subActions, []);

		$this->setGlobal('context', $context);
	}

	public function defaultSubActionContent(
		string $subActionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void
	{
		if (empty($subActionName))
			return;

		$context = $this->global('context');
		$scriptUrl = $this->global('scripturl');

		$context['post_url'] =  $scriptUrl . '?' .
			AdminService::POST_URL . $subActionName . ';' .
			$context['session_var'] . '=' . $context['session_id'] . ';save';

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		if (!empty($templateParams))
			$context = array_merge($context, $templateParams);

		$context['page_title'] = $this->getText($this->getActionName() . '_' . $subActionName . '_title');
		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : (self::AREA . '_' . $subActionName);

		$context[$context['admin_menu_name']]['tab_data'] += [
			'title' => $context['page_title'],
			'description' => $this->getText($this->getActionName() . '_' . $subActionName . '_description'),
		];

		$this->setGlobal('context', $context);
	}

	public function configVars(bool $save = false): void
	{
		$this->requireOnce('ManageServer');

		$this->configVars = $this->formService->getConfigVarsSettings();

		array_unshift($this->configVars, [
			'title',
			Breeze::PATTERN . self::AREA . '_settings_title'
		]);

		if ($save)
			$this->saveConfigVars();

		prepareDBSettingContext($this->configVars);
	}

	public function permissionsConfigVars(bool $save = false): void
	{
		$this->setLanguage(Breeze::NAME . PermissionsService::IDENTIFIER);

		$this->configVars = [
			['title', Breeze::PATTERN . self::AREA . '_permissions_title'],
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

		$feature = $this->isEnable($featureName);

		if (!$feature && !empty($redirectUrl))
			$this->redirect($redirectUrl);

		return $feature;
	}

	public function getActionName(): string
	{
		return self::AREA;
	}
}
