<?php

/**
 * BreezeAjax
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica Gonz�lez <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica Gonz�lez
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
 * Jessica Gonz�lez.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');


	/* Wrapper functions */
	function WrapperBreeze_AjaxPost() { BreezeAjax::Post(); }
	function WrapperBreeze_AjaxPostComment() { BreezeAjax::PostComment(); }
	function WrapperBreeze_AjaxDelete() { BreezeAjax::Delete(); }

abstract class BreezeAjax
{
	public static $query;

	public static function call()
	{
		/* Load stuff */
		loadtemplate('BreezeAjax');

		/* Handling the subactions */
		$sa = new BreezeGlobals('get');

		$subActions = array(
			'post' => 'WrapperBreeze_AjaxPost',
			'postcomment' => 'WrapperBreeze_AjaxPostComment',
			'delete' => 'WrapperBreeze_AjaxDelete'
		);

		/* Does the subaction even exist? */
		if (in_array($sa->getRaw('sa'), array_keys($subActions)))
			$subActions[$sa->getRaw('sa')]();

		/* No?  then tell them there was an error... */
		/* else */
			/* some redirect here.. */
	}

	/* Deal with the status... */
	public static function postStatus()
	{
		global $context;

		/* You aren't allowed in here, let's show you a nice message error... */
		if (!allowedTo('breeze_postStatus'))
			return false;

		checkSession('post', '', false);

		/* Set some values */
		$context['Breeze']['ajax'] = array(
			'ok' => false,
			'data' => ''
		);

		/* Load all the things we need */
		$data = new BreezeGlobals('post');
		$query = Breeze::query();
		$parser = new BreezeParser();
		$settings = Breeze::settings();
		$text = Breeze::text();

		/* Do this only if there is something to add to the database */
		if ($data->validateBody('content'))
		{
			$body = $data->getValue('content');

			/* Needed for the notification by mention */
			$noti_info = array(
				'wall_owner' => $data->getValue('owner_id'),
				'wall_poster' => $data->getValue('poster_id'),
			);

			/* Build the params array for the query */
			$params = array(
				'owner_id' => $data->getValue('owner_id'),
				'poster_id' => $data->getValue('poster_id'),
				'time' => time(),
				'body' => $parser->displayText($body, $noti_info)
			);

			/* Store the status */
			$query->InsertStatus($params);

			/* Get the newly created status, we just need the id */
			$new_status = $query->getLastStatus();

			$params['id'] = $new_status['status_id'];

			/* The status was added, build the server response */
			$display = new BreezeDisplay($params, 'status');

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = true;
			$context['Breeze']['ajax']['data'] =  $display->HTML();
		}

		else
			$context['Breeze']['ajax']['ok'] = false;

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

		/* Get the status data */
		$data = new BreezeGlobals('post');
		$query = BreezeQuery::getInstance();
		$temp_id_exists = $query->GetSingleValue('status', 'id', $data->getValue('status_id'));
		$parser = new BreezeParser();
		$notification = new BreezeNotifications();
		$tools = BreezeSettings::getInstance();

		/* The status do exists and the data is valid*/
		if ($data->ValidateBody('content') && !empty($temp_id_exists))
		{
			$body = $data->getValue('content');

			/* Build the params array for the query */
			$params = array(
				'status_id' => $data->getValue('status_id'),
				'status_owner_id' => $data->getValue('status_owner_id'),
				'poster_id' => $data->getValue('poster_comment_id'),
				'profile_owner_id' => $data->getValue('profile_owner_id'),
				'time' => time(),
				'body' => $parser->Display($body)
			);

			/* Store the comment */
			$query->InsertComment($params);

			/* Once the comment was added, get it's ID from the DB */
			$new_comment = $query->GetLastComment();

			$params['id'] = $new_comment['comments_id'];

			/* Send out the notifications first thing to do, is to collect all the users who had posted on this status */
			$temp_comments = $query->GetCommentsByStatus($data->getValue('status_id'));

			/* Create the users array */
			foreach($temp_comments as $c)
				$notification_users[] = $c['poster_id'];

			/* Load the user's info */
			$users_to_load = array(
				$data->getValue('poster_comment_id'),
				$data->getValue('status_owner_id'),
				$data->getValue('profile_owner_id')
			);
			$users_data = BreezeSubs::LoadUserInfo($users_to_load);

			$user_who_commented = $users_data[$data->getValue('poster_comment_id')];
			$user_who_created_the_status = $users_data[$data->getValue('status_owner_id')];
			$user_who_owns_the_profile = $users_data[$data->getValue('profile_owner_id')];

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
							'user_who_commented' => $data->getValue('poster_comment_id'),
							'user_who_created_the_status' => $data->getValue('status_owner_id'),
							'user_who_owns_the_profile' => $data->getValue('profile_owner_id')
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
	public static function Delete()
	{
		global $context;

		/* You aren't allowed in here, let's show you a nice message error... */
		isAllowedTo('breeze_deleteStatus');

		$context['Breeze']['ajax']['ok'] = '';
		$context['Breeze']['ajax']['data'] = '';

		/* Get the data */
		$sa = new BreezeGlobals('post');
		$query = BreezeQuery::getInstance();
		$temp_id_exists = $query->GetSingleValue($sa->getRaw('type') == 'status' ? 'status' : 'comments', 'id', $sa->getValue('id'));

			switch ($sa->getRaw('type'))
			{
				case 'status':
					/* Do this only if the status wasn't deleted already */
					if (!empty($temp_id_exists))
					{
						$query->DeleteStatus($sa->getValue('id'));
						$context['Breeze']['ajax']['ok'] = 'ok';
					}

					else
						$context['Breeze']['ajax']['ok'] = 'deleted';

					break;
				case 'comment':
					/* Do this only if the comment wasn't deleted already */
					if (!empty($temp_id_exists))
					{
						$query->DeleteComment($sa->getValue('id'));
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