<?php

/**
 * BreezeAjax
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2017, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

class BreezeAjax
{
	protected $_noJS = false;
	protected $_redirectURL = '';
	public $subActions = [];
	protected $_userSettings = [];
	protected $_params = [];
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

		// Needed to show some error strings.
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
		global $user_info, $context, $db_show_debug;

		// Handling the subactions
		$data = $this->_app->data('get');

		// Safety first, hardcode the actions and oh boy there are a lot!!!
		$this->subActions = array(
			'post' => 'post',
			'postcomment' => 'postComment',
			'delete' => 'delete',
			'usersmention' => 'usersMention',
			'cleanlog' => 'cleanLog',
			'fetch' => 'fetchStatus',
			'fetchc' => 'fetchComment',
			'fetchLog' => 'fetchLog',
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

		// Don't show da stuffz!.
		if ($this->_noJS)
			$db_show_debug = false;

		checkSession('request');

		// Get the current user settings.
		$this->_userSettings = $this->_app['query']->getUserSettings($user_info['id']);
		$this->_currentUser = $user_info['id'];

		// Temporarily turn this into a normal var
		$call = $this->subActions;

		$externalHandler = false;

		// So you think you can do it better huh?
		$externalHandler = call_integration_hook('integrate_breeze_ajax_actions', [$this]);

		// Somebody else has done all the dirty work for us YAY!
		if ($externalHandler)
			return;

		// Does the subaction even exist?
		if (isset($call[$data->get('sa')]))
		{
			// Get the data.
			$this->_data = $this->_app->data('request');

			// This is somehow ugly but its faster.
			$this->{$call[$data->get('sa')]}();

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
		// Build plain normal vars...
		$owner = $this->_data->get('statusOwner');
		$poster = $this->_currentUser;
		$content = $this->_data->get('message');
		$mentionedUsers = [];

		// Sorry, try to play nicer next time
		if (!$owner || !$poster || !$content)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $owner,
			));

		// Flood control.
		if (!$this->_app['tools']->floodControl())
			return $this->setResponse(array(
				'message' => 'flood',
				'type' => 'error',
				'owner' => $owner,
			));

		// Are you the profile owner? no? then feel my wrath!
		if ($this->_currentUser != $owner)
			allowedTo('breeze_postStatus');

		// Any mentions?
		if ($this->_app['tools']->modSettings('enable_mentions') && allowedTo('mention'))
		{
			require_once($this->_app['tools']->sourceDir . '/Mentions.php');
			$mentionedUsers = Mentions::getMentionedMembers($content);
			$content = Mentions::getBody($content, $mentionedUsers);
		}

		$body = $this->_data->validateBody($content);

		// Do this only if there is something to add to the database
		if (!empty($body))
		{
			$this->_params = array(
				'profile_id' => $owner,
				'poster_id' => $poster,
				'time' => time(),
				'body' => $body,
			);

			// Maybe a last minute change before inserting the new status?
			call_integration_hook('integrate_breeze_before_insertStatus', [&$this->_params]);

			// Store the status
			$this->_params['id'] = $this->_app['query']->insertStatus($this->_params);

			// Aftermath stuff.
			$this->_params += array(
				'canHas' => $this->_app['tools']->permissions('Status', $owner, $poster),
				'time_raw' => time(),
				'likes' => [],
			);

			// All went good or so it seems...
			if (!empty($this->_params['id']))
			{
				// Time to fire up some notifications...
				$this->_app['query']->insertNoti($this->_params, 'status');

				// Any mentions? fire up some notifications.
				if (!empty($mentionedUsers))
				{
					$mentionData = $this->_params;

					// Add the mentioned users.
					$mentionData['users'] = $mentionedUsers;

					// The inner type.
					$mentionData['innerType'] = 'sta';

					// Don't really need the body.
					unset($mentionData['body']);

					// Done!
					$this->_app['query']->insertNoti($mentionData, 'mention');
					unset($mentionData);
				}

				// Likes.
				if ($this->_app['tools']->modSettings('enable_likes'))
					$this->_params['likes'] =  array(
						'count' => 0,
						'already' => false,
						'can_like' => allowedTo('likes_like') && ($this->_currentUser != $poster),
						'can_view_like' => allowedTo('likes_view'),
					);

				// The status was inserted, tell everyone!
				call_integration_hook('integrate_breeze_after_insertStatus', [$this->_params]);

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'info',
					'message' => 'published',
					'data' => $this->_app['display']->HTML($this->_params, 'status', true, $poster, false),
					'owner' => $owner,
				));
			}

			// Something went terrible wrong!
			else
				return $this->setResponse(['owner' => $owner,]);
		}

		// There was an (generic) error
		else
			return $this->setResponse(['owner' => $owner,]);
	}

	/**
	 * BreezeAjax::postComment()
	 *
	 * Gets the data from the client and stores a new comment in the DB.
	 * @return
	 */
	public function postComment()
	{
		$this->_data = $this->_app->data('request');

		// Trickery, there's always room for moar!
		$statusID = $this->_data->get('statusID');
		$statusPoster = $this->_data->get('statusPoster');
		$poster = $this->_currentUser;
		$owner = $this->_data->get('owner');
		$content = $this->_data->get('message');
		$mentionedUsers = [];

		// Sorry, try to play nice next time
		if (!$statusID || !$statusPoster || !$poster || !$owner || !$content)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $poster,
			));

		// Flood control.
		if (!$this->_app['tools']->floodControl())
			return $this->setResponse(array(
				'message' => 'flood',
				'type' => 'error',
				'owner' => $owner,
			));

		// Are you the profile owner? no? then feel my wrath!
		if ($this->_currentUser != $owner)
			allowedTo('breeze_postComments');

		// So, you're popular huh?
		if ($this->_app['tools']->modSettings('enable_mentions') && allowedTo('mention'))
		{
			require_once($this->_app['tools']->sourceDir . '/Mentions.php');
			$mentionedUsers = Mentions::getMentionedMembers($content);
			$content = Mentions::getBody($content, $mentionedUsers);
		}

		// Load all the things we need.
		$idExists = $this->_app['query']->getSingleValue('status', 'status_id', $statusID);

		$body = $this->_data->validateBody($content);

		// The status do exists and the data is valid.
		if (!empty($body) && !empty($idExists))
		{
			// Build the params array for the query
			$this->_params = array(
				'status_id' => $statusID,
				'status_owner_id' => $statusPoster,
				'poster_id' => $poster,
				'profile_id' => $owner,
				'time' => time(),
				'body' => $body,
			);

			// Before inserting the comment...
			call_integration_hook('integrate_breeze_before_insertComment', [&$this->_params]);

			// Store the comment
			$this->_params['id'] = $this->_app['query']->insertComment($this->_params);

			// Aftermath stuff.
			$this->_params += array(
				'time_raw' => time(),
				'canHas' => $this->_app['tools']->permissions('Comments', $owner, $poster),
				'likes' => [],
			);

			// The Comment was inserted ORLY???
			if (!empty($this->_params['id']))
			{
				// Time to fire up some notifications...
				$this->_app['query']->insertNoti($this->_params, 'comment');

				// Any mentions? fire up some notifications.
				if (!empty($mentionedUsers))
				{
					$mentionData = $this->_params;

					// Add the mentioned users.
					$mentionData['users'] = $mentionedUsers;

					// The inner type.
					$mentionData['innerType'] = 'com';

					// Don't really need the body.
					unset($mentionData['body']);

					// Done!
					$this->_app['query']->insertNoti($mentionData, 'mention');
					unset($mentionData);
				}

				// Likes.
				if ($this->_app['tools']->modSettings('enable_likes'))
					$this->_params['likes'] =  array(
						'count' => 0,
						'already' => false,
						'can_like' => allowedTo('likes_like')  && ($this->_currentUser != $poster),
						'can_view_like' => allowedTo('likes_view'),
					);

				// The comment was created, tell the world or just those who want to know...
				call_integration_hook('integrate_breeze_after_insertComment', [$this->_params]);

				// Send the data back to the browser
				return $this->setResponse(array(
					'type' => 'info',
					'message' => 'published_comment',
					'data' => $this->_app['display']->HTML($this->_params, 'comment', true, $poster, false),
					'owner' => $owner,
					'statusID' => false,
				));
			}

			// Something wrong with the server.
			else
				return $this->setResponse(['owner' => $owner, 'type' => 'error',]);
		}

		// There was an error
		else
			return $this->setResponse(['owner' => $owner, 'type' => 'error',]);
	}

	/**
	 * BreezeAjax::delete()
	 *
	 * Handles the deletion of both comments an status
	 * @return
	 */
	public function delete()
	{
		// Set some much needed vars
		$id = $this->_data->get('bid');
		$type = $this->_data->get('type');
		$profileOwner = $this->_data->get('profileOwner');
		$poster = $this->_data->get('poster');

		// No valid ID, no candy for you!
		if (empty($id))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $profileOwner,
			));

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

		// Tell them someone has deleted the message already
		if (empty($idExists))
			return $this->setResponse(array(
				'type' => 'error',
				'message' => 'already_deleted_'. strtolower($type),
				'owner' => $profileOwner,
			));

		$typeCall = 'delete'. ucfirst($type);

		// Mess up the vars before performing the query
		call_integration_hook('integrate_breeze_before_delete', [&$type, &$id, &$profileOwner, &$poster]);

		// Do the query dance!
		$this->_app['query']->{$typeCall}($id, $profileOwner);

		// Tell everyone what just happened here...
		call_integration_hook('integrate_breeze_after_delete', [$type, $id, $profileOwner, $poster]);

		// Send the data back to the browser
		return $this->setResponse(array(
			'type' => 'info',
			'message' => 'delete_'. $type,
			'owner' => $profileOwner,
			'data' => $type .'_id_'.$id,
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
		$toSave = [];
		$user = $this->_data->get('u');

		// Make sure we have the correct user.
		if (empty($user) || $this->_currentUser != $user)
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $this->_currentUser,
			));

		// Handling data.
		foreach ($this->_data->get('breezeSettings') as $k => $v)
		{
			// Checkboxes and alert settings. Either 1 or 0, nothing more.
			if (strpos($k, 'alert_') !== false || Breeze::$allSettings[$k] == 'CheckBox')
				$toSave[$k] = $v === 1 ? $v : 0;

			// Integers, BreezeData should return any numeric value casted as integer so do check thats indeed the case.
			elseif (Breeze::$allSettings[$k] == 'Int')
				$toSave[$k] = is_int($v) ? $v : 0;

			// The rest.
			else
				$toSave[$k] = $v;
		}

		// BlockList only allows numbers and commas.
		if (!empty($toSave['blockList']))
		{
			$tempList = explode(',', preg_replace('/[^0-9,]/', '', $toSave['blockList']));

			foreach ($tempList as $key => $value)
				if ($value == '')
					unset($tempList[$key]);

			$toSave['blockList'] = implode(',', $tempList);

			unset($tempList);
		}

		// Gotta make sure the user is respecting the admin limit for the about me block.
		if ($this->_app['tools']->setting('allowed_maxlength_aboutMe') && !empty($toSave['aboutMe']) && strlen($toSave['aboutMe']) >= $this->_app['tools']->setting('allowed_maxlength_aboutMe'))
			$toSave['aboutMe'] = substr($toSave['aboutMe'], 0, $this->_app['tools']->setting('allowed_maxlength_aboutMe'));

		// Do the insert already!
		$this->_app['query']->insertUserSettings($toSave, $user);

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
	public function fetchStatus()
	{
		global $context;

		$id = $this->_data->get('userID');
		$maxIndex = $this->_data->get('maxIndex');
		$numberTimes = $this->_data->get('numberTimes');
		$comingFrom = $this->_data->get('comingFrom');
		$return = '';
		$data = [];

		// The usual checks.
		if (empty($id) || empty($maxIndex) || empty($numberTimes) || empty($comingFrom))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $id,
			));

		// If this is an user's wall request, we need to check if the current user is on the user's wall ignore list.
		if (!empty($comingFrom) && $comingFrom == 'wall' && $this->_currentUser != $id)
		{
			$stalk = $this->_app['tools']->stalkingCheck($id);

			if (!empty($stalk))
				return $this->setResponse(array(
					'message' => 'wrong_values',
					'type' => 'error',
					'owner' => $this->_currentUser,
				));
		}

		// Calculate the start value.
		$start = $maxIndex * $numberTimes;

		// Pass the user ID or IDs depending where are we coming from....
		$fetch = $comingFrom == 'wall' ? $this->_data->get('buddies') : $this->_data->get('userID');

		// Re-globalized!
		$context['Breeze']['comingFrom'] = $comingFrom;

		// Get the right call to the DB.
		$call = ($comingFrom == 'profile' ? 'getStatusByProfile' : 'getStatusByUser');

		$data = $this->_app['query']->{$call}($fetch, $maxIndex, $start);

		if (!empty($data) && !empty($data['data']))
		{
			$return .= $this->_app['display']->HTML($data['data'], 'status', false, $data['users'], true);

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
				'message' => 'loading_end',
				'data' => 'end',
				'owner' => $id,
			));
	}

	public function fetchLog()
	{
		global $context;

		$id = $this->_data->get('userID');
		$maxIndex = $this->_data->get('maxIndex');
		$numberTimes = $this->_data->get('numberTimes');
		$comingFrom = $this->_data->get('comingFrom');
		$return = '';
		$data = [];

		// The usual checks.
		if (empty($id) || empty($maxIndex) || empty($numberTimes) || empty($comingFrom))
			return $this->setResponse(array(
				'message' => 'wrong_values',
				'type' => 'error',
				'owner' => $id,
			));

		if ($this->_currentUser != $id)
		{
			$stalk = $this->_app['tools']->stalkingCheck($id);

			if (!empty($stalk))
				return $this->setResponse(array(
					'message' => 'wrong_values',
					'type' => 'error',
					'owner' => $this->_currentUser,
				));
		}

		// Calculate the start value.
		$start = $maxIndex * $numberTimes;

		// With the given values, fetch the alerts!
		$data = $this->_app['log']->get($id, $maxIndex, $start);

		// Got something?
		if (!empty($data) && !empty($data['data']))
		{
			// Load the right template.
			loadtemplate(Breeze::$name .'Functions');

			$return .= breeze_activity($data['data'], true);

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
				'message' => 'loadingAlerts_end',
				'data' => 'end',
				'owner' => $id,
			));
	}

	/**
	 * BreezeAjax::cover()
	 *
	 * Gets an HTTP request for uploading and storing a new cover image. Checks if the user has permission to do so, checks the image itself and all other possible checks.
	 * @return
	 */
	public function cover()
	{
		$data = $this->_app->data('get');

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
		$maxFileSize = ($this->_app['tools']->setting('cover_max_image_size') ? $this->_app['tools']->setting('cover_max_image_size') : '250') * 1000;
		$maxFileWidth = ($this->_app['tools']->setting('cover_max_image_width') ? $this->_app['tools']->setting('cover_max_image_width') : '1500');
		$maxFileHeight = ($this->_app['tools']->setting('cover_max_image_height') ? $this->_app['tools']->setting('cover_max_image_height') : '500');

		// Get the image.
		$uploadHandler = new BreezeUpload(array(
			'script_url' => $this->_app['tools']->boardUrl .'/',
			'upload_dir' => $this->_app['tools']->boardDir . Breeze::$coversFolder,
			'upload_url' => $this->_app['tools']->boardUrl .'/breezeFiles/',
			'user_dirs' => true,
			'max_file_size' => $maxFileSize,
			'max_width' => $maxFileWidth,
			'max_height' => $maxFileHeight,
			'print_response' => false,
			'thumbnail' => array(
				'crop' => false,
				'max_width' => 300,
				'max_height' => 100,
			)
		), true, $this->_app['tools']->text('cover_errors'));

		// Get the file info.
		$fileResponse = $uploadHandler->get_response();
		$file = $fileResponse['files'][0];

		// Is there any errors? this uses the server error response in a weird way...
		if (!empty($file->error))
		{
			// Give some more info.
			$replaceValues = ['size' => $this->_app['tools']->formatBytes($maxFileSize, true), 'height' => $maxFileHeight, 'width' => $maxFileWidth];

			if ($this->_noJS)
				return $this->setResponse(array(
					'message' => $this->_app['tools']->parser($file->error, $replaceValues),
					'type' => 'error',
					'owner' => $this->_currentUser,
				));

			else
			{
				$file->error = $this->_app['tools']->parser($file->error, $replaceValues);
				return $this->_response = $file;
			}
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

			$newFile = sha1($this->_app->data()->normalizeString($fileInfo['filename'])) .'.dat';

			// Hay Bibi, ¿Por qué no eres una niña normal?
			if (function_exists('exif_imagetype'))
				$fileInfo['mime'] = image_type_to_mime_type(exif_imagetype($folder . $file->name));

			// Get a not so reliable mimetype.
			if (!empty($fileInfo['extension']) && empty($fileInfo['mime']))
				$fileInfo['mime'] = 'image/' . $fileInfo['extension'];

			rename($folder . $file->name, $folder . $newFile);
			rename($folderThumbnail . $file->name, $folderThumbnail . $newFile);

			// And again get the file info, this time just get what we need.
			$fileInfo['basename'] = pathinfo($folder . $newFile, PATHINFO_BASENAME);
			$fileInfo['filename'] = pathinfo($folder . $newFile, PATHINFO_FILENAME);

			// Store the new cover info.
			$this->_app['query']->insertUserSettings(['cover'=> $fileInfo], $this->_currentUser);

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
			unset($fileInfo);

			// All done.
			return;
		}
	}

	public function coverDelete()
	{
		// Delete the cover at once!
		if (!empty($this->_userSettings['cover']))
		{
			$this->_app['tools']->deleteCover($this->_userSettings['cover']['basename'], $this->_currentUser);

			// Remove the setting from the users options.
			$this->_app['query']->insertUserSettings(['cover'=> ''], $this->_currentUser);

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
		// Get the mood ID, can't work without it...
		if (!$this->_data->get('moodID'))
			return $this->setResponse(array(
				'message' => 'error_server',
				'data' => '',
				'type' => 'error',
				'owner' => $this->_currentUser,
			));

		// Get the moods array.
		$allMoods = $this->_app['mood']->getActive();

		// There isn't a mood with the selected ID.
		if (!in_array($this->_data->get('moodID'), array_keys($allMoods)))
			return $this->setResponse(array(
			'message' => 'error_server',
			'data' => '',
			'type' => 'error',
			'owner' => $this->_currentUser,
		));

		// Go ahead and store the new ID.
		$this->_app['query']->insertUserSettings(['mood'=> $this->_data->get('moodID')], $this->_currentUser);

		// Get the image.
		$image = $allMoods[$this->_data->get('moodID')]['image_html'];

		$moodHistory = !empty($this->_userSettings['moodHistory']) ? $this->_userSettings['moodHistory'] : [];

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
				$moodHistory = [];

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
			$this->_app['query']->insertUserSettings(['moodHistory'=> $moodHistory], $this->_currentUser);

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
						'moodHistory' => json_encode(end($moodHistory)),
					),
				));
		}

		// Build the response.
		return $this->setResponse(array(
			'type' => 'info',
			'message' => 'moodChanged',
			'data' => json_encode(array('user' => $this->_data->get('user'), 'image' => $image)),
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
				'message' => 'error_server',
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
	protected function setResponse($data = [])
	{
		// Fill out a generic response.
		$this->_response = array(
			'message' => 'error_server',
			'data' => '',
			'type' => 'error',
			'owner' => 0,
			'extra' => '',
		);

		// Overwrite the generic response with the actual data.
		if (!empty($data) && is_array($data))
			$this->_response = $data + $this->_response;

		$message = $this->_app['tools']->text($data['type'] .'_'. $data['message']);

		// Get the actual message. If there is no text string then assume the called method already filled the key with an appropriated message.
		$this->_response['message'] = !empty($message) ? $message : $data['message'];
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
