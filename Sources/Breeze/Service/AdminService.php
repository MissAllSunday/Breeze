<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Breeze;

class AdminService extends BaseService implements ServiceInterface
{
	public const IDENTIFIER = 'BreezeAdmin';
	public const POST_URL = 'action=admin;area=breezeadmin;sa=settings';

	/**
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @var Text
	 */
	protected $text;

	/**
	 * @var array
	 */
	private $configVars = [];

	public function __construct(Settings $settings, Text $text)
	{
		$this->settings = $settings;
		$this->text = $text;
	}

	public function initSettingsPage($subActions): void
	{
		$context = $this->global('context');

		$this->requireOnce('ManageSettings');
		$this->text->setLanguage(self::IDENTIFIER);
		$this->settings->setTemplate(self::IDENTIFIER);

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
		    ['check', Breeze::PATTERN . 'master', 'subtext' => $this->text->get('master_sub')],
		    ['check', Breeze::PATTERN . 'force_enable', 'subtext' => $this->text->get('force_enable_sub')],
		    ['int', Breeze::PATTERN . 'allowed_max_num_users', 'size' => 3, 'subtext' => $this->text->get('allowed_max_num_users_sub')],
		    ['int', Breeze::PATTERN . 'allowed_maxlength_aboutMe', 'size' => 4, 'subtext' => $this->text->get('allowed_maxlength_aboutMe_sub')],
		    ['check', Breeze::PATTERN . 'mood', 'subtext' => $this->text->get('mood_sub')],
		    ['text', Breeze::PATTERN . 'mood_label', 'subtext' => $this->text->get('mood_label_sub')],
		    ['select', Breeze::PATTERN . 'mood_placement',
		        [
		            $this->text->getSmf('custom_profile_placement_standard'),
		            $this->text->getSmf('custom_profile_placement_icons'),
		            $this->text->getSmf('custom_profile_placement_above_signature'),
		            $this->text->getSmf('custom_profile_placement_below_signature'),
		            $this->text->getSmf('custom_profile_placement_below_avatar'),
		            $this->text->getSmf('custom_profile_placement_above_member'),
		            $this->text->getSmf('custom_profile_placement_bottom_poster'),
		        ],
		        'subtext' => $this->text->get('mood_placement_sub'),
		        'multiple' => false,
		    ],
		    ['int', Breeze::PATTERN . 'flood_messages', 'size' => 3, 'subtext' => $this->text->get('flood_messages_sub')],
		    ['int', Breeze::PATTERN . 'flood_minutes', 'size' => 3, 'subtext' => $this->text->get('flood_minutes_sub')],
		];

		prepareDBSettingContext($this->configVars);
	}

	public function saveConfigVars(): void
	{
		checkSession();
		saveDBSettings($this->configVars);

		$this->redirect(self::POST_URL);
	}

	public function setSubActionContent(string $actionName): void
	{
		if (empty($actionName))
			return;

		$context = $this->global('context');

		$context['page_title'] = $this->text->get('page_' . $actionName . '_title');
		$context['sub_template'] = $actionName;
		$context[$context['admin_menu_name']]['tab_data'] = [
		    'title' => $context['page_title'],
		    'description' => $this->text->get('page_' . $actionName . '_description'),
		];

		$this->setGlobal('context', $context);
	}
}
