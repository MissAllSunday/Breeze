<?php

/**
 * BreezeUser
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
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
	function wall($memID)
	{
		global $txt, $context, $memberContext;
		global $modSettings,  $user_info;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');
		loadtemplate(Breeze::$name .'Blocks');
		loadLanguage(Breeze::$name);

		// We kinda need all this stuff, don't ask why, just nod your head...
		$query = $this['query'];
		$tools = $this['tools'];
		$data = Breeze::data('get');
		$log = $this['log'];
		$usersToLoad = array();

		// Weird I know!!
		require_once($tools->sourceDir . '/Profile-View.php');
		summary($memID);

		// Don't show this.
		unset($context[$context['profile_menu_name']]['tab_data']);
		$context['show_load_time'] = false;

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
		);

		// Does the admin has set a max limit?
		if ($tools->enable('allowed_max_num_users'))
			$context['Breeze']['max_users'] = (int) $tools->setting('allowed_max_num_users');

		// Get profile owner settings.
		$context['Breeze']['settings']['owner'] = $query->getUserSettings($context['member']['id']);

		// Check if this user allowed to be here
		$this->checkPermissions();

		// We need to make sure we have all your info...
		if (empty($context['Breeze']['user_info'][$user_info['id']]))
			$tools->loadUserInfo($user_info['id']);

		// Does the current user (AKA visitor) is also the owner?
		if ($context['user']['is_owner'])
			$context['Breeze']['settings']['visitor'] = $context['Breeze']['settings']['owner'];

		// Nope? :(
		else
			$context['Breeze']['settings']['visitor'] = $query->getUserSettings($user_info['id']);

		// Need to wipe out all currently loaded layers and re-add some of them in a very specific order...
		$context['template_layers'] = array();
		$context['template_layers'][] = 'html';
		$context['template_layers'][] = 'user_wall';
		$context['template_layers'][] = 'generic_menu_dropdown';

		// Avoid SMF to call the default subtemplate.
		$context['sub_template'] = 'user_wall_dummy';

		$context['canonical_url'] = $this['tools']->scriptUrl . '?action=profile;u=' . $context['member']['id'];
		$context['member']['status'] = array();
		$context['Breeze']['tools'] = $tools;

		// I can haz cover?
		if ($tools->enable('cover') && !empty($context['Breeze']['settings']['owner']['cover']))
		{
			$context['Breeze']['cover'] = $this['tools']->scriptUrl . '?action=breezecover;u=' . $context['member']['id'];

			addInlineCss('
.header {background-image: url('. $context['Breeze']['cover'] .'); height:'. (!empty($context['Breeze']['settings']['owner']['cover_height']) ? $context['Breeze']['settings']['owner']['cover_height'] : '380') .'px;}');
		}

		// Set up some vars for pagination.
		$maxIndex = !empty($context['Breeze']['settings']['visitor']['pagination_number']) ? $context['Breeze']['settings']['visitor']['pagination_number'] : 5;
		$currentPage = $data->validate('start') == true ? $data->get('start') : 0;

		// Load all the status.
		$status = $query->getStatusByProfile($context['member']['id'], $maxIndex, $currentPage);

		// Load users data.
		if (!empty($status['users']))
			$usersToLoad = $usersToLoad + $status['users'];

		// Pass the status info.
		if (!empty($status['data']))
			$context['member']['status'] = $status['data'];

		// Applying pagination.
		if (!empty($status['pagination']))
			$context['page_index'] = $status['pagination'];

		// Page name depends on pagination.
		$context['page_title'] = $this['tools']->parser($tools->text('profile_of_username'), array(
			'name' => $context['member']['name']
		));

		// Get the profile views.
		if (!$user_info['is_guest'] && !empty($context['Breeze']['settings']['owner']['visitors']))
		{
			$context['Breeze']['views'] = $this->trackViews();

			// If there is a limit then lets count the total so we can know if we are gonna use the compact style.
			$maxVisitors = !empty($context['Breeze']['max_users']) && $context['Breeze']['settings']['owner']['how_many_visitors'] ? max((int) $context['Breeze']['max_users'], (int) $context['Breeze']['settings']['owner']['how_many_visitors']) : 5;


			// How many visitors are we gonna show?
			if (!empty($context['Breeze']['views']) && is_array($context['Breeze']['views']) && count($context['Breeze']['views']) >= $maxVisitors)
			{
				$context['Breeze']['views'] = array_slice($context['Breeze']['views'], 0, $maxVisitors);

				// Load their data
				$usersToLoad = array_merge($usersToLoad, array_keys($context['Breeze']['views']));
			}
		}

		// Show buddies only if there is something to show.
		if (!empty($context['Breeze']['settings']['owner']['buddies']) && !empty($context['member']['buddies']))
		{
			// Hold your horses!
			$maxBuddies = !empty($context['Breeze']['max_users']) && $context['Breeze']['settings']['owner']['how_many_buddies'] ? max((int) $context['Breeze']['max_users'], (int) $context['Breeze']['settings']['owner']['how_many_buddies']) : 5;

			if (count($context['member']['buddies']) >= $maxBuddies)
				$context['member']['buddies'] = array_slice($context['member']['buddies'], 0, $maxBuddies);

			$usersToLoad = array_merge($usersToLoad, $context['member']['buddies']);
		}

		// Load the icon's css.
		loadCSSFile('//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', array('external' => true));

		// These file are only used here and on the general wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
		loadJavascriptFile('breeze/breezePost.js', array('default_theme' => true, 'defer' => true,));
		loadJavascriptFile('breeze/breezeTabs.js', array('default_theme' => true, 'defer' => true,));
		loadJavascriptFile('breeze/breezeLoadMore.js', array('local' => true, 'default_theme' => true, 'defer' => true,));

		if (!empty($modSettings['enable_mentions']) && allowedTo('mention'))
		{
			loadJavascriptFile('jquery.atwho.js', array('default_theme' => true, 'defer' => true), 'smf_atwho');
			loadJavascriptFile('mentions.js', array('default_theme' => true, 'defer' => true), 'smf_mention');
		}

		// Setup the log activity.
		if (!empty($context['Breeze']['settings']['owner']['activityLog']))
		{
			$maxIndexAlert = $maxIndex = !empty($context['Breeze']['settings']['visitor']['number_alert']) ? $context['Breeze']['settings']['visitor']['number_alert'] : 5;
			$alerts =  $this['log']->get($context['member']['id'], $maxIndexAlert, 0);
			$context['Breeze']['log'] = $alerts['data'];

			// Loadmore for log alerts. Don't show this if there aren't enough items to display.
			if ($alerts['count'] >= $maxIndexAlert)
				addInlineJavascript('
	var logLoad = new breezeLoadMore({
		pagination : {
			maxIndex : '. $maxIndexAlert .',
			totalItems : ' . $alerts['count'] . ',
			userID : '. $context['member']['id'] .'
		},
		button : {
			id: \'alertLoad\',
			text : '. JavaScriptEscape($tools->text('load_more')) .',
			appendTo : \'#tab-activity\'
		},
		target : {
			css : \'breezeActivity\',
			appendTo : \'#breezeAppendToLog\'
		},
		urlSa : \'fetchLog\',
		hidePagination : false
	});
	', true);
		}

		// Does the user wants to use the load more button?
		if (!empty($context['Breeze']['settings']['visitor']['load_more']))
		{
			// Let us pass a lot of data to the client and I mean a lot!
			addInlineJavascript('
	$(function(){
		var statusLoad = new breezeLoadMore({
			pagination : {
				maxIndex : '. $maxIndex .',
				totalItems : ' . $status['count'] . ',
				userID : '. $context['member']['id'] .'
			},
			button : {
				id: \'statusLoad\',
				text : '. JavaScriptEscape($tools->text('load_more')) .',
				appendTo : \'#tab-wall\'
			},
			target : {
				css : \'breeze_status\',
				appendTo : \'#breezeAppendTo\'
			},
			urlSa : \'fetch\',
			hidePagination : true
		});
	});', true);
		}

		// Initialize the tabs.
		addInlineJavascript('
	var bTabs = new breezeTabs(\'ul.breezeTabs\', \'wall\');', true);

		addInlineJavascript('
	breeze.tools.comingFrom = "'. $context['Breeze']['comingFrom'] .'";');

		// Pass the profile owner settings to the client all minus the about me stuff.
		$toClient = $context['Breeze']['settings']['owner'];
		unset($toClient['aboutMe']);
		$bOwnerSettings = '';
		foreach (Breeze::$allSettings as $k => $v)
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
				'settings' => array(),
				'edit' => array(),
			),
		);

		$context['page_title'] = $data->get('sa') && $tools->text('user_settings_name_alerts_'. $data->get('sa')) ? $tools->text('user_settings_name_alerts_'. $data->get('sa')) : $tools->text('user_settings_name_alerts_settings');
		$context['page_desc'] = $data->get('sa') && $tools->text('user_settings_name_alerts_'. $data->get('sa') .'_desc') ? $tools->text('user_settings_name_alerts_'. $data->get('sa') .'_desc') : $tools->text('user_settings_name_alerts_settings_desc');

		// Call the right action.
		$call = 'alert' .($data->get('sa') ? ucfirst($data->get('sa')) : 'Settings');

		// Call the right function.
		$this->{$call}();
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
			$form->addCheckBox(array(
				'name' => 'alert_'. $a,
				'checked' => !empty($userSettings['alert_'. $a]) ? true : false
			));

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		$form->addButton(array('name' => 'submit'));

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
			var checkboxes = $(\'ul.quickbuttons\').find(\':checkbox\');
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
		$form->addCheckBox(array(
			'name' => 'wall',
			'checked' => !empty($userSettings['wall']) ? true : false,
		));

		// General wall setting.
		$form->addCheckBox(array(
			'name' => 'general_wall',
			'checked' => !empty($userSettings['general_wall']) ? true : false,
		));

		// Pagination.
		$form->addText(array(
			'name' => 'pagination_number',
			'value' => !empty($userSettings['pagination_number']) ? $userSettings['pagination_number'] : 0,
			'size' => 3,
			'maxlength' => 3,
		));

		// Number of alerts in recent activity page.
		$form->addText(array(
			'name' => 'number_alert',
			'value' => !empty($userSettings['number_alert']) ? $userSettings['number_alert'] : 0,
			'size' => 3,
			'maxlength' => 3,
		));

		// Add the load more button.
		$form->addCheckBox(array(
			'name' => 'load_more',
			'checked' => !empty($userSettings['load_more']) ? true : false
		));

		// Activity Log.
		$form->addCheckBox(array(
			'name' => 'activityLog',
			'checked' => !empty($userSettings['activityLog']) ? true : false
		));

		// Only show this is the admin has enable the buddy feature.
		if (!empty($modSettings['enable_buddylist']))
		{
			// Allow ignored users.
			$form->addCheckBox(array(
				'name' => 'kick_ignored',
				'checked' => !empty($userSettings['kick_ignored']) ? true : false
			));

			// Number of alerts in recent activity page.
			$form->addText(array(
				'name' => 'blockList',
				'value' => !empty($userSettings['blockList']) ? implode(',', $userSettings['blockList']) : '',
				'size' => 20,
				'maxlength' => 90,
			));

			// Buddies block.
			$form->addCheckBox(array(
				'name' => 'buddies',
				'checked' => !empty($userSettings['buddies']) ? true : false
			));

			// How many buddies are we gonna show?
			$form->addText(array(
				'name' => 'how_many_buddies',
				'value' => !empty($userSettings['how_many_buddies']) ? ($maxUsers && $userSettings['how_many_buddies'] >= $maxUsers ? $maxUsers : $userSettings['how_many_buddies']) : 0,
				'size' => 3,
				'maxlength' => 3,
			));
		}

		// Profile visitors.
		$form->addCheckBox(array(
			'name' => 'visitors',
			'checked' => !empty($userSettings['visitors']) ? true : false
		));

		// How many visitors are we gonna show?
		$form->addText(array(
			'name' => 'how_many_visitors',
			'value' => !empty($userSettings['how_many_visitors']) ? ($maxUsers && $userSettings['how_many_visitors'] >= $maxUsers ? $maxUsers : $userSettings['how_many_visitors']) : 0,
			'size' => 3,
			'maxlength' => 3,
		));

		// Clean visitors log
		$form->addHTML(array(
			'name' => 'clean_visitors',
			'html' => $this['tools']->parser('<a href="{href}" class="clean_log">'. $this['tools']->text('user_settings_clean_visitors') .'</a>', array(
				'href' => $this['tools']->scriptUrl .'?action=breezeajax;sa=cleanlog;log=visitors;u='. $context['member']['id'] .';rf=profile',
			))
		));

		// About me textarea.
		$form->addTextArea(array(
			'name' => 'aboutMe',
			'value' => !empty($userSettings['aboutMe']) ? $userSettings['aboutMe'] : '',
			'size' => array('rows' => 10, 'cols' => 50, 'maxLength' => $tools->setting('allowed_maxlength_aboutMe') ? $tools->setting('allowed_maxlength_aboutMe') : 1024)
		));

		// Cover height.
		if ($tools->enable('cover'))
			$form->addText(array(
				'name' => 'cover_height',
				'value' => !empty($userSettings['cover_height']) ? $userSettings['cover_height'] : 0,
				'size' => 3,
				'maxlength' => 3,
			));

		$form->addButton(array('name' => 'submit'));

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
			$form->addHTML(array(
				'name' => 'cover_delete',
				'html' => $this['tools']->parser('<a href="{href}" class="cover_delete you_sure">{text}</a>', array(
					'text' => $this['tools']->text('user_settings_cover_delete'),
					'href' => $this['tools']->scriptUrl .'?action=breezeajax;sa=coverdelete;u='. $context['member']['id'] .';rf=profile',
				)). '<br /><img src="'. $this['tools']->scriptUrl .'?action=breezecover;u='. $context['member']['id'] .';thumb=1" class ="current_cover" />'
			));

		// Cover upload option.
		$form->addHTML(array(
			'name' => 'cover_select',
			'html' => '<span class="">
				<input id="fileupload" type="file" name="files">
			</span>
			<br />
			<div id="progress" class="progress">
				<div class="progress-bar progress-bar-success"></div>
			</div>
			<div id="files" class="files"></div>'
		));

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();

		// Need a lot of Js files :(
		loadJavascriptFile('breeze/fileUpload/vendor/jquery.ui.widget.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/load-image.all.min.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/canvas-to-blob.min.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.iframe-transport.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload-process.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload-image.js', array('local' => true, 'default_theme' => true));
		loadJavascriptFile('breeze/fileUpload/jquery.fileupload-validate.js', array('local' => true, 'default_theme' => true));

		// @todo replace the hardcoded text strings
		addInlineJavascript('
	$(function () {
	var uploadButton = $(\'<a/>\')
		.addClass(\'button_submit uploadButton\')
		.prop(\'disabled\', true)
		.text(\'upload\')
		.one(\'click\', function (e) {
			e.preventDefault();
			$(this).data().submit();
		}),
	cancelButton = $(\'<a/>\')
		.addClass(\'button_submit cancelButton\')
		.prop(\'disabled\', false)
		.text(\'cancel\')
		.one(\'click\', function (e) {
			e.preventDefault();
			var inData = $(this).data();
			$(\'.b_cover_preview\').fadeOut(\'slow\', function() {
				$(\'#files\').empty();
				delete inData.files;
				$(\'#fileupload\').prop(\'disabled\', false);
			});
		});

		$(\'#fileupload\').fileupload({
			dataType: \'json\',
			url : '. JavaScriptEscape($this['tools']->scriptUrl .'?action=breezeajax;sa=cover;rf=profile;u='. $context['member']['id'] .';area='. (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings') .';js=1;'. $context['session_var'] .'='. $context['session_id']) .',
			autoUpload: false,
			getNumberOfFiles: 1,
			disableImageResize: /Android(?!.*Chrome)|Opera/
				.test(window.navigator.userAgent),
			previewMaxWidth: 100,
			previewMaxHeight: 100,
			previewCrop: true,
			maxNumberOfFiles: 1,
			add: function (e, data) {
				data.context = $(\'#files\');
				$(\'#fileupload\').prop(\'disabled\', true);
				$.each(data.files, function (index, file) {

					var node = $(\'<p/>\').addClass(\'b_cover_preview\').append(\'<img src="\' + URL.createObjectURL(file) + \'"/ style="max-width: 300px;">\');
					node.appendTo(data.context);
				});
				data.context.append(uploadButton.clone(true).data(data));
				data.context.append(cancelButton.clone(true).data(data));
			}
		}).on(\'fileuploaddone\', function (e, data) {

			$(\'#fileupload\').prop(\'disabled\', false);
			ajax_indicator(false);
			if (data.result.error) {
				data.abort();
				$(\'.b_cover_preview\').replaceWith(\'<div class="errorbox">\' + data.result.error + \'</div>\');
			}

			else {
				$(\'.b_cover_preview\').replaceWith(\'<div class="\'+ data.result.type +\'box">\' + data.result.message + \'</div>\');

				// Replace the old cover preview with the new one.
				if (data.result.type == \'info\') {
					var image = JSON.parse(data.result.data);
					// Gotta make sure it exists...
					var imgsrc = \''. $this['tools']->scriptUrl .'?action=breezecover;u='. $context['member']['id'] .';thumb=1\';
					var imgcheck = imgsrc.width;

					if (imgcheck != 0)
						$(\'.current_cover\').attr(\'src\', imgsrc);
				}
			}

			data.abort();
		}).on(\'fileuploadfail\', function (e, data) {
				ajax_indicator(false);
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

		// This user cannot see any profile.
		if (!allowedTo('profile_view'))
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
