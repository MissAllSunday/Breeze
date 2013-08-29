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
		// Needed to show error strings
		loadLanguage(Breeze::$name);

		// Load all the things we need
		$this->_query = $query;
		$this->_parser = $parser;
		$this->_mention = $mention;
		$this->_settings = $settings;
		$this->_notifications = $notifications;
		$this->_text = $text;
		$this->_display = $display;
		$this->_tools = $tools;
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
	function generalWall()
	{
		global $txt, $scripturl, $context, $memberContext, $sourcedir;
		global $modSettings,  $user_info;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');
		writeLog(true);

		// Set all the page stuff
		$context['page_title'] = $txt['Breeze_general_wall'];
		$context['sub_template'] = 'general_wall';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=wall',
			'name' => $context['page_title'],
		);

		// By default this is se set as empty, makes life easier, for me at least...
		$context['Breeze'] = array();

		// We need to log the action we're currently on
		$context['Breeze']['commingFrom'] = 'wall';

		// Time to overheat the server!
		if (!empty($user_info['buddies']))
		{
			// Get the latest status
			$status = $this->_query->getStatusByUser($user_info['buddies']);
			$context['Breeze']['status'] = $status['data'];

			// Get the latest activity
			$context['Breeze']['activity'] = $this->_query->getActivityLog($user_info['buddies']);

				// Load users data
				if (!empty($status['users']))
					$this->_tools->loadUserInfo($status['users']);
		}

		// No buddies huh? worry not! heres the latest status...

		// Headers
		Breeze::headersHook('profile');
	}

	// Show a single status with all it's comments
	function singleStatus()
	{
		global $user_info, $scripturl, $context, $memberContext;

		// Load all we need
		loadtemplate(Breeze::$name);

		// Prepare all we need
		$globals = new BreezeGlobals('get');
		$tools = BreezeSettings::getInstance();
		$query = BreezeQuery::getInstance();
		$status = array();
		$status['comments'] = array();

		// Headers
		BreezeTools::headers();

		// Set all the page stuff
		$context['sub_template'] = 'singleStatus';
		$context['page_title'] = $tools->getText('singleStatus_pageTitle');
		$context['canonical_url'] = $scripturl . '?action=wall;sa=singlestatus';

		// The visitor's permissions
		$context['Breeze']['visitor']['post_status'] = allowedTo('breeze_postStatus');
		$context['Breeze']['visitor']['post_comment'] = allowedTo('breeze_postComments');
		$context['Breeze']['visitor']['delete_status_comments'] = allowedTo('breeze_deleteStatus');

		// get the status data
		if ($globals->validate('statusID'))
			$status = $query->GetStatusByID($globals->getValue('statusID'));

		// If no ID is set, then load the lastest status
		else
			$status = $query->GetStatusByLast();

		// Get tue user id
		$users_to_load[] = $status['owner_id'];

		// Load the corresponding comments
		$status['comments'] = $query->GetCommentsByStatus($status['id']);

		// Get the id for all the users who commented on the status
		foreach($status['comments'] as $c)
			$users_to_load[] = $c['poster_id'];

		// We have all the IDs, let's prune the array a little
		$new_temp_array = array_unique($users_to_load);

		// Load the data
		loadMemberData($new_temp_array, false, 'profile');
		foreach($new_temp_array as $u)
		{
			loadMemberContext($u);
			$user = $memberContext[$u];
			$context['Breeze']['user_info'][$user['id']] = BreezeUserInfo::Profile($user);
		}

		// Send the data to the template
		$context['Breeze']['singleStatus'] = $status;
	}
}
