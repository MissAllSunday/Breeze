<?php

/**
 * BreezeDisplay
 *
 * The purpose of this file is to create proper html based on the type and the info it got.
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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
	die('No direct access...');

class BreezeDisplay
{
	private $returnArray = array();
	private $params = array();
	private $UserInfo;
	private $tools;
	private $parse;
	private $type;

	function __construct($tools, $text)
	{
		global $breezeController;

		// Sometimes $breezeController won't be set
		if (empty($breezeController))
			$breezeController = new BreezeController();

		$this->tools = $tools;
		$this->text = $text;

		// The visitor's permissions
		$this->permissions = array(
			'poststatus' => allowedTo('breeze_postStatus'),
			'postcomments' => allowedTo('breeze_postComments'),
			'deleteStatus' => allowedTo('breeze_deleteStatus')
		);
	}

	public function HTML($params, $type)
	{
		global $context;

		if (empty($params) || empty($type))
			return false;

		$this->params = $params;
		$this->type = $type;

		// Load the user info
		$this->tools->loadUserInfo($this->params['poster_id']);

		// Set the normal time first...
		$this->params['time_raw'] = timeformat($this->params['time'], false);

		// Set the elapsed time
		$this->params['time'] = $this->tools->timeElapsed($this->params['time']);

		loadtemplate(Breeze::$name .'Display');

		// Pass everything to the template
		$context['template_layers'] = array();
		$context['sub_template'] = 'main';
		$context['Breeze']['type'] = $this->type;
		$context['Breeze']['params'] = $this->params;
		$context['Breeze']['permissions'] = $this->permissions;
		$context['Breeze']['text'] = $this->text;

		// Done
		return template_main();
	}
}
