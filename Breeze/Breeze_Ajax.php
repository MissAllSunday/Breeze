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
		/* We need all of this, really, we do. */
		Breeze::LoadMethod(array(
			'Settings',
			'Subs',
			'Globals',
			'Data',
			'DB',
			'UserInfo',
			'Validate'
		));
		loadtemplate('BreezeAjax');

		/* Handling the subactions */
		$sa = Breeze_Globals::factory('get');

		$subActions = array(
			'post' => 'self::Post',
			'postcomment' => 'self::PostComment',
			'delete' => 'self::Delete'
		/* More actions here... */
		);

		/* Does the subaction even exist? */
		if ($sa->validate('sa') && in_array($sa->raw('sa'), array_keys($subActions)))
			call_user_func($subActions[$sa->raw('sa')]);

		/* No?  then tell them there was an error... */
	}

	/* Deal with the status... */
	private function Post()
	{
		global $context;

		$context['breeze']['ok'] = '';
		$context['breeze']['post']['data'] = '';

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
			$data = $query->DataResult();

			$breeze_user_info = Breeze_UserInfo::Profile($profile_id, true);

			/* Breeze parser... comming soon :P */
			/* $query->data_result['body'] = Breeze::Parser($query->data_result['body']); */

			/* It's all OK... */
			$context['breeze']['ok'] = 'ok';
			$context['breeze']['post']['data'] = '
				<div class="windowbg">
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="breeze_user_status_avatar">
							'.$breeze_user_info.'
						</div>
						<div class="breeze_user_status_comment">
							'.$data['body'].'
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
		global $context;

		/* By default it will show an error, we only do stuff if necesary */
		$context['breeze']['ok'] = '';

		/* Get the status data */
		$send_data = Breeze_Globals::factory('post');

		/* Check if the status where this comment was posted do exists */
		$validate = Breeze_Validate::getInstance();

		/* /* The status do exists */
		if (in_array($send_data->see('status_id'), array_keys($validate->Get('status'))))
		{
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
				$data = $query->DataResult();

				$breeze_user_info = Breeze_UserInfo::Profile($profile_id, true);

				/* Breeze parser... comming soon :P */
				/* $query->data_result['body'] = Breeze::Parser($query->data_result['body']); */

				/* It's all OK... */
				$context['breeze']['ok'] = 'ok';
				$context['breeze']['post']['data'] = '
					<div class="description" id ="comment_id_'.$data['id'].'">
						<div class="breeze_user_inner">
							<div class="breeze_user_status_avatar">
								'.$breeze_user_info.'<br />
								'.$data['time'].'
							</div>
							<div class="breeze_user_status_comment">
								'.$data['body'].'<br />
								<a href="javascript:void(0)" id="'.$data['id'].'" class="breeze_delete_comment">Delete</a>
							</div>
							<div class="clear"></div>
						</div>
					</div>';
			}
		}
			$context['template_layers'] = array();
			$context['sub_template'] = 'post_status';
	}

	/* Handles the deletion of both comments an status */
	private function Delete()
	{
		global $context;

		$context['breeze']['ok'] = '';
		$context['breeze']['post']['data'] = '';

		/* Get the data */
		$sa = Breeze_Globals::factory('post');

		/* Check if the comment/tatus do exists */
		$validate = Breeze_Validate::getInstance();

		if (in_array($sa->see('id'), array_keys($validate->Get($sa->see('type')))))
		{
			/* Is this a comment? */
			if ($sa->validate('type') && $sa->see('type') == 'comment')
			{

				/* Perform the query */
				$params = array(
					'where' => 'id = {int:id}'
				);

				$data = array(
					'id' => $sa->see('id')
				);
				$deletedata = new Breeze_DB('breeze_comment');
				$deletedata->Params($params, $data);
				$deletedata->DeleteData();

				/* It's all OK... */
				$context['breeze']['post']['data'] = '';
				$context['breeze']['ok'] = 'ok';
			}

			/* No?  then it must be a status... */
			elseif ($sa->validate('type') && $sa->see('type') == 'status')
			{

				/* Perform the query, delete the comments first */
				$comment_params = array(
					'where' => 'status_id = {int:id}'
				);

				$comment_data = array(
					'id' => $sa->see('id')
				);
				$delete_comment_data = new Breeze_DB('breeze_comment');
				$delete_comment_data->Params($comment_params, $comment_data);
				$delete_comment_data->DeleteData();

				/* Then delete the status */
				$status_params = array(
					'where' => 'id = {int:id}'
				);

				$status_data = array(
					'id' => $sa->see('id')
				);
				$delete_status_data = new Breeze_DB('breeze_status');
				$delete_status_data->Params($status_params, $status_data);
				$delete_status_data->DeleteData();

				/* It's all OK... */
				$context['breeze']['post']['data'] = '';
				$context['breeze']['ok'] = 'ok';
			}
		}

		/* This comment/status was already been deleted, lets tell the user about it. */
		else
		{
			$context['breeze']['post']['data'] = '';
			$context['breeze']['ok'] = 'deleted';
		}

		$context['template_layers'] = array();
		$context['sub_template'] = 'post_status';
	}
}
