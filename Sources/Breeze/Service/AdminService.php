<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;
use Breeze\Repository\RepositoryInterface;

class AdminService extends BaseService implements ServiceInterface
{
	public const IDENTIFIER = 'BreezeAdmin';
	public const POST_URL = 'action=admin;area=breezeadmin;sa=';

	/**
	 * @var array
	 */
	protected $configVars = [];

	public const PERMISSIONS = [
	    'deleteComments',
	    'deleteOwnComments',
	    'deleteProfileComments',
	    'deleteStatus',
	    'deleteOwnStatus',
	    'deleteProfileStatus',
	    'postStatus',
	    'postComments',
	    'canCover',
	    'canMood'
	];

	/**
	 * @var MoodService
	 */
	protected $moodService;

	public function __construct(RepositoryInterface $repository, ServiceInterface $moodService)
	{
		$this->moodService = $moodService;

		parent::__construct($repository);
	}

	public function initSettingsPage($subActions): void
	{
		$context = $this->global('context');

		$this->requireOnce('ManageSettings');
		$this->requireOnce('ManageServer');

		$this->setLanguage(self::IDENTIFIER);
		$this->setTemplate(self::IDENTIFIER);

		loadGeneralSettingParameters(array_combine($subActions, $subActions), 'general');

		$context[$context['admin_menu_name']]['tab_data'] = [
		    'tabs' => [
		        'general' => [],
		        'settings' => [],
		        'moodList' => [],
		        'moodEdit' => [],
		        'permissions' => [],
		        'donate' => [],
		    ],
		];

		$this->setGlobal('context', $context);
	}

	public function configVars(): void
	{
		$this->requireOnce('ManageServer');

		$this->configVars = [
		    ['title', Breeze::PATTERN . 'page_settings'],
		    ['check', Breeze::PATTERN . 'master', 'subtext' => $this->getText('master_sub')],
		    ['check', Breeze::PATTERN . 'force_enable', 'subtext' => $this->getText('force_enable_sub')],
		    ['int', Breeze::PATTERN . 'allowed_max_num_users', 'size' => 3, 'subtext' => $this->getText('allowed_max_num_users_sub')],
		    ['int', Breeze::PATTERN . 'allowed_maxlength_aboutMe', 'size' => 4, 'subtext' => $this->getText('allowed_maxlength_aboutMe_sub')],
		    ['check', Breeze::PATTERN . 'mood', 'subtext' => $this->getText('mood_sub')],
		    ['text', Breeze::PATTERN . 'mood_label', 'subtext' => $this->getText('mood_label_sub')],
		    ['select', Breeze::PATTERN . 'mood_placement',
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
		    ['int', Breeze::PATTERN . 'flood_messages', 'size' => 3, 'subtext' => $this->getText('flood_messages_sub')],
		    ['int', Breeze::PATTERN . 'flood_minutes', 'size' => 3, 'subtext' => $this->getText('flood_minutes_sub')],
		];

		prepareDBSettingContext($this->configVars);
	}

	public function permissionsConfigVars(): void
	{
		$this->configVars = [
		    ['title', Breeze::PATTERN . 'page_permissions'],
		];

		foreach (self::PERMISSIONS as $permission)
			$this->configVars[] = [
			    'permissions',
			    'breeze_' . $permission,
			    0,
			    $this->getText('permissionname_breeze_' . $permission)
			];

		prepareDBSettingContext($this->configVars);
	}

	public function saveConfigVars(): void
	{
		checkSession();
		saveDBSettings($this->configVars);
	}

	public function setSubActionContent(string $actionName, array $templateParams): void
	{
		global $context;

		if (empty($actionName))
			return;

		if (!isset($context[Breeze::NAME]))
			$context[Breeze::NAME] = [];

		$context[Breeze::NAME][$actionName] = $templateParams;

		$context['page_title'] = $this->getText('page_' . $actionName . '_title');
		$context['sub_template'] = $actionName;
		$context[$context['admin_menu_name']]['tab_data'] = [
		    'title' => $context['page_title'],
		    'description' => $this->getText('page_' . $actionName . '_description'),
		];
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

	public function showMoodList($listId, int $start = 0): void
	{
		$scriptUrl = $this->global('scripturl');

		$listOptions = $this->moodService->getMoodList([
		    'id' => $listId,
		    'title' => $this->getText('page_' . $listId . '_title'),
		    'base_href' => $scriptUrl . '?' . self::POST_URL . $listId,
		    'items_per_page' => 10,
		], $start);

		$this->requireOnce('Subs-List');

		createList($listOptions);
	}
}
