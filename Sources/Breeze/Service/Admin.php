<?php

declare(strict_types=1);


namespace Breeze\Service;

class Admin extends BaseService implements ServiceInterface
{
	public const IDENTIFIER = 'BreezeAdmin';

	/**
	 * @var Settings
	 */
	protected $settings;

	/**
	 * @var Text
	 */
	protected $text;

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

	public function setSubActionContent(string $actionName): void
	{
		if (empty($actionName))
			return;

		$context = $this->global('context');

		$context['page_title'] = $this->text->get('page_'. $actionName .'_title');
		$context['sub_template'] = $actionName;
		$context[$context['admin_menu_name']]['tab_data'] = [
		    'title' => $context['page_title'],
		    'description' => $this->text->get('page_'. $actionName .'_description'),
		];
	}
}
