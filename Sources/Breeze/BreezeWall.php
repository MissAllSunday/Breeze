<?php

/**
 * BreezeWall
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeWall
{
	protected $userSettings = array();

	/**
	 * BreezeAjax::__construct()
	 *
	 * Sets the needed properties, loads language and template files
	 * @return
	 */
	public function __construct($tools, $display, $parser, $query, $notifications, $mention, $log)
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
		$this->_notifications = $notifications;
		$this->_display = $display;
		$this->_tools = $tools;
		$this->log = $log;
	}

	/**
	 * BreezeAjax::call()
	 *
	 * Master method, calls the appropriated methods depending on the specified subaction.
	 * @return
	 */
	public function call()
	{
		global $context, $user_info, $settings;

		// Handling the subactions
		$data = Breeze::data('get');

		// Safety first, hardcode the actions
		$this->subActions = array(
			'general' => 'generalWall',
			'single' => 'singleStatus',
			'singleComment' => 'singleComment',
			'log' => 'log',
		);

		// Master setting is off, back off!
		if (!$this->_tools->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		// Guest aren't allowed, sorry.
		is_not_guest($this->_tools->text('error_no_access'));

		// Load the user settings.
		$this->userSettings = $this->_query->getUserSettings($user_info['id']);

		// We need to load the current user's data
		if (empty($context['Breeze']['user_info'][$user_info['id']]))
			$this->_tools->loadUserInfo($user_info['id'], false, 'profile');

		// By default this is set as empty, makes life easier, for me at least...
		$context['Breeze'] = array();

		// We need to log the action we're currently on
		$context['Breeze']['comingFrom'] = 'wall';

		// This isn't nice, however, pass the tools object to the view.
		$context['Breeze']['tools'] = $this->_tools;

		// These file are only used here and on the profile wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
		$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.caret.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/jquery.atwho.js"></script>
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeTabs.js"></script>';

		// Are mentions enabled?
		if ($this->_tools->enable('mention'))
			$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeMention.js"></script>';

		// Temporarily turn this into a normal var
		$call = $this->subActions;

		// Add your own subactions
		call_integration_hook('integrate_breeze_wall_actions', array(&$call));

		// Does the sub-action even exist?
		if (isset($call[$data->get('sa')]))
		{
			// Obscure, evil stuff...
			writeLog(true);

			// This is somehow ugly but its faster.
			$this->$call[$data->get('sa')]();
		}

		// By default lets load the general wall
		else
			$this->$call['general']();

		// We should see other people...
		unset($call);
	}

	/**
	 * BreezeAjax::generalWall()
	 *
	 * Shows the latest activity form your buddies.
	 * @return
	 */
	public function generalWall()
	{
		global $scripturl, $context, $sourcedir, $user_info,  $settings;

		// Guest don't have any business here... back off!
		if ($user_info['is_guest'])
			redirectexit();

		// You actually need to enable this... if you haven't done so, lets tell you about it!
		if (empty($this->userSettings['general_wall']))
			fatal_lang_error('Breeze_cannot_see_general_wall');

		// We cannot live without globals...
		$data = Breeze::data('get');

		// The (soon to be) huge array...
		$status = array(
			'data' => array(),
			'users' => array(),
			'pagination' => '',
			'count' => 0
		);

		// Pagination max index and current page
		$maxIndex = !empty($this->userSettings['pagination_number']) ? $this->userSettings['pagination_number'] : 5;
		$currentPage = ($data->validate('start') == true) ? $data->get('start') : 0;

		// Set all the page stuff
		$context['page_title'] = $this->_tools->text('general_wall');
		$context['sub_template'] = 'general_wall';
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=wall',
			'name' => $context['page_title'],
		);

		// Time to overheat the server!
		if (!empty($this->userSettings['buddiesList']))
		{
			// Get the latest status
			$status = $this->_query->getStatusByUser($this->userSettings['buddiesList'], $maxIndex, $currentPage);
			$context['Breeze']['status'] = $status['data'];

			// Get the latest activity
			$context['Breeze']['log'] = $this->log->getActivity($this->userSettings['buddiesList']);

			// Load users data.
			if (!empty($status['users']))
				$this->_tools->loadUserInfo($status['users']);

			// Applying pagination.
			if (!empty($status['pagination']))
				$context['page_index'] = $status['pagination'];
		}

		// Need to pass some vars to the browser.
		$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[

		breeze.tools.comingFrom = ' . JavaScriptEscape($context['Breeze']['comingFrom']) . ';

		breeze.pagination = {
			maxIndex : '. $maxIndex .',
			totalItems : ' . $status['count'] . ',
			buddies : '. json_encode($this->userSettings['buddiesList']) .',
			userID : '. $user_info['id'] .'
		};
	// ]]></script>';

		// Does the user wants to use the load more button?
		if (!empty($this->userSettings['load_more']))
			$context['insert_after_template'] .= '
	<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/breezeLoadMore.js"></script>';
	}

	/**
	 * BreezeAjax::singleStatus()
	 *
	 * Used for notifications mostly, shows a single status/comment and if appropriated, highlights a specific comment.
	 * @return
	 */
	function singleStatus()
	{
		global $scripturl, $context, $user_info;

		$data = Breeze::data('get');

		// We need the status ID!
		if (!$data->validate('bid'))
			fatal_lang_error('no_access', false);

		// Load it.
		$status = $this->_query->getStatusByID($data->get('bid'));

		if (!empty($this->userSettings['buddies']))
		{
			// Get the latest activity
			$context['Breeze']['activity'] = $this->_query->getActivityLog($this->userSettings['buddies']);

			// Load users data
			if (!empty($status['users']))
				$this->_tools->loadUserInfo($status['users']);
		}

		// Load the users data
		$this->_tools->loadUserInfo($status['users']);

		$context['Breeze']['status'] = $status['data'];

		// Set all the page stuff
		$context['sub_template'] = 'general_wall';
		$context['page_title'] = $this->_tools->text('singleStatus_pageTitle');
		$context['canonical_url'] = $scripturl .'?action=wall;area=single;bid='. $data->get('bid');

		// There cannot be any pagination
		$context['page_index'] = array();

		// Are we showing a comment? if so, highlight it.
		if ($data->get('cid'))
			$context['insert_after_template'] .= '
	<script type="text/javascript"><!-- // --><![CDATA[;
		document.getElementById(\'comment_id_'. $data->get('cid') .'\').className = "windowbg3";
	// ]]></script>';
	}
}
