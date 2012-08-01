<?php

/**
 * BreezeParser
 *
 * The purpose of this file is to identify something in a tezt string and convert that to something different, for example, a url into an actual html link.
 * @package Breeze mod
 * @version 1.0 Beta 3
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

class BreezeParser
{
	private $notification;

	function __construct()
	{
		$this->notification = Breeze::notifications();
		$this->settings = Breeze::settings();
		$this->tools = Breeze::tools();

		/* Regex stuff */
		$this->regex = array(
			'url' => '~(?<=[\s>\.(;\'"]|^)((?:http|https)://[\w\-_%@:|]+(?:\.[\w\-_%]+)*(?::\d+)?(?:/[\w\-_\~%\.@!,\?&;=#(){}+:\'\\\\]*)*[/\w\-_\~%@\?;=#}\\\\])~i',
			'mention' => '~{([\s\w,;-_\[\]\\\/\+\.\~\$\!]+)}~u'
		);
	}

	public function display($string, $mention_info = false)
	{
		$this->s = $string;
		$temp = get_class_methods('BreezeParser');
		$temp = BreezeTools::remove($temp, array('__construct', 'display'), false);

		/* Used to notify the user */
		if ($mention_info)
			$this->mention_info = $mention_info;

		foreach ($temp as $t)
			$this->s = $this->$t($this->s);

		return $this->s;
	}

	/* Convert any valid urls on to links */
	private function urltoLink($s)
	{
		if (preg_match_all($this->regex['url'], $s, $matches))
			foreach($matches[0] as $m)
				$s = str_replace($m, '<a href="'.$m.'" class="bbc_link" target="_blank">'.$m.'</a>', $s);

		return $s;
	}

	private function mention($s)
	{
		global $user_info, $scripturl;

		$tempQuery = Breeze::quickQuery('members');

		/* Serach for all possible names */
		if (preg_match_all($this->regex['mention'], $s, $matches, PREG_SET_ORDER))
			foreach($matches as $m)
				$querynames[] = $m[1];

		/* Nothing was found */
		else
			return $s;

		/* Let's make a quick query here... */
		$tempParams = array (
			'rows' => 'id_member, member_name, real_name',
			'where' => 'LOWER(real_name) IN({array_string:names}) OR LOWER(member_name) IN({array_string:names})',
		);
		$tempData = array(
			'names' => array_unique($querynames),
		);
		$tempQuery->params($tempParams, $tempData);
		$tempQuery->getData('id_member', false);
		$searchNames = !is_array($tempQuery->dataResult()) ? array($tempQuery->dataResult()) : $tempQuery->dataResult();
		reset($matches);

		/* We got some results */
		if (!empty($searchNames))
		{
			/* Lets get the names */
			foreach($matches as $m)
				$names[] = trim($m[1]);

				/* You can't tag yourself */
				foreach($names as $name)
					if (!array_key_exists($user_info['id'], $searchNames))
					{
						$id = $this->tools->returnKey($name, $searchNames);
						
						echo '<pre>';print_r($id);echo '</pre>';

						/* is this a valid user? */
						if (!empty($id))
							$s = str_replace('{'.$name .'}', '@<a href="' . $scripturl . '?action=profile;u=' . $id . '">' . $name . '</a>', $s);

						else
							$s = str_replace('{'.$name .'}', '@' . $name, $s);
					}

			reset($matches);
		}

		/* There is no users, so just replace the names with a nice @ */
		else
			foreach($matches as $m)
				$s = str_replace($m[0], '@'.$m[1], $s);

		/* We are done mutilating the string, lets returning it */
		return $s;
	}
}