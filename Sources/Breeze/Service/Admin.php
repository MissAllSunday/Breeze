<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Controller\Admin\General;

class Admin extends Base
{
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

	public function hookAdminMenu(array &$adminMenu): void
	{
		$this->text->setLanguage('admin');

		$adminMenu['config']['areas']['breezeAdmin'] = [
		    'label' => $this->text->get('page_main'),
		    'file' => '',
		    'function' => General::class . '::do#',
		    'icon' => 'smiley',
		    'subsections' => [
		        'general' => [$this->text->get('page_main')],
		        'settings' => [$this->text->get('page_settings')],
		        'permissions' => [$this->text->get('page_permissions')],
		        'cover' => [$this->text->get('page_cover')],
		        'donate' => [$this->text->get('page_donate')],
		    ],
		];

		if ($this->settings->enable('mood'))
		{
			$admin_menu['config']['areas']['breezeAdmin']['subsections']['moodList'] = [$this->text->get('page_mood')];
			$admin_menu['config']['areas']['breezeAdmin']['subsections']['moodEdit'] = [$this->text->get('page_mood_create')];
		}
	}
}
