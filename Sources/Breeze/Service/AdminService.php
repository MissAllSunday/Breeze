<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Repository\RepositoryInterface;

class AdminService extends BaseService implements ServiceInterface
{
	public const IDENTIFIER = 'Admin';
	public const AREA = 'breezeAdmin';
	public const POST_URL = 'action=admin;area=breezeAdmin;sa=';

	/**
	 * @var array
	 */
	protected $configVars = [];

	public function initSettingsPage($subActions): void
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

		$this->configVars = [
			[
				'title',
				Breeze::PATTERN . 'page_settings_title'
			],
			[
				'check',
				Breeze::PATTERN . 'master',
				'subtext' => $this->getText('master_sub')
			],
			[
				'check', Breeze::PATTERN . 'force_enable',
				'subtext' => $this->getText('force_enable_sub'),
			],
			[
				'int',
				Breeze::PATTERN . 'allowed_max_num_users',
				'size' => 3,
				'subtext' => $this->getText('allowed_max_num_users_sub'),
			],
			[
				'int',
				Breeze::PATTERN . 'allowed_maxlength_aboutMe',
				'size' => 4,
				'subtext' => $this->getText('allowed_maxlength_aboutMe_sub'),
			],
			[
				'check',
				Breeze::PATTERN . 'mood',
				'subtext' => $this->getText('mood_sub'),
			],
			[
				'text',
				Breeze::PATTERN . 'mood_label',
				'subtext' => $this->getText('mood_label_sub'),
			],
			[
				'select',
				Breeze::PATTERN . 'mood_placement',
				[
					$this->getSmfText('custom_profile_placement_standard'),
					$this->getSmfText('custom_profile_placement_icons'),
					$this->getSmfText('custom_profile_placement_above_signature'),
					$this->getSmfText('custom_profile_placement_below_signature'),
					$this->getSmfText('custom_profile_placement_below_avatar'),
					$this->getSmfText('custom_profile_placement_above_member'),
					$this->getSmfText('custom_profile_placement_bottom_poster'),
				],
				'subtext' => $this->getText('mood_placement_sub'),
				'multiple' => false,
			],
			[
				'int',
				Breeze::PATTERN . 'flood_messages',
				'size' => 3,
				'subtext' => $this->getText('flood_messages_sub'),
			],
			[
				'int',
				Breeze::PATTERN . 'flood_minutes',
				'size' => 3,
				'subtext' => $this->getText('flood_minutes_sub')
			],
		];

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

	public function coverConfigVars(bool $save = false): void
	{
		$this->configVars = [
			['title', Breeze::PATTERN . 'page_permissions'],
			['check', Breeze::PATTERN . 'cover', 'subtext' => $this->getText('cover_sub')],
			['int', Breeze::PATTERN . 'cover_max_image_size', 'size' => 3, 'subtext' => $this->getText('cover_max_image_size_sub')],
			['int', Breeze::PATTERN . 'cover_max_image_width', 'size' => 4, 'subtext' => $this->getText('cover_max_image_width_sub')],
			['int', Breeze::PATTERN . 'cover_max_image_height', 'size' => 3, 'subtext' => $this->getText('cover_max_image_height_sub')],
			['text', Breeze::PATTERN . 'cover_image_types', 'size' => 25, 'subtext' => $this->getText('cover_image_types_sub')],
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

	public function setSubActionContent(
		string $actionName,
		array $templateParams = [],
		string $smfTemplate = ''
	): void
	{
		if (empty($actionName))
			return;

		$context = $this->global('context');
		$scriptUrl = $this->global('scripturl');

		$context['post_url'] =  $scriptUrl . '?' .
			AdminService::POST_URL . $actionName . ';' .
			$context['session_var'] . '=' . $context['session_id'] . ';save';

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		if (!empty($templateParams))
			$context = array_merge($context, $templateParams);

		$context['page_title'] = $this->getText('page_' . $actionName . '_title');
		$context['sub_template'] = !empty($smfTemplate) ?
			$smfTemplate : (Breeze::NAME . self::IDENTIFIER . '_' . $actionName);
		$context[$context['admin_menu_name']]['tab_data'] = [
			'title' => $context['page_title'],
			'description' => $this->getText('page_' . $actionName . '_description'),
		];

		$this->setGlobal('context', $context);
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
}
