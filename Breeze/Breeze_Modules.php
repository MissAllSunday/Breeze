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

class Breeze_Modules
{
	public function __construct($id)
	{
		$this->id = $id;
		Breeze::LoadMethod(array('Subs','DB', 'Logs'));
	}

	public function GetAllModules()
	{
		return get_class_methods('Breeze_Modules');
	}

	public function GetBuddies()
	{
		global $context;

		$query_params = array(
			'rows' =>'buddy_list',
			'where' => 'id_member={int:id_member}',
			'limit' => 1
		);
		$query_data = array(
			'id_member' => $this->id
		);

		$query = new Breeze_DB('members');
		$query->Params($query_params, $query_data);
		$query->GetData(null, true);
		$temp = $query->DataResult();
		$temp2 = explode(',', $temp['buddy_list']);
		$columns = 3;
		$counter = 0;
		$array['title'] = 'Buddies';

		if (!empty($temp['buddy_list']))
		{
			$array['data'] = '<table><tr>';

			foreach($temp2 as $t)
			{
				$user = Breeze_Subs::LoadUserInfo($t);

				$array['data'] .= '<td> <img src="'.$user['avatar']['href'].'" width="40px;" /><br />'.$user['link'].' </td>';

				if ($counter % $columns == 0)
					$array['data'] .= '</tr><tr>';

				$counter++;
			}
			$array['data'] .= '</tr></table>';
		}

		return $array;
	}

	function GetVisits()
	{
		$return = '';
		$logs = new Breeze_logs($this->id);
		$temp = $logs->GetProfileVisits();

		$array['title'] = 'Latest Visitors';
		$array['data'] = '<ul class="breeze_last_visits">';

		if (!empty($temp))
			foreach($temp as $t)
			{
				$user = Breeze_Subs::LoadUserInfo($t['user']);

				$array['data'] .= '<li>'.$user['link'].'</li>';
			}

		$array['data'] .= '</ul>';

		return $array;
	}
}
