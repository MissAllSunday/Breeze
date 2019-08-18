<?php

declare(strict_types=1);

/**
 * BreezeAjax
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

class BreezeBuddy
{
	protected $_app;
	protected $_userReceiver = 0;
	protected $_userSender = 0;
	protected $_senderConfirm = 0;
	protected $_receiverConfirm = 0;
	protected $_call = '';
	protected $_data = false;

    /**
     * BreezeBuddy::__construct()
     *
     * Sets all the needed vars, loads the language file.
     * @param $app
     */
	public function __construct(Breeze $app)
	{
		$this->_app = $app;

		// Needed to show some strings.
		loadLanguage(Breeze::NAME);

		// Easily delete our alerts.
		$this->_deleteAlert = function($alertID = 0, $userID = 0){
			global $sourcedir;

			if (empty($alertID) || empty($userID))
				return false;

			require_once($sourcedir . '/Profile-Modify.php');

			return alert_delete($alertID, $userID);
		};
	}

	/**
	 * BreezeAjax::call()
	 *
	 * Calls the right method for each subaction, calls setResponse().
	 * @see BreezeBuddy::setResponse()
	 * @return void
	 */
	public function call()
	{
		global $user_info;

		checkSession('request');
		is_not_guest();

		$this->_data = $this->_app->data('request');
		$this->_call = $this->_data->get('sa');
		$this->_alertID = $this->_data->get('aid');
		$subActions = [
		    'confirm',
		    'confirmed',
		    'decline',
		    'block',
		];

		// Figure it out what are we gonna do... check the subactions first!
		if ($this->_call && in_array($this->_call, $subActions))
		{
			// We need a "sender" ID.
			$this->_senderConfirm = $this->_data->get('sender');
			$this->_receiverConfirm = $user_info;

			if (!$this->_senderConfirm)
				fatal_lang_error('no_access', false);

			$this->{$this->_data->get('sa')}();
		}

		// An standard add/delete call.
		else
		{
			// To avoid confusions, set these properties here.
			$this->_userReceiver = $this->_data->get('u');
			$this->_userSender = $user_info;

			// Make sure we got something.
			if (!$this->_userReceiver)
				fatal_lang_error('no_access', false);

			// Remove if it's already there...
			if (in_array($this->_userReceiver, $this->_userSender['buddies']))
				$this->delete();

			// ...or add if it's not and if it's not you.
			elseif ($this->_userSender != $this->_userReceiver)
				$this->add();
		}

		// Anyway, show a nice landing page.
		$this->setResponse();
	}

	public function add()
	{
		global $context;

		// Ran the request through some checks.
		if ($this->check() == true)
			return;

		// Do we want to show the message or the confirmation page?
		if ($this->_data->get('msent'))
		{
			// Get the message and store it on the receiver's petition list.
			$buddyMessage = $this->_data->get('breezeBuddyMessage');

			// Gotta add the sender to the receiver's petition list...
			$this->receiverSettings['petitionList'][$this->_userSender['id']] = !empty($buddyMessage['content']) ? $buddyMessage['content'] : '';

			// Store the new petition list.
			$this->_app['query']->insertUserSettings(['petitionList' => $this->receiverSettings['petitionList']], $this->_userReceiver);

			// Create a nice alert to let the user know you want to be his/her buddy!
			$this->_app['query']->insertNoti([
			    'receiver_id' => $this->_userReceiver,
			    'id_member' => $this->_userSender['id'],
			    'member_name' => $this->_userSender['username'],
			    'time' => time(),
			    'text' => 'confirm',
			    'sender' => $this->_userSender['id'],
			    'receiver' => $this->_userReceiver,
			], 'buddyConfirm');

			// Get the receiver's link
			$this->_app['tools']->loadUserInfo($this->_userReceiver);

			// I actually need to use $context['Breeze']['user_info'] a lot more...
			return $this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_confirm'), [
			    'receiver' => $context['Breeze']['user_info'][$this->_userReceiver]['linkFacebox'],
			]);
		}

		$context['sub_template'] = 'buddy_message';

		$this->_app['tools']->loadUserInfo($this->_userReceiver);

		$this->_app['form']->setOptions([
		    'name' => 'breezeBuddyMessage',
		    'url' => $this->_app['tools']->scriptUrl . '?action=buddy;msent=1;u=' . $this->_userReceiver,
		    'character_set' => $context['character_set'],
		    'title' => $this->_app['tools']->text('buddy_title'),
		]);

		// The actual textarea...
		$this->_app['form']->addTextArea([
		    'name' => 'content',
		    'value' => '',
		    'size' => ['rows' => 10, 'cols' => 60, 'maxLength' => 2048],
		    'fullText' => $this->_app['tools']->text('buddy_message'),
		    'fullDesc' => $this->_app['tools']->parser($this->_app['tools']->text('buddy_message_desc'), [
		        'receiver' => $context['Breeze']['user_info'][$this->_userReceiver]['linkFacebox'],
		    ]),
		]);

		// Session stuff.
		$this->_app['form']->addHiddenField($context['session_var'], $context['session_id']);
		$this->_app['form']->addButton(['name' => 'submit']);

		$this->_response = $this->_app['form']->display();
	}

	/**
	 * BreezeAjax::check()
	 *
	 * Checks if the receiver does indeed want you as his/her buddy.
	 * @return boolean True if you are blocked, false if you're a good boy/girl!
	 */
	protected function check()
	{
		global $context;

		// Get the receiver's user settings.
		$this->receiverSettings = $this->_app['query']->getUserSettings($this->_userReceiver);

		// Theres always the chance you are already friends...
		if (in_array($this->_userSender['id'], $this->receiverSettings['buddiesList']))
		{
			// Perhaps it is YOU the one who doesn't have the receiver as friend? if so, just add the receiver to your buddy list.
			if (!in_array($this->_userReceiver, $this->_userSender['buddies']))
			{
				$this->_userSender['buddies'][] = $this->_userReceiver;
				updateMemberData($this->_userSender['id'], ['buddy_list' => implode(',', $this->_userSender['buddies'])]);
			}

			// Tell the user you are already friends.
			$this->_app['tools']->loadUserInfo($this->_userReceiver);

			$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_already_buddy'), [
			    'receiver' => $context['Breeze']['user_info'][$this->_userReceiver]['linkFacebox'],
			]);

			return true;
		}

		// Are you on his/her ignore/block list?
		if ((!empty($this->receiverSettings['ignoredList']) && in_array($this->_userSender['id'], $this->receiverSettings['ignoredList'])) || (!empty($this->receiverSettings['blockList']) && in_array($this->_userSender['id'], $this->receiverSettings['blockList'])))
		{
			$this->_response = $this->_app['tools']->text('buddy_blocked');

			return true;
		}

		// So, you're not blocked, lets see if you already sent a petition.
		if (!empty($this->receiverSettings['petitionList']) && in_array($this->_userSender['id'], array_keys($this->receiverSettings['petitionList'])))
		{
			$this->_app['tools']->loadUserInfo($this->_userReceiver);

			// Let this user know that an alert has already been sent.
			$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_already_sent'), [
			    'receiver' => $context['Breeze']['user_info'][$this->_userReceiver]['linkFacebox'],
			]);

			return true;
		}

		// And you passed the test!
		return false;
	}

	public function delete()
	{
		global $user_info, $context;

		// Easy, just delete the entry and be done with it.
		$user_info['buddies'] = array_diff($user_info['buddies'], [$this->_userReceiver]);

		// Update the settings.
		updateMemberData($user_info['id'], ['buddy_list' => implode(',', $user_info['buddies'])]);

		// Now, get the receiver's buddy list and delete the sender from it.
		$this->receiverSettings = $this->_app['query']->getUserSettings($this->_userReceiver);

		if (!empty($this->receiverSettings['buddiesList']))
		{
			$this->receiverSettings['buddiesList'] = array_diff($this->receiverSettings['buddiesList'], [$this->_userSender['id']]);
			updateMemberData($this->_userReceiver, ['buddy_list' => implode(',', $this->receiverSettings['buddiesList'])]);
		}

		// Get the receiver's data.
		$this->_app['tools']->loadUserInfo($this->_userReceiver);

		$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_delete_done'), [
		    'receiver' => $context['Breeze']['user_info'][$this->_userReceiver]['linkFacebox'],
		]);
	}

	// When the petitioner wants to add the receiver as friend
	public function confirm()
	{
		global $context;

		// Load the icon's css.
		loadCSSFile('//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', ['external' => true]);

		$context['sub_template'] = 'buddy_message';

		// Load the sender's data.
		$this->_app['tools']->loadUserInfo($this->_senderConfirm);

		// Get this user's buddy message
		$this->_receiverConfirmSettings = $this->_app['query']->getUserSettings($this->_receiverConfirm['id']);

		$receiverPetiList = $this->_receiverConfirmSettings['petitionList'];

		// If there is a message, show it, else show a generic standard message.
		$context['buddyMessage'] = !empty($receiverPetiList) && !empty($receiverPetiList[$this->_senderConfirm]) ? un_htmlspecialchars(parse_bbc($receiverPetiList[$this->_senderConfirm])) : false;

		// An explicatory title.
		$context['buddy_message_title'] = $this->_app['tools']->parser($this->_app['tools']->text('buddy_sender_message_title'), [
		    'sender' => $context['Breeze']['user_info'][$this->_senderConfirm]['linkFacebox'],
		]);

		// Set a nice an unique page title.
		$context['page_title'] = $this->_app['tools']->parser($this->_app['tools']->text('buddy_chose_title'), [
		    'sender' => $context['Breeze']['user_info'][$this->_senderConfirm]['name'],
		]);

		// Prepare the options.
		$confirm = $this->_app['tools']->scriptUrl . '?action=buddy;sa=confirmed;sender=' . $this->_senderConfirm . (!empty($this->_alertID) ? ';aid=' . $this->_alertID : '');
		$decline = $this->_app['tools']->scriptUrl . '?action=buddy;sa=decline;sender=' . $this->_senderConfirm . (!empty($this->_alertID) ? ';aid=' . $this->_alertID : '');

		$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_chose'), [
		    'href_confirm' => $confirm,
		    'href_decline' => $decline,
		]);
	}

	// When receiver confirmed the friendship!
	public function confirmed()
	{
		global $user_info;

		// Royal Entrance Fanfare please!
		$senderSettings = $this->_app['query']->getUserSettings($this->_senderConfirm);
		$this->receiverSettings = $this->_app['query']->getUserSettings($this->_receiverConfirm['id']);

		// Gotta use $user_info instead of $this->_receiverConfirm because we want this change to take effect immediately!
		if (empty($user_info['buddies']) || (!empty($user_info['buddies']) && !in_array($this->_senderConfirm, $user_info['buddies'])))
		{
			$user_info['buddies'][] = $this->_senderConfirm;
			updateMemberData($user_info['id'], ['buddy_list' => implode(',', $user_info['buddies'])]);
		}

		// Now update the sender's buddy list. Gotta check if the receiver isn't already there BUT we need to be careful since the sender's buddy list can be empty!
		if (empty($senderSettings['buddyList']) || (!empty($senderSettings['buddyList']) && !in_array($this->_receiverConfirm['id'], $senderSettings['buddyList'])))
		{
			$senderSettings['buddyList'][] = $this->_receiverConfirm['id'];
			updateMemberData($this->_senderConfirm, ['buddy_list' => implode(',', $senderSettings['buddyList'])]);
		}

		// Let the sender know the receiver gladly accepted the invitation.
		$this->_app['query']->insertNoti([
		    'receiver_id' => $this->_senderConfirm,
		    'id_member' => $this->_receiverConfirm['id'],
		    'member_name' => $this->_receiverConfirm['username'],
		    'time' => time(),
		    'text' => 'confirmed',
		    'sender' => $this->_senderConfirm,
		    'receiver' => $this->_receiverConfirm['id'],
		], 'buddyConfirm');

		// Does the sender wants the world to take note of this great achievement?
		if (!empty($senderSettings['alert_buddyConfirmation']))
			$this->_app['query']->createLog([
			    'member' => $this->_senderConfirm,
			    'content_type' => 'buddyConfirmed',
			    'content_id' => 0,
			    'time' => time(),
			    'extra' => [
			        'sender' => $this->_senderConfirm,
			        'receiver' => $this->_receiverConfirm['id'],
			        'toLoad' => [$this->_receiverConfirm['id'], $this->_senderConfirm],
			    ],
			]);

		// How about the receiver?
		if (!empty($this->receiverSettings['alert_buddyConfirmation']))
			$this->_app['query']->createLog([
			    'member' => $this->_receiverConfirm['id'],
			    'content_type' => 'buddyConfirmed',
			    'content_id' => 0,
			    'time' => time(),
			    'extra' => [
			        'sender' => $this->_senderConfirm,
			        'receiver' => $this->_receiverConfirm['id'],
			        'toLoad' => [$this->_receiverConfirm['id'], $this->_senderConfirm],
			    ],
			]);

		// Can't directly call closures from object properties :(
		if ($this->_alertID)
		{
			$_deleteAlert = $this->_deleteAlert;

			// Delete the alert.
			$_deleteAlert($this->_alertID, $user_info['id']);
		}

		// Lastly, set a nice confirmed message.
		$this->_response = $this->_app['tools']->text('buddy_confirmed_done');
	}

	// When the receiver user denies the request DUH!
	public function decline()
	{
		global $user_info;

		// Can't directly call closures from object properties :(
		if ($this->_alertID)
		{
			$_deleteAlert = $this->_deleteAlert;

			// Delete the alert.
			$_deleteAlert($this->_alertID, $user_info['id']);
		}

		// Gotta check if any of both users were added as friends prior to this invite, if so, remove it.
		$user_info['buddies'] = array_diff($user_info['buddies'], [$this->_senderConfirm]);

		// Update the settings.
		updateMemberData($user_info['id'], ['buddy_list' => implode(',', $user_info['buddies'])]);

		// Now, get the receiver's buddy list and delete the receiver from it.
		$senderSettings = $this->_app['query']->getUserSettings($this->_senderConfirm);

		if (!empty($senderSettings['buddiesList']))
		{
			$this->receiverSettings['buddiesList'] = array_diff($this->receiverSettings['buddiesList'], [$user_info['id']]);
			updateMemberData($this->_senderConfirm, ['buddy_list' => implode(',', $senderSettings['buddiesList'])]);
		}

		// Offer an option to block this person
		$this->_response = $this->_app['tools']->parser($this->_app['tools']->text('buddy_decline'), [
		    'href' => $this->_app['tools']->scriptUrl . '?action=buddy;sa=block;sender=' . $this->_senderConfirm,
		]);
	}

	// When you want to block the sender from ever invite you again!
	public function block()
	{
		// Get the current user settings.
		$currentSettings = $this->_app['query']->getUserSettings($this->_receiverConfirm['id']);

		// Add this person to the user's "block" list.
		$blockList = !empty($currentSettings['blockList']) ? $currentSettings['blockList'] : [];

		// There might be the possibility that you already are blocking this user...
		if (in_array($this->_senderConfirm, $blockList))
			return $this->_response = $this->_app['tools']->text('buddy_already_blocked');

		// Add the user.
		$blockList[] = $this->_senderConfirm;

		$this->_app['query']->insertUserSettings(['blockList' => implode(',', $blockList)], $this->_receiverConfirm['id']);

		$this->_response = $this->_app['tools']->text('buddy_blocked_done');
	}

	// Whatever the action performed, show a landing "done" page.
	protected function setResponse()
	{
		global $context;

		// Get the template and language files.
		loadLanguage(Breeze::NAME);
		loadtemplate(Breeze::NAME . 'Functions');

		// All the generic stuff.
		if (!isset($context['page_title']))
			$context['page_title'] = $this->_app['tools']->text('buddy_title');

		if (!isset($context['sub_template']))
			$context['sub_template'] = 'buddy_request';

		$context['linktree'][] = [
		    'url' => $this->_app['tools']->scriptUrl . '?action=buddy' . (!empty($this->_call) ? ';sa=' . $this->_call : '') . (!empty($this->_senderConfirm) ? ';sender=' . $this->_senderConfirm : '') . (!empty($this->_userReceiver) ? ';u=' . $this->_senderConfirm : '') . ';' . $context['session_var'] . '=' . $context['session_id'] . (!empty($this->_alertID) ? ';aid=' . $this->_alertID : ''),
		    'name' => $context['page_title'],
		];

		$context['response'] = !empty($this->_response) ? $this->_response : '';
	}
}
