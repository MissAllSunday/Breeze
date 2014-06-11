<?php

/**
 * BreezeAdmin
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeAdmin extends Breeze
{
	function __construct()
	{
		parent::__construct();
	}

	function call()
	{
		global $txt, $scripturl, $context, $sourcedir, $settings;
		global $modSettings;

		require_once($sourcedir . '/ManageSettings.php');
		loadLanguage('BreezeAdmin');
		loadtemplate('BreezeAdmin');

		$context['page_title'] = $txt['Breeze_page_panel'];

		$subActions = array(
			'general' => 'main',
			'settings' => 'settings',
			'moodList' => 'moodList',
			'permissions' => 'permissions',
			'donate' => 'donate',
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'general' => array(),
				'settings' => array(),
				'moodList' => 'moodList',
				'permissions' => array(),
				'donate' => array(),
			),
		);

		// Admin bits
		$context['html_headers'] .= '
	<script type="text/javascript">!window.jQuery && document.write(unescape(\'%3Cscript src="http://code.jquery.com/jquery-1.9.1.min.js"%3E%3C/script%3E\'))</script>
	<script src="'. $settings['default_theme_url'] .'/js/jquery.zrssfeed.js" type="text/javascript"></script>
	<script type="text/javascript">
	var breeze_feed_error_message = '. JavaScriptEscape($this['tools']->adminText('feed_error_message')) .';

	$(document).ready(function (){
		$(\'#breezelive\').rssfeed(\''. Breeze::$supportSite .'\',
		{
			limit: 5,
			header: false,
			date: true,
			linktarget: \'_blank\',
			errormsg: breeze_feed_error_message,
			'.(!empty($modSettings['setting_secureCookies']) ? 'ssl: true,' : '').'
	   });
	});
	 </script>';

		// Call the sub-action.
		$this->$subActions[$_REQUEST['sa']]();
	}

	function main()
	{
		global $scripturl, $context;

		// Get the version
		$context['Breeze']['version'] = Breeze::$version;

		// The support site RSS feed
		$context['Breeze']['support'] = Breeze::$supportSite;

		// Set all the page stuff
		$context['page_title'] = $this['tools']->adminText('page_main');
		$context['sub_template'] = 'admin_home';
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this['tools']->adminText('page_welcome'),
		);

		// Get the credits.
		$context['Breeze']['credits'] = $this->credits();
	}

	function settings()
	{
		global $scripturl, $context, $sourcedir;

		// Load stuff
		$data = Breeze::data('request');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Breeze::$name .' - '. $this['tools']->adminText('page_settings');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this['tools']->adminText('page_settings_desc'),
		);

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
			array('title', Breeze::$txtpattern .'page_settings'),
			array('check', Breeze::$txtpattern .'master', 'subtext' => $this['tools']->adminText('master_sub')),
			array('check', Breeze::$txtpattern .'force_enable', 'subtext' => $this['tools']->adminText('force_enable_sub')),
			array('check', Breeze::$txtpattern .'notifications', 'subtext' => $this['tools']->adminText('notifications_sub')),
			array('text', Breeze::$txtpattern .'allowed_actions', 'size' => 60, 'subtext' => $this['tools']->adminText('allowed_actions_sub')),
			array('check', Breeze::$txtpattern .'mention', 'subtext' => $this['tools']->adminText('mention_sub')),
			array('int', Breeze::$txtpattern .'mention_limit', 'size' => 3, 'subtext' => $this['tools']->adminText('mention_limit_sub')),
			array('int', Breeze::$txtpattern .'allowed_max_num_users', 'size' => 3, 'subtext' => $this['tools']->adminText('allowed_max_num_users_sub')),
			array('check', Breeze::$txtpattern .'parseBBC', 'subtext' => $this['tools']->adminText('parseBBC_sub')),
			array('int', Breeze::$txtpattern .'allowed_maxlength_aboutMe', 'size' => 4, 'subtext' => $this['tools']->adminText('allowed_maxlength_aboutMe_sub')),
			array('check', Breeze::$txtpattern .'cover', 'subtext' => $this['tools']->adminText('cover_sub')),
			array('check', Breeze::$txtpattern .'likes', 'subtext' => $this['tools']->adminText('likes_sub')),
			array('check', Breeze::$txtpattern .'mood', 'subtext' => $this['tools']->adminText('mood_sub')),
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

	function permissions()
	{
		global $scripturl, $context, $sourcedir, $txt;

		// This page needs the general strings.
		loadLanguage(Breeze::$name);

		// Load stuff
		$data = Breeze::data('request');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Breeze::$name .' - '. $this['tools']->adminText('page_permissions');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this['tools']->adminText('page_permissions_desc'),
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

	public function moodList()
	{
		global $context;

		$context['sub_template'] = 'manage_mood';

		// Get out main instance.
		$mood = $this['mood'];

		// Need to know a few things.
		$context['mood']['isDirWritable'] = $mood->isDirWritable();

		// Go get some...
		$mood->getAll(false);
	}

	// Pay no attention to the girl behind the curtain.
	function donate()
	{
		global $context;

		// Page stuff
		$context['page_title'] = Breeze::$name .' - '. $this['tools']->adminText('page_donate');
		$context['sub_template'] = 'admin_donate';
		$context['Breeze']['donate'] = $this['tools']->adminText('page_donate_exp');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this['tools']->adminText('page_donate_desc'),
		);
	}
}
