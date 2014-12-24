<?php

/**
 * BreezeAjax
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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
	protected $_app;

	/**
	 * BreezeAjax::__construct()
	 *
	 * Sets all the needed vars, loads the language file
	 * @return void
	 */
	public function __construct($app)
	{
		$this->_app = $app;

		// Needed to show some error strings
		loadLanguage(Breeze::$name);

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

		// Safety first, hardcode the actions and oh boy there are a lot!!!
		$this->subActions = array(
			'post' => 'post',
			'postcomment' => 'postComment',
			'delete' => 'delete',
			'usersmention' => 'usersMention',
			'cleanlog' => 'cleanLog',
			'fetch' => 'fetchStatus',
			'fetchc' => 'fetchComment',
			'usersettings' => 'userSettings',
			'cover' => 'cover',
			'coverdelete' => 'coverDelete',
			'moodchange' => 'moodChange',
		);

		// Build the correct redirect URL
		$this->comingFrom = $data->get('rf') == true ? $data->get('rf') : 'wall';

		// Master setting is off, back off!
		if (!$this->_app['tools']->enable('master'))
			fatal_lang_error('Breeze_error_no_valid_action', false);

		// Gotta love globals...
		$context['Breeze']['tools'] = $this->_app['tools'];

		// Not using JavaScript?
		if (!$data->get('js'))
			$this->_noJS = true;

		// Get the current user settings.
		$this->_userSettings = $this->_app['query']->getUserSettings($user_info['id']);
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

		// Get the data.
		$this->_data = Breeze::data('request');

		// Build plain normal vars...
		$owner = $this->_data->get('statusOwner');
		$poster = $this->_data->get('statusPoster');
		$content = $this->_data->get('content');
		$mentions = array();

		// Any mentions?
		if ($this->_data->get('mentions'))
			$mentions = array_filter($this->_data->get('mentions'));

		// Sorry, try to play nicer next time
		if (!$owner || !$poster || !$content)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $owner,
			));

		// Are you the profile owner? no? then feel my wrath!
		if ($this->_currentUser != $owner)
			allowedTo('breeze_postStatus');

		$body = $this->_data->validateBody($content);

		// Do this only if there is something to add to the database
		if (!empty($body))
		{
			$this->_params = array(
				'owner_id' => $owner,
				'poster_id' => $poster,
				'time' => time(),
				'body' => $this->_app['tools']->enable('mention') ? $this->_app['mention']->preMention($body, $mentions) : $body,
			);

			// Maybe a last minute change before inserting the new status?
			call_integration_hook('integrate_breeze_before_insertStatus', array(&$this->_params));

			// Store the status
			$this->_params['id'] = $this->_app['query']->insertStatus($this->_params);
			$this->_params['canHas'] = $this->_app['tools']->permissions('Status', $owner, $poster),
			$this->_params['time_raw'] = time();

			// All went good or so it seems...
			if (!empty($this->_params['id']))
			{
				// Time to fire up some notifications...
				$this->_app['query']->insertNoti($this->_params, 'status');

				// Likes.
				$this->_params['likes'] =  array(
					'count' => 0,
					'already' => false,
					'can_like' => allowedTo('breeze_canLike') && ($this->_currentUser != $poster),
				);

				// Parse the content.
				$this->_params['body'] = $this->_app['parser']->display($this->_params['body']);

				// The status was inserted, tell everyone!
				call_integration_hook('integrate_breeze_after_insertStatus', array($this->_params));

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'info',
					'message' => 'published',
					'data' => $this->_app['display']->HTML($this->_params, 'status', true, $poster),
					'owner' => $owner,
				));
			}

			// Something went terrible wrong!
			else
				return $this->setResponse(array('owner' => $owner,));
		}

		// There was an (generic) error
		else
			return $this->setResponse(array('owner' => $owner,));
	}

	/**
	 * BreezeAjax::postComment()
	 *
	 * Gets the data from the client and stores a new comment in the DB.
	 * @return
	 */
	public function postComment()
	{
		checkSession('request', '', false);

		$this->_data = Breeze::data('request');

		// Trickery, there's always room for moar!
		$statusID = $this->_data->get('statusID');
		$statusPoster = $this->_data->get('statusPoster');
		$poster = $this->_data->get('poster');
		$owner = $this->_data->get('owner');
		$content = $this->_data->get('content');
		$mentions = array();

		// So, you're popular huh?
		if ($this->_data->get('mentions'))
			$mentions = $this->_data->get('mentions');

		// Sorry, try to play nice next time
		if (!$statusID || !$statusPoster || !$poster || !$owner || !$content)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $poster,
			));

		// Are you the profile owner? no? then feel my wrath!
		if ($this->_currentUser != $owner)
			allowedTo('breeze_postComments');

		// Load all the things we need
		$idExists = $this->_app['query']->getSingleValue('status', 'status_id', $statusID);

		$body = $this->_data->validateBody($content);

		// The status do exists and the data is valid
		if (!empty($body) && !empty($idExists))
		{
			// Build the params array for the query
			$this->_params = array(
				'status_id' => $statusID,
				'status_owner_id' => $statusPoster,
				'poster_id' => $poster,
				'profile_id' => $owner,
				'time' => time(),
				'body' => $this->_app['tools']->enable('mention') ? $this->_app['mention']->preMention($body, $mentions) : $body,
			);

			// Before inserting the comment...
			call_integration_hook('integrate_breeze_before_insertComment', array(&$this->_params));

			// Store the comment
			$this->_params['id'] = $this->_app['query']->insertComment($this->_params);
			$this->_params['time_raw'] = time();
			$this->_params'canHas'] = $this->_app['tools']->permissions('Comments', $owner, $poster),

			// The Comment was inserted ORLY???
			if (!empty($this->_params['id']))
			{
				// Time to fire up some notifications...
				$this->_app['query']->insertNoti($this->_params, 'comment');

				// Likes.
				$this->_params['likes'] =  array(
					'count' => 0,
					'already' => false,
					'can_like' => allowedTo('breeze_canLike')  && ($this->_currentUser != $poster),
				);

				// Parse the content.
				$this->_params['body'] = $this->_app['parser']->display($this->_params['body']);

				// The comment was created, tell the world or just those who want to know...
				call_integration_hook('integrate_breeze_after_insertComment', array($this->_params));

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'info',
					'message' => 'published_comment',
					'data' => $this->_app['display']->HTML($this->_params, 'comment', true, $poster),
					'owner' => $owner,
					'statusID' => false,
				));
			}

			// Something wrong with the server.
			else
				return $this->setResponse(array('owner' => $owner, 'type' => 'error',));
		}

		// There was an error
		else
			return $this->setResponse(array('owner' => $owner, 'type' => 'error',));
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
			$canHas = $this->_app['tools']->permissions(ucfirst($type), $profileOwner, $poster);

			// Die, die my darling!
			if (!$canHas['delete'])
				fatal_lang_error('Breeze_error_delete'. ucfirst($type), false);

			$idExists = $this->_app['query']->getSingleValue(
				$type,
				$type .'_id',
				$id
			);

			// Do this only if the message wasn't deleted already
			if (!empty($idExists))
			{
				$typeCall = 'delete'. ucfirst($type);

				// Mess up the vars before performing the query
				call_integration_hook('integrate_breeze_before_delete', array(&$type, &$id, &$profileOwner, &$poster));

				// Do the query dance!
				$this->_app['query']->$typeCall($id, $profileOwner);

				// Tell everyone what just happened here...
				call_integration_hook('integrate_breeze_after_delete', array($type, $id, $profileOwner, $poster));

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'info',
					'message' => 'delete_'. $type,
					'owner' => $profileOwner,
					'data' => $type .'_id_'.$id,
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
		if ($this->_app['tools']->setting('allowed_maxlength_aboutMe') && !empty($toSave['aboutMe']) && strlen($toSave['aboutMe']) >= $this->_app['tools']->setting('allowed_maxlength_aboutMe'))
			$toSave['aboutMe'] = substr($toSave['aboutMe'], 0, $this->_app['tools']->setting('allowed_maxlength_aboutMe'));

		// Do the insert already!
		$this->_app['query']->insertUserSettings($toSave, $this->_data->get('u'));

		// Done! set the redirect.
		return $this->setResponse(array(
			'type' => 'info',
			'message' => 'updated_settings',
			'owner' => $this->_data->get('u'),
			'extra' => array('area' => $this->_data->get('area'),),
		));
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

		// Get the global vars.
		$data = Breeze::data('request');

		$id = $data->get('userID');
		$maxIndex = $data->get('maxIndex');
		$numberTimes = $data->get('numberTimes');
		$comingFrom = $data->get('comingFrom');
		$return = '';

		// The usual checks.
		if (empty($id) || empty($maxIndex) || empty($numberTimes) || empty($comingFrom))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $id,
			));

		// Calculate the start value.
		$start = $maxIndex * $numberTimes;

		// Pass the user ID or IDs depending where are we coming from....
		$fetch = $comingFrom == 'wall' ? $data->get('buddies') : $data->get('userID');

		// Re-globalized!
		$context['Breeze']['comingFrom'] = $comingFrom;

		// Get the right call to the DB.
		$call = $comingFrom == 'profile' ? 'getStatusByProfile' : 'getStatusByUser';

		$data = $this->_app['query']->$call($fetch, $maxIndex, $start);

		if (!empty($data['data']))
		{
			$return .= $this->_app['display']->HTML($data['data'], 'status', false, $data['users']);

			return $this->setResponse(array(
				'type' => 'info',
				'message' => '',
				'data' => $return,
				'owner' => $id,
			));
		}

		else
			return $this->setResponse(array(
				'type' => 'info',
				'message' => 'end',
				'data' => 'end',
				'owner' => $id,
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
		return $this->_response = $this->_app['query']->userMention($match);
	}

	/**
	 * BreezeAjax::cover()
	 *
	 * Gets an HTTP request for uploading and storing a new cover image. Checks if the user has permission to do so, checks the image itself and all other possible checks.
	 * @return
	 */
	public function cover()
	{
		$data = Breeze::data('get');

		// This feature needs to be enable.
		if (!$this->_app['tools']->enable('cover') || empty($data))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $this->_currentUser,
			));

		// This makes things easier.
		$folder = $this->_app['tools']->boardDir . Breeze::$coversFolder . $this->_currentUser .'/';
		$folderThumbnail = $this->_app['tools']->boardDir . Breeze::$coversFolder . $this->_currentUser .'/thumbnail/';
		$folderThumbnailUrl = $this->_app['tools']->boardUrl . Breeze::$coversFolder . $this->_currentUser .'/thumbnail/';

		// Get the image.
		$uploadHandler = new UploadHandler(array(
			'script_url' => $this->_app['tools']->boardUrl .'/',
			'upload_dir' => $this->_app['tools']->boardDir . Breeze::$coversFolder,
			'upload_url' => $this->_app['tools']->boardUrl .'/breezeFiles/',
			'user_dirs' => true,
			'max_file_size' => $this->_app['tools']->enable('cover_max_size') ? ($this->_app['tools']->setting('cover_max_size') .'000') : 250000,
			'max_width' => $this->_app['tools']->enable('cover_max_width') ? $this->_app['tools']->setting('cover_max_width') : 1500,
			'max_height' => $this->_app['tools']->enable('cover_max_height') ? $this->_app['tools']->setting('cover_max_height') : 500,
			'print_response' => false,
			'thumbnail' => array(
				'crop' => false,
				'max_width' => 300,
				'max_height' => 100,
			)
		));

		// Get the file info.
		$fileResponse = $uploadHandler->get_response();
		$file = $fileResponse['files'][0];

		// Is there any errors? this uses the server error response in a weird way...
		if (!empty($file->error))
		{
			if ($this->_noJS)
				return $this->setResponse(array(
					'message' => $file->error,
					'type' => 'error',
					'owner' => $this->_currentUser,
				));

			else
				return $this->_response = $file;
		}

		// Do changes only if the image was uploaded.
		if ($file->name)
		{
			// Check the file.
			require_once($this->_app['tools']->sourceDir . '/Subs-Graphics.php');

			if (!checkImageContents($folder . $file->name))
			{
				// Gotta delete the uploaded image.
				$this->_app['tools']->deleteCover($file->name, $this->_currentUser);

				// Return a nice error message.
				return $this->setResponse(array(
					'message' => 'cover_error_check',
					'type' => 'error',
					'owner' => $this->_currentUser,
					'data' => $folder . $file->name,
				));
			}

			// If there is an already uploaded cover, make sure to delete it.
			if (!empty($this->_userSettings['cover']))
				$this->_app['tools']->deleteCover($this->_userSettings['cover']['basename'], $this->_currentUser);

			$fileInfo = pathinfo($folder . $file->name);
			$newFile = sha1($file->name) .'.'. $fileInfo['extension'];

			// Just so we don't end with some silly names..
			rename($folder . $file->name, $folder . $newFile);
			rename($folderThumbnail . $file->name, $folderThumbnail . $newFile);

			// And again get the file info...
			$fileInfo = pathinfo($folder . $newFile);

			// Store the new cover info.
			$this->_app['query']->insertUserSettings(array('cover'=> json_encode($fileInfo)), $this->_currentUser);

			// Create an inner alert for this.
			if (!empty($this->_userSettings['alert_cover']))
				$this->_app['query']->createLog(array(
					'member' => $this->_currentUser,
					'content_type' => 'cover',
					'content_id' => 0,
					'time' => time(),
					'extra' => array(
						'buddy_text' => 'cover',
						'toLoad' => array($this->_currentUser),
						'image' => $folderThumbnailUrl . $newFile,
					),
				));

			$this->setResponse(array(
				'message' => 'cover_done',
				'type' => 'info',
				'owner' => $this->_currentUser,
				'data' => json_encode($fileInfo),
			));

			// Don't need this.
			unset($file);

			// All done.
			return;
		}
	}

	public function coverDelete()
	{
		$this->_data = Breeze::data('request');

		// Delete the cover at once!
		if (!empty($this->_userSettings['cover']))
		{
			$this->_app['tools']->deleteCover($this->_userSettings['cover']['basename'], $this->_currentUser);

			// Remove the setting from the users options.
			$this->_app['query']->insertUserSettings(array('cover'=> ''), $this->_currentUser);

			// Build the response.
			return $this->setResponse(array(
				'type' => 'info',
				'message' => 'cover_deleted',
				'owner' => $this->_data->get('u'),
				'extra' => array('area' => 'breezesettings',),
			));
		}

		// Nothing to delete...
		else
			return $this->setResponse(array(
				'type' => 'error',
				'message' => 'no_cover_deleted',
				'owner' => $this->_data->get('u'),
				'extra' => array('area' => 'breezesettings',),
			));
	}

	public function moodChange()
	{
		$this->_data = Breeze::data();

		// Get the mood ID, can't work without it...
		if ($this->_data->get('moodID'))
		{
			// Get the moods array.
			$allMoods = $this->_app['mood']->getActive();

			// There isn't a mood with the selected ID.
			if (!in_array($this->_data->get('moodID'), array_keys($allMoods)))
				return $this->setResponse(array(
				'message' => $this->_app['tools']->text('error_server'),
				'data' => '',
				'type' => 'error',
				'owner' => $this->_currentUser,
			));

			// Go ahead and store the new ID.
			$this->_app['query']->insertUserSettings(array('mood'=> $this->_data->get('moodID')), $this->_currentUser);

			// Get the image.
			$image = $allMoods[$this->_data->get('moodID')]['image_html'];

			$moodHistory = !empty($this->_userSettings['moodHistory']) ? json_decode($this->_userSettings['moodHistory'], true) : array();

			// User has no history, go make one then!
			if (empty($moodHistory))
				$moodHistory[] = array(
					'date' => time(),
					'id' => $this->_data->get('moodID'),
				);

			else
			{
				// Gotta make sure the last added item is different than the one we're trying to add.
				$lastItem = end($moodHistory);

				if ($lastItem['id'] == $this->_data->get('moodID'))
					$moodHistory = array();

				// Nope! its a different one!
				else
					$moodHistory[] = array(
					'date' => time(),
					'id' => $this->_data->get('moodID'),
				);

				// One last thing we need to do, cut off old entries.
				if (count($moodHistory) > 20)
					$moodHistory = array_slice($moodHistory, -20);
			}

			// Anyway, save the values and move on...
			if (!empty($moodHistory))
			{
				$this->_app['query']->insertUserSettings(array('moodHistory'=> json_encode($moodHistory)), $this->_currentUser);

				// Create an inner alert for this.
				if (!empty($this->_userSettings['alert_mood']))
					$this->_app['query']->createLog(array(
						'member' => $this->_currentUser,
						'content_type' => 'mood',
						'content_id' => $this->_data->get('moodID'),
						'time' => time(),
						'extra' => array(
							'buddy_text' => 'mood',
							'toLoad' => array($this->_currentUser),
							'moodHistory' => serialize(end($moodHistory)),
						),
					));
			}

			// Build the response.
			return $this->setResponse(array(
				'type' => 'info',
				'message' => 'moodChanged',
				'data' => json_encode(array('user' => $this->_data->get('user'), 'image' => $image)),
				'owner' => $this->_currentUser,
				'extra' => array('area' => 'breezesettings',),
			));
		}

		// Something happen :(
		else
			return $this->setResponse(array(
				'message' => $this->_app['tools']->text('error_server'),
				'data' => '',
				'type' => 'error',
				'owner' => $this->_currentUser,
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

		// Set the header.
		header('Content-Type: application/json');

		// Is there a custom message? Use it
		if (!empty($this->_response))
			echo json_encode($this->_response);

		// Fall to a generic server error, this should never happen but just want to be sure...
		else
			echo json_encode(array(
				'message' => $this->_app['tools']->text('error_server'),
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
				'message' => $this->_app['tools']->text('error_server'),
				'data' => '',
				'type' => 'error',
				'owner' => 0,
				'extra' => '',
			);

		// If we didn't get all the params, set them to an empty var and don't forget to convert the message to a proper text string
		$this->_response = array(
			'message' => !empty($data['message']) ? $this->_app['tools']->text($data['type'] .'_'. $data['message']) : $this->_app['tools']->text('error_server'),
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

		if (!empty($this->_response['message']) && !empty($this->_response['type']))
			$this->_app['tools']->setResponse($this->_response['message'], $this->_response['type']);

		// Build the strings as a valid syntax to pass by $_GET
		$userString = $this->comingFrom == 'profile' ? ';u='. $this->_response['owner'] : '';

		// A special area perhaps?
		if (!empty($this->_response['extra']))
			foreach ($this->_response['extra'] as $k => $v)
				$extraString .= ';'. $k .'='. $v;

		$this->_redirectURL .= 'action='. $this->comingFrom . $extraString . $userString;
	}
}
