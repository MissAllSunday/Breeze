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

abstract class BreezeAjax extends Breeze
{
	public static $query;

	public static function call()
	{
		/* Load stuff */
		loadtemplate('BreezeAjax');

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
		if (in_array($sglobals->getValue('sa'), array_keys($subActions)))
			call_user_func($subActions[$sglobals->getValue('sa')]);

		/* No?  then tell them there was an error... */
		/* else */
			/* some redirect here.. */
	}

	/* Deal with the status... */
	public static function post()
	{
		global $context;

		/* You aren't allowed in here, let's show you a nice message error... */
		if (!allowedTo('breeze_postStatus'))
			return false;

		checkSession('post', '', false);

		/* Set some values */
		$context['Breeze']['ajax'] = array(
			'ok' => '',
			'data' => ''
		);

		/* Call the parent */
		parent::__construct();

		/* Load all the things we need */
		$data = $this->sGlobals('post');
		$query = $this->query();
		$parser = $this->parser();
		$mention = $this->mention();
		$settings = $this->settings();

		/* Do this only if there is something to add to the database */
		if ($data->validateBody('content'))
		{
			$body = $data->getValue('content');

			$params = array(
					'owner_id' => $data->getValue('owner_id'),
					'poster_id' => $data->getValue('poster_id'),
					'time' => time(),
					'body' => $mention->preMention($body),
				);

			/* Store the status */
			$query->insertStatus($params);

			/* Get the newly created status, we just need the id */
			$newStatus = $query->getLastStatus();

			/* Set the ID */
			$params['id'] = $newStatus['status_id'];

			/* Build the notifications */
			$mention->mention(array(
				'wall_owner' => $data->getValue('owner_id'),
				'wall_poster' => $data->getValue('poster_id'),
				'status_id' => $params['id'],
			));

			/* Parse the content */
			$params['body'] = $parser->display($params['body']);

			/* The status was added, build the server response */
			$display = new Breezedisplay($params, 'status');

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] =  $display->HTML();
		}

		else
			$context['Breeze']['ajax']['ok'] = 'error';

			$context['template_layers'] = array();
			$context['sub_template'] = 'breeze_post';
	}

	/* Basically the same as Post */
	public static function postComment()
	{
		global $context, $scripturl;

		/* You aren't allowed in here, let's show you a nice message error... */
		if (!allowedTo('breeze_postComments'))
			return false;

		checkSession('post', '', false);

		/* By default it will show an error, we only do stuff if necesary */
		$context['Breeze']['ajax']['ok'] = '';

		/* Call the parent */
		parent::__construct();

		/* Load all the things we need */
		$data = $this->sGlobals('post');
		$query = $this->query();
		$parser = $this->parser();
		$mention = $this->mention();
		$settings = $this->settings();
		$temp_id_exists = $query->getSingleValue('status', 'id', $data->getValue('status_id'));

		/* The status do exists and the data is valid*/
		if ($data->validateBody('content') && !empty($temp_id_exists))
		{
			$body = $data->getValue('content');

			/* Build the params array for the query */
			$params = array(
				'status_id' => $data->getValue('status_id'),
				'status_owner_id' => $data->getValue('status_owner_id'),
				'poster_id' => $data->getValue('poster_comment_id'),
				'profile_owner_id' => $data->getValue('profile_owner_id'),
				'time' => time(),
				'body' => $mention->preMention($body)
			);

			/* Store the comment */
			$query->insertComment($params);

			/* Once the comment was added, get it's ID from the DB */
			$new_comment = $query->getLastComment();

			/* Set the ID */
			$params['id'] = $new_comment['comments_id'];

			/* build the notification */
			$mention->mention(array(
				'wall_owner' => $data->getValue('owner_id'),
				'wall_poster' => $data->getValue('poster_id'),
				'wall_status_owner' => $data->getValue('status_owner_id'),
				'comment_id' => $params['id'],
				'status_id' => $data->getValue('status_id'),
			));

			/* Parse the content */
			$params['body'] = $parser->display($params['body']);

			/* The comment was added, build the server response */
			$display = new Breezedisplay($params, 'comment');

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] =  $display->HTML();
		}

		else
			$context['Breeze']['ajax']['ok'] = 'error';

			$context['template_layers'] = array();
			$context['sub_template'] = 'breeze_post';

			unset($temp_id_exists);
	}

	/* Handles the deletion of both comments an status */
	public static function delete()
	{
		global $context;

		/* You aren't allowed in here, let's show you a nice message error... */
		isAllowedTo('breeze_deleteStatus');

		$context['Breeze']['ajax']['ok'] = '';
		$context['Breeze']['ajax']['data'] = '';

		/* Call the parent */
		parent::__construct();

		/* Get the data */
		$sa = $this->sGlobals('post');
		$query = $this->query();
		$temp_id_exists = $query->getSingleValue($sa->getValue('type') == 'status' ? 'status' : 'comments', 'id', $sa->getValue('id'));

			switch ($sa->getValue('type'))
			{
				case 'status':
					/* Do this only if the status wasn't deleted already */
					if (!empty($temp_id_exists))
					{
						$query->deleteStatus($sa->getValue('id'));
						$context['Breeze']['ajax']['ok'] = 'ok';
					}

					else
						$context['Breeze']['ajax']['ok'] = 'deleted';

					break;
				case 'comment':
					/* Do this only if the comment wasn't deleted already */
					if (!empty($temp_id_exists))
					{
						$query->deleteComment($sa->getValue('id'));
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

	/* Mark a notification as read */
	public static function notimark()
	{
		global $context;

		checkSession('post', '', false);

		$keys = array();

		/* Set some values */
		$context['Breeze']['ajax'] = array(
			/* By default we assume all went terrible wrong... */
			'ok' => 'error',
			/* This will be empty anyway, maybe in the future I will find a use for it */
			'data' => 'error_'
		);

		/* Call the parent */
		parent::__construct();

		/* Load what we need */
		$sa = $this->sGlobals('request');
		$query = $this->query();
		$notifications = Breeze::notifications();

		/* Get the data */
		$noti = $sa->getValue('content');
		$user = $sa->getValue('user');

		/* Is this valid data? */
		if (empty($noti) || empty($user))
			$context['Breeze']['ajax']['ok'] = 'error';

		/* We must make sure this noti really exists, we just must!!! */
		$noti_temp = $notifications->getToUser($user);

		if (empty($noti_temp) || !array_key_exists($noti, $noti_temp))
			$context['Breeze']['ajax']['ok'] = 'error';

		else
		{
			/* All is good, mark this as read */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] = 'ok';
			$notifications->markAsRead($noti);
		}

		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';
	}
}