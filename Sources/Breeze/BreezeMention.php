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
	protected $_regex;
	protected $_query;
	protected $_searchNames = array();
	protected $_queryNames = array();

	function __construct()
	{
		$this->_regex = '~{([\s\w,;-_\[\]\\\/\+\.\~\$\!]+)}~u';
		$this->_notification = Breeze::notifications();
		$this->_settings = Breeze::settings();
	}

	/*
	 * Converts raw data to a preformatted text
	 *
	 * Gets the raw string and converts it to a formatted string: {id,real_name,display_name} to be saved by the database.
	 * @see BreezeAjax class
	 * @access protected
	 * @return string the formatted string
	 */
	public function preMention($string)
	{
		/* Oh, common! really? */
		if (empty($string))
			return false;

		$this->_string = $string;

		/* Search for all possible names */
		if (preg_match_all($this->_regex, $this->_string, $matches, PREG_SET_ORDER))
			foreach($matches as $m)
				$this->_queryNames[] = trim($m[1]);

		/* Do this if we have something */
		if (!empty($this->_queryNames))
		{
			/* Load and set what we need */
			$this->_query = Breeze::quickQuery('members');

			/* We need an array */
			$this->_queryNames = is_array($this->_queryNames) ? $this->_queryNames : array($this->_queryNames);

			/* Sorry, you just can't tag a single person more than once */
			$this->_queryNames = array_unique($this->_queryNames);

			/* Don't abuse... sorry, hardcoded for now */
			if (count($this->_queryNames) >= 10)
				$this->_queryNames = array_slice($this->_queryNames, 0, 10);

			/* Let's make a quick query here... */
			$this->_query->params(
				array (
					'rows' => 'id_member, member_name, real_name',
					'where' => 'real_name IN({array_string:names}) OR member_name IN({array_string:names})',
				),
				array(
					'names' => $this->_queryNames,
				)
			);
			$this->_query->getData('id_member', false);

			/* Get the actual users */
			$this->_searchNames = !is_array($this->_query->dataResult()) ? array($this->_query->dataResult()) : $this->_query->dataResult();

			/* We got some results */
			if (!empty($this->_searchNames))
			{
				/* Let's create the notification */
				foreach ($this->_searchNames as $name)
				{
					/* Ugly, but we need to associate the raw name with the actual names somehow... */
					foreach ($this->_queryNames as $query)
					{
						if (in_array($query, $name))
							$name['raw_name'] = $query;

						/* No? then use the display name and hope for the best... */
						else
							$name['raw_name'] = $name['member_name'];
					}

					/* Let's create the preformat */
					$find[] = '{'. $name['raw_name'] .'}';
					$replace[] = '{'. $name['id_member'] .','. $name['member_name'] .','. $name['real_name'] .'}';
				}
			}

			/* Finally do the replacement */
			$this->_string = str_replace($find, $replace, $this->_string);
		}

		return $this->_string;
	}

	public function mention($noti_info)
	{
		global $user_info;

		/* You can't notify yourself */
		if (array_key_exists($user_info['id'], $this->_searchNames))
			unset($this->_searchNames[$user_info['id']]);

		foreach ($this->_searchNames as $name)
		{
			/* Append the mentioned user ID */
			$noti_info['wall_mentioned'] = $name['id_member'];

			/* Notification here */
			$this->_notification->createMention(array(
				'user' => $user_info['id'],
				'user_to' => $name['id_member'],
				'type' => 'mention',
				'time' => time(),
				'read' => 0,
				'content' => $noti_info,
			));
		}
	}
}