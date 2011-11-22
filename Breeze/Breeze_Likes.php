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

class Breeze_Likes
{
	private static $instance;

	private function __construct()
	{
		LoadBreezeMethod(array('Breeze_DB', 'Breeze_Validate'));
	}

	public static function getInstance()
	{
		if (!self::$instance)
		 {
			self::$instance = new Breeze_Likes();
		}
		return self::$instance;
	}

	public function LoadByStatus($statusID)
	{
		$return = '';

		if (empty($statusID))
			return $return;

		else
		{
			$query_params = array(
				'rows' =>'id, status_id, comment_id, profile_id, userwholiked_id, time, liked',
				'order' => '{raw:sort}',
				'where' => 'status_id={int:status_id}'
			);
			$query_data = array(
				'sort' => 'id',
				'status_id' => $statusID
			);
			$query = new Breeze_DB('breeze_likes');
			$query->Params($query_params, $query_data);
			$query->GetData('status_id');
			
			if (!empty($query->data_result))
				$return = $query->data_result;

			return $return;
		}
	}

	public function LoadByComment($commentID)
	{
		$return = '';

		if (empty($commentID))
			return $return;

		else
		{
			$query_params = array(
				'rows' =>'id, status_id, comment_id, profile_id, userwholiked_id, time, liked',
				'order' => '{raw:sort}',
				'where' => 'status_id={int:comment_id}'
			);
			$query_data = array(
				'sort' => 'id',
				'status_id' => $commentID
			);
			$query = new Breeze_DB('breeze_likes');
			$query->Params($query_params, $query_data);
			$query->GetData('comment_id');
			
			if (!empty($query->data_result))
				$return = $query->data_result;

			return $return;
		}
	}
	
	public function LoadByProfile($profileID)
	{
		$return = '';

		if (empty($profileID))
			return $return;

		else
		{
			$query_params = array(
				'rows' =>'id, status_id, comment_id, profile_id, userwholiked_id, time, liked',
				'order' => '{raw:sort}',
				'where' => 'status_id={int:profile_id}'
			);
			$query_data = array(
				'sort' => 'id',
				'status_id' => $profileID
			);
			$query = new Breeze_DB('breeze_likes');
			$query->Params($query_params, $query_data);
			$query->GetData('profile_id');
			
			if (!empty($query->data_result))
				$return = $query->data_result;

			return $return;
		}
	}
	
	public static function Insert($data)
	{
		if (empty($data))
			return;

		$validate = Breeze_Validate::getInstance();

		/* You cannot like the same comment, in the same status, in the same profile twice... */
		$query_params = array(
			'rows' =>'id, status_id, comment_id, profile_id, userwholiked_id, liked',
			'where' => 'status_id={int:status_id} AND comment_id={int:comment_id} AND profile_id={int:profile_id} AND userwholiked_id={int:userwholiked_id}',
			'limit' => 1
		);
		$query_data = array(
			'status_id' => $data['status_id'],
			'comment_id' => $data['comment_id'],
			'profile_id' => $data['profile_id'],
			'userwholiked_id' => $data['userwholiked_id']
		);
		$query = new Breeze_DB('breeze_likes');
		$query->Params($query_params, $query_data);
		$query->GetData();

		if ($query->data_result != '' && in_array($data['status_id'], array_keys($validate->Get('status'))) && in_array($data['comment_id'], array_keys($validate->Get('comment'))))
		{
			$params = array(
				'set' =>'liked={int:like_change}',
				'where' => 'id = {int:id}',
			);

			$data = array(
				'like_change' => $data['liked'],
				'id' => $data['id']
			);

			$updatedata = new Breeze_DB('breeze_likes');
			$updatedata->Params($params, $data);
			$updatedata->UpdateData();
		}

		else
		{
			/* We still need to check if the status/comment exists */
			$validate = Breeze_Validate::getInstance();

			if (in_array($data['status_id'], array_keys($validate->Get('status'))) && in_array($data['comment_id'], array_keys($validate->Get('comment'))))
			{
				$insert_data = array(
					'userwholiked_id' => 'int',
					'status_id' => 'int',
					'comment_id' => 'int',
					'profile_id' => 'int',
					'time' => 'int',
					'liked' => 'int'
				);
				$insert_values = array(
					$data['userwholiked_id'],
					$data['status_id'],
					$data['comment_id'],
					$data['profile_id'],
					time(),
					$data['liked']
				);
				$insert_indexes = array(
					'id'
				);
				$insert = new Breeze_DB('breeze_likes');
				$insert->InsertData($insert_data, $insert_values, $insert_indexes);
			}
		}
	}
}
