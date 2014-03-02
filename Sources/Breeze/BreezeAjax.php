<?php

/**
 * BreezeAjax
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica Gonzalez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeAjax
{
	protected $_noJS = false;
	protected $_redirectURL = '';
	public $subActions = array();
	protected $_userSettings = array();
	protected $_params = array();
	protected $_currentUser;

	/**
	 * BreezeAjax::__construct()
	 *
	 * Sets all the needed vars, loads the language file
	 * @return void
	 */
	public function __construct($tools, $display, $parser, $query, $notifications, $mention, $log)
	{
		// Needed to show some error strings
		loadLanguage(Breeze::$name);

		// Load all the things we need
		$this->_query = $query;
		$this->_parser = $parser;
		$this->_mention = $mention;
		$this->_notifications = $notifications;
		$this->_display = $display;
		$this->_tools = $tools;

		// Set an empty var, by default lets pretend everything went wrong...
		$this->_response = '';
	}

	/**
	 * BreezeAjax::call()
	 *
	 * Calls the right method for each subaction, calls returnResponse().
	 * @see BreezeAjax::returnResponse()
	 * @return void
	 */
	public function call()
	{
		global $user_info, $context;

		// Handling the subactions
		$data = Breeze::data('get');

		// Safety first, hardcode the actions
		$this->subActions = array(
			'post' => 'post',
			'postcomment' => 'postComment',
			'delete' => 'delete',
			'notimark' => 'notimark',
			'notidelete' => 'notidelete',
			'multiNoti' => 'multiNoti',
			'usersmention' => 'usersMention',
			'cleanlog' => 'cleanLog',
			'fetch' => 'fetchStatus',
			'fetchc' => 'fetchComment',
			'fetchNoti' => 'fetchNoti',
			'usersettings' => 'userSettings',
		);

		// Build the correct redirect URL
		$this->comingFrom = $data->get('rf') == true ? $data->get('rf') : 'wall';

		// Master setting is off, back off!
		if (!$this->_tools->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		// Gotta love globals...
		$context['Breeze']['tools'] = $this->_tools;

		// Not using JavaScript?
		if (!$data->get('js'))
			$this->_noJS = true;

		// Get the current user settings.
		$this->_userSettings = $this->_query->getUserSettings($user_info['id']);
		$this->_currentUser = $user_info['id'];

		// Temporarily turn this into a normal var
		$call = $this->subActions;

		// Add your own sub-actions
		call_integration_hook('integrate_breeze_ajax_actions', array(&$call));

		// Does the subaction even exist?
		if (isset($call[$data->get('sa')]))
		{
			// This is somehow ugly but its faster.
			$this->$call[$data->get('sa')]();

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
	 * Gets the data from the client and stores a new status in the DB using BreezeQuery object.
	 * @return
	 */
	public function post()
	{
		checkSession('request', '', false);

		// Get the data
		$this->_data = Breeze::data('request');

		// Build plain normal vars...
		$statusOwner = $this->_data->get('statusOwner');
		$statusPoster = $this->_data->get('statusPoster');
		$statusContent = $this->_data->get('statusContent');
		$statusMentions = array();

		// Any mentions?
		if ($this->_data->get('mentions'))
			$statusMentions = $this->_data->get('mentions');

		// Sorry, try to play nicer next time
		if (!$statusOwner || !$statusPoster || !$statusContent)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $statusOwner,
			));

		// Are you the profile owner? no? then feel my wrath!
		if ($this->_currentUser != $statusOwner)
			allowedTo('breeze_postStatus');

		$body = $this->_data->validateBody($statusContent);

		// Do this only if there is something to add to the database
		if (!empty($body))
		{
			$this->_params = array(
				'owner_id' => $statusOwner,
				'poster_id' => $statusPoster,
				'time' => time(),
				'body' => $this->_tools->enable('mention') ? $this->_mention->preMention($body, $statusMentions) : $body,
			);

			// Maybe a last minute change before inserting the new status?
			call_integration_hook('integrate_breeze_before_insertStatus', array(&$this->_params));

			// Store the status
			$this->_params['id'] = $this->_query->insertStatus($this->_params);
			$this->_params['time_raw'] = time();

			// All went good or so it seems...
			if (!empty($this->_params['id']))
			{
				// Build the notification(s) via BreezeMention
				if ($this->_tools->enable('mention'))
					$this->_mention->mention(
						array(
							'wall_owner' => $statusOwner,
							'wall_poster' => $statusPoster,
							'status_id' => $this->_params['id'],
						),
						array(
							'name' => 'status',
							'id' => $this->_params['id'],
						)
					);

				// Parse the content
				$this->_params['body'] = $this->_parser->display($this->_params['body']);

				// The status was inserted, tell everyone!
				call_integration_hook('integrate_breeze_after_insertStatus', array($this->_params));

				$logStatus = $this->_params;
				unset($logStatus['body']);

				// Send out a log for this postingStatus action.
				if (!empty($this->_userSettings['activityLog']))
					$this->_notifications->create(array(
						'sender' => $statusPoster,
						'receiver' => $statusPoster,
						'type' => 'logStatus',
						'time' => time(),
						'viewed' => 3, // 3 is a special case to indicate that this is a log entry, cannot be seen or unseen
						'content' => $logStatus,
						'type_id' => $this->_params['id'],
						'second_type' => 'status',
					));

				// Does the wall owner wants to be notified?
				if ($statusOwner != $statusPoster)
				{
					// Get the wall owner's settings.
					$uSettings = $this->_query->getUserSettings($statusOwner);

					if (!empty($uSettings['noti_on_status']))
						$this->_notifications->create(array(
							'sender' => $statusPoster,
							'receiver' => $statusOwner,
							'type' => 'wallOwner',
							'time' => time(),
							'viewed' => 0,
							'content' => $logStatus,
							'type_id' => $this->_params['id'],
							'second_type' => 'status',
						));
				}

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'success',
					'message' => 'published',
					'data' => $this->_display->HTML($this->_params, 'status', true, $statusPoster),
					'owner' => $statusOwner,
				));
			}

			// Something went terrible wrong!
			else
				return $this->setResponse(array('owner' => $statusOwner,));
		}

		// There was an (generic) error
		else
			return $this->setResponse(array('owner' => $statusOwner,));
	}

	/**
	 * BreezeAjax::postComment()
	 *
	 * Gets the data from the client and stores a new comment in the DB.
	 * @return
	 */
	public function postComment()
	{
		global $scripturl;

		checkSession('request', '', false);

		$this->_data = Breeze::data('request');

		// Trickery, there's always room for moar!
		$commentStatus = $this->_data->get('commentStatus');
		$commentStatusPoster = $this->_data->get('commentStatusPoster');
		$commentPoster = $this->_data->get('commentPoster');
		$commentOwner = $this->_data->get('commentOwner');
		$commentContent = $this->_data->get('commentContent');
		$commentMentions = array();

		// So, you're popular huh?
		if ($this->_data->get('mentions'))
			$commentMentions = $this->_data->get('mentions');

		// Sorry, try to play nice next time
		if (!$commentStatus || !$commentStatusPoster || !$commentPoster || !$commentOwner || !$commentContent)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $commentStatusPoster,
			));

		// Are you the profile owner? no? then feel my wrath!
		if ($this->_currentUser != $commentOwner)
			allowedTo('breeze_postComments');

		// Load all the things we need
		$temp_id_exists = $this->_query->getSingleValue('status', 'status_id', $commentStatus);

		$body = $this->_data->validateBody($commentContent);

		// The status do exists and the data is valid
		if (!empty($body) && !empty($temp_id_exists))
		{
			// Build the params array for the query
			$this->_params = array(
				'status_id' => $commentStatus,
				'status_owner_id' => $commentStatusPoster,
				'poster_id' => $commentPoster,
				'profile_id' => $commentOwner,
				'time' => time(),
				'body' => $this->_tools->enable('mention') ? $this->_mention->preMention($body, $commentMentions) : $body
			);

			// Before inserting the comment...
			call_integration_hook('integrate_breeze_before_insertComment', array(&$this->_params));

			// Store the comment
			$this->_params['id'] = $this->_query->insertComment($this->_params);
			$this->_params['time_raw'] = time();

			// The Comment was inserted ORLY???
			if (!empty($this->_params['id']))
			{
				// Build the notification(s) for this comment via BreezeMention
				if ($this->_tools->enable('mention'))
					$this->_mention->mention(
						array(
							'wall_owner' => $commentOwner,
							'wall_poster' => $commentPoster,
							'wall_status_owner' => $commentStatusPoster,
							'comment_id' => $this->_params['id'],
							'status_id' => $commentStatus,),
						array(
								'name' => 'comments',
								'id' => $this->_params['id'],)
					);

				// Parse the content.
				$this->_params['body'] = $this->_parser->display($this->_params['body']);

				// The comment was created, tell the world or just those who want to know...
				call_integration_hook('integrate_breeze_after_insertComment', array($this->_params));

				$logComment = $this->_params;
				unset($logComment['body']);

				// Send out a log for this postingStatus action.
				if (!empty($this->_userSettings['activityLog']))
					$this->_notifications->create(array(
						'sender' => $commentPoster,
						'receiver' => $commentPoster,
						'type' => 'logComment',
						'time' => time(),
						'viewed' => 3, // 3 is a special case to indicate that this is a log entry, cannot be seen or unseen
						'content' => $logComment,
						'type_id' => $this->_params['id'],
						'second_type' => 'comment',
					));

				// Does the Status poster wants to be notified? transitive relation anyone?
				// This only applies if the wall owner, the status poster and the comment poster are different persons!
				if (($commentOwner != $commentPoster) &&  ($commentPoster != $commentStatusPoster))
				{
					$uStatusSettings = $this->_query->getUserSettings($commentStatusPoster);

					if (!empty($uStatusSettings['noti_on_comment']))
						$this->_notifications->create(array(
							'sender' => $commentPoster,
							'receiver' => $commentStatusPoster,
							'type' => 'commentStatus',
							'time' => time(),
							'viewed' => 0,
							'content' => $logComment,
							'type_id' => $this->_params['id'],
							'second_type' => 'comment',
						));
				}

				else if (($commentOwner != $commentPoster) &&  ($commentOwner != $commentStatusPoster))
				{
					$uOwnerSettings = $this->_query->getUserSettings($commentOwner);

					if (!empty($uStatusSettings['noti_on_comment_owner']))
						$this->_notifications->create(array(
							'sender' => $commentPoster,
							'receiver' => $commentStatusPoster,
							'type' => 'commentStatusOwner',
							'time' => time(),
							'viewed' => 0,
							'content' => $logComment,
							'type_id' => $this->_params['id'],
							'second_type' => 'comment',
						));
				}

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'success',
					'message' => 'published_comment',
					'data' => $this->_display->HTML($this->_params, 'comment', true, $commentPoster),
					'owner' => $commentOwner,
				));
			}

			// Something wrong with the server.
			else
				return $this->setResponse(array('owner' => $commentOwner, 'type' => 'error',));
		}

		// There was an error
		else
			return $this->setResponse(array('owner' => $commentOwner, 'type' => 'error',));
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
		$this->_data = Breeze::data('request');

		// Set some much needed vars
		$id = $this->_data->get('bid');
		$type = $this->_data->get('type');
		$profileOwner = $this->_data->get('profileOwner');
		$poster = $this->_data->get('poster');

		// Get the data
		if (!empty($id))
		{
			// You aren't allowed in here, let's show you a nice message error...
			$canHas = $this->_tools->permissions(ucfirst($type), $profileOwner, $poster);

			// Die, die my darling!
			if (!$canHas['delete'])
				fatal_lang_error('Breeze_error_delete'. ucfirst($type), false);

			$temp_id_exists = $this->_query->getSingleValue(
				$type,
				$type .'_id',
				$id
			);

			// Do this only if the message wasn't deleted already
			if (!empty($temp_id_exists))
			{
				$typeCall = 'delete'. ucfirst($type);

				// Mess up the vars before performing the query
				call_integration_hook('integrate_breeze_before_delete', array(&$type, &$id, &$profileOwner, &$poster));

				// Do the query dance!
				$this->_query->$typeCall($id, $profileOwner);

				// Tell everyone what just happened here...
				call_integration_hook('integrate_breeze_after_delete', array($type, $id, $profileOwner, $poster));

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'success',
					'message' => 'delete_'. $type,
					'owner' => $profileOwner,
				));
			}

			// Tell them someone has deleted the message already
			else
				return $this->setResponse(array(
					'type' => 'error',
					'message' => 'already_deleted_'. strtolower($type),
					'owner' => $profileOwner,
				));
		}

		// No valid ID, no candy for you!
		else
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $profileOwner,
			));
	}

	/**
	 * BreezeAjax::userSettings()
	 *
	 * Saves the current user settings into the DB.
	 * @return
	 */
	public function userSettings()
	{
		checkSession('request', '', false);

		$toSave = array();

		// Get the values.
		$this->_data = Breeze::data('request');

		// Handling data.
		$toSave = $this->_data->get('breezeSettings');

		// Gotta make sure the user is respecting the admin limit for the about me block.
		if ($this->_tools->setting('allowed_maxlength_aboutMe') && !empty($toSave['aboutMe']) && strlen($toSave['aboutMe']) >= $this->_tools->setting('allowed_maxlength_aboutMe'))
			$toSave['aboutMe'] = substr($toSave['aboutMe'], 0, $this->_tools->setting('allowed_maxlength_aboutMe'));

		// Do the insert already!
		$this->_query->insertUserSettings($toSave, $this->_data->get('u'));

		// Done! set the redirect.
		return $this->setResponse(array(
			'type' => 'success',
			'message' => 'updated_settings',
			'owner' => $this->_data->get('u'),
			'extra' => array('area' => $this->_data->get('area'),),
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
		$this->_data = Breeze::data('request');

		// Get the data
		$noti = $this->_data->get('content');
		$user = $this->_data->get('user');

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
				'message' => 'noti_'. ($viewed == 0 ? 'un' : '') .'markasread_after',
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
		$this->_data = Breeze::data('request');

		// Get the data
		$noti = $this->_data->get('content');
		$user = $this->_data->get('user');

		// Is this valid data?
		if (empty($noti) || empty($user))
			return;

		// Are we dealing with logs? if so, the process gets much much easier for me!
		if ($this->_data->validate('log'))
		{
			$this->_query->deleteNoti($noti, $user);

			return $this->setResponse(array(
				'type' => 'success',
				'message' => 'noti_delete_after',
				'owner' => $user,
				'extra' => array('area' => 'breezelogs',),
			));
		}

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
	 * Handles mass actions, mark as read/unread and deletion of multiple notifications at once.
	 * @return void
	 */
	public function multiNoti()
	{
		checkSession('request', '', false);

		// Get the global vars
		$this->_data = Breeze::data('request');

		// Start with getting the data
		$do = $this->_data->get('multiNotiOption');
		$idNoti = $this->_data->get('idNoti');
		$user = $this->_data->get('user');

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
			$call = ($do == 'delete' ? 'delete' : 'mark') . 'Noti';

			// Set the "viewed" var
			$viewed = $do == 'read' ? 1 : 0;

			// $set the "viewed" var
			$this->_query->$call($idNoti, $user, $viewed);

			return $this->setResponse(array(
				'type' => 'success',
				'message' => $do == 'delete' ? 'notiMulti_delete_after' : ($viewed == 1 ? 'notiMulti_markasread_after' : 'notiMulti_unmarkasread_after'),
				'owner' => $user,
				'extra' => array('area' => $this->_data->validate('log') ? 'breezelogs' : 'breezenoti',),
			));
		}
	}

	/**
	 * BreezeAjax::fetchStatus()
	 *
	 * Used for pagination, gets X amount of status from either a single wall or an array of buddies IDs.
	 * @return
	 */
	protected function fetchStatus()
	{
		global $context;

		checkSession('request', '', false);

		// Get the global vars
		$data = Breeze::data('request');

		$id = $data->get('userID');
		$maxIndex = $data->get('maxIndex');
		$numberTimes = $data->get('numberTimes');
		$comingFrom = $data->get('comingFrom');
		$return = '';

		// The usual checks
		if (empty($id) || empty($maxIndex) || empty($numberTimes) || empty($comingFrom))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $id,
			));

		// Calculate the start value
		$start = $maxIndex * $numberTimes;

		// Pass the user ID or IDs depending where are we coming from....
		$fetch = $comingFrom == 'wall' ? $data->get('buddies') : $data->get('userID');

		// Re-globalized!
		$context['Breeze']['comingFrom'] = $comingFrom;

		// Get the right call to the DB
		$call = $comingFrom == 'profile' ? 'getStatusByProfile' : 'getStatusByUser';

		$data = $this->_query->$call($fetch, $maxIndex, $start);

		if (!empty($data['data']))
		{
			$return .= $this->_display->HTML($data['data'], 'status', false, $data['users']);

			return $this->setResponse(array(
				'type' => 'success',
				'message' => '',
				'data' => $return,
				'owner' => $id,
			));
		}

		else
			return $this->setResponse(array(
				'type' => 'success',
				'message' => 'end',
				'data' => 'end',
				'owner' => $id,
			));
	}

	/**
	 * BreezeAjax::fetchNoti()
	 *
	 * Gets all unread notifications for the passed user ID.
	 * @return
	 */
	protected function fetchNoti()
	{
		checkSession('request', '', false);

		$data = Breeze::data('request');
		$u = $data->get('u');

		// This is easy, get and return all notifications as a json object, don't  worry, the actual query is cached ;)
		return $this->setResponse(array(
			'type' => 'success',
			'message' => 'success',
			'data' => $this->_notifications->doStream($u),
			'owner' => $u, // Don't really need this, just send some dummy data.
		));
	}

	/**
	 * BreezeAjax::usersMention()
	 *
	 * Creates an array of searchable users
	 * @return void
	 */
	protected function usersMention()
	{
		checkSession('request', '', false);

		// Need it.
		$data = Breeze::data('get');

		// Get the query to match
		$match = $data->get('match');

		// Lets see if there are any results to this search.
		return $this->_response = $this->_query->userMention($match);
	}

	/**
	 * BreezeAjax::cleanLog()
	 *
	 * Deletes the visitors log for each user's wall.
	 * @return void
	 */
	protected function cleanLog()
	{
		global $user_info;

		checkSession('request', '', false);

		// Get the global vars
		$this->_data = Breeze::data('request');

		// Get the data
		$log = $this->_data->get('log');
		$user = $this->_data->get('u');

		// An extra check
		if (empty($log) || empty($user) || $user_info['id'] != $user)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'extra' => array('area' => 'breezesettings',),
				'owner' => $user,
			));

		// Ready to go!
		$this->_query->deleteViews($user);

		return $this->setResponse(array(
			'type' => 'success',
			'message' => 'noti_visitors_clean',
			'owner' => $user,
			'extra' => array('area' => 'breezesettings',),
		));
	}

	/**
	 * BreezeAjax::returnResponse()
	 *
	 * Returns a json encoded response back to the browser. Check and redirects an user if they aren't using JS.
	 * @return
	 */
	protected function returnResponse()
	{
		global $modSettings;

		// No JS? fine... jut send them to whatever url they're from
		if ($this->_noJS == true)
		{
			// Build the redirect url
			$this->setRedirect();

			// And to the page we go!
			return redirectexit($this->_redirectURL);
		}

		// Kill anything else
		ob_end_clean();

		if (!empty($modSettings['CompressedOutput']))
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
				'message' => $this->_tools->text('error_server'),
				'data' => '',
				'type' => 'error',
				'owner' => 0,
			));

		// Done
		obExit(false);
	}

	/**
	 * BreezeAjax::setResponse()
	 *
	 * Creates a valid array with the data provided by each callable method.
	 * @return
	 */
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
			'message' => !empty($data['message']) ? ($this->_noJS == false ? $this->_tools->text($data['type'] .'_'. $data['message']) : $data['message']) : 'server',
			'data' => !empty($data['data']) ? $data['data'] : '',
			'type' => !empty($data['type']) ? $data['type'] : 'error',
			'owner' => !empty($data['owner']) ? $data['owner'] : 0,
			'extra' => !empty($data['extra']) ? $data['extra'] : '',
		);
	}

	/**
	 * BreezeAjax::setRedirect()
	 *
	 * Set a valid url with the params provided.
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

		// A special area perhaps?
		if (!empty($this->_response['extra']))
			foreach ($this->_response['extra'] as $k => $v)
				$extraString .= ';'. $k .'='. $v;

		$this->_redirectURL .= 'action='. $this->comingFrom . $messageString . $extraString . $userString;
	}
}
