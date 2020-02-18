<?php

declare(strict_types=1);


namespace Breeze\Service;

class Admin extends Base implements ServiceInterface
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

	public function initSettingsPage($subActions)
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

	public function setGeneralPageContent(): void
	{
		$context = $this->global('context');
	}
}
