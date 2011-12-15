<?php

/**
 * Breeze_
 * 
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	/* We can't call a static method from a string... let's do this the old way instead... */
	function Breeze_Admin_Main()
	{
		global $txt, $scripturl, $context;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		Breeze::LoadMethod(array('Settings','Subs'));

		/* Set all the page stuff */
		$context['page_title'] = $txt['breeze_admin_settings_main'];
		$context['sub_template'] = 'admin_home';

		/* Headers */
		Breeze_Subs::Headers(true);

		/* Tell them if their server is up to the challange*/
		$context['breeze']['versions'] = Breeze_Subs::Check_Versions();
		
		/* Load the rss url from the database */
		$rss = Breeze_Settings::getInstance();
		$context['breeze']['rss_url'] = $rss->get('breeze_admin_settings_rss_url');

	}

	function Breeze_Admin_Settings()
	{
		global $scripturl, $txt, $context, $sourcedir;

		loadLanguage('Breeze');
		$context['sub_template'] = 'show_settings';

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
				array('check', 'breeze_admin_settings_enable', 'subtext' => $txt['breeze_admin_settings_enable_sub']),
				array('select', 'breeze_admin_settings_menuposition', array('home' => $txt['home'], 'help' => $txt['help'], 'profile' => $txt['profile']), 'subtext' => $txt['breeze_admin_settings_menuposition_sub']),
				array('check', 'breeze_admin_settings_enablegeneralwall', 'subtext' => $txt['breeze_admin_settings_enablegeneralwall_sub']),
		);

		$context['post_url'] = $scripturl . '?action=admin;area=breezesettings;save';

		// Saving?
		if (isset($_GET['save']))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezesettings');
		}

		prepareDBSettingContext($config_vars);
	}

	/* Pay no attention to the girl behind the curtain */
	function Breeze_Admin_Donate()
	{
		global $txt, $context, $scripturl;

		loadLanguage('Breeze');
		loadtemplate('Breeze');
		Breeze::LoadMethod('Subs');

		/* Headers */
		Breeze_Subs::Headers(true);

		/* Page stuff */
		$context['page_title'] = $txt['breeze_admin_settings_donate'];
		$context['sub_template'] = 'admin_donate';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=breezedonate',
			'name' => $txt['breeze_admin_settings_donate']
		);
	}