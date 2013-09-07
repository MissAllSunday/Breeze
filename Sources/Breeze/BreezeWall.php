<?php

/**
 * BreezeWall
 *
 * The purpose of this file is to show a general wall where user can see status and updates from other users or buddies
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

class BreezeWall
{
	public function __construct($settings, $text, $query, $notifications, $parser, $mention, $display, $tools)
	{
		global $user_info, $memberContext, $context;

		// Needed to show error strings
		loadLanguage(Breeze::$name);

		// Load the templates
		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		// Display all the JavaScript bits
		Breeze::headersHook('profile');

		// Load all the things we need
		$this->_query = $query;
		$this->_parser = $parser;
		$this->_mention = $mention;
		$this->_settings = $settings;
		$this->_notifications = $notifications;
		$this->_text = $text;
		$this->_display = $display;
		$this->_tools = $tools;

		// We need to load the current user's data
		if (empty($context['Breeze']['user_info'][$user_info['id']]))
			$this->_tools->loadUserInfo($user_info['id'], false, 'profile');

		// The member viewing this page
		$this->member = $memberContext[$user_info['id']];

		// To make things easier, set a context var
		$context['member'] = $memberContext[$user_info['id']];
	}

	public function call()
	{
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
		global $txt, $scripturl, $context, $sourcedir;
		global $modSettings;

		// Guest don't have any business here... back off!
		if ($this->member['is_guest'])
			redirectexit();

		$globals = Breeze::sGlobals('get');

		// Obscure, evil stuff...
		writeLog(true);

		// Pagination max index and current page
		$maxIndex = !empty($this->member['options']['Breeze_pagination_number']) ? $this->member['options']['Breeze_pagination_number'] : 5;
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
		$context['Breeze']['commingFrom'] = 'wall';

		// Time to overheat the server!
		if (!empty($this->member['buddies']))
		{
			// Get the latest status
			$status = $this->_query->getStatusByUser($this->member['buddies'], $maxIndex, $currentPage);
			$context['Breeze']['status'] = $status['data'];

			// Get the latest activity
			$context['Breeze']['activity'] = $this->_query->getActivityLog($this->member['buddies']);

			// Load users data
			if (!empty($status['users']))
				$this->_tools->loadUserInfo($status['users']);

			// Applying pagination.
			if (!empty($status['pagination']))
				$context['page_index'] = $status['pagination'];
		}

		// No buddies huh? worry not! here's the latest status...
		// coming soon... LOL

		// Headers
		Breeze::headersHook('profile');
	}

	// Show a single status with all it's comments
	function singleStatus()
	{
		global $scripturl, $context, $memberContext, $modSettings,  $user_info, $breezeController;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$globals = Breeze::sGlobals('get');

		// This is still part of the whole wall stuff
		$context['Breeze']['commingFrom'] == 'wall';

		// Display all the JavaScript bits
		Breeze::headersHook('profile');

		// We are gonna load the status from the user array so we kinda need both the user ID and a status ID
		if (!$globals->validate('u') || !$globals->validate('bid'))
			fatal_lang_error('no_access', false);

		// Load the single status
		$data = $this->_query->getStatusByID($globals->getValue('bid'), $globals->getValue('u'));

		// Load the users data
		$this->_tools->loadUserInfo($data['users']);

		$context['Breeze']['single'] = array($data['data']);

		// Set all the page stuff
		$context['sub_template'] = 'singleStatus';
		$context['page_title'] = $this->_text->getText('singleStatus_pageTitle');
		$context['canonical_url'] = $scripturl .'?action=wall;area=single;u='. $globals->getValue('u') .';bid='. $globals->getValue('bid');
	}
}
