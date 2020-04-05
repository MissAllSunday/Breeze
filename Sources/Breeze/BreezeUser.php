<?php

declare(strict_types=1);

/**
 * BreezeUser
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2019, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

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
	 * @param $memID
	 */
	function userWall($memID): void
	{
		global $txt, $context, $memberContext;
		global $modSettings,  $user_info;

		loadtemplate(Breeze::NAME);
		loadtemplate(Breeze::NAME . 'Functions');
		loadtemplate(Breeze::NAME . 'Blocks');
		loadLanguage(Breeze::NAME);

		// We kinda need all this stuff, don't ask why, just nod your head...
		$query = $this['query'];
		$tools = $this['tools'];
		$data = $this->data('get');
		$log = $this['log'];
		$usersToLoad = [];

		// Weird I know!!
		require_once($tools->sourceDir . '/Profile-View.php');
		summary($memID);

		// Don't show this.
		unset($context[$context['profile_menu_name']]['tab_data']);

		// Default values.
		$status = [];
		$context['Breeze'] = [
			'cover' => '',
			'views' => false,
			'log' => false,
			'buddiesLog' => false,
			'comingFrom' => 'profile',
			'settings' => [
				'owner' => [],
				'visitor' => [],
			],
		];

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
		$context['template_layers'] = [];
		$context['template_layers'][] = 'html';
		$context['template_layers'][] = 'user_wall';
		$context['template_layers'][] = 'generic_menu_dropdown';

		// Avoid SMF to call the default subtemplate.
		$context['sub_template'] = 'user_wall_dummy';

		$context['canonical_url'] = $this['tools']->scriptUrl . '?action=profile;u=' . $context['member']['id'];
		$context['member']['status'] = [];
		$context['Breeze']['tools'] = $tools;

		// I can haz cover?
		if ($tools->enable('cover') && !empty($context['Breeze']['settings']['owner']['cover']))
		{
			$context['Breeze']['cover'] = $this['tools']->scriptUrl . '?action=breezecover;u=' . $context['member']['id'];

			addInlineCss('
.header {background-image: url(' . $context['Breeze']['cover'] . '); height:' . (!empty($context['Breeze']['settings']['owner']['cover_height']) ? $context['Breeze']['settings']['owner']['cover_height'] : '380') . 'px;}');
		}

		// Set up some vars for pagination.
		$maxIndex = !empty($context['Breeze']['settings']['visitor']['pagination_number']) ? $context['Breeze']['settings']['visitor']['pagination_number'] : 5;
		$currentPage = true == $data->validate('start') ? $data->get('start') : 0;

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
		$context['page_title'] = $this['tools']->parser($tools->text('profile_of_username'), [
			'name' => $context['member']['name']
		]);

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
		loadCSSFile('//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', ['external' => true]);

		// These file are only used here and on the general wall thats why I'm stuffing them here rather than in Breeze::notiHeaders()
		loadJavascriptFile('breeze/post.js', ['default_theme' => true, 'defer' => true, 'async' => true]);
		loadJavascriptFile('breeze/tabs.js', ['default_theme' => true, 'defer' => true]);
		loadJavascriptFile('breeze/loadMore.js', ['external' => false, 'default_theme' => true, 'defer' => true]);

		if (!empty($modSettings['enable_mentions']) && allowedTo('mention'))
		{
			loadJavascriptFile('jquery.atwho.min.js', ['default_theme' => true, 'defer' => true, 'async' => true], 'smf_atwho');
			loadJavascriptFile('jquery.caret.min.js', ['default_theme' => true, 'defer' => true, 'async' => true], 'smf_caret');
			loadJavascriptFile('mentions.js', ['default_theme' => true, 'defer' => true, 'async' => true], 'smf_mention');
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
			maxIndex : ' . $maxIndexAlert . ',
			totalItems : ' . $alerts['count'] . ',
			userID : ' . $context['member']['id'] . '
		},
		button : {
			id: \'alertLoad\',
			text : ' . JavaScriptEscape($tools->text('load_more')) . ',
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
				maxIndex : ' . $maxIndex . ',
				totalItems : ' . $status['count'] . ',
				userID : ' . $context['member']['id'] . '
			},
			button : {
				id: \'statusLoad\',
				text : ' . JavaScriptEscape($tools->text('load_more')) . ',
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
	breeze.tools.comingFrom = "' . $context['Breeze']['comingFrom'] . '";');

		// Pass the profile owner settings to the client, all minus the about me stuff.
		$toClient = $context['Breeze']['settings']['owner'];
		unset($toClient['aboutMe']);
		$bOwnerSettings = '';
		foreach (Breeze::$allSettings as $k => $v)
			$bOwnerSettings .= '
	breeze.ownerSettings.' . $k . ' = ' . (isset($toClient[$k]) ? (is_array($toClient[$k]) ? json_encode($toClient[$k]) : JavaScriptEscape($toClient[$k])) : 'false') . ';';

		addInlineJavascript($bOwnerSettings);
		unset($toClient);

		// Lastly, load all the users data from this bunch of user IDs.
		if (!empty($usersToLoad))
			$tools->loadUserInfo(array_unique($usersToLoad));
	}

	/**
	 * BreezeUser::alerts()
	 *
	 * Creates alert settings and configuration pages.
	 */
	function userAlerts(): void
	{
		global $context;

		loadtemplate(Breeze::NAME);
		loadtemplate(Breeze::NAME . 'Functions');

		$data = $this->data('get');
		$tools = $this['tools'];

		// Create the tabs for the template.
		$context[$context['profile_menu_name']]['tab_data'] = [
			'title' => $tools->text('user_settings_name_alerts'),
			'description' => $tools->text('user_settings_name_alerts_desc'),
			'icon' => 'profile_hd.png',
			'tabs' => [
				'settings' => [],
				'edit' => [],
			],
		];

		$context['page_title'] = $data->get('sa') && $tools->text('user_settings_name_alerts_' . $data->get('sa')) ? $tools->text('user_settings_name_alerts_' . $data->get('sa')) : $tools->text('user_settings_name_alerts_settings');
		$context['page_desc'] = $data->get('sa') && $tools->text('user_settings_name_alerts_' . $data->get('sa') . '_desc') ? $tools->text('user_settings_name_alerts_' . $data->get('sa') . '_desc') : $tools->text('user_settings_name_alerts_settings_desc');

		// Call the right action.
		$call = 'alert' . ($data->get('sa') ? ucfirst($data->get('sa')) : 'UserSettingsController');

		// Call the right function.
		$this->{$call}();
	}

	public function alertSettings(): void
	{
		global $context;

		$context['Breeze_redirect'] = 'alerts';
		$context['sub_template'] = 'member_options';

		// Get the user settings.
		$userSettings = $this['query']->getUserSettings($context['member']['id']);

		// Create the form.
		$form = $this['form'];

		// Group all these values into an array. Makes it easier to save the changes.
		$form->setOptions([
			'name' => 'breezeSettings',
			'url' => $this['tools']->scriptUrl . '?action=breezeajax;sa=usersettings;rf=profile;u=' . $context['member']['id'] . ';area=' . (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings'),
			'character_set' => $context['character_set'],
			'title' => $context['page_title'],
			'desc' => $context['page_desc'],
		]);

		// Get all inner alerts.
		foreach ($this['log']->alerts as $a)
			$form->addCheckBox([
				'name' => 'alert_' . $a,
				'checked' => !empty($userSettings['alert_' . $a]) ? true : false
			]);

		// Add a nice "check all" link.
		$form->addHTML([
			'name' => 'checkAll',
			'html' => '<a id="select_all">' . $this['tools']->text('user_settings_checkAll') . '</a>',
		]);

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		$form->addButton(['name' => 'submit']);

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();

		addInlineJavascript('
	$(function(){
		$(\'#select_all\').on(\'click\', function() {
			var checkboxes = $(\'form[name="breezeSettings"]\').find(\':checkbox\');
			checkboxes.prop("checked", !checkboxes.prop("checked"));
		});
	});', true);
	}

	public function alertEdit(): void
	{
		global $context, $scripturl, $txt;

		$context['sub_template'] = 'alert_edit';
		$data = $this->data();
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
	 */
	function userSettings(): void
	{
		global $context, $txt, $modSettings, $user_info;

		loadtemplate(Breeze::NAME);
		loadtemplate(Breeze::NAME . 'Functions');
		loadJavaScriptFile('suggest.js', ['minimize' => true], 'smf_suggest');

		$data = $this->data('get');
		$tools = $this['tools'];

		// Is there an admin limit?
		$maxUsers = $tools->setting('allowed_max_num_users') ? $tools->setting('allowed_max_num_users') : 0;

		// Set the page title
		$context['page_title'] = $tools->text('user_settings_name');
		$context['sub_template'] = 'member_options';
		$context['page_desc'] = $tools->text('user_settings_name_desc');
		$context['Breeze_redirect'] = '';

		// Get the user settings.
		$context['Breeze']['UserSettings'] = $userSettings = $this['query']->getUserSettings($context['member']['id']);

		// Create the form.
		$form = $this['form'];

		// Load the user's info.
		$tools->loadUserInfo(array_unique($userSettings['blockListIDs']));

		// We only need the block list users
		$context['Breeze']['UserSettings']['blockListUserData'] = array_intersect_key($context['Breeze']['user_info'], array_flip($userSettings['blockListIDs']));

		// Group all these values into an array. Makes it easier to save the changes.
		$form->setOptions([
			'name' => 'breezeSettings',
			'url' => $this['tools']->scriptUrl . '?action=breezeajax;sa=usersettings;rf=profile;u=' . $context['member']['id'] . ';area=' . (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings'),
			'character_set' => $context['character_set'],
			'title' => $context['page_title'],
			'desc' => $context['page_desc'],
		]);

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		// Per user master setting.
		$form->addCheckBox([
			'name' => 'wall',
			'checked' => !empty($userSettings['wall']),
		]);

		// UserSettingsController wall setting.
		$form->addCheckBox([
			'name' => 'general_wall',
			'checked' => !empty($userSettings['general_wall']),
		]);

		// Pagination.
		$form->addText([
			'name' => 'pagination_number',
			'value' => !empty($userSettings['pagination_number']) ? $userSettings['pagination_number'] : 0,
			'size' => 3,
			'maxlength' => 3,
		]);

		// Number of alerts in recent activity page.
		$form->addText([
			'name' => 'number_alert',
			'value' => !empty($userSettings['number_alert']) ? $userSettings['number_alert'] : 0,
			'size' => 3,
			'maxlength' => 3,
		]);

		// Add the load more button.
		$form->addCheckBox([
			'name' => 'load_more',
			'checked' => !empty($userSettings['load_more'])
		]);

		// Activity Log.
		$form->addCheckBox([
			'name' => 'activityLog',
			'checked' => !empty($userSettings['activityLog'])
		]);

		// Only show this is the admin has enable the buddy feature.
		if (!empty($modSettings['enable_buddylist']))
		{
			// Allow ignored users.
			$form->addCheckBox([
				'name' => 'kick_ignored',
				'checked' => !empty($userSettings['kick_ignored'])
			]);

			// Block list.
			$form->addHTML([
				'name' => 'blockList',
				'html' => '
					<input type="text" name="breezeSettings[blockList]" id="blockList" value="" size="30">
					<div id="to_item_list_container"></div>',
				'size' => 30,
				'maxlength' => 900,
			]);

			// Buddies block.
			$form->addCheckBox([
				'name' => 'buddies',
				'checked' => !empty($userSettings['buddies']) ? true : false
			]);

			// How many buddies are we gonna show?
			$form->addText([
				'name' => 'how_many_buddies',
				'value' => !empty($userSettings['how_many_buddies']) ? ($maxUsers && $userSettings['how_many_buddies'] >= $maxUsers ? $maxUsers : $userSettings['how_many_buddies']) : 0,
				'size' => 3,
				'maxlength' => 3,
			]);
		}

		// Profile visitors.
		$form->addCheckBox([
			'name' => 'visitors',
			'checked' => !empty($userSettings['visitors']) ? true : false
		]);

		// How many visitors are we gonna show?
		$form->addText([
			'name' => 'how_many_visitors',
			'value' => !empty($userSettings['how_many_visitors']) ? ($maxUsers && $userSettings['how_many_visitors'] >= $maxUsers ? $maxUsers : $userSettings['how_many_visitors']) : 0,
			'size' => 3,
			'maxlength' => 3,
		]);

		// Clean visitors log
		$form->addHTML([
			'name' => 'clean_visitors',
			'html' => $this['tools']->parser('<a href="{href}" class="clean_log">' . $this['tools']->text('user_settings_clean_visitors') . '</a>', [
				'href' => $this['tools']->scriptUrl . '?action=breezeajax;sa=cleanlog;log=visitors;u=' . $context['member']['id'] . ';rf=profile',
			])
		]);

		// About me textarea.
		$form->addTextArea([
			'name' => 'aboutMe',
			'value' => !empty($userSettings['aboutMe']) ? $userSettings['aboutMe'] : '',
			'size' => ['rows' => 10, 'cols' => 50, 'maxLength' => $tools->setting('allowed_maxlength_aboutMe') ? $tools->setting('allowed_maxlength_aboutMe') : 1024]
		]);

		// CoverController height.
		if ($tools->enable('cover'))
			$form->addText([
				'name' => 'cover_height',
				'value' => !empty($userSettings['cover_height']) ? $userSettings['cover_height'] : 0,
				'size' => 3,
				'maxlength' => 3,
			]);

		$form->addButton(['name' => 'submit']);

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();
	}

	/**
	 * BreezeUser::coverSettings()
	 *
	 * Uploads an user image for their wall.
	 */
	function userCoverSettings(): void
	{
		global $context, $memID, $txt, $user_info;

		// Another check just in case.
		if (!$this['tools']->enable('cover') || !allowedTo('breeze_canCover'))
			redirectexit();

		loadtemplate(Breeze::NAME);
		loadtemplate(Breeze::NAME . 'Functions');

		$data = $this->data('get');

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
		$form->setOptions([
			'name' => 'breezeSettings',
			'url' => $this['tools']->scriptUrl . '?action=breezeajax;sa=cover;rf=profile;u=' . $context['member']['id'] . ';area=' . (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings'),
			'character_set' => $context['character_set'],
			'title' => $context['page_title'],
			'desc' => $context['page_desc'],
		]);

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		// Remove a cover image.
		if (!empty($userSettings['cover']))
			$form->addHTML([
				'name' => 'cover_delete',
				'html' => $this['tools']->parser('<a href="{href}" class="cover_delete you_sure">{text}</a>', [
					'text' => $this['tools']->text('user_settings_cover_delete'),
					'href' => $this['tools']->scriptUrl . '?action=breezeajax;sa=coverdelete;u=' . $context['member']['id'] . ';rf=profile',
				]) . '<br /><img src="' . $this['tools']->scriptUrl . '?action=breezecover;u=' . $context['member']['id'] . ';thumb=1" class ="current_cover" />'
			]);

		// Prepare some image max values.
		$maxFileSize = ($this['tools']->setting('cover_max_image_size') ? $this['tools']->setting('cover_max_image_size') : '250');
		$maxFileWidth = ($this['tools']->setting('cover_max_image_width') ? $this['tools']->setting('cover_max_image_width') : '1500');
		$maxFileHeight = ($this['tools']->setting('cover_max_image_height') ? $this['tools']->setting('cover_max_image_height') : '500');

		// Add the dot to the allowed extensions.
		$acceptedFiles = implode(',', array_map(function($val) { return '.' . $val;}, explode(',', $this['tools']->setting('cover_image_types') ? $this['tools']->setting('cover_image_types') : 'jpg,jpeg,png')));

		// CoverController upload option.
		$form->addHTML([
			'fullDesc' => $this['tools']->parser(
				$this['tools']->text('user_settings_cover_select_sub'),
				[
					'fileTypes' => $acceptedFiles,
					'width' => $maxFileWidth,
					'height' => $maxFileHeight,
					'size' => $maxFileSize,
				]
			),
			'name' => 'cover_select',
			'html' => '
	<div id="coverUpload" class="descbox">
		<h5>' . $this['tools']->text('cu_dictDefaultMessage') . '</h5>
			<a class="button_submit fileinput-button">' . $this['tools']->text('cu_add') . '</a>
	</div>
	<div id="actions" class="cu-actions">
	</div>
	<div class="files cu-files" id="cu-previews">
		<div id="template">
			<div class="cu-fileInfo">
				<img data-dz-thumbnail />
				<p class="progressBar" role="progressBar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></p>
			</div>
			<div class="cu-fileUI">
				<p class="name" data-dz-name></p>
				<p class="size" data-dz-size></p>
				<p class="error" data-dz-errormessage></p>
				<p class="message" data-dz-message></p>
				<p class="attach-ui">
					<a class="button_submit attach-ui start">' . $this['tools']->text('general_upload') . '</a>
				</p>
			</div>
		</div>
	</div>'
		]);

		// Send the form to the template.
		$context['Breeze']['UserSettings']['Form'] = $form->display();

		// Need a lot of Js files :(
		loadJavascriptFile('dropzone.min.js', ['defer' => true], 'smf_dropzone');
		loadJavascriptFile('breeze/coverUpload.js', ['external' => false, 'default_theme' => true, 'defer' => true,], 'breeze_cover');

		// dropzone handles mb only...
		$maxFileSizeMB = $maxFileSize * 0.001;

		// Add the dot to the allowed extensions.
		$acceptedFiles = implode(',', array_map(function($val) { return '.' . $val;}, explode(',', $this['tools']->setting('cover_image_types') ? $this['tools']->setting('cover_image_types') : 'jpg,jpeg,png')));

		addInlineJavascript('
	var dzOptions = {
		maxFilesize: ' . $maxFileSizeMB . ',
		maxFileWidth: ' . ($maxFileWidth) . ',
		maxFileHeight: ' . $maxFileHeight . ',
		acceptedFiles: ' . JavaScriptEscape($acceptedFiles) . ',
		baseImgsrc: \'' . $this['tools']->scriptUrl . '?action=breezecover;u=' . $context['member']['id'] . ';thumb=1\',
		dictRemoveFile: ' . (JavaScriptEscape($this['tools']->text('general_cancel'))) . ',
		dictResponseError: ' . (JavaScriptEscape($this['tools']->text('error_wrong_values'))) . ',
		dictMaxFilesExceeded: ' . (JavaScriptEscape($this['tools']->text('cu_dictMaxFilesExceeded'))) . ',
		dictFileTooBig: ' . (JavaScriptEscape($this['tools']->parser($this['tools']->text('cu_dictFileTooBig'), ['maxFilesize' => $maxFileSize]))) . ',
		dictInvalidFileType: ' . (JavaScriptEscape($this['tools']->text('cu_dictInvalidFileType'))) . ',
		dictFallbackMessage: ' . (JavaScriptEscape($this['tools']->text('cu_dictFallbackMessage'))) . ',
		maxWidthMessage: ' . JavaScriptEscape($this['tools']->parser(
			$this['tools']->text('cu_max_width'),
			[
				'width' => $maxFileWidth,
			]
		)) . ',
		maxHeightMessage: ' . JavaScriptEscape($this['tools']->parser(
			$this['tools']->text('cu_max_height'),
			[
				'height' => $maxFileHeight,
			]
		)) . ',
	};', false);
	}

	/**
	 * BreezeUser::trackViews()
	 *
	 * Handles profile views, create or update views.
	 * @return bool
	 */
	function trackViews()
	{
		global $user_info, $context;

		$data = [];

		// Don't log guest views
		if (true == $user_info['is_guest'])
			return false;

		// Do this only if t hasn't been done before
		$views = cache_get_data(Breeze::NAME . '-tempViews-' . $context['member']['id'] . '-by-' . $user_info['id'], 60);

		if (empty($views))
		{
			// Get the profile views
			$views = $this['query']->getViews($context['member']['id']);

			// Don't track own views
			if ($context['member']['id'] == $user_info['id'])
				return $views;

			// Don't have any views yet?
			if (empty($views))
			{
				// Build the array
				$views[$user_info['id']] = [
					'user' => $user_info['id'],
					'last_view' => time(),
					'views' => 1,
				];

				// Insert the data
				updateMemberData($context['member']['id'], ['breeze_profile_views' => json_encode($views)]);

				// Set the temp cache
				cache_put_data(Breeze::NAME . '-tempViews-' . $context['member']['id'] . '-by-' . $user_info['id'], $views, 60);

				// Load the visitors data
				$this['tools']->loadUserInfo(array_keys($views));

				// Cut it off
				return $views;
			}

			// Does this member has been here before?
			if (!empty($views[$user_info['id']]))
			{
				// Update the data then
				$views[$user_info['id']]['last_view'] = time();
				$views[$user_info['id']]['views'] = $views[$user_info['id']]['views'] + 1;
			}

			// First time huh? I'll be gentle...
			else
				$views[$user_info['id']] = [
					'user' => $user_info['id'],
					'last_view' => time(),
					'views' => 1,
				];

			// Either way, update the table.
			$this['query']->updateProfileViews($context['member']['id'], $views);

			// ...and set the temp cache
			cache_put_data(Breeze::NAME . '-tempViews-' . $context['member']['id'] . '-by-' . $user_info['id'], $views, 60);
		}

		// Don't forget to load the visitors data
		$this['tools']->loadUserInfo(array_keys($views));

		return $views;
	}

	/**
	 * BreezeUser::checkPermissions
	 *
	 * Sets and checks different profile related permissions.
	 */
	function checkPermissions(): void
	{
		global $context, $memberContext, $user_info;

		$tools = $this['tools'];
		$query = $this['query'];

		// Another page already checked the permissions and if the mod is enable, but better be safe...
		if (!$tools->enable(SettingsEntity::MASTER))
			redirectexit();

		// If the owner doesn't have any settings don't show the wall, go straight to the static page unless the admin forced it.
		if (empty($context['Breeze']['settings']['owner']) && !$tools->enable('force_enable'))
			redirectexit('action=profile;area=static;u=' . $context['member']['id']);

		// If we are forcing the wall, lets check the admin setting first
		elseif ($tools->enable('force_enable'))
			if (!isset($context['Breeze']['settings']['owner']['wall']))
				$context['Breeze']['settings']['owner']['wall'] = 1;

		// Do the normal check, do note this is not an elseif check, its separate.
		elseif (empty($context['Breeze']['settings']['owner']['wall']))
				redirectexit('action=profile;area=static;u=' . $context['member']['id']);

		// This user cannot see any profile.
		if (!allowedTo('profile_view'))
			redirectexit('action=profile;area=static;u=' . $context['member']['id']);

		// Does an ignored user wants to see your wall? never!!!
		if (isset($context['Breeze']['settings']['owner']['kick_ignored']) && !empty($context['Breeze']['settings']['owner']['kick_ignored']) && !empty($context['Breeze']['settings']['owner']['ignoredList']))
		{
			// Make this an array
			$ignored = explode(',', $context['Breeze']['settings']['owner']['ignoredList']);

			if (in_array($user_info['id'], $ignored ))
				redirectexit('action=profile;area=static;u=' . $context['member']['id']);
		}


		// All passed, se a nice session var to make sure you are really you!
		$_SESSION['Breeze']['owner'] = $context['member']['id'];
	}
}
