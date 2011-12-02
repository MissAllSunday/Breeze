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

class Breeze_Logs
{
	function __construct($profile, $user = null)
	{
		$this->profile = (int)$profile;

		if (!empty($user))
			$this->user = (int)$user;

		Breeze::LoadMethod('DB');
	}

	/* Log profile visits */
	public function ProfileVisits()
	{
		global $user_info, $context;

		if (empty($this->profile))
			return;

		/* Don't log this if the user is visiting his/her own profile */
		if ($this->profile == $user_info['id'])
			return;
			
		/* Do not log guest people */
		if ($context['user']['is_guest'])
			return;

		$params = array(
			'rows' => 'profile, user, time, id',
			'where' => 'profile = {int:profile} AND user = {int:user}'
		);
		$data = array(
			'profile' => $this->profile,
			'user' => $user_info['id']
		);

		$temp = new Breeze_DB('breeze_visit_log');
		$temp->Params($params, $data);
		$temp->GetData('id');

		$temp2 = $temp->DataResult();

		/* Is this the first time? */
		if (empty($temp2))
		{
			$insert_data = array(
				'profile' => 'int',
				'user' => 'int',
				'time' => 'int'
			);
			$insert_values = array(
				$this->profile,
				$user_info['id'],
				time()
			);
			$insert_indexes = array(
				'id'
			);
			$insert = new Breeze_DB('breeze_visit_log');
			$insert->InsertData($insert_data, $insert_values, $insert_indexes);
		}

		/* No? then update the time*/
		else
		{
			$update_params = array(
				'set' =>'time = {int:time}',
				'where' => 'profile = {int:profile} AND user = {int:user}',
			);

			$update_data = array(
				'user' => $user_info['id'],
				'profile' => $this->profile,
				'time' => time()
			);

			$update = new Breeze_DB('breeze_visit_log');
			$update->Params($update_params, $update_data);
			$update->UpdateData();
		}
	}

	public function GetProfileVisits()
	{
		$last_week = strtotime('-1 week');

		$params = array(
			'rows' => 'profile, user, time, id',
			'where' => 'time >= {int:last_week} AND profile = {int:profile}'
		);
		$data = array(
			'last_week' => $last_week,
			'profile' => $this->profile
		);

		$temp = new Breeze_DB('breeze_visit_log');
		$temp->Params($params, $data);
		$temp->GetData('id');

		$temp2 = $temp->DataResult();

		if (!empty($temp2))
			return $temp2;

		else
			return array();
	}
	
	/* Get the latest status made */
	public function GetLatestStatus()
	{
		$last_week = strtotime('-1 week');

		$params = array(
			'rows' => 'id, owner_id, poster_id, time',
			'where' => 'time >= {int:last_week} AND owner_id != {int:profile} AND poster_id = {int:profile}',
			'limit' => 3
		);
		$data = array(
			'last_week' => $last_week,
			'profile' => $this->profile
		);

		$temp = new Breeze_DB('breeze_status');
		$temp->Params($params, $data);
		$temp->GetData();

		$temp2 = $temp->DataResult();

		if (!empty($temp2))
			return $temp2;

		else
			return array();
	
	
	}
}