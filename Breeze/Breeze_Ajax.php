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

abstract class Breeze_Ajax
{
	private static $query;
	private static $compare;

	public static function Call()
	{
		/* Load stuff */
		Breeze::Load(array(
			'Globals'
		));
		loadtemplate('BreezeAjax');

		/* Handling the subactions */
		$sa = new Breeze_Globals('get');

		/* Load the query class */
		self::$query = Breeze_Query::getInstance();
		
		/* Compare */
		self::$compare = self::$query->GetStatus();

		$subActions = array(
			'post' => self::Post(),
			'postcomment' => self::PostComment(),
			'delete' => self::Delete()
		);

		/* Does the subaction even exist? */
		if ($sa->Validate('sa') && in_array($sa->Raw('sa'), array_keys($subActions)))
			call_user_func($subActions[$sa->raw('sa')]);

		/* No?  then tell them there was an error... */
		/* else */
			/* some redirect here.. */
	}

	/* Deal with the status... */
	private static function Post()
	{
		global $context;

		Breeze::Load(array(
			'Query',
			'Display',
			'Globals'
		));

		/* Set some values */
		$context['Breeze']['ajax']['ok'] = '';
		$context['Breeze']['ajax']['data'] = '';

		/* Get the status data */
		$data = new Breeze_Globals('post');

		/* Do this only if the data is good */
		if ($data->Validate('content'))
		{
			/* Build the params array for the query */
			$params = array(
				$data->See('owner_id'),
				$data->See('poster_id'),
				time(),
				$data->See('content')
			);

			/* Store the status */
			self::$query->InsertStatus($params);

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
	private function PostComment()
	{
		global $context;

		Breeze::Load(array(
			'Query',
			'Display',
			'Globals'
		));

		/* By default it will show an error, we only do stuff if necesary */
		$context['Breeze']['ajax']['ok'] = '';

		/* Get the status data */
		$data = new Breeze_Globals('post');

		/* The status do exists */
		if (in_array($data->See('status_id'), self::$compare) && $data->Validate('content'))
		{
			/* Build the params array for the query */
			$params = array(
				$data->See('status_id'),
				$data->See('status_owner_id'),
				$data->See('poster_comment_id'),
				$data->See('profile_owner_id'),
				time(),
				$data->See('content')
			);

			/* Store the status */
			self::$query->InsertComment($params);

			/* The comment was added, build the server response */
			$display = new Breeze_Display($params, 'comment');

			/* Send the data to the template */
			$context['Breeze']['ajax']['ok'] = 'ok';
			$context['Breeze']['ajax']['data'] =  $display->HTML();
		}

			$context['template_layers'] = array();
			$context['sub_template'] = 'breeze_post';
	}

	/* Handles the deletion of both comments an status */
	private function Delete()
	{
		global $context;

		$context['Breeze']['ajax']['ok'] = '';
		$context['Breeze']['ajax']['data'] = '';

		/* Get the data */
		$sa = new Breeze_Globals('post');

		/* The status do exists */
		if (in_array($sa->See('id'), self::$compare))
		{
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
		}

		/* This comment/status was deleted already, let's tell the user about it. */
		else
			$context['Breeze']['ajax']['ok'] = 'deleted';

		$context['template_layers'] = array();
		$context['sub_template'] = 'breeze_post';
	}
}