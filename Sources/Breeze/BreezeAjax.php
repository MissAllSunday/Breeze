<?php

/**
 * BreezeAjax
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
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

class BreezeAjax
{
	protected $noJS = false;
	protected $redirectURL = '';
	public $subActions = array();

	/**
	 * BreezeAjax::__construct()
	 *
	 * @return
	 */
	public function __construct($settings, $text, $query, $notifications, $parser, $mention)
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

		// Set an empty var, by default lets pretend everything went wrong...
		$this->_response = '';
	}

	/**
	 * BreezeAjax::call()
	 *
	 * @return
	 */
	public function call()
	{
		// Handling the subactions
		$sglobals = Breeze::sGlobals('get');

		// Safety first, hardcode the actions
		$this->subActions = array(
			'post' => 'post',
			'postcomment' => 'postComment',
			'delete' => 'delete',
			'notimark' => 'notimark',
			'notidelete' => 'notidelete',
			'usersmention' => 'usersMention',
		);

		// Not using JavaScript?
		if ($sglobals->getValue('js') == false)
			$this->noJS = true;

		// Temporarily turn this into a normal var
		$call = $this->subActions;

		// Does the subaction even exist?
		if (isset($call[$sglobals->getValue('sa')]))
		{
			// This is somehow ugly.
			$this->$call[$sglobals->getValue('sa')]();

			// Send the response back to the browser
			$this->returnResponse();
		}

		// Sorry pal...
		else
			fatal_lang_error ($this->_text->getText('error_no_valid_action'));
	}

	/**
	 * BreezeAjax::post()
	 *
	 * @return
	 */
	public function post()
	{
		checkSession('request', '', false);

		// Get the data
		$this->_data = Breeze::sGlobals('request');

		// Sorry, try to play nice next time
		if (!$this->_data->getValue('owner_id') || !$this->_data->getValue('poster_id') || !$this->_data->getValue('content'))
			return;

		// Do this only if there is something to add to the database
		if ($this->_data->validateBody('content'))
		{
			// You aren't allowed in here, let's show you a nice static page...
			$this->permissions('postStatus', $this->_data->getValue('owner_id'));

			$body = $this->_data->getValue('content');

			$params = array(
				'owner_id' => $this->_data->getValue('owner_id'),
				'poster_id' => $this->_data->getValue('poster_id'),
				'time' => time(),
				'body' => $this->_mention->preMention($body),
			);

			// Store the status
			$this->_query->insertStatus($params);

			// Get the newly created status, we just need the id
			$newStatus = $this->_query->getLastStatus();

			// Set the ID
			$params['id'] = $newStatus['status_id'];

			// Build the notifications
			$this->_mention->mention(array(
				'wall_owner' => $this->_data->getValue('owner_id'),
				'wall_poster' => $this->_data->getValue('poster_id'),
				'status_id' => $params['id'],
			));

			// Parse the content
			$params['body'] = $this->_parser->display($params['body']);

			// The status was added, build the server response
			$display = new BreezeDisplay($params, 'status');

			// Send the data back to the browser
			$this->_response = array(
				'type' => 'ok',
				'data' => $display->HTML()
			);

			// Se the redirect url
			if (true == $this->noJS)
				$this->redirectURL = 'action=profile;u='. $this->_data->getValue('owner_id');

			// End it
			return;
		}

		// There was an error
		else
			$this->_response = false;
	}

	/**
	 * BreezeAjax::postComment()
	 *
	 * @return
	 */
	public function postComment()
	{
		global $scripturl;

		checkSession('request', '', false);

		$this->_data = Breeze::sGlobals('request');

		// Sorry, try to play nice next time
		if (!$this->_data->getValue('status_owner_id') || !$this->_data->getValue('status_owner_id') || !$this->_data->getValue('poster_comment_id') || !$this->_data->getValue('profile_owner_id') || !$this->_data->getValue('content'))
			return;

		// Load all the things we need
		$temp_id_exists = $this->_query->getSingleValue('status', 'id', $this->_data->getValue('status_id'));

		// The status do exists and the data is valid
		if ($this->_data->validateBody('content') && !empty($temp_id_exists))
		{
			// You aren't allowed in here, let's show you a nice static page...
			$this->permissions('postComments', $this->_data->getValue('profile_owner_id'));

			$body = $this->_data->getValue('content');

			// Build the params array for the query
			$params = array(
				'status_id' => $this->_data->getValue('status_id'),
				'status_owner_id' => $this->_data->getValue('status_owner_id'),
				'poster_id' => $this->_data->getValue('poster_comment_id'),
				'profile_owner_id' => $this->_data->getValue('profile_owner_id'),
				'time' => time(),
				'body' => $this->_mention->preMention($body)
			);

			// Store the comment
			$this->_query->insertComment($params);

			// Once the comment was added, get it's ID from the DB
			$new_comment = $this->_query->getLastComment();

			// Set the ID
			$params['id'] = $new_comment['comments_id'];

			// build the notification
			$this->_mention->mention(array(
				'wall_owner' => $this->_data->getValue('profile_owner_id'),
				'wall_poster' => $this->_data->getValue('poster_comment_id'),
				'wall_status_owner' => $this->_data->getValue('status_owner_id'),
				'comment_id' => $params['id'],
				'status_id' => $this->_data->getValue('status_id'),
			));

			// Parse the content
			$params['body'] = $this->_parser->display($params['body']);

			// The comment was added, build the server response
			$display = new BreezeDisplay($params, 'comment');

			// Send the data back to the browser
			$this->_response = array(
				'type' => 'ok',
				'data' => $display->HTML()
			);

			// Se the redirect url
			if (true == $this->noJS)
				$this->redirectURL = 'action=profile;u='. $this->_data->getValue('profile_owner_id');

			// End it
			return;
		}

		// There was an error
		else
			$this->_response = false;
	}

	/**
	 * BreezeAjax::delete()
	 *
	 * Handles the deletion of both comments an status
	 * @return
	 */
	public function delete()
	{
		checkSession('request', '', false);

		// Get the global vars
		$this->_data = Breeze::sGlobals('request');

		// Get the data
		if ($this->_data->getValue('id') != false)
		{
			// You aren't allowed in here, let's show you a nice message error...
			$this->permissions('delete'. ucfirst($this->_data->getValue('type')), false);

			$temp_id_exists = $this->_query->getSingleValue(
				$this->_data->getValue('type') == 'status' ? 'status' : 'comments',
				'id',
				$this->_data->getValue('id')
			);

			// Do this only if the message wasn't deleted already
			if (!empty($temp_id_exists))
			{
				$type = 'delete'. ucfirst($this->_data->getValue('type'));
				$this->_query->$type($this->_data->getValue('id'));

				// Send the data back to the browser
				$this->_response = array(
					'data' => $this->_text->getText('success_delete'),
					'type' => 'ok'
				);

				// Se the redirect url
				if (true == $this->noJS)
					$this->redirectURL = 'action=profile';

				// End it!
				return;
			}

			// Tell them someone has deleted the message already
			else
			{
				$this->_response = array(
					'data' => $this->_text->getText('already_deleted'),
					'type' => 'deleted'
				);

				// Se the redirect url
				if (true == $this->noJS)
					$this->redirectURL = 'action=profile;m=message_deleted';

				// Don't forget to end it
				return;
			}

			unset($temp_id_exists);
		}

		// Either way, pass the response
		$this->_response = false;
	}

	/**
	 * BreezeAjax::notimark()
	 *
	 * Mark a notification as read
	 * @return
	 */
	public function notimark()
	{
		checkSession('request', '', false);

		// Get the global vars
		$this->_data = Breeze::sGlobals('request');

		// Get the data
		$noti = $this->_data->getValue('content');
		$user = $this->_data->getValue('user');

		// Is this valid data?
		if (empty($noti) || empty($user))
		{
			if (true == $this->noJS)
				$this->redirectURL = 'action=profile;area=breezenoti;u='. $user .';m=noti_novalid';

			// Stop the process
			return;
		}

		// We must make sure this noti really exists, we just must!!!
		$noti_temp = $this->_notifications->getToUser($user, true);

		if (empty($noti_temp) || !isset($noti_temp[$noti]))
		{
			// Tell the user about it
			if (true == $this->noJS)
				$this->redirectURL = 'action=profile;area=breezenoti;u='. $user .';m=noti_markasreaddeleted';

			// Stop the process
			return;
		}

		else
		{
			// All is good, mark this as read
			$this->_query->markNoti($noti, $user, $noti_temp[$noti]['viewed']);
			$this->_response = array(
				'data' => $this->_text->getText('noti_markasread_after'),
				'type' => 'ok'
			);

			// Se the redirect url
			if (true == $this->noJS)
				$this->redirectURL = 'action=profile;area=breezenoti;u='. $user .';m=noti_'. (!empty($noti_temp[$noti]['viewed']) ? 'un' : '') .'markasread;';

			// If we manage to get this far we don't have to worry about stoping the process, still, safety first!
			return;
		}
	}

	/**
	 * BreezeAjax::notidelete()
	 *
	 * Deletes a notification by ID
	 * @return
	 */
	public function notidelete()
	{
		checkSession('request', '', false);

		// Get the global vars
		$this->_data = Breeze::sGlobals('request');

		// Get the data
		$noti = $this->_data->getValue('content');
		$user = $this->_data->getValue('user');

		// Is this valid data?
		if (empty($noti) || empty($user))
			return;

		// We must make sure this noti really exists, we just must!!!
		$noti_temp = $this->_notifications->getToUser($user);

		if (empty($noti_temp) || !array_key_exists($noti, $noti_temp))
		{
			$this->_response = array(
				'data' => $this->_text->getText('already_deleted_noti'),
				'type' => 'deleted'
			);

			// Se the redirect url
			if (true == $this->noJS)
				$this->redirectURL = 'action=profile;area=breezenoti;m=noti_delete;u='. $user;

			return;
		}

		else
		{
			// All is good, delete it
			$this->_query->deleteNoti($noti, $user);
			$this->_response = array(
				'data' => $this->_text->getText('noti_delete_after'),
				'type' => 'ok'
			);

			// Se the redirect url
			if (true == $this->noJS)
				$this->redirectURL = 'action=profile;area=breezenoti;u='. $user;

			return;
		}
	}

	/**
	 * BreezeAjax::usersMention()
	 *
	 * Creates an array of searchable users
	 * @return void
	 */
	protected function usersMention()
	{
		// For testing purposes only, just pass the result directly
		$this->_response = $this->_query->userMention();
		return;
	}

	/**
	 * BreezeAjax::returnResponse()
	 *
	 * Returns a json encoded response back to the browser
	 * @param array The array that will be sent to the browser
	 * @return
	 */
	protected function returnResponse()
	{
		global $modSettings;

		// No JS? fine... jut send them to whatever url they're from
		if (true == $this->noJS && !empty($this->redirectURL))
		{
			redirectexit($this->redirectURL);

			// Safety first!
			return;
		}

		// Kill anything else
		ob_end_clean();

		if (!empty($modSettings['enableCompressedOutput']))
			@ob_start('ob_gzhandler');

		else
			ob_start();

		// Send the header
		header('Content-Type: application/json');

		// Is there a custom message? Use it
		if (!empty($this->_response))
			echo json_encode($this->_response);

		// No? then show the standard error message
		else
			echo json_encode(array(
				'data' => $this->_text->getText('error_message'),
				'type' => 'error'
			));

		// Done
		obExit(false);
	}

	protected function permissions($type = false, $owner_id = false)
	{
		global $user_info;

		// Profile owner?
		$is_owner = !empty($owner_id) ? ($owner_id == $user_info['id']) : true;

		// Check for the proper permission
		if (!$is_owner && !empty($type))
			isAllowedTo('breeze_'. $type);


		// Just a generic "is owner"
		else
			if(!$is_owner)
				fatal_lang_error($this->_text->getText('error_no_valid_action'));
	}
}
