<?php

/**
 * Breeze_
 *
 * The purpose of this file is, a procedural set of functions that handles the admin pages for Breeze
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

	/* Let's do this the old way... */
	function breeze_admin_main()
	{
		global $scripturl, $context;

		loadtemplate('BreezeAdmin');
		Breeze::loadFile(array('Settings','Subs'));

		$text = BreezeSettings::getInstance();

		/* Get the version */
		$context['Breeze']['version'] = Breeze::$breezeVersion;

		/* Set all the page stuff */
		$context['page_title'] = $text->getText('admin_settings_main');
		$context['sub_template'] = 'breeze_admin_home';

		/* Headers */
		BreezeSubs::headers(true);
	}

	function breeze_admin_settings()
	{
		global $scripturl, $context, $sourcedir;

		Breeze::loadFile(array('Settings', 'Globals'));

		$text = BreezeSettings::getInstance();
		$context['sub_template'] = 'show_settings';
		$globals = new BreezeGlobals('request');

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
				array('check', 'BreezeMod_admin_settings_enable', 'subtext' => $text->getText('admin_settings_enable_sub')),
				array('select', 'BreezeMod_admin_settings_menuposition', array('home' => $text->getText('admin_settings_home'), 'help' => $text->getText('admin_settings_help'), 'profile' => $text->getText('admin_settings_profile')), 'subtext' => $text->getText('admin_settings_menuposition_sub')),
				array('check', 'BreezeMod_admin_enable_limit', 'subtext' => $text->getText('admin_enable_limit_sub')),
				array('select', 'BreezeMod_admin_limit_timeframe', array('hour' => $text->getText('user_settings_time_hour'), 'day' => $text->getText('user_settings_time_day'), 'week' => $text->getText('user_settings_time_week'), 'month' => $text->getText('user_settings_time_month'), 'year' => $text->getText('user_settings_time_year')), 'subtext' => $text->getText('admin_limit_timeframe_sub'))
		);

		$context['post_url'] = $scripturl . '?action=admin;area=breezesettings;save';

		/* Saving? */
		if ($globals->validateGlobal('save') == true)
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezesettings');
		}

		prepareDBSettingContext($config_vars);
	}

	/* Pay no attention to the girl behind the curtain */
	function breeze_admin_donate()
	{
		global $context, $scripturl;

		loadtemplate('BreezeAdmin');
		Breeze::loadFile(array(
			'Subs', 
			'Settings'
		));

		/* Headers */
		BreezeSubs::headers(true);

		/* Text strings */
		$text = BreezeSettings::getInstance();

		/* Page stuff */
		$context['page_title'] = $text->getText('admin_settings_donate');
		$context['sub_template'] = 'breeze_admin_donate';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=admin;area=breezedonate',
			'name' => $text->getText('admin_settings_donate')
		);
	}