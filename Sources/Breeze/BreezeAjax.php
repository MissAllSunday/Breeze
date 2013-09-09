<?php

/**
 * BreezeAjax
 *
 * The purpose of this file is
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
			'multiNoti' => 'multiNoti',
			'usersmention' => 'usersMention',
			'cleanlog' => 'cleanLog'
		);

		// Build the correct redirect URL
		$this->comingFrom = $sglobals->getValue('rf') == true ? $sglobals->getValue('rf') : 'wall';

		// Master setting is off, back off!
		if (!$this->_settings->enable('admin_settings_enable'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		// Not using JavaScript?
		if (!$sglobals->getValue('js'))
			$this->noJS = true;

		// Temporarily turn this into a normal var
		$call = $this->subActions;

		// Does the subaction even exist?
		if (isset($call[$sglobals->getValue('sa')]))
		{
			// This is somehow ugly but its faster.
			$this->$call[$sglobals->getValue('sa')]();

			// Send the response back to the browser
			$this->returnResponse();
		}

		// Sorry pal...
		else
			fatal_lang_error('Breeze_error_no_valid_action', false);
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
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $this->_data->getValue('owner_id'),
			));

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

			// Maybe a last minute change before inserting the new status?
			call_integration_hook('integrate_breeze_before_insertStatus', array(&$params));

			// Store the status
			$params['id'] = $this->_query->insertStatus($params);

			// All went good or so it seems...
			if (!empty($params['id']))
			{
				// Build the notifications
				$this->_mention->mention(
					array(
						'wall_owner' => $this->_data->getValue('owner_id'),
						'wall_poster' => $this->_data->getValue('poster_id'),
						'status_id' => $params['id'],),
					array(
							'name' => 'status',
							'id' => $params['id'],)
				);

				// Parse the content
				$params['body'] = $this->_parser->display($params['body']);

				// The status was inserted, tell everyone!
				call_integration_hook('integrate_breeze_after_insertStatus', array($params));

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'success',
					'message' => 'published',
					'data' => $this->_display->HTML($params, 'status'),
					'owner' => $this->_data->getValue('owner_id'),
				));
			}

			// Something went terrible wrong!
			else
				return $this->setResponse(array('owner' => $this->_data->getValue('owner_id'),));
		}

		// There was an (generic) error
		else
			return $this->setResponse(array('owner' => $this->_data->getValue('owner_id'),));
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

		// Trickery, there's always room for moar!
		$status_id = $this->_data->getValue('status_id');
		$status_owner_id = $this->_data->getValue('status_owner_id'. ($this->noJS == true ? $status_id : ''));
		$poster_comment_id = $this->_data->getValue('poster_comment_id'. ($this->noJS == true ? $status_id : ''));
		$profile_owner_id = $this->_data->getValue('profile_owner_id'. ($this->noJS == true ? $status_id : ''));
		$content = $this->_data->getValue('content');

		// Sorry, try to play nice next time
		if (!$status_owner_id || !$poster_comment_id || !$profile_owner_id || !$content)
			if (true == $this->noJS)
				return $this->setResponse(array(
					'message' => 'wrong_values',
					'type' => 'error',
					'owner' => $this->_data->getValue('owner_id'),
				));

		// Load all the things we need
		$temp_id_exists = $this->_query->getSingleValue('status', 'status_id', $status_id);

		// The status do exists and the data is valid
		if ($this->_data->validateBody('content') && !empty($temp_id_exists))
		{
			// You aren't allowed in here, let's show you a nice static page...
			$this->permissions('postComments', $profile_owner_id);

			$body = $this->_data->getValue('content');

			// Build the params array for the query
			$params = array(
				'status_id' => $status_id,
				'status_owner_id' => $status_owner_id,
				'poster_id' => $poster_comment_id,
				'profile_owner_id' => $profile_owner_id,
				'time' => time(),
				'body' => $this->_mention->preMention($body)
			);

			// Before inserting the comment...
			call_integration_hook('integrate_breeze_before_insertComment', array(&$params));

			// Store the comment
			$params['id'] = $this->_query->insertComment($params);

			// The Comment was inserted
			if (!empty($params['id']))
			{
				// build the notification
				$this->_mention->mention(
					array(
						'wall_owner' => $profile_owner_id,
						'wall_poster' => $poster_comment_id,
						'wall_status_owner' => $status_owner_id,
						'comment_id' => $params['id'],
						'status_id' => $status_id,),
					array(
							'name' => 'comments',
							'id' => $params['id'],)
				);

				// Parse the content
				$params['body'] = $this->_parser->display($params['body']);

				// The comment was created, tell the world of just those who want it to know...
				call_integration_hook('integrate_breeze_after_insertComment', array(&$params));

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'success',
					'message' => 'published_comment',
					'data' => $this->_display->HTML($params, 'comment'),
					'owner' => $profile_owner_id,
				));
			}

			// Something wrong with the server
			else
				return $this->setResponse(array('owner' => $this->_data->getValue('owner_id'), 'type' => 'error',));
		}

		// There was an error
		else
			return $this->setResponse(array('owner' => $this->_data->getValue('owner_id'), 'type' => 'error',));
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

		// Set some much needed vars
		$id = $this->_data->getValue('bid');
		$type = $this->_data->getValue('type');
		$profile_owner = $this->_data->getValue('profile_owner');

		// Get the data
		if ($id != false)
		{
			// You aren't allowed in here, let's show you a nice message error...
			$this->permissions('delete'. ucfirst($this->_data->getValue('type')), false);

			$temp_id_exists = $this->_query->getSingleValue(
				$type == 'status' ? 'status' : 'comments',
				($type == 'status' ? 'status' : 'comments') .'_id',
				$id
			);

			// Do this only if the message wasn't deleted already
			if (!empty($temp_id_exists))
			{
				$typeCall = 'delete'. ucfirst($type);

				// Do the query dance!
				$this->_query->$typeCall($id, $profile_owner);

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'success',
					'message' => 'delete_'. $type,
					'owner' => $profile_owner,
				));
			}

			// Tell them someone has deleted the message already
			else
				return $this->setResponse(array(
					'type' => 'error',
					'message' => 'already_deleted_'. $type,
					'owner' => $profile_owner,
				));
		}

		// No valid ID, no candy for you!
		else
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $profile_owner,
			));
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
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'extra' => array('area' => 'breezenoti',),
				'owner' => $user,
			));

		// We must make sure this noti really exists, we just must!!!
		$noti_temp = $this->_query->getNotificationByReceiver($user, true);

		if (empty($noti_temp['data']) || !isset($noti_temp['data'][$noti]))
			return $this->setResponse(array(
				'message' => 'already_deleted_noti',
				'type' => 'error',
				'extra' => array('area' => 'breezenoti',),
				'owner' => $user,
			));

		else
		{
			// Whatever you choose, I'll do the opposite!
			$viewed = !$noti_temp['data'][$noti]['viewed'];

			// All is good, mark this as read
			$this->_query->markNoti($noti, $user, $viewed);

			// All done!
			return $this->setResponse(array(
				'type' => 'success',
				'message' => 'noti_markasread_after',
				'owner' => $user,
				'extra' => array('area' => 'breezenoti',),
			));
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
		$noti_temp = $this->_query->getNotificationByReceiver($user, true);

		if (empty($noti_temp['data']) || !array_key_exists($noti, $noti_temp['data']))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'extra' => array('area' => 'breezenoti',),
				'owner' => $user,
			));

		else
		{
			// All good, delete it
			$this->_query->deleteNoti($noti, $user);

			return $this->setResponse(array(
				'type' => 'success',
				'message' => 'noti_delete_after',
				'owner' => $user,
				'extra' => array('area' => 'breezenoti',),
			));
		}
	}

	/**
	 * BreezeAjax::multiNoti()
	 *
	 * Handles mass actions, mark as read/unread and deletion of multiple notifications at once
	 * @return void
	 */
	public function multiNoti()
	{
		checkSession('request', '', false);

		// Get the global vars
		$this->_data = Breeze::sGlobals('request');

		// Start with getting the data
		$do = $this->_data->getValue('multiNotiOption');
		$idNoti = $this->_data->getValue('idNoti');
		$user = $this->_data->getValue('user');

		if (empty($do) || empty($idNoti) || empty($user))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'extra' => array('area' => 'breezenoti',),
				'owner' => $user,
			));

		else
		{
			// Figure it out what we're gonna do
			$call = ($do == 'delete' ? 'delete' ? 'mark') . 'Noti';

			// $set the "viewed" var
			$this->_query->$call($idNoti, $user, $viewed);

			// Set the "viewed" var
			$viewed = $do == 'read' ? 1 : 0;

			return $this->setResponse(array(
				'type' => 'success',
				'message' => $do == 'delete' ? 'notiMulti_delete_after' : ($viewed == 1 ? 'notiMulti_markasread_after' : 'notiMulti_unmarkasread_after'),
				'owner' => $user,
				'extra' => array('area' => 'breezenoti',),
			));
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
		// Just pass the result directly
		return $this->_response = $this->_query->userMention();
	}

	/**
	 * BreezeAjax::cleanLog()
	 *
	 * Deletes the visitors log
	 * @return void
	 */
	protected function cleanLog()
	{
		global $user_info;

		checkSession('request', '', false);

		// Get the global vars
		$this->_data = Breeze::sGlobals('request');

		// Get the data
		$log = $this->_data->getValue('log');
		$user = $this->_data->getValue('u');

		// An extra check
		if (empty($log) || empty($user) || $user_info['id'] != $user)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'extra' => array('area' => 'breezenoti',),
				'owner' => $user,
			));

		// Ready to go!
		$this->_query->deleteViews($user);

		return $this->setResponse(array(
			'type' => 'success',
			'message' => 'noti_visits_clean',
			'owner' => $user,
			'extra' => array('area' => 'breezesettings',),
		));
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
		if ($this->noJS == true)
		{
			// Build the redirect url
			$this->setRedirect();

			// And to the page we go!
			return redirectexit($this->_redirectURL);
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

		// Fall to a generic server error, this should never happen but just want to be sure...
		else
			echo json_encode(array(
				'message' => $this->_text->getText('error_server'),
				'data' => '',
				'type' => 'error',
				'owner' => 0,
			));

		// Done
		obExit(false);
	}

	protected function setResponse($data = array())
	{
		// Data is empty, fill out a generic response
		if (empty($data))
			$data = array(
				'message' => 'server',
				'data' => '',
				'type' => 'error',
				'owner' => 0,
				'extra' => '',
			);

		// If we didn't get all the params, set them to an empty var and don't forget to convert the message to a proper text string
		$this->_response = array(
			'message' => $this->noJS == false ? $this->_text->getText($data['type'] .'_'. $data['message']) : $data['message'],
			'data' => !empty($data['data']) ? $data['data'] : '',
			'type' => $data['type'],
			'owner' => !empty($data['owner']) ? $data['owner'] : 0,
			'extra' => !empty($data['extra']) ? $data['extra'] : '',
		);
	}

	/**
	 * BreezeAjax::setRedirect()
	 *
	 * Set a valid url with the params provided
	 * @param array $message Includes the type and the actual message to send back as a response
	 * @param int $user If we're coming from the profile area we need to redirect to that specific user's profile page.
	 * @return
	 */
	protected function setRedirect()
	{
		$messageString = '';
		$userString = '';
		$extraString = '';

		// Build the strings as a valid syntax to pass by $_GET
		if (!empty($this->_response['message']) && !empty($this->_response['type']))
				$messageString .= ';mstype='. $this->_response['type'] .';msmessage='. $this->_response['message'];

		$userString = $this->comingFrom == 'profile' ? ';u='. $this->_response['owner'] : '';

		// A special are perhaps?
		if (!empty($this->_response['extra']))
			foreach ($this->_response['extra'] as $k => $v)
				$extraString .= ';'. $k .'='. $v;

		$this->_redirectURL .= 'action='. $this->comingFrom . $messageString . $extraString . $userString;
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
