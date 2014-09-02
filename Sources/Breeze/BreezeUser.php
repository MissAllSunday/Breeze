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
		global $txt, $scripturl, $context, $memberContext, $sourcedir;
		global $modSettings,  $user_info, $memID, $settings, $boarddir;
		global $boardurl;

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
		if ($tools->setting('allowed_max_num_users'))
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
		$context['sub_template'] = 'user_wall';
		$context += array(
			'page_title' => sprintf($txt['profile_of_username'], $context['member']['name']),
			'can_send_pm' => allowedTo('pm_send'),
			'can_have_buddy' => allowedTo('profile_identity_own') && !empty($modSettings['enable_buddylist']),
			'can_issue_warning' => allowedTo('issue_warning') && $modSettings['warning_settings'][0] == 1,
			'can_view_warning' => (allowedTo('moderate_forum') || allowedTo('issue_warning') || allowedTo('view_warning_any') || ($context['user']['is_owner'] && allowedTo('view_warning_own')) && $modSettings['warning_settings'][0] === 1)
		);
		$context['canonical_url'] = $scripturl . '?action=profile;u=' . $context['member']['id'];
		$context['member']['status'] = array();
		$context['Breeze']['tools'] = $tools;

		// Can this user have a cover?
		if ($tools->setting('cover') && allowedTo('breeze_canCover') && !empty($context['Breeze']['settings']['owner']['cover']) && file_exists($boarddir . Breeze::$coversFolder . $context['member']['id'] .'/'. $context['Breeze']['settings']['owner']['cover']))
			$context['Breeze']['cover'] = $boardurl . Breeze::$coversFolder . $context['member']['id'] .'/'. $context['Breeze']['settings']['owner']['cover'];

		// Set up some vars for pagination
		$maxIndex = !empty($context['Breeze']['settings']['visitor']['pagination_number']) ? $context['Breeze']['settings']['visitor']['pagination_number'] : 5;
		$currentPage = $data->validate('start') == true ? $data->get('start') : 0;

		// Load all the status
		$data = $query->getStatusByProfile($context['member']['id'], $maxIndex, $currentPage);

		// Load users data
		if (!empty($data['users']))
			$usersToLoad = $usersToLoad + $data['users'];

		// Pass the status info
		if (!empty($data['data']))
			$context['member']['status'] = $data['data'];

		// Applying pagination.
		if (!empty($data['pagination']))
			$context['page_index'] = $data['pagination'];

		// Page name depends on pagination
		$context['page_title'] = sprintf($tools->text('profile_of_username'), $context['member']['name']);

		// Get the profile views
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

		// Show buddies only if there is something to show
		if (!empty($context['Breeze']['settings']['owner']['buddies']) && !empty($context['member']['buddies']))
		{
			// Hold your horses!
			if (!empty($context['Breeze']['max_users']) && count($context['member']['buddies']) >= $context['Breeze']['max_users'])
				$context['Breeze']['compact']['buddy'] = true;

			$usersToLoad = array_merge($usersToLoad, $context['member']['buddies']);
		}

		// Show this user recent activity
		if (!empty($context['Breeze']['settings']['owner']['activityLog']))
			$context['Breeze']['log'] = $log->getActivity($context['member']['id']);

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
	 * BreezeUser::settings()
	 *
	 * Creates a form for each user to configure their wall settings.
	 * @return
	 */
	function settings()
	{
		global $context, $scripturl, $txt, $modSettings;
		global $user_info, $settings, $boardurl, $boarddir;

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
		$form->setFormName('breezeSettings');

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
			'<a href="'. $scripturl .'?action=breezeajax;sa=cleanlog;log=visitors;u='. $context['member']['id'] .';rf=profile;'. $context['session_var'] .'='. $context['session_id'] .'" class="clean_log">%s</a>'
		);

		// About me textarea.
		$form->addTextArea(
			'aboutMe',
			!empty($userSettings['aboutMe']) ? $userSettings['aboutMe'] : '',
			array('rows' => 10, 'cols' => 50, 'maxLength' => $tools->setting('allowed_maxlength_aboutMe') ? $tools->setting('allowed_maxlength_aboutMe') : 1024)
		);

		// The cover upload settings.
		if (allowedTo('breeze_canCover') && $tools->setting('cover'))
		{
			$form->addHr();

			// Remove a cover image.
			if (!empty($userSettings['cover']))
				$form->addHTML(
					'cover_delete',
					'<a href="'. $scripturl .'?action=breezeajax;sa=coverdelete;u='. $context['member']['id'] .';rf=profile;'. $context['session_var'] .'='. $context['session_id'] .'" class="cover_delete">%s</a>
					'. (file_exists($boarddir . Breeze::$coversFolder . $context['member']['id'] .'/thumbnail/'. $userSettings['cover']) ? '<br /><img src="'. $boardurl . Breeze::$coversFolder . $context['member']['id'] .'/thumbnail/'. $userSettings['cover'] .'" class ="" />' : '') .''
				);

			// Cover upload option
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

			// Print some jQuery goodies...
			$context['insert_after_template'] .= '
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/fileUpload/jquery.ui.widget.js"></script>
			<script src="http://blueimp.github.io/JavaScript-Load-Image/js/load-image.min.js"></script>
			<script src="http://blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/fileUpload/jquery.iframe-transport.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/fileUpload/jquery.fileupload.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/fileUpload/jquery.fileupload-process.js"></script>
			<script type="text/javascript" src="'. $settings['default_theme_url'] .'/js/fileUpload/jquery.fileupload-image.js"></script>
			<script type="text/javascript"><!-- // --><![CDATA[
	jQuery(function () {
		\'use strict\';
		var uploadButton = jQuery(\'<button/>\')
				.addClass(\'clear\')
				.prop(\'disabled\', true)
				.text(\'upload\')
				.on(\'click\', function (e) {
					e.preventDefault();
					var $this = jQuery(this),
					data = $this.data();
					data.submit().always(function () {
					});
				});
		var cancelButton = jQuery(\'<button/>\')
				.addClass(\'clear\')
				.text('. JavaScriptEscape($tools->text('confirm_cancel')) .')
				.on(\'click\', function (e) {
					e.preventDefault();
					var $this = jQuery(this),
						data = $this.data();
					data.abort();
					$this.parent().fadeOut(1000);
					$(\'#fileupload\').prop(\'disabled\', false);
				});
		jQuery(\'#fileupload\').fileupload({
			url: '. JavaScriptEscape($scripturl .'?action=breezeajax;sa=cover;u='. $context['member']['id'] .';rf=profile;js=1;'. $context['session_var'] .'='. $context['session_id']) .',
			dataType: \'json\',
			autoUpload: false,
			getNumberOfFiles: 1,
			maxNumberOfFiles: 1,
			acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
			maxFileSize: 5000000, // @todo add a setting here
			// Enable image resizing, except for Android and Opera,
			// which actually support image resizing, but fail to
			// send Blob objects via XHR requests:
			disableImageResize: /Android(?!.*Chrome)|Opera/
				.test(window.navigator.userAgent),
			previewMaxWidth: 100,
			previewMaxHeight: 100,
			previewCrop: true
		}).on(\'fileuploadadd\', function (e, data) {
			data.context = jQuery(\'<div/>\').appendTo(\'#files\');
			$.each(data.files, function (index, file) {
				var node = jQuery(\'<p/>\')
						.append(jQuery(\'<span/>\').text(file.name));
				if (!index) {
					node
						.append(\'<br>\')
						.append(uploadButton.data(data))
						.append(cancelButton.data(data));
				}
				node.appendTo(data.context);
			});
		}).on(\'fileuploadprocessalways\', function (e, data) {
			var index = data.index,
				file = data.files[index],
				node = jQuery(data.context.children()[index]);

			$(this).prop(\'disabled\', true);
			if (file.preview) {
				node
					.prepend(\'<br>\')
					.prepend(file.preview);
			}
			if (file.error) {
				node
					.append(\'<br>\')
					.append(jQuery(\'<span class="text-danger"/>\').text(file.error));
			}
			if (index + 1 === data.files.length) {
				data.context.find(\'button\')
					.prop(\'disabled\', !!data.files.error);
			}
		}).on(\'fileuploadprogressall\', function (e, data) {
			var progress = parseInt(data.loaded / data.total * 100, 10);
			jQuery(\'#progress .progress-bar\').css(
				\'width\',
				progress + \'%\'
			);
		}).on(\'fileuploaddone\', function (e, data) {
			console.log(data);
			jQuery.each(data.result.files, function (index, file) {
				if (file.url) {
					jQuery(data.context.children()[index])
						.replaceWith(\'<div id="profile_success">'. $tools->text('user_settings_cover_done') .'</div>\');
				} else if (file.error) {
					var error = jQuery(\'<span class="text-danger"/>\').text(file.error);
					jQuery(data.context.children()[index])
						.append(\'<br>\')
						.append(error);
				}
			});
		}).on(\'fileuploadfail\', function (e, data) {console.log(data);
			if (data.result) {
				$.each(data.result.files, function (index, file) {
					var error = jQuery(\'<span class="text-danger"/>\').text(\'File upload failed.\');
					jQuery(data.context.children()[index])
						.append(\'<br>\')
						.append(error);
				});
			}
		}).prop(\'disabled\', !$.support.fileInput)
			.parent().addClass($.support.fileInput ? undefined : \'disabled\');
	});
			// ]]></script>';
	}

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();
	}

	/**
	 * BreezeUser::notiSettings()
	 *
	 * Creates notification related settings
	 * @return
	 */
	function notiSettings()
	{
		global $context, $memID, $scripturl, $txt, $user_info;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$data = Breeze::data('get');

		// Set the page title
		$context['page_title'] = $this['tools']->text('user_settings_name_settings');
		$context['sub_template'] = 'member_options';
		$context['page_desc'] = $this['tools']->text('user_settings_name_settings_desc');

		// Need to tell the form the page it needs to display when redirecting back after saving.
		$context['Breeze_redirect'] = 'breezenotisettings';

		// Get the user settings.
		$userSettings = $this['query']->getUserSettings($context['member']['id']);

		// Create the form.
		$form = $this['form'];

		// Group all these values into an array.
		$form->setFormName('breezeSettings');

		// Notification settings
		$form->addSection('name_settings');

		// How many seconds before closing the notifications?
		$form->addText(
			'clear_noti',
			!empty($userSettings['clear_noti']) ? $userSettings['clear_noti'] : 0,
			3,
			3
		);

		// Noti on status
		$form->addCheckBox(
			'noti_on_status',
			!empty($userSettings['noti_on_status']) ? true : false
		);

		// Noti on comment
		$form->addCheckBox(
			'noti_on_comment',
			!empty($userSettings['noti_on_comment']) ? true : false
		);

		// Noti on comment for profile owner
		$form->addCheckBox(
			'noti_on_comment_owner',
			!empty($userSettings['noti_on_comment_owner']) ? true : false
		);

		// Noti on mention
		$form->addCheckBox(
			'noti_on_mention',
			!empty($userSettings['noti_on_mention']) ? true : false
		);

		// Send the form to the template
		$context['Breeze']['UserSettings']['Form'] = $form->display();
	}

	/**
	 * BreezeUser::notifications()
	 *
	 * Shows all notifications, both read and unread, options for read, unread and delete.
	 * @return
	 */
	function notifications()
	{
		global $context, $user_info, $scripturl, $options;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$context['Breeze']['settings']['owner'] = $this['query']->getUserSettings($context['member']['id']);

		// Globals...
		$data = Breeze::data('request');

		// We kinda need all this stuff, don't ask why, just nod your head...
		$query = $this['query'];
		$notifications = $this['notifications'];
		$tools = $this['tools'];
		$tempNoti = $query->getNotificationByReceiver($context['member']['id'], true);

		// Load the users data
		if (!empty($tempNoti['users']))
			$tools->loadUserInfo($tempNoti['users']);

		// Pass the info to the template
		if ($notifications->prepare($context['member']['id'], true))
			$context['Breeze']['noti'] = $notifications->getMessages();

		// Tell everyone where we've been
		$context['Breeze']['comingFrom'] = 'profile';

		// Set all the page stuff
		$context['sub_template'] = 'user_notifications';
		$context['page_title'] = $tools->text('noti_title');
		$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=notifications;u=' . $context['member']['id'];
		$context['Breeze']['is_log'] = false;

		// Print some jQuery goodies...
		$context['insert_after_template'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
			jQuery(document).on(\'change\', \'input[name="check_all"]\',function() {
				jQuery(\'.idNoti\').prop("checked" , this.checked);
			});
		// ]]></script>';
	}

	/**
	 * BreezeUser::notiLogs()
	 *
	 * Shows all logs, option to mass delete them.
	 * @return
	 */
	function notiLogs()
	{
		global $context, $user_info, $scripturl, $options;

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		$context['Breeze']['settings']['owner'] = $this['query']->getUserSettings($context['member']['id']);

		// Globals...
		$data = Breeze::data('request');

		// We kinda need all this stuff, don't ask why, just nod your head...
		$logs = $this['log']->getActivity($context['member']['id']);
		$tools = $this['tools'];

		// Pass the info to the template
		$context['Breeze']['noti'] = $logs;

		// Tell everyone where we've been
		$context['Breeze']['comingFrom'] = 'profile';

		// And tell this is a log page...
		$context['Breeze']['is_log'] = true;

		// Set all the page stuff
		$context['sub_template'] = 'user_notifications';
		$context['page_title'] = $tools->text('user_notilogs_name');
		$context['member']['is_owner'] = $context['member']['id'] == $user_info['id'];
		$context['canonical_url'] = $scripturl . '?action=profile;area=breezelogs;u=' . $context['member']['id'];

		// Print some jQuery goodies...
		$context['insert_after_template'] .= '
		<script type="text/javascript"><!-- // --><![CDATA[
			jQuery(document).on(\'change\', \'input[name="check_all"]\',function() {
				jQuery(\'.idNoti\').prop("checked" , this.checked);
			});
		// ]]></script>';
	}

	/**
	 * breezeBuddyRequest()
	 *
	 * Replaces the standard buddy action in SMF.
	 * @return
	 */
	function breezeBuddyRequest()
	{
		global $context, $user_info, $scripturl, $memberContext, $breezeController;

		// Do a quick check to ensure people aren't getting here illegally!
		if (!$context['member']['is_owner'])
			fatal_lang_error('no_access', false);

		loadtemplate(Breeze::$name);
		loadtemplate(Breeze::$name .'Functions');

		if (empty($breezeController))
			$breezeController = new BreezeController();

		$context['Breeze']['settings']['owner'] = $query->getUserSettings($context['member']['id']);

		// Load all we need
		$buddies = $breezeController->get('buddy');
		$data = Breeze::data('request');
		$query = $this['query'];

		// Set all the page stuff
		$context['sub_template'] = 'Breeze_buddy_list';
		$context['page_title'] = $tools->text('noti_title');
		$context['canonical_url'] = $scripturl . '?action=profile;area=breezebuddies;u=' . $context['member']['id'];

		// Show a nice message for confirmation
		if ($data->validate('inner') == true)
			switch ($globals->getRaw('inner'))
			{
				case 1:
					$context['Breeze']['inner_message'] = $tools->text('buddyrequest_confirmed_inner_message');
					break;
				case 2:
					$context['Breeze']['inner_message'] = $tools->text('buddyrequest_confirmed_inner_message_de');
					break;
				default:
					$context['Breeze']['inner_message'] = '';
					break;
			}

		else
			$context['Breeze']['inner_message'] = '';

		// Send the buddy request(s) to the template
		$context['Breeze']['Buddy_Request'] = $buddies->showBuddyRequests($context['member']['id']);

		if ($data->validate('from') == true && $data->validate('message') == 'confirm' && $user_info['id'] != $globals->get('from'))
		{
			// Load Subs-Post to use sendpm
			Breeze::load('Subs-Post');

			// ...and a new friendship is born, yay!
			$user_info['buddies'][] = $globals->get('from');
			$context['Breeze']['user_info'][$globals->get('from')]['buddies'][] = $user_info['id'];

			// Update both users buddy array.
			updateMemberData($user_info['id'], array('buddy_list' => implode(',', $user_info['buddies'])));
			updateMemberData($globals->get('from'), array('buddy_list' => implode(',', $context['Breeze']['user_info'][$globals->get('from')]['buddies'])));

			// Send a pm to the user
			$recipients = array(
				'to' => array($globals->get('from')),
				'bcc' => array(),
			);

			// @todo make this a guest account
			$from = array(
				'id' => $user_info['id'],
				'name' => $user_info['name'],
				'username' => $user_info['username'],
			);

			// @todo let the user to send a customized message/title
			$subject = $tools->text('buddyrequest_confirmed_subject');
			$message = $tools->text('buddyrequest_confirmed_message');
			$noti = $globals->get('noti');

			sendpm($recipients, $subject, $message, false, $from);

			// Destroy the notification
			$query->deleteNoti($noti, $user_info['id']);

			// Redirect back to the profile buddy request page
			redirectexit('action=profile;area=breezebuddies;inner=1;u=' . $user_info['id']);
		}

		// Declined?
		elseif ($data->validate('message') == 'decline')
		{
			$noti = $globals->get('noti');

			// Destroy the notification
			$query->deleteNoti($noti, $user_info['id']);

			// Redirect back to the profile buddy request page
			redirectexit('action=profile;area=breezebuddies;inner=2;u=' . $user_info['id']);
		}
	}

	/* // Show all possible message regarding the buddy system
	function breezeBuddyMessage()
	{
		global $context, $scripturl, $breezeController;

		loadtemplate('BreezeBuddy');

		// Get the params
		$data = Breeze::data('request');
		$message = $globals->get('message')

		// Get on the guest list!
		if (empty($message))
			redirectexit('action=profile');

		// Lets stuff the memory!
		if (empty($breezeController))
			$breezeController = new BreezeController();

		// Set all the page stuff
		$context['sub_template'] = 'Breeze_buddy_message';
		$context['page_title'] = $tools->text('noti_title');
		$context['canonical_url'] = $scripturl . '?action=breezebuddyrequest';

		// Linktree here someday!

	} */

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
