<?php

/**
 * BreezeController
 *
 * The purpose of this file is to create all dependencies on demand, in a centred, unique class
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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

class BreezeController
{
	protected $dependencies = array();

	public function __construct()
	{
		$this->container = new BreezeContainer();

		$this->set();
	}

	protected function set()
	{
		// Globals
		$this->container->globals = $this->container->asShared(function ($c)
		{
			return new BreezeGlobals();
		});

		// Form
		$this->container->form = $this->container->asShared(function ($c)
		{
			return new BreezeForm($c->tools);
		});

		// Tools
		$this->container->tools = $this->container->asShared(function ($c)
		{
			return new BreezeTools($c->settings, $c->text);
		});

		// Display
		$this->container->display = $this->container->asShared(function ($c)
		{
			return new BreezeDisplay($c->tools);
		});

		// Parser
		$this->container->parser = $this->container->asShared(function ($c)
		{
			return new BreezeParser($c->tools);
		});

		// Query
		$this->container->query = $this->container->asShared(function ($c)
		{
			return new BreezeQuery($c->tools, $c->parser);
		});

		// Notifications
		$this->container->notifications = $this->container->asShared(function ($c)
		{
			return new BreezeNotifications($c->tools, $c->query);
		});

		// Mention
		$this->container->mention = $this->container->asShared(function ($c)
		{
			return new BreezeMention($c->query, $c->notifications);
		});

		// Log
		$this->container->log = $this->container->asShared(function ($c)
		{
			return new BreezeLog($c->tools, $c->query);
		});
	}

	public function get($var)
	{
		return $this->container->$var;
	}
}
