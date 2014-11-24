<?php

/**
 * BreezeUser
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeUser extends Breeze
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * BreezeUser::wall()
	 *
	 * Main function, shows the wall, activity, buddies, visitors and any other possible info.
	 * @return
	 */
	function wall()
	{
		global $txt, $context, $memberContext;
		global $modSettings,  $user_info, $memID;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');
		loadLanguage(Breeze::$name);

		// We kinda need all this stuff, don't ask why, just nod your head...
		$query = $this['query'];
		$tools = $this['tools'];
		$data = Breeze::data('get');
		$log = $this['log'];
		$usersToLoad = array();

		// Default values.
		$status = array();
		$context['Breeze'] = array(
			'cover' => '',
			'views' => false,
			'log' => false,
			'buddiesLog' => false,
			'comingFrom' => 'profile',
			'settings' => array(
				'owner' => array(),
				'visitor' => array(),
			),
			'compact' => array(
				'visitors' => false,
				'buddy' => false,
			),
		);

		// Does the admin has set a max limit?
		if ($tools->enable('allowed_max_num_users'))
			$context['Breeze']['max_users'] = (int) $tools->setting('allowed_max_num_users');

		// Is owner?
		$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];

		// Get profile owner settings.
		$context['Breeze']['settings']['owner'] = $query->getUserSettings($context['member']['id']);

		// Check if this user allowed to be here
		$this->checkPermissions();

		// We need to make sure we have all your info...
		if (empty($context['Breeze']['user_info'][$user_info['id']]))
			$tools->loadUserInfo($user_info['id']);

		// Does the current user (AKA visitor) is also the owner?
		if ($context['member']['is_owner'])
			$context['Breeze']['settings']['visitor'] = $context['Breeze']['settings']['owner'];

		// Nope? :(
		else
			$context['Breeze']['settings']['visitor'] = $query->getUserSettings($user_info['id']);

		// Set all the page stuff
		$context['template_layers'] = array();
		$context['sub_template'] = 'user_wall';
		$context += array(
			'page_title' => sprintf($txt['profile_of_username'], $context['member']['name']),
			'can_send_pm' => allowedTo('pm_send'),
			'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
			'can_issue_warning' => allowedTo('issue_warning') && $modSettings['warning_settings'][0] == 1,
			'can_view_warning' => (allowedTo('moderate_forum') || allowedTo('issue_warning') || allowedTo('view_warning_any') || ($context['user']['is_owner'] && allowedTo('view_warning_own')) && $modSettings['warning_settings'][0] === 1)
		);
		$context['canonical_url'] = $this['tools']->scriptUrl . '?action=profile;u=' . $context['member']['id'];
		$context['member']['status'] = array();
		$context['Breeze']['tools'] = $tools;

		// Can this user have a cover?
		if ($tools->enable('cover') && allowedTo('breeze_canCover') && !empty($context['Breeze']['settings']['owner']['cover']) && file_exists($this['tools']->boardDir . Breeze::$coversFolder . $context['member']['id'] .'/'. $context['Breeze']['settings']['owner']['cover']['basename']))
			$context['Breeze']['cover'] = $this['tools']->boardUrl . Breeze::$coversFolder . $context['member']['id'] .'/'. $context['Breeze']['settings']['owner']['cover']['basename'];

		// Set up some vars for pagination.
		$maxIndex = !empty($context['Breeze']['settings']['visitor']['pagination_number']) ? $context['Breeze']['settings']['visitor']['pagination_number'] : 5;
		$currentPage = $data->validate('start') == true ? $data->get('start') : 0;

		// Load all the status.
		$data = $query->getStatusByProfile($context['member']['id'], $maxIndex, $currentPage);

		// Load users data.
		if (!empty($data['users']))
			$usersToLoad = $usersToLoad + $data['users'];

		// Pass the status info.
		if (!empty($data['data']))
			$context['member']['status'] = $data['data'];

		// Applying pagination.
		if (!empty($data['pagination']))
			$context['page_index'] = $data['pagination'];

		// Page name depends on pagination.
		$context['page_title'] = sprintf($tools->text('profile_of_username'), $context['member']['name']);

		// Get the profile views.
		if (!$user_info['is_guest'] && !empty($context['Breeze']['settings']['owner']['visitors']))
		{
			$context['Breeze']['views'] = $this->trackViews();

			// If there is a limit then lets count the total so we can know if we are gonna use the compact style.
			if (!empty($context['Breeze']['max_users']) && count($context['Breeze']['views']) >= $context['Breeze']['max_users'])
				$context['Breeze']['compact']['visitors'] = true;

			// Nope? then use the user defined value
			else
			{
				// How many visitors are we gonna show?
				if (!empty($context['Breeze']['settings']['owner']['how_many_visitors']) && is_array($context['Breeze']['views']) && count($context['Breeze']['views']) >= $context['Breeze']['settings']['owner']['how_many_visitors'])
					$context['Breeze']['views'] = array_slice($context['Breeze']['views'], 0, $context['Breeze']['settings']['owner']['how_many_visitors']);
			}

			// Load their data
			if (!empty($context['Breeze']['views']))
				$usersToLoad = array_merge($usersToLoad, array_keys($context['Breeze']['views']));
		}

		// Show buddies only if there is something to show.
		if (!empty($context['Breeze']['settings']['owner']['buddies']) && !empty($context['member']['buddies']))
		{
			// Hold your horses!
			if (!empty($context['Breeze']['max_users']) && count($context['member']['buddies']) >= $context['Breeze']['max_users'])
				$context['Breeze']['compact']['buddy'] = true;

			$usersToLoad = array_merge($usersToLoad, $context['member']['buddies']);
		}

		// @todo this is a temp thing...
		if (!empty($context['Breeze']['settings']['owner']['activityLog']))
		{
			$maxIndex = 5;
			$start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
			$alerts =  $this['log']->get($context['member']['id'], $maxIndex, $start);
			$context['Breeze']['log'] = $alerts['data'];
		}

		// These file are only used here and on the general wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
		loadJavascriptFile('breezeTabs.js', array('local' => true, 'default_theme' => true));

		// Are mentions enabled?
		// if ($tools->enable('mention'))
			// loadJavascriptFile('breezeMention.js', array('local' => true, 'default_theme' => true));

		// Does the user wants to use the load more button?
		if (!empty($context['Breeze']['settings']['visitor']['load_more']))
			loadJavascriptFile('breezeLoadMore.js', array('local' => true, 'default_theme' => true));

		// Need to pass some vars to the browser :(
		addInlineJavascript('
	breeze.pagination = {
		maxIndex : '. $maxIndex .',
		totalItems : ' . $data['count'] . ',
		userID : '. $context['member']['id'] .'
	};');

		addInlineJavascript('
	breeze.tools.comingFrom = "'. $context['Breeze']['comingFrom'] .'";');

		// Pass the profile owner settings to the client all minus the about me stuff.
		$toClient = $context['Breeze']['settings']['owner'];
		unset($toClient['aboutMe']);
		$bOwnerSettings = '';
		foreach (Breeze::$allSettings as $k)
			$bOwnerSettings .= '
	breeze.ownerSettings.'. $k .' = '. (isset($toClient[$k]) ? (is_array($toClient[$k]) ? json_encode($toClient[$k]) : JavaScriptEscape($toClient[$k])) : 'false') .';';

		addInlineJavascript($bOwnerSettings);
		unset($toClient);

		// Lastly, load all the users data from this bunch of user IDs
		if (!empty($usersToLoad))
			$tools->loadUserInfo(array_unique($usersToLoad));
	}

	/**
	 * BreezeUser::alerts()
	 *
	 * Creates alert settings and configuration pages.
	 * @return
	 */
	function alerts()
	{
		global $context;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$data = Breeze::data('get');
		$tools = $this['tools'];

		// Create the tabs for the template.
		$context[$context['profile_menu_name']]['tab_data'] = array(
			'title' => $tools->text('user_settings_name_alerts'),
			'description' => $tools->text('user_settings_name_alerts_desc'),
			'icon' => 'profile_hd.png',
			'tabs' => array(
				'settings' => array(
				),
				'edit' => array(
				),
			),
		);

		$context['page_title'] = !empty($data->get('sa')) && $tools->text('user_settings_name_alerts_'. $data->get('sa')) ? $tools->text('user_settings_name_alerts_'. $data->get('sa')) : $tools->text('user_settings_name_alerts_settings');
		$context['page_desc'] = !empty($data->get('sa')) && $tools->text('user_settings_name_alerts_'. $data->get('sa') .'_desc') ? $tools->text('user_settings_name_alerts_'. $data->get('sa') .'_desc') : $tools->text('user_settings_name_alerts_settings_desc');

		// Call the right action.
		$call = 'alert' .(!empty($data->get('sa')) ? ucfirst($data->get('sa')) : 'Settings');

		// Call the right function.
			$this->$call();
	}

	public function alertSettings()
	{
		global $context;

		$context['Breeze_redirect'] = 'alerts';
		$context['sub_template'] = 'member_options';

		// Get the user settings.
		$userSettings = $this['query']->getUserSettings($context['member']['id']);

		// Create the form.
		$form = $this['form'];

		// Group all these values into an array. Makes it easier to save the changes.
		$form->setOptions(array(
			'name' => 'breezeSettings',
			'url' => $this['tools']->scriptUrl .'?action=breezeajax;sa=usersettings;rf=profile;u='. $context['member']['id'] .';area='. (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings'),
			'character_set' => $context['character_set'],
			'title' => $context['page_title'],
			'desc' => $context['page_desc'],
		));

		// Get all inner alerts.
		foreach ($this['log']->alerts as $a)
			$form->addCheckBox(
				'alert_'. $a,
				!empty($userSettings['alert_'. $a]) ? true : false
			);

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		$form->addButton('submit');

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();
	}

	public function alertEdit()
	{
		global $context, $scripturl, $txt;

		$context['sub_template'] = 'alert_edit';
		$data = Breeze::data();
		$maxIndex = 10;
		$start = (int) isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
		$alerts =  $this['log']->get($context['member']['id'], $maxIndex, $start);
		$count = $alerts['count'];

		// Get the alerts.
		$context['alerts'] = $alerts['data'];
		$toMark = false;

		// Create the pagination.
		$context['pagination'] = constructPageIndex($scripturl . '?action=profile;area=alerts;sa=edit;u=' . $context['member']['id'], $start, $count, $maxIndex, false);

		addInlineJavascript('
	$(function(){
		$(\'#select_all\').on(\'change\', function() {
			var checkboxes = $(\'#mark_all\').find(\':checkbox\');
			if($(this).prop(\'checked\')) {
				checkboxes.prop(\'checked\', true);
			}
			else {
				checkboxes.prop(\'checked\', false);
			}
		});
	});', true);

		// Set a nice message.
		if (!empty($_SESSION['update_message']))
		{
			$context['update_message'] = $txt['profile_updated_own'];
			unset($_SESSION['update_message']);
		}

		// Saving multiple changes?
		if ($data->get('save') && $data->get('mark'))
			$toMark = $data->get('mark');

		// A single change.
		if ($data->get('delete') && $data->get('aid'))
			$toMark = $data->get('aid');

		// Save the changes.
		if (!empty($toMark))
		{
			checkSession('request');

			// Call it!
			$this['query']->deleteLog($toMark);

			// Set a nice update message.
			$_SESSION['update_message'] = true;

			// Redirect.
			redirectexit('action=profile;area=alerts;sa=edit;u=' . $context['member']['id']);
		}
	}

	/**
	 * BreezeUser::settings()
	 *
	 * Creates a form for each user to configure their wall settings.
	 * @return
	 */
	function settings()
	{
		global $context, $txt, $modSettings, $user_info;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$data = Breeze::data('get');
		$tools = $this['tools'];

		// Is there an admin limit?
		$maxUsers = $tools->setting('allowed_max_num_users') ? $tools->setting('allowed_max_num_users') : 0;

		// Set the page title
		$context['page_title'] = $tools->text('user_settings_name');
		$context['sub_template'] = 'member_options';
		$context['page_desc'] = $tools->text('user_settings_name_desc');
		$context['Breeze_redirect'] = '';

		// Get the user settings.
		$userSettings = $this['query']->getUserSettings($context['member']['id']);

		// Create the form.
		$form = $this['form'];

		// Group all these values into an array. Makes it easier to save the changes.
		$form->setOptions(array(
			'name' => 'breezeSettings',
			'url' => $this['tools']->scriptUrl .'?action=breezeajax;sa=usersettings;rf=profile;u='. $context['member']['id'] .';area='. (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings'),
			'character_set' => $context['character_set'],
			'title' => $context['page_title'],
			'desc' => $context['page_desc'],
		));

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		// Per user master setting.
		$form->addCheckBox(
			'wall',
			!empty($userSettings['wall']) ? true : false
		);

		// General wall setting.
		$form->addCheckBox(
			'general_wall',
			!empty($userSettings['general_wall']) ? true : false
		);

		// Pagination.
		$form->addText(
			'pagination_number',
			!empty($userSettings['pagination_number']) ? $userSettings['pagination_number'] : 0,
			3,3
		);

		// Add the load more button.
		$form->addCheckBox(
			'load_more',
			!empty($userSettings['load_more']) ? true : false
		);

		// How many options to be displayed when mentioning.
		$form->addText(
			'how_many_mentions',
			!empty($userSettings['how_many_mentions']) ? $userSettings['how_many_mentions'] : 0,
			3,3
		);

		// Activity Log.
		$form->addCheckBox(
			'activityLog',
			!empty($userSettings['activityLog']) ? true : false
		);

		// Only show this is the admin has enable the buddy feature.
		if (!empty($modSettings['enable_buddylist']))
		{
			// Allow ignored users.
			$form->addCheckBox(
				'kick_ignored',
				!empty($userSettings['kick_ignored']) ? true : false
			);

			// Buddies block.
			$form->addCheckBox(
				'buddies',
				!empty($userSettings['buddies']) ? true : false
			);

			// How many buddies are we gonna show?
			$form->addText(
				'how_many_buddies',
				!empty($userSettings['how_many_buddies']) ? ($maxUsers && $userSettings['how_many_buddies'] >= $maxUsers ? $maxUsers : $userSettings['how_many_buddies']) : 0,
				3,3
			);
		}

		// Profile visitors.
		$form->addCheckBox(
			'visitors',
			!empty($userSettings['visitors']) ? true : false
		);

		// How many visitors are we gonna show?
		$form->addText(
			'how_many_visitors',
			!empty($userSettings['how_many_visitors']) ? ($maxUsers && $userSettings['how_many_visitors'] >= $maxUsers ? $maxUsers : $userSettings['how_many_visitors']) : 0,
			3,3
		);

		// Clean visitors log
		$form->addHTML(
			'clean_visitors',
			'<a href="'. $this['tools']->scriptUrl .'?action=breezeajax;sa=cleanlog;log=visitors;u='. $context['member']['id'] .';rf=profile;'. $context['session_var'] .'='. $context['session_id'] .'" class="clean_log">%s</a>'
		);

		// About me textarea.
		$form->addTextArea(
			'aboutMe',
			!empty($userSettings['aboutMe']) ? $userSettings['aboutMe'] : '',
			array('rows' => 10, 'cols' => 50, 'maxLength' => $tools->setting('allowed_maxlength_aboutMe') ? $tools->setting('allowed_maxlength_aboutMe') : 1024)
		);

		$form->addButton('submit');

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();
	}

	/**
	 * BreezeUser::coverSettings()
	 *
	 * Uploads an user image for their wall.
	 * @return
	 */
	function coverSettings()
	{
		global $context, $memID, $txt, $user_info;

		// Another check just in case.
		if (!$this['tools']->enable('cover') || !allowedTo('breeze_canCover'))
			redirectexit();

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$data = Breeze::data('get');

		// Set the page title
		$context['page_title'] = $this['tools']->text('user_settings_name_cover');
		$context['sub_template'] = 'member_options';
		$context['page_desc'] = $this['tools']->text('user_settings_name_cover_desc');

		// Need to tell the form the page it needs to display when redirecting back after saving.
		$context['Breeze_redirect'] = 'breezecover';

		// Get the user settings.
		$userSettings = $this['query']->getUserSettings($context['member']['id']);

		// Create the form.
		$form = $this['form'];

		// Group all these values into an array. Makes it easier to save the changes.
		$form->setOptions(array(
			'name' => 'breezeSettings',
			'url' => $this['tools']->scriptUrl .'?action=breezeajax;sa=cover;rf=profile;u='. $context['member']['id'] .';area='. (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings'),
			'character_set' => $context['character_set'],
			'title' => $context['page_title'],
			'desc' => $context['page_desc'],
		));

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		// Remove a cover image.
		if (!empty($userSettings['cover']))
			$form->addHTML(
				'cover_delete',
				'<a href="'. $this['tools']->scriptUrl .'?action=breezeajax;sa=coverdelete;u='. $context['member']['id'] .';rf=profile;'. $context['session_var'] .'='. $context['session_id'] .'" class="cover_delete">%s</a>
				'. (file_exists($this['tools']->boardDir . Breeze::$coversFolder . $context['member']['id'] .'/thumbnail/'. $userSettings['cover']['basename']) ? '<br /><img src="'. $this['tools']->boardUrl . Breeze::$coversFolder . $context['member']['id'] .'/thumbnail/'. $userSettings['cover']['basename'] .'" class ="current_cover" />' : '') .''
			);

		// Cover upload option.
		$form->addHTML(
			'cover_select',
			'<span class="">
				<input id="fileupload" type="file" name="files">
			</span>
			<br />
			<div id="progress" class="progress">
				<div class="progress-bar progress-bar-success"></div>
			</div>
			<div id="files" class="files"></div>'
		);

		$form->addButton('submit');

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();

		loadJavascriptFile('breeze/fileUpload/vendor/jquery.ui.widget.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/load-image.all.min.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/canvas-to-blob.min.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.iframe-transport.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload-process.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload-image.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload-validate.js', array('local' => true, 'default_theme' => true));
		addInlineJavascript('
	$(function () {
		$(\'#fileupload\').on(\'click\', function (e) {
			$(\'#files\').empty();
		});

		$(\'#fileupload\').fileupload({
			dataType: \'json\',
			url : '. JavaScriptEscape($this['tools']->scriptUrl .'?action=breezeajax;sa=cover;rf=profile;u='. $context['member']['id'] .';area='. (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings') .';js=1') .',
			autoUpload: false,
			getNumberOfFiles: 1,
			disableImageResize: /Android(?!.*Chrome)|Opera/
				.test(window.navigator.userAgent),
			previewMaxWidth: 100,
			previewMaxHeight: 100,
			previewCrop: true,
			maxNumberOfFiles: 1,
			add: function (e, data) {
				data.context = jQuery(\'[name="Submit"]\')
					.on(\'click\', function (e) {
						e.preventDefault();
						data.submit();
						return false;
					}).data(data);
			}
		}).on(\'fileuploadadd\', function (e, data) {
			data.context = jQuery(\'<div/>\').appendTo(\'#files\');
			$.each(data.files, function (index, file) {

				var node = jQuery(\'<div/>\')
						.append(\'<p class="b_cover_preview"><img src="\' + URL.createObjectURL(file) + \'"/ style="max-width: 300px;"></p>\');

				node.appendTo(data.context);
			});
		}).on(\'fileuploaddone\', function (e, data) {

			if (data.result.error) {
				$(\'.b_cover_preview\').replaceWith(\'<div class="errorbox">\' + data.result.error + \'</div>\');
			}

			else {
				$(\'.b_cover_preview\').replaceWith(\'<div class="\'+ data.result.type +\'box">\' + data.result.message + \'</div>\');

				// Replace the old cover preview with the new one.
				if (data.result.type == \'info\') {
					var image = JSON.parse(data.result.data);
					// Gotta make sure it exists...
					var imgsrc = \''. $this['tools']->boardUrl . Breeze::$coversFolder . $context['member']['id'] .'/thumbnail/\' + image.basename;
					var imgcheck = imgsrc.width;

					if (imgcheck != 0)
						$(\'.current_cover\').attr(\'src\', imgsrc);
				}
			}

			data.abort();
		}).on(\'fileuploadfail\', function (e, data) {
				$(\'.b_cover_preview\').replaceWith(\'<div class="errorbox">'. $this['tools']->text('error_server') .'</div>\');
				data.abort();
		}).on(\'always\', function (e, data) {

		});
	});', true);
	}

	/**
	 * BreezeUser::trackViews()
	 *
	 * Handles profile views, create or update views.
	 * @return
	 */
	function trackViews()
	{
		global $user_info, $context;

		$data = array();

		// Don't log guest views
		if ($user_info['is_guest'] == true)
			return false;

		// Do this only if t hasn't been done before
		$views = cache_get_data(Breeze::$name .'-tempViews-'. $context['member']['id'].'-by-'. $user_info['id'], 60);

		if (empty($views))
		{
			// Get the profile views
			$views = $this['query']->getViews($context['member']['id']);

			// Don't track own views
			if ($context['member']['id'] == $user_info['id'])
				return !empty($views) ? json_decode($views, true) : false;

			// Don't have any views yet?
			if (empty($views))
			{
				// Build the array
				$views[$user_info['id']] = array(
					'user' => $user_info['id'],
					'last_view' => time(),
					'views' => 1,
				);

				// Insert the data
				updateMemberData($context['member']['id'], array('breeze_profile_views' => json_encode($views)));

				// Set the temp cache
				cache_put_data(Breeze::$name .'-tempViews-'. $context['member']['id'].'-by-'. $user_info['id'], $views, 60);

				// Load the visitors data
				$this['tools']->loadUserInfo(array_keys($views));

				// Cut it off
				return $views;
			}

			// Get the data
			$views = json_decode($views, true);

			// Does this member has been here before?
			if (!empty($views[$user_info['id']]))
			{
				// Update the data then
				$views[$user_info['id']]['last_view'] = time();
				$views[$user_info['id']]['views'] = $views[$user_info['id']]['views'] + 1;
			}

			// First time huh?
			else
			{
				// Build the array
				$views[$user_info['id']] = array(
					'user' => $user_info['id'],
					'last_view' => time(),
					'views' => 1,
				);
			}

			// Either way, update the table
			updateMemberData($context['member']['id'], array('breeze_profile_views' => json_encode($views)));

			// ...and set the temp cache
			cache_put_data(Breeze::$name .'-tempViews-'. $context['member']['id'].'-by-'. $user_info['id'], $views, 60);
		}

		// Don't forget to load the visitors data
		$this['tools']->loadUserInfo(array_keys($views));

		return $views;
	}

	/**
	 * BreezeUser::checkPermissions
	 *
	 * Sets and checks different profile related permissions.
	 * @return
	 */
	function checkPermissions()
	{
		global $context, $memberContext, $user_info;

		$tools = $this['tools'];
		$query = $this['query'];

		// DUH! winning!
		$context['insert_after_template'] .= Breeze::who(true);

		// Another page already checked the permissions and if the mod is enable, but better be safe...
		if (!$tools->enable('master'))
			redirectexit();

		// If the owner doesn't have any settings don't show the wall, go straight to the static page unless the admin forced it.
		if (empty($context['Breeze']['settings']['owner']) && !$tools->enable('force_enable'))
			redirectexit('action=profile;area=static;u='.$context['member']['id']);

		// If we are forcing the wall, lets check the admin setting first
		elseif ($tools->enable('force_enable'))
			if (!isset($context['Breeze']['settings']['owner']['wall']))
				$context['Breeze']['settings']['owner']['wall'] = 1;

		// Do the normal check, do note this is not an elseif check, its separate.
		else
			if (empty($context['Breeze']['settings']['owner']['wall']))
				redirectexit('action=profile;area=static;u='.$context['member']['id']);

		// This user cannot see his/her own profile and cannot see any profile either
		if (!allowedTo('profile_view_own') && !allowedTo('profile_view_any'))
			redirectexit('action=profile;area=static;u='. $context['member']['id']);

		// This user cannot see his/her own profile and it's viewing his/her own profile
		if (!allowedTo('profile_view_own') && $user_info['id'] == $context['member']['id'])
			redirectexit('action=profile;area=static;u='. $context['member']['id']);

		// This user cannot see any profile and it's  viewing someone else's wall
		if (!allowedTo('profile_view_any') && $user_info['id'] != $context['member']['id'])
			redirectexit('action=profile;area=static;u='. $context['member']['id']);

		// Does an ignored user wants to see your wall? never!!!
		if (isset($context['Breeze']['settings']['owner']['kick_ignored']) && !empty($context['Breeze']['settings']['owner']['kick_ignored']) && !empty($context['Breeze']['settings']['owner']['ignoredList']))
		{
			// Make this an array
			$ignored = explode(',', $context['Breeze']['settings']['owner']['ignoredList']);

			if (in_array($user_info['id'], $ignored ))
				redirectexit('action=profile;area=static;u='.$context['member']['id']);
		}
	}
}
