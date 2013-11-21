<?php

/**
 * BreezeController
 *
 * The purpose of this file is to create all dependencies on demand, in a centered, unique class
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

		// Settings
		$this->container->settings = $this->container->asShared(function ($c)
		{
			return new BreezeSettings();
		});

		// Text
		$this->container->text = $this->container->asShared(function ($c)
		{
			return new BreezeText();
		});

		// Tools
		$this->container->tools = $this->container->asShared(function ($c)
		{
			return new BreezeTools($c->settings, $c->text);
		});

		// Display
		$this->container->display = $this->container->asShared(function ($c)
		{
			return new BreezeDisplay($c->tools, $c->text);
		});

		// Parser
		$this->container->parser = $this->container->asShared(function ($c)
		{
			return new BreezeParser($c->settings, $c->tools);
		});

		// Query
		$this->container->query = $this->container->asShared(function ($c)
		{
			return new BreezeQuery($c->settings, $c->text, $c->tools, $c->parser);
		});

		// Form
		$this->container->form = $this->container->asShared(function ($c)
		{
			return new BreezeForm($c->text);
		});

		// Notifications
		$this->container->notifications = $this->container->asShared(function ($c)
		{
			return new BreezeNotifications($c->settings, $c->text, $c->tools, $c->query);
		});

		// Buddy
		$this->container->buddy = $this->container->asShared(function ($c)
		{
			return new BreezeBuddy($c->settings,$c->query, $c->notifications, $c->text);
		});

		// Mention
		$this->container->mention = $this->container->asShared(function ($c)
		{
			return new BreezeMention($c->settings, $c->query, $c->notifications);
		});

		// Log
		$this->container->log = $this->container->asShared(function ($c)
		{
			return new BreezeLog($c->settings, $c->text, $c->tools, $c->query);
		});
	}

	public function get($var)
	{
		return $this->container->$var;
	}
}
