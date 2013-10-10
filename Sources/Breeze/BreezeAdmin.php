<?php

/**
 * BreezeAdmin
 *
 * The purpose of this file is, a procedural set of functions that handles the admin pages for Breeze
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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
	die('No direct access...');

function Breeze_Admin_Index()
{
		global $txt, $scripturl, $context, $sourcedir, $settings;

		require_once($sourcedir . '/ManageSettings.php');
		loadLanguage('Breeze');
		$context['page_title'] = $txt['Breeze_admin_settings_admin_panel'];

		$subActions = array(
			'general' => 'Breeze_Admin_Main',
			'settings' => 'Breeze_Admin_Settings',
			'permissions' => 'Breeze_Admin_Permissions',
			'style' => 'Breeze_Admin_Style',
			'maintenance' => 'Breeze_Admin_Maintenance',
			'donate' => 'Breeze_Admin_Donate',
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'general' => array(),
				'settings' => array(),
				'permissions' => array(),
				'style' => array(),
				'maintenance' => array(),
				'donate' => array(),
			),
		);

		// Call the sub-action
		$subActions[$_REQUEST['sa']]();
}

function Breeze_Admin_Main()
{
	global $scripturl, $context, $breezeController, $settings;

	loadtemplate('BreezeAdmin');

	$text = $breezeController->get('text');
	$headers = $breezeController->get('tools');

	// Admin bits
	$context['html_headers'] .= '
<link href="'. $settings['default_theme_url'] .'/css/breeze.css" rel="stylesheet" type="text/css" />
<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.js" type="text/javascript"></script>
<script type="text/javascript">
var breeze_feed_error_message = '. JavaScriptEscape($text->getText('feed_error_message')) .';

jQuery(document).ready(function (){
	jQuery(\'#breezelive\').rssfeed(\''. Breeze::$supportSite .'\',
	{
		limit: 5,
		header: false,
		date: true,
		linktarget: \'_blank\',
		errormsg: breeze_feed_error_message
   });
});
 </script>';

	// Get the version
	$context['Breeze']['version'] = Breeze::$version;

	// The support site RSS feed
	$context['Breeze']['support'] = Breeze::$supportSite;

	// Set all the page stuff
	$context['page_title'] = $text->getText('admin_settings_main');
	$context['sub_template'] = 'admin_home';
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $text->getText('admin_settings_admin_panel'),
		'description' => $text->getText('admin_welcome'),
	);

	// Get the credits
	$context['Breeze']['credits'] = Breeze::credits();

	// Headers
	Breeze::headersHook('admin');
}

function Breeze_Admin_Settings()
{
	global $scripturl, $context, $sourcedir, $breezeController;

	loadtemplate('Admin');

	// Load stuff
	$text = $breezeController->get('text');
	$globals = Breeze::sGlobals('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $text->getText('admin_settings_main');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $text->getText('admin_settings_settings'),
		'description' => $text->getText('admin_settings_settings_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'admin_settings_settings'),
		array('check', Breeze::$txtpattern .'admin_settings_enable', 'subtext' => $text->getText('admin_settings_enable_sub')),
		array('check', Breeze::$txtpattern .'admin_settings_force_enable', 'subtext' => $text->getText('admin_settings_force_enable_sub')),
		array('check', Breeze::$txtpattern .'admin_enable_limit', 'subtext' => $text->getText('admin_enable_limit_sub')),
		array('select', Breeze::$txtpattern .'admin_limit_timeframe', array('hour' => $text->getText('user_settings_time_hour'), 'day' => $text->getText('user_settings_time_day'), 'week' => $text->getText('user_settings_time_week'), 'month' => $text->getText('user_settings_time_month'), 'year' => $text->getText('user_settings_time_year')), 'subtext' => $text->getText('admin_limit_timeframe_sub')),
		array('text', Breeze::$txtpattern .'allowedActions', 'size' => 56, 'subtext' => $text->getText('allowedActions_sub')),
		array('int', Breeze::$txtpattern .'admin_mention_limit', 'size' => 3, 'subtext' => $text->getText('admin_mention_limit_sub')),
	);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=settings;save';

	// Saving?
	if ($globals->validate('save') == true)
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=breezeadmin;sa=settings');
	}

	prepareDBSettingContext($config_vars);
}

function Breeze_Admin_Permissions()
{
	global $scripturl, $context, $sourcedir, $breezeController, $txt;

	loadtemplate('Admin');

	// Load stuff
	$text = $breezeController->get('text');
	$globals = Breeze::sGlobals('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $text->getText('admin_settings_main');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $text->getText('admin_settings_permissions'),
		'description' => $text->getText('admin_settings_permissions_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'admin_settings_permissions'),
		array('permissions', 'breeze_deleteComments', 0, $txt['permissionname_breeze_deleteComments']),
		array('permissions', 'breeze_postStatus', 0, $txt['permissionname_breeze_postStatus']),
		array('permissions', 'breeze_postComments', 0, $txt['permissionname_breeze_postComments']),
	);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=permissions;save';

	// Saving?
	if ($globals->validate('save') == true)
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=breezeadmin;sa=permissions');
	}

	prepareDBSettingContext($config_vars);
}

function Breeze_Admin_Style()
{
	global $scripturl, $context, $sourcedir, $breezeController, $txt;

	loadtemplate('Admin');

	// Load stuff
	$text = $breezeController->get('text');
	$globals = Breeze::sGlobals('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $text->getText('admin_settings_sub_style');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $text->getText('admin_settings_sub_style'),
		'description' => $text->getText('admin_settings_sub_style_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'admin_settings_sub_style'),
		array('int', Breeze::$txtpattern .'admin_posts_for_mention', 'size' => 3, 'subtext' => $text->getText('admin_posts_for_mention_sub')),
	);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=style;save';

	// Saving?
	if ($globals->validate('save') == true)
	{
		checkSession();
		saveDBSettings($config_vars);
		redirectexit('action=admin;area=breezeadmin;sa=style');
	}

	prepareDBSettingContext($config_vars);
}

// Pay no attention to the girl behind the curtain
function Breeze_Admin_Donate()
{
	global $context, $scripturl, $breezeController;

	loadtemplate('BreezeAdmin');

	// Headers
	$headers = $breezeController->get('tools');
	Breeze::headersHook('admin');

	// Text strings
	$text = $breezeController->get('text');

	// Page stuff
	$context['page_title'] = Breeze::$name .' - '. $text->getText('admin_settings_donate');
	$context['sub_template'] = 'admin_donate';
	$context['Breeze']['donate'] = $text->getText('donate');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
		'description' => $text->getText('admin_settings_donate_desc'),
	);
}
