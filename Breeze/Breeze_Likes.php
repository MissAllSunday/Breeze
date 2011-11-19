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
		LoadBreezeMethod('Breeze_DB');
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
				'rows' =>'id, status_id comment_id, profile_id, userwholiked_id, time, liked',
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
				'rows' =>'id, status_id comment_id, profile_id, userwholiked_id, time, liked',
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
				'rows' =>'id, status_id comment_id, profile_id, userwholiked_id, time, liked',
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
}
