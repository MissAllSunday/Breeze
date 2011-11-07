<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	/* We can't call a static method from a string... let's do this the old way instead... */
	function Breeze_Admin_Main()
	{
		global $txt, $scripturl, $context;

		loadLanguage('Breeze');
		loadtemplate('Breeze');

		/* Set all the page stuff */
		$context['page_title'] = $txt['breeze_admin_settings_main'];
		$context['sub_template'] = 'admin_home';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=breezeindex',
			'name' => $txt['breeze_admin_settings_main']
		);

		/* Headers */
		Breeze_Subs::Headers(true);

		/* Tell them if their server is up to the challange*/
		$context['breeze']['versions'] = Breeze_Subs::Check_Versions();

		/* Load all admin logs */
		$context['breeze']['logs']['admin'] = Breeze_Logs::Get('admin', 5);

		/* Get the relative date */
		$context['breeze']['logs']['admin']['date'] = Breeze_Subs::Time_Elapsed($context['breeze']['logs']['admin']['date']);
	}

	function Breeze_Admin_Settings()
	{
		global $txt, $context;

		loadLanguage('BreezeAdmin');
		loadtemplate('BreezeAdmin');

		/* Page stuff */
		$context['page_title'] = $txt['breeze_admin_settings_settings'];
		$context['sub_template'] = 'admin_settings';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=breezesettings',
			'name' => $txt['breeze_admin_settings_settings']
		);

		/* Load the settings */
		$context['breeze']['settings'] = $this->breeze['global_settings'];

		/* Saving... */
		if (Breeze_Globals::Is_Set('save'))
		{
			checkSession();
			redirectexit('action=admin;area=breezesettings');

			$data = array(
				'enable' => 'int',
				'menu_position' => 'int',
				'enable_general_wall' => 'int',
			);
			$values = array(
				Breeze_Globals::Post('enable'),
				Breeze_Globals::Post('menu_position'),
				Breeze_Globals::Post('enable_general_wall')
			);
			$indexes = array(
				'id'
			);
			$insert = new Breeze_DB('breeze_logs');
			$insert->InsertData($data, $values, $indexes);
		}
	}

	/* Pay no attention to the girl behind the curtain */
	function Breeze_Admin_Donate()
	{
		global $txt, $context;

		loadLanguage('BreezeAdmin');
		loadtemplate('BreezeAdmin');

		/* Page stuff */
		$context['page_title'] = $txt['breeze_admin_settings_donate'];
		$context['sub_template'] = 'admin_donate';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=breezedonate',
			'name' => $txt['breeze_admin_settings_donate']
		);
	}

?>
