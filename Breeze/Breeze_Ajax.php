<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Ajax
{
	private function __construct()
	{
	}

	public static function factory()
	{
		LoadBreezeMethod('Breeze_Globals');
		loadtemplate('BreezeAjax');

		/* Handling the subactions */
		$sa = Breeze_Globals::factory('get');
		$subActions = array(
			'post' => 'self::Post',
			'postcomment' => 'self::PostComment'
		/* More actions here... */
		);

		/* Does the subaction even exist? */
		if ($sa->validate('sa') && in_array($sa->raw('sa'), array_keys($subActions)))
			call_user_func($subActions[$sa->raw('sa')]);
	}

	/* Deal with the status... */
	private function Post()
	{
		global $context, $user_info, $memberContext;

		$context['breeze']['validate'] = false;

		/* We need all of this, really, we do. */
		LoadBreezeMethod(array(
			'Breeze_Settings',
			'Breeze_Subs',
			'Breeze_Globals',
			'Breeze_Data',
			'Breeze_DB',
			'Breeze_UserInfo'
		));

		/* Get the status data */
		$send_data = Breeze_Globals::factory('post');

		/* The status was posted by the profile owner? */
		if ($send_data->see('owner_id') == $send_data->see('poster_id'))
			$profile_id = $send_data->see('owner_id');

		/* No? */
		else
			$profile_id = $send_data->see('poster_id');

		/* Build the params array for the query */
		$params = array(
			'owner_id' => $send_data->see('owner_id'),
			'poster_id' => $send_data->see('poster_id'),
			'body' => $send_data->see('content'),
			'type' => 'status'
		);

		/* Send the data far far away to be processed... */
		$status = Breeze_Data::factory('status');

		/* Lets check if the are no errors before doing the insert */
		if ($status->Check($send_data->see('content')))
		{
			$context['breeze']['validate'] = true;
			$status->Record($params);

			/* ...and just like that, this status was added to the database...
			and now we get the same status from the DB to build the server response. */

			$query_params = array(
				'rows' =>'id, owner_id, poster_id, time, body',
				'order' => '{raw:sort}',
			);
			$query_data = array(
				'sort' => 'id ASC',
			);
			$query = new Breeze_DB('breeze_status');
			$query->Params($query_params, $query_data);
			$query->GetData(null, true);

			$breeze_user_info = Breeze_UserInfo::Profile($profile_id);

			/* Breeze parser... comming soon :P */
			/* $query->data_result['body'] = Breeze::Parser($query->data_result['body']); */


			$context['breeze']['post']['status'] = '
				<div class="windowbg">
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="breeze_user_status_avatar">
							'.$breeze_user_info.'
						</div>
						<div class="breeze_user_status_comment">
							'.$query->data_result['body'].'
						</div>
						<div class="clear"></div>
					</div>
				<span class="botslice"><span></span></span>
				</div>';
		}

			$context['template_layers'] = array();
			$context['sub_template'] = 'post_status';
	}

	/* Basically the same as Post, get the data from the form, send it to Breeze_Data, if Breeze_Data says OK, then fill the html and send it to the ajax template */
	private function PostComment()
	{
		global $context, $user_info, $memberContext;

		$context['breeze']['validate'] = true;

		/* We need all of this, really, we do. */
		LoadBreezeMethod(array(
			'Breeze_Settings',
			'Breeze_Subs',
			'Breeze_Globals',
			'Breeze_Data',
			'Breeze_DB',
			'Breeze_UserInfo'
		));

		/* Get the status data */
		$send_data = Breeze_Globals::factory('post');

		$profile_id = $send_data->see('poster_comment_id');

		/* Build the params array for the query */
		$params = array(
			'status_id' => $send_data->see('status_id'),
			'status_owner_id' => $send_data->see('status_owner_id'),
			'poster_comment_id' => $send_data->see('poster_comment_id'),
			'profile_owner_id' => $send_data->see('profile_owner_id'),
			'body' => $send_data->see('content'),
			'type' => 'comment'
		);

		/* Send the data far far away to be processed... */
		$status = Breeze_Data::factory('comment');

		/* Lets check if the are no errors before doing the insert */
		if ($status->Check($send_data->see('content')))
		{
			$context['breeze']['validate'] = true;
			$status->Record($params);

			/* ...and just like that, this status was added to the database...
			and now we get the same status from the DB to build the server response. */

			$query_params = array(
				'rows' =>'id, status_id status_owner_id, poster_comment_id, profile_owner_id, time, body',
				'order' => '{raw:sort}',
				'where' => 'status_id={int:status_id}'
			);
			$query_data = array(
				'sort' => 'id ASC',
				'status_id' => $send_data->see('status_id')
			);
			$query = new Breeze_DB('breeze_comment');
			$query->Params($query_params, $query_data);
			$query->GetData(null, true);

			$breeze_user_info = Breeze_UserInfo::Profile($profile_id);

			/* Breeze parser... comming soon :P */
			/* $query->data_result['body'] = Breeze::Parser($query->data_result['body']); */


			$context['breeze']['post']['status'] = '
				<div class="windowbg2">
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="breeze_user_status_avatar">
							'.$breeze_user_info.'
						</div>
						<div class="breeze_user_status_comment">
							'.$query->data_result['body'].'
						</div>
						<div class="clear"></div>
					</div>
				<span class="botslice"><span></span></span>
				</div>';
		}

			$context['template_layers'] = array();
			$context['sub_template'] = 'post_status';
	}
}
