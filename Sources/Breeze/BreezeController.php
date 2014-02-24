<?php

/**
 * BreezeController
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
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
			return new BreezeTools();
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
			return new BreezeMention($c->tools, $c->query, $c->notifications);
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
