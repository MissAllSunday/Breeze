<?php

/**
 * BreezeAjax
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 2
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


	/* Wrapper functions */
	function WrapperBreeze_AjaxPost() { BreezeAjax::post(); }
	function WrapperBreeze_AjaxPostComment() { BreezeAjax::postComment(); }
	function WrapperBreeze_AjaxDelete() { BreezeAjax::delete(); }

abstract class BreezeAjax
{
	public static $query;

	public static function call()
	{
		/* Load stuff */
		loadtemplate('BreezeAjax');

		/* Handling the subactions */
		$sglobals = breeze::sGlobals('get');

		$subActions = array(
			'post' => 'WrapperBreeze_AjaxPost',
			'postcomment' => 'WrapperBreeze_AjaxPostComment',
			'delete' => 'WrapperBreeze_AjaxDelete'
		);

		/* Does the subaction even exist? */
		if (in_array($sglobals->Raw('sa'), array_keys($subActions)))
			$subActions[$sglobals->Raw('sa')]();

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

		/* Load all the things we need */
		$data = Breeze::sGlobals('post');
		$query = Breeze::query();
		$parser = Breeze::parser();
		$settings = Breeze::settings();

		/* Do this only if there is something to add to the database */
		if ($data->ValidateBody('content'))
		{
			$body = $data->See('content');

			/* Needed for the notification by mention */
			$noti_info = array(
				'wall_owner' => $data->See('owner_id'),
				'wall_poster' => $data->See('poster_id'),
			);

			/* Build the params array for the query */
			$params = array(
				'owner_id' => $data->See('owner_id'),
				'poster_id' => $data->See('poster_id'),
				'time' => time(),
				'body' => $parser->Display($body, $noti_info)
			);

			/* Store the status */
			$query->insertStatus($params);

			/* Get the newly created status, we just need the id */
			$new_status = $query->getLastStatus();

			$params['id'] = $new_status['status_id'];

			/* The status was added, build the server response */
			$display = new BreezeDisplay($params, 'status');

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

		/* Load all the things we need */
		$data = Breeze::sGlobals('post');
		$query = Breeze::query();
		$parser = Breeze::parser();
		$settings = Breeze::settings();
		$temp_id_exists = $query->getSingleValue('status', 'id', $data->See('status_id'));
		$notifications = Breeze::notifications();

		/* The status do exists and the data is valid*/
		if ($data->ValidateBody('content') && !empty($temp_id_exists))
		{
			$body = $data->See('content');

			/* Build the params array for the query */
			$params = array(
				'status_id' => $data->See('status_id'),
				'status_owner_id' => $data->See('status_owner_id'),
				'poster_id' => $data->See('poster_comment_id'),
				'profile_owner_id' => $data->See('profile_owner_id'),
				'time' => time(),
				'body' => $parser->Display($body)
			);

			/* Store the comment */
			$query->insertComment($params);

			/* Once the comment was added, get it's ID from the DB */
			$new_comment = $query->getLastComment();

			$params['id'] = $new_comment['comments_id'];

			/* Send out the notifications first thing to do, is to collect all the users who had posted on this status */
			$temp_comments = $query->getCommentsByStatus($data->See('status_id'));

			/* Create the users array */
			foreach($temp_comments as $c)
				$notification_users[] = $c['poster_id'];

			/* Load the user's info */
			$users_to_load = array(
				$data->See('poster_comment_id'),
				$data->See('status_owner_id'),
				$data->See('profile_owner_id')
			);
			$users_data = BreezeSubs::LoadUserInfo($users_to_load);

			$user_who_commented = $users_data[$data->See('poster_comment_id')];
			$user_who_created_the_status = $users_data[$data->See('status_owner_id')];
			$user_who_owns_the_profile = $users_data[$data->See('profile_owner_id')];

			/* Send it already! */
			if (!empty($notification_users))
			{
				foreach($notification_users as $nu)
				{
					$notification_params = array(
						'user' => $nu,
						'type' => 'comment',
						'time' => time(),
						'read' => 0,
						'content' => array(
							'user_who_commented' => $data->See('poster_comment_id'),
							'user_who_created_the_status' => $data->See('status_owner_id'),
							'user_who_owns_the_profile' => $data->See('profile_owner_id')
						)
					);

					$notification->Create($notification_params);
				}
			}

			/* The comment was added, build the server response */
			$display = new BreezeDisplay($params, 'comment');

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

		/* Get the data */
		$sa = new BreezeGlobals('post');
		$query = BreezeQuery::getInstance();
		$temp_id_exists = $query->getSingleValue($sa->Raw('type') == 'status' ? 'status' : 'comments', 'id', $sa->See('id'));

			switch ($sa->Raw('type'))
			{
				case 'status':
					/* Do this only if the status wasn't deleted already */
					if (!empty($temp_id_exists))
					{
						$query->deleteStatus($sa->See('id'));
						$context['Breeze']['ajax']['ok'] = 'ok';
					}

					else
						$context['Breeze']['ajax']['ok'] = 'deleted';

					break;
				case 'comment':
					/* Do this only if the comment wasn't deleted already */
					if (!empty($temp_id_exists))
					{
						$query->deleteComment($sa->See('id'));
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
}