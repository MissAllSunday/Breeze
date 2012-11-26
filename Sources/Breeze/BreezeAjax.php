<?php

/**
 * BreezeAjax
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
	die('Hacking attempt...');

class BreezeAjax extends Breeze
{
	public $query;

	/**
	 * BreezeAjax::__construct()
	 *
	 * @return
	 */
	public function __construct()
	{
		/* Load stuff */
		loadtemplate('BreezeAjax');

		/* Load all the things we need */
		$this->_data = $this->sGlobals('request');
		$this->_query = $this->query();
		$this->_parser = $this->parser();
		$this->_mention = $this->mention();
		$this->_settings = $this->settings();
		$this->_notifications = $this->notifications();
	}

	/**
	 * BreezeAjax::call()
	 *
	 * @return
	 */
	public function call()
	{
		/* Handling the subactions */
		$sglobals = $this->sGlobals('get');

		$subActions = array(
			'post' => 'BreezeAjax::post',
			'postcomment' => 'BreezeAjax::postComment',
			'delete' => 'BreezeAjax::delete',
			'notimarkasread' => 'BreezeAjax::notimark',
			'notidelete' => 'BreezeAjax::notidelete',
		);

		/* Does the subaction even exist? */
		/* @todo, call the method rather than calling the function */
		if (in_array($sglobals->getValue('sa'), array_keys($subActions)))
			call_user_func($subActions[$sglobals->getValue('sa')]);

		/* No?  then tell them there was an error... */
		/* else */
		/* some redirect here.. */
	}

	/**
	 * BreezeAjax::post()
	 *
	 * @return
	 */
	public function post()
	{
		global $context;

		/* You aren't allowed in here, let's show you a nice message error... */
		if (!allowedTo('breeze_postStatus'))
			return false;

		checkSession('post', '', false);

		/* Set some values */
		$context['Breeze']['ajax'] = array('ok' => '', 'data' => '');

		/* Do this only if there is something to add to the database */
		if ($this->_data->validateBody('content'))
		{
			$body = $this->_data->getValue('content');

			$params = array(
				'owner_id' => $this->_data->getValue('owner_id'),
				'poster_id' => $this->_data->getValue('poster_id'),
				'time' => time(),
				'body' => $this->_mention->preMention($body),
				);

			/* Store the status */
			$this->_query->insertStatus($params);

			/* Get the newly created status, we just need the id */
			$newStatus = $this->_query->getLastStatus();

			/* Set the ID */
			$params['id'] = $newStatus['status_id'];

			/* Build the notifications */
			$this->_mention->mention(array(
				'wall_owner' => $this->_data->getValue('owner_id'),
				'wall_poster' => $this->_data->getValue('poster_id'),
				'status_id' => $params['id'],
				));

			/* Parse the content */
			$params['body'] = $this->_parser->display($params['body']);

			/* The status was added, build the server response */
			$display = new Breezedisplay($params, 'status');

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] = $display->HTML();
		}

		else
			$context['Breeze']['ajax']['ok'] = 'error';

		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';
	}

	/**
	 * BreezeAjax::postComment()
	 *
	 * @return
	 */
	public function postComment()
	{
		global $context, $scripturl;

		/* You aren't allowed in here, let's show you a nice message error... */
		if (!allowedTo('breeze_postComments'))
			return false;

		checkSession('post', '', false);

		/* By default it will show an error, we only do stuff if necesary */
		$context['Breeze']['ajax']['ok'] = '';

		/* Load all the things we need */
		$temp_id_exists = $this->_query->getSingleValue('status', 'id', $this->_data->getValue('status_id'));

		/* The status do exists and the data is valid*/
		if ($this->_data->validateBody('content') && !empty($temp_id_exists))
		{
			$body = $this->_data->getValue('content');

			/* Build the params array for the query */
			$params = array(
				'status_id' => $this->_data->getValue('status_id'),
				'status_owner_id' => $this->_data->getValue('status_owner_id'),
				'poster_id' => $this->_data->getValue('poster_comment_id'),
				'profile_owner_id' => $this->_data->getValue('profile_owner_id'),
				'time' => time(),
				'body' => $this->_mention->preMention($body));

			/* Store the comment */
			$this->_query->insertComment($params);

			/* Once the comment was added, get it's ID from the DB */
			$new_comment = $this->_query->getLastComment();

			/* Set the ID */
			$params['id'] = $new_comment['comments_id'];

			/* build the notification */
			$this->_mention->mention(array(
				'wall_owner' => $this->_data->getValue('owner_id'),
				'wall_poster' => $this->_data->getValue('poster_id'),
				'wall_status_owner' => $this->_data->getValue('status_owner_id'),
				'comment_id' => $params['id'],
				'status_id' => $this->_data->getValue('status_id'),
				));

			/* Parse the content */
			$params['body'] = $this->_parser->display($params['body']);

			/* The comment was added, build the server response */
			$display = new Breezedisplay($params, 'comment');

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] = $display->HTML();
		}

		else
			$context['Breeze']['ajax']['ok'] = 'error';

		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';

		unset($temp_id_exists);
	}

	/* Handles the deletion of both comments an status */
	/**
	 * BreezeAjax::delete()
	 *
	 * @return
	 */
	public function delete()
	{
		global $context;

		/* You aren't allowed in here, let's show you a nice message error... */
		isAllowedTo('breeze_deleteStatus');

		$context['Breeze']['ajax']['ok'] = '';
		$context['Breeze']['ajax']['data'] = '';

		/* Get the data */
		$temp_id_exists = $this->_query->getSingleValue($this->_data->getValue('type') == 'status' ?
			'status':'comments', 'id', $this->_data->getValue('id'));

		switch ($this->_data->getValue('type'))
		{
			case 'status':
				/* Do this only if the status wasn't deleted already */
				if (!empty($temp_id_exists))
				{
					$this->_query->deleteStatus($this->_data->getValue('id'));
					$context['Breeze']['ajax']['ok'] = 'ok';
				}

				else
					$context['Breeze']['ajax']['ok'] = 'deleted';

				break;
			case 'comment':
				/* Do this only if the comment wasn't deleted already */
				if (!empty($temp_id_exists))
				{
					$this->_query->deleteComment($this->_data->getValue('id'));
					$context['Breeze']['ajax']['ok'] = 'ok';
				}

				else
					$context['Breeze']['ajax']['ok'] = 'deleted';

				break;
		}

		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';

		unset($temp_id_exists);
	}

	/**
	 * BreezeAjax::notimark()
	 *
	 * Mark a notification as read
	 * @return
	 */
	public function notimark()
	{
		global $context;

		checkSession('post', '', false);

		/* Set some values */
		$context['Breeze']['ajax'] = array(
				'ok' => 'error',
				'data' => 'error_');

		/* Get the data */
		$noti = $this->_data->getValue('content');
		$user = $this->_data->getValue('user');

		/* Is this valid data? */
		if (empty($noti) || empty($user))
			$context['Breeze']['ajax']['ok'] = 'error';

		/* We must make sure this noti really exists, we just must!!! */
		$noti_temp = $this->_notifications->getToUser($user);

		if (empty($noti_temp) || !array_key_exists($noti, $noti_temp))
			$context['Breeze']['ajax']['ok'] = 'error';

		else
		{
			/* All is good, mark this as read */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] = 'ok';
			$this->_notifications->markAsRead($noti);
		}

		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';
	}

	/**
	 * BreezeAjax::notidelete()
	 *
	 * Deletes a notification by ID
	 * @return
	 */
	public function notidelete()
	{
		global $context;

		checkSession('post', '', false);

		/* Set some values */
		$context['Breeze']['ajax'] = array(
			'ok' => 'error',
			'data' => 'error_'
		);

		/* Get the data */
		$noti = $this->_data->getValue('content');
		$user = $this->_data->getValue('user');

		/* Is this valid data? */
		if (empty($noti) || empty($user))
			$context['Breeze']['ajax']['ok'] = 'error';

		/* We must make sure this noti really exists, we just must!!! */
		$noti_temp = $this->_notifications->getToUser($user);

		if (empty($noti_temp) || !array_key_exists($noti, $noti_temp))
			$context['Breeze']['ajax']['ok'] = 'error';

		else
		{
			/* All is good, mark this as read */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] = 'ok';
			$this->_notifications->delete($noti);
		}

		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';
	}
}
