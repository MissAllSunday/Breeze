<?php

/**
 * Breeze_
 *
 * The purpose of this file is
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
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
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');


	/* Wrapper functions */
	function WrapperBreeze_AjaxPost() { Breeze_Ajax::Post(); }
	function WrapperBreeze_AjaxPostComment() { Breeze_Ajax::PostComment(); }
	function WrapperBreeze_AjaxDelete() { Breeze_Ajax::Delete(); }

abstract class Breeze_Ajax
{
	public static $query;
	public static $compare;

	public static function Call()
	{
		/* Load stuff */
		Breeze::Load(array(
			'Query',
			'Display',
			'Globals'
		));
		loadtemplate('BreezeAjax');

		/* Handling the subactions */
		$sa = new Breeze_Globals('get');

		/* Load the query class */
		self::$query = Breeze_Query::getInstance();

		$subActions = array(
			'post' => 'WrapperBreeze_AjaxPost',
			'postcomment' => 'WrapperBreeze_AjaxPostComment',
			'delete' => 'WrapperBreeze_AjaxDelete'
		);

		/* Does the subaction even exist? */
		if (in_array($sa->Raw('sa'), array_keys($subActions)))
			$subActions[$sa->Raw('sa')]();

		/* No?  then tell them there was an error... */
		/* else */
			/* some redirect here.. */
	}

	/* Deal with the status... */
	public static function Post()
	{
		global $context;

		/* Set some values */
		$context['Breeze']['ajax']['ok'] = '';
		$context['Breeze']['ajax']['data'] = '';

		/* Get the status data */
		$data = new Breeze_Globals('post');

		/* Do this only if there is something to add to the database */
		if ($data->ValidateBody('content'))
		{
			/* Build the params array for the query */
			$params = array(
				'owner_id' => $data->See('owner_id'),
				'poster_id' => $data->See('poster_id'),
				'time' => time(),
				'body' => $data->See('content')
			);

			/* Store the status */
			self::$query->InsertStatus($params);

			/* Get the newly created status, we just need the id */
			$new_status = self::$query->GetSingleStatus();

			$params['id'] = $new_status['status_id'];

			/* The status was added, build the server response */
			$display = new Breeze_Display($params, 'status');

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
	public static function PostComment()
	{
		global $context;

		/* By default it will show an error, we only do stuff if necesary */
		$context['Breeze']['ajax']['ok'] = '';

		/* Get the status data */
		$data = new Breeze_Globals('post');

		/* The status do exists */
		if ($data->ValidateBody('content') && in_array($data->Raw('status_id'), array_keys(self::$query->GetStatus())))
		{
			/* Build the params array for the query */
			$params = array(
				'status_id' => $data->See('status_id'),
				'status_owner_id' => $data->See('status_owner_id'),
				'poster_id' => $data->See('poster_comment_id'),
				'profile_owner_id' => $data->See('profile_owner_id'),
				'time' => time(),
				'body' => $data->See('content')
			);

			/* Store the comment */
			self::$query->InsertComment($params);

			/* Once the comment was added, get it's ID form the DB */
			$new_comment = self::$query->GetSingleComment();

			$params['id'] = $new_comment['comments_id'];

			/* The comment was added, build the server response */
			$display = new Breeze_Display($params, 'comment');

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] =  $display->HTML();
		}

		else
			$context['Breeze']['ajax']['ok'] = 'error';

			$context['template_layers'] = array();
			$context['sub_template'] = 'breeze_post';
	}

	/* Handles the deletion of both comments an status */
	public static function Delete()
	{
		global $context;

		$context['Breeze']['ajax']['ok'] = '';
		$context['Breeze']['ajax']['data'] = '';

		/* Get the data */
		$sa = new Breeze_Globals('post');

			switch ($sa->See('type'))
			{
				case 'status':
					self::$query->DeleteStatus($sa->See('id'));
					break;
				case 'comment':
					self::$query->DeleteComment($sa->See('id'));
					break;
			}

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = 'ok';



		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';
	}
}