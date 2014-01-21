<?php

/**
 * BreezeAdmin
 *
 * The purpose of this file is, a procedural set of functions that handles the admin pages for Breeze
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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
		global $txt, $scripturl, $context, $sourcedir, $settings, $breezeController;

		require_once($sourcedir . '/ManageSettings.php');
		loadLanguage('BreezeAdmin');
		loadtemplate('Admin');

		$context['page_title'] = $txt['Breeze_page_panel'];

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$tools = $breezeController->get('tools');

		$subActions = array(
			'general' => 'Breeze_Admin_Main',
			'settings' => 'Breeze_Admin_Settings',
			'permissions' => 'Breeze_Admin_Permissions',
			'style' => 'Breeze_Admin_Style',
			'donate' => 'Breeze_Admin_Donate',
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'general' => array(),
				'settings' => array(),
				'permissions' => array(),
				'style' => array(),
				'donate' => array(),
			),
		);

		// Admin bits
		$context['html_headers'] .= '
<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.js" type="text/javascript"></script>
<script type="text/javascript">
var breeze_feed_error_message = '. JavaScriptEscape($tools->adminText('feed_error_message')) .';

$(document).ready(function (){
	$(\'#breezelive\').rssfeed(\''. Breeze::$supportSite .'\',
	{
		limit: 5,
		header: false,
		date: true,
		linktarget: \'_blank\',
		errormsg: breeze_feed_error_message
   });
});
 </script>';

		// Call the sub-action
		$subActions[$_REQUEST['sa']]();
}

function Breeze_Admin_Main()
{
	global $scripturl, $context, $breezeController;

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');

	// Get the version
	$context['Breeze']['version'] = Breeze::$version;

	// The support site RSS feed
	$context['Breeze']['support'] = Breeze::$supportSite;

	// Set all the page stuff
	$context['page_title'] = $tools->adminText('page_main');
	$context['sub_template'] = 'admin_home';
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $tools->adminText('page_panel'),
		'description' => $tools->adminText('page_welcome'),
	);

	// Get the credits
	$context['Breeze']['credits'] = Breeze::credits();
}

function Breeze_Admin_Settings()
{
	global $scripturl, $context, $sourcedir, $breezeController;

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');

	// Load stuff
	$data = Breeze::data('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $tools->adminText('page_settings');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $tools->adminText('page_settings'),
		'description' => $tools->adminText('page_settings_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'page_settings'),
		array('check', Breeze::$txtpattern .'enable', 'subtext' => $tools->adminText('enable_sub')),
		array('check', Breeze::$txtpattern .'force_enable', 'subtext' => $tools->adminText('force_enable_sub')),
		array('text', Breeze::$txtpattern .'allowed_actions', 'size' => 60, 'subtext' => $tools->adminText('allowed_actions_sub')),
		array('int', Breeze::$txtpattern .'mention_limit', 'size' => 3, 'subtext' => $tools->adminText('mention_limit_sub')),
	);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=settings;save';

	// Saving?
	if ($data->validate('save') == true)
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

	// This page needs the general strings
	loadLanguage('Breeze');

	if (empty($breezeController))
		$breezeController = new BreezeController();

	$tools = $breezeController->get('tools');

	// Load stuff
	$data = Breeze::data('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $tools->adminText('page_permissions');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $tools->adminText('page_permissions'),
		'description' => $tools->adminText('page_permissions_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'page_permissions'),
	);

	foreach (Breeze::$permissions as $p)
		$config_vars[] = array('permissions', 'breeze_'. $p, 0, $txt['permissionname_breeze_'. $p]);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=permissions;save';

	// Saving?
	if ($data->validate('save') == true)
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

	// Load stuff
	$tools = $breezeController->get('tools');
	$data = Breeze::data('request');
	$context['sub_template'] = 'show_settings';
	$context['page_title'] = $tools->adminText('_sub_style');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => Breeze::$name .' - '. $tools->adminText('_sub_style'),
		'description' => $tools->adminText('_sub_style_desc'),
	);

	require_once($sourcedir . '/ManageServer.php');

	$config_vars = array(
		array('title', Breeze::$txtpattern .'_sub_style'),
		array('int', Breeze::$txtpattern .'admin_posts_for_mention', 'size' => 3, 'subtext' => $tools->adminText('admin_posts_for_mention_sub')),
	);

	$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=style;save';

	// Saving?
	if ($data->validate('save') == true)
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

	// Headers
	$tools = $breezeController->get('tools');

	// Page stuff
	$context['page_title'] = Breeze::$name .' - '. $tools->adminText('_donate');
	$context['sub_template'] = 'admin_donate';
	$context['Breeze']['donate'] = $tools->adminText('donate');
	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $context['page_title'],
		'description' => $tools->adminText('_donate_desc'),
	);
}
