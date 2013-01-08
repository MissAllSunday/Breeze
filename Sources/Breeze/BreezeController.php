<?php

/**
 * BreezeController
 *
 * The purpose of this file is to create all dependencies on demand, in a centered, unique class
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
	die('No direct access...');

class BreezeController
{
	public function __construct()
	{
		$this->container-> = new BreezeContainer();
	}

	public function globals()
	{
		return $this->container->globals = $this->container->asShared(function ($c)
		{
			return new BreezeGlobals();
		});
	}

	public function settings()
	{
		return $this->container->settings = $this->container->asShared(function ($c)
		{
			return new BreezeSettings();
		});
	}

	public function text()
	{
		return $this->container->text = $this->container->asShared(function ($c)
		{
			return new BreezeText();
		});
	}

	public function tools()
	{
		return $this->container->tools = $this->container->asShared(function ($c)
		{
			return new BreezeTools($c->settings, $c->text);
		});
	}

	public function query()
	{
		return $this->container->query = $this->container->asShared(function ($c)
		{
			return new BreezeQuery($c->settings, $c->text, $c->tools, $c->parser);
		});
	}

	public function form()
	{
		return $this->container->form = $this->container->asShared(function ($c)
		{
			return new BreezeForm($c->text);
		});
	}

	public function notifications()
	{
		return $this->container->notifications = $this->container->asShared(function ($c)
		{
			return new BreezeNotifications($c->settings, $c->text, $c->tools, $c->query);
		});
	}

	public function parser()
	{
		return $this->container->parser = $this->container->asShared(function ($c)
		{
			return new BreezParser($c->settings, $c->tools, $c->notifications);
		});
	}

	public function mention()
	{
		return $this->container->parser = $this->container->asShared(function ($c)
		{
			return new BreezeMention($c->settings, $c->notifications);
		});
	}
}