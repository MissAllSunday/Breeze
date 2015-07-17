<?php

/**
 * BreezeWall
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeWall
{
	protected $userSettings = array();
	protected $_app;

	/**
	 * BreezeAjax::__construct()
	 *
	 * Sets the needed properties, loads language and template files
	 * @return
	 */
	public function __construct($app)
	{
		// Needed to show error strings
		loadLanguage(Breeze::$name);

		// Load the templates
		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		// Load all the things we need
		$this->_app = $app;
	}

	/**
	 * BreezeAjax::call()
	 *
	 * Master method, calls the appropriated methods depending on the specified subaction.
	 * @return
	 */
	public function call()
	{
		global $context, $user_info, $modSettings;

		// Handling the subactions
		$data = Breeze::data('get');

		// Safety first, hardcode the actions
		$this->subActions = array(
			'general' => 'generalWall',
			'single' => 'singleStatus',
			'singleComment' => 'singleComment',
			'log' => 'log',
			'userdiv' => 'userDiv',
		);

		// Master setting is off, back off!
		if (!$this->_app['tools']->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		// Guest aren't allowed, sorry.
		is_not_guest($this->_app['tools']->text('error_no_access'));

		// Load the user settings.
		$this->userSettings = $this->_app['query']->getUserSettings($user_info['id']);

		// We need to load the current user's data
		if (empty($context['Breeze']['user_info'][$user_info['id']]))
			$this->_app['tools']->loadUserInfo($user_info['id'], false, 'profile');

		// By default this is set as empty, makes life easier, for me at least...
		$context['Breeze'] = array();

		// We need to log the action we're currently on
		$context['Breeze']['comingFrom'] = 'wall';

		// This isn't nice, however, pass the tools object to the view.
		$context['Breeze']['tools'] = $this->_app['tools'];

		addInlineJavascript('
	breeze.tools.comingFrom = "'. $context['Breeze']['comingFrom'] .'";');

		// These file are only used here and on the profile wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
		loadJavascriptFile('breeze/breezePost.js', array('default_theme' => true, 'defer' => true,));
		loadJavascriptFile('breezeTabs.js', array('local' => true, 'default_theme' => true));

		if (!empty($modSettings['enable_mentions']) && allowedTo('mention'))
		{
			loadJavascriptFile('jquery.atwho.js', array('default_theme' => true, 'defer' => true), 'smf_atwho');
			loadJavascriptFile('mentions.js', array('default_theme' => true, 'defer' => true), 'smf_mention');
		}

		// Load the icon's css.
		loadCSSFile('//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array('external' => true));

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
		global $context, $user_info;

		// Guest don't have any business here... back off!
		if ($user_info['is_guest'])
			redirectexit();

		// You actually need to enable this... if you haven't done so, lets tell you about it!
		if (empty($this->userSettings['general_wall']))
			fatal_lang_error('Breeze_cannot_see_general_wall', false);

		// Get some stuffz
		$data = Breeze::data('get');

		// These file are only used here and on the general wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
		loadJavascriptFile('breeze/breezePost.js', array('local' => true, 'default_theme' => true, 'defer' => true,));
		loadJavascriptFile('breeze/breezeTabs.js', array('local' => true, 'default_theme' => true, 'defer' => true,));

		// The (soon to be) huge array...
		$status = array(
			'data' => array(),
			'users' => array(),
			'pagination' => '',
			'count' => 0
		);

		// Pass your settings to the template.
		$context['Breeze']['settings']['visitor'] = $this->userSettings;
		$context['Breeze']['log'] = array();

		// Pagination max index and current page.
		$maxIndex = !empty($this->userSettings['pagination_number']) ? $this->userSettings['pagination_number'] : 5;
		$currentPage = (int) (($data->validate('start') == true) ? $data->get('start') : 0);

		// Set all the page stuff.
		$context['page_title'] = $this->_app['tools']->text('general_wall');
		$context['sub_template'] = 'general_wall';
		$context['linktree'][] = array(
			'url' => $this->_app['tools']->scriptUrl . '?action=wall',
			'name' => $context['page_title'],
		);

		// Time to overheat the server!
		if (!empty($this->userSettings['buddiesList']))
		{
			// Get the latest status
			$status = $this->_app['query']->getStatusByUser($this->userSettings['buddiesList'], $maxIndex, $currentPage);
			$context['Breeze']['status'] = $status['data'];

			// Get the latest activity
			$alerts = $this->_app['log']->get($this->userSettings['buddiesList'], 10, 0);
			$context['Breeze']['log'] = $alerts['data'];

			// Load users data.
			if (!empty($status['users']))
				$this->_app['tools']->loadUserInfo($status['users']);

			// Applying pagination.
			if (!empty($status['pagination']))
				$context['page_index'] = $status['pagination'];
		}

		// The tabs script.
		addInlineJavascript('
	var bTabs = new breezeTabs(\'ul.breezeTabs\', \'wall\');', true);

		// Need to pass some vars to the browser :(
		addInlineJavascript('
	breeze.pagination = {
		maxIndex : '. $maxIndex .',
		totalItems : ' . $status['count'] . ',
		userID : '. $user_info['id'] .'
	};', true);

		// Does the user wants to use the load more button?
		if (!empty($context['Breeze']['settings']['visitor']['load_more']))
		{
			addInlineJavascript('
	breeze.text.load_more = '. JavaScriptEscape($this->_app['tools']->text('load_more')) .';
	breeze.text.page_loading_end = '. JavaScriptEscape($this->_app['tools']->text('page_loading_end')) .';', true);
			loadJavascriptFile('breezeLoadMore.js', array('local' => true, 'default_theme' => true));
		}
	}

	/**
	 * BreezeAjax::singleStatus()
	 *
	 * Used for notifications mostly, shows a single status/comment and if appropriated, highlights a specific comment.
	 * @return
	 */
	function singleStatus()
	{
		global $context, $user_info;

		$data = Breeze::data('get');

		// Disable the activity tab
		$context['Breeze']['disableTabs'] = true;

		// We need the status ID!
		if (!$data->validate('bid'))
			fatal_lang_error('no_access', false);

		// Load it.
		$status = $this->_app['query']->getStatusByID($data->get('bid'));

		// Load the users data.
		$this->_app['tools']->loadUserInfo($status['users']);

		$context['Breeze']['status'] = $status['data'];

		// Set all the page stuff
		$context['sub_template'] = 'general_wall';
		$context['page_title'] = $this->_app['tools']->text('singleStatus_pageTitle');
		$context['canonical_url'] = $this->_app['tools']->scriptUrl .'?action=wall;area=single;bid='. $data->get('bid');

		// There cannot be any pagination.
		$context['page_index'] = array();

		// Are we showing a comment? if so, highlight it.
		if ($data->get('cid'))
			addInlineJavascript('
		document.getElementById(\'comment_id_'. $data->get('cid') .'\').className = "windowbg3";', true);
	}

	/**
	 * BreezeAjax::userDiv()
	 *
	 * Shows user information.
	 * @return
	 */
	function userDiv()
	{
		global $context, $memberContext, $db_show_debug, $user_info;

		// Don't show nasty things.
		$db_show_debug = false;

		// Set an empty array, just for fun...
		$context['BreezeUser']  = array();

		// Need to load a bunch of language files, mostly just for one single txt string
		loadLanguage('Help');
		loadLanguage('Profile');

		// We only want to output our little layer here.
		$context['template_layers'] = array();
		$context['sub_template'] = 'userDiv';
		$context['page_title'] = '';

		$data = Breeze::data('request');
		$userID = $data->get('u');

		// No ID? shame on you!
		if (empty($userID))
			return false;

		// By this point the user info should be loaded already, still, better be safe...
		if(!isset($memberContext[$userID]))
			$this->_app['tools']->loadUserInfo($userID);

		// Pass the data to the template.
		$context['BreezeUser'] = $memberContext[$userID];

		$context['user']['is_owner'] = ($context['BreezeUser'] == $user_info['id']);
	}
}
