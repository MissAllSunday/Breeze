<?php

/**
 * BreezeMention
 *
 * The purpose of this file is to identify a mention in a string and convert that to a more easy to use format which will be used by the aprser class later, oh, and create the mention notification..
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

class BreezeMention
{
	protected $_notification;
	protected $_settings;
	protected $_tools;
	protected $_string;
	protected $_regex = '~{([\s\w,;-_\[\]\\\/\+\.\~\$\!]+)}~u';
	protected $_query;

	function __construct()
	{
	}

	public function mention($string, $noti_info = false)
	{
		global $user_info;

		/* Oh, common! really? */
		if (empty($string))
			return false;

		$this->_string = $string;

		/* Search for all possible names */
		if (preg_match_all($this->_regex, $this->_string, $matches, PREG_SET_ORDER))
			foreach($matches as $m)
				$querynames[] = trim($m[1]);

		/* Nothing was found */
		else
			return $this->_string;

		/* Load and set what we need */
		$this->_query = Breeze::quickQuery('members');
		$this->_notification = Breeze::notifications();
		$this->_settings = Breeze::settings();
		$this->_tools = Breeze::tools();
		$searchNames = array();

		/* Sorry, you just can't tag a single person more than once */
		$queryNames = array_unique($queryNames);

		/* Don't abuse... sorry, hardcoded for now*/
		if (count($queryNames) >= 10)
			$queryNames = array_slice($queryNames, 0, 10);

		/* Let's make a quick query here... */
		$tempParams = array (
			'rows' => 'id_member, member_name, real_name',
			'where' => 'real_name IN({array_string:names}) OR member_name IN({array_string:names})',
		);
		$tempData = array(
			'names' => $querynames,
		);
		$this->_query->params($tempParams, $tempData);
		$this->_query->getData('id_member', false);

		/* Get the actual users */
		$searchNames = !is_array($this->_query->dataResult()) ? array($this->_query->dataResult()) : $this->_query->dataResult();

		/* We got some results */
		if (!empty($searchNames))
		{
			/* You can't notify yourself */
			if (array_key_exists($user_info['id'], $searchNames))
				unset($searchNames[$user_info['id']]);

			/* Lets create the notification */
			foreach ($searchNames as $name)
			{
				$params = array(
					'user' => $user_info['id'],
					'user_to' => $name['id_member'],
					'type' => 'mention',
					'time' => time(),
					'read' => 0,
					'content' => $noti_info,
				);

				/* Notification here */
				$this->_notification->createMention($params);

				/* Ugly but necessary to include both display and real name */
				foreach ($querynames as $query)
				{
					$key = array_search($query, $name)

					$find[] = '{'. $name['member_name'] .'}';
				}

				/* Building the pre-format, format is as follows: {id,name,display} */
				$replace[] = '{'. $name['id_member'] .','. $name['member_name'] .','. $name['real_name'] .'}';
			}
		}

		/* Finally do the replacement */
		$this->_string = str_replace($find, $replace, $this->_string);

		/* Return the string */
		return $this->_string;
	}
}