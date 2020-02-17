<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Controller\Admin\Admin;

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

	public function hookAdminMenu(array &$adminMenu): array
	{


		return $adminMenu;
	}
}
