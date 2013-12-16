<?php

/**
 * BreezeWall
 *
 * The purpose of this file is to show a general wall where user can see status and updates from other users or buddies
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

class BreezeWall
{
	public function __construct($settings, $text, $query, $notifications, $parser, $mention, $display, $tools, $log)
	{
		// Needed to show error strings
		loadLanguage(Breeze::$name);

		// Load the templates
		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		// Load all the things we need
		$this->_query = $query;
		$this->_parser = $parser;
		$this->_mention = $mention;
		$this->_settings = $settings;
		$this->_notifications = $notifications;
		$this->_text = $text;
		$this->_display = $display;
		$this->_tools = $tools;
		$this->log = $log;
	}

	public function call()
	{
		global $context, $user_info;

		// Handling the subactions
		$sglobals = Breeze::sGlobals('get');

		// Safety first, hardcode the actions
		$this->subActions = array(
			'general' => 'generalWall',
			'single' => 'singleStatus',
			'singleComment' => 'singleComment',
			'log' => 'log',
		);

		// Master setting is off, back off!
		if (!$this->_settings->enable('admin_settings_enable'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		// Guest aren't allowed, sorry.
		is_not_guest($this->_text->getText('error_no_access'));

		// Load the user settings.
		$this->userSettings = $this->_query->getUserSettings($user_info['id']);

		// We need to load the current user's data
		if (empty($context['Breeze']['user_info'][$user_info['id']]))
			$this->_tools->loadUserInfo($user_info['id'], false, 'profile');

		// Temporarily turn this into a normal var
		$call = $this->subActions;

		// Does the sub-action even exist?
		if (isset($call[$sglobals->getValue('sa')]))
		{
			// This is somehow ugly but its faster.
			$this->$call[$sglobals->getValue('sa')]();
		}

		// By default lets load the general wall
		else
			$this->$call['general']();
	}

	// Get the latest entries of your buddies
	public function generalWall()
	{
		global $txt, $scripturl, $context, $sourcedir, $user_info;
		global $modSettings, $settings;

		// Guest don't have any business here... back off!
		if ($user_info['is_guest'])
			redirectexit();

		// You actually need to enable this... if you haven't done so, lets tell you about it!
		if (empty($this->userSettings['general_wall']))
			fatal_lang_error('Breeze_cannot_see_general_wall');

		// We cannot live without globals...
		$globals = Breeze::sGlobals('get');

		// The (soon to be) huge array...
		$status = array();

		// Obscure, evil stuff...
		writeLog(true);

		// Pagination max index and current page
		$maxIndex = !empty($this->userSettings['pagination_number']) ? $this->userSettings['pagination_number'] : 5;
		$currentPage = $globals->validate('start') == true ? $globals->getValue('start') : 0;

		// Set all the page stuff
		$context['page_title'] = $txt['Breeze_general_wall'];
		$context['sub_template'] = 'general_wall';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=wall',
			'name' => $context['page_title'],
		);

		// By default this is set as empty, makes life easier, for me at least...
		$context['Breeze'] = array();

		// We need to log the action we're currently on
		$context['Breeze']['comingFrom'] = 'wall';

		// Time to overheat the server!
		if (!empty($this->userSettings['buddies']))
		{
			// Get the latest status
			$status = $this->_query->getStatusByUser($this->userSettings['buddies'], $maxIndex, $currentPage);
			$context['Breeze']['status'] = $status['data'];

			// Get the latest activity
			$context['Breeze']['log'] = $this->log->getActivity($this->userSettings['buddies']);

			// Load users data.
			if (!empty($status['users']))
				$this->_tools->loadUserInfo($status['users']);

			// Applying pagination.
			if (!empty($status['pagination']))
				$context['page_index'] = $status['pagination'];

	// These file are only used here and on the profile wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
	$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.caret.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.atwho.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeTabs.js"></script>';

	// Does the user wants to use the load more button?
	if (!empty($context['Breeze']['settings']['visitor']['load_more']))
		$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeScroll.js"></script>';

			// Need to pass some vars to the browser :(
			$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[

		breeze.tools.comingFrom = ' . JavaScriptEscape($context['Breeze']['comingFrom']) . ';

		breeze.pagination = {
			maxIndex : '. $maxIndex .',
			totalItems : ' . $status['count'] . ',
			buddies : '. json_encode($this->userSettings['buddies']) .',
		};
	// ]]></script>';
		}

		// No buddies huh? worry not! here's the latest status...
		// coming soon... LOL

	}

	// Show a single status with all it's comments
	function singleStatus()
	{
		global $scripturl, $context, $modSettings,  $user_info;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$globals = Breeze::sGlobals('get');

		// This is still part of the whole wall stuff.
		$context['Breeze']['comingFrom'] = 'wall';

		// user settings.
		$this->userSettings = $this->_query->getUserSettings($user_info['id']);

		// We need the status ID!
		if (!$globals->validate('bid'))
			fatal_lang_error('no_access', false);

		// Load it.
		$data = $this->_query->getStatusByID($globals->getValue('bid'));

		if (!empty($this->userSettings['buddies']))
		{
			// Get the latest activity
			$context['Breeze']['activity'] = $this->_query->getActivityLog($this->userSettings['buddies']);

			// Load users data
			if (!empty($status['users']))
				$this->_tools->loadUserInfo($status['users']);
		}

		// Load the users data
		$this->_tools->loadUserInfo($data['users']);

		$context['Breeze']['status'] = $data['data'];

		// Set all the page stuff
		$context['sub_template'] = 'general_wall';
		$context['page_title'] = $this->_text->getText('singleStatus_pageTitle');
		$context['canonical_url'] = $scripturl .'?action=wall;area=single;bid='. $globals->getValue('bid');

		// There cannot be any pagination
		$context['page_index'] = array();

		// Are we showing a comment? if so, hightlight it.
		if ($globals->getValue('cid'))
			$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[;
		document.getElementById(\'comment_id_'. $globals->getValue('cid') .'\').className = "windowbg3";
	// ]]></script>';
	}
}
