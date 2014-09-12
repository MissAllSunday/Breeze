<?php

/**
 * BreezeNotifications
 *
 * The purpose of this file is to fetch all notifications for X user
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeNotifications
{
	protected $_params = array();
	protected $_user = 0;
	protected $_returnArray = array();
	protected $_usersData = array();
	public $types = array();
	protected $_currentUser;
	protected $_currentUserSettings = array();
	protected $_messages = array();
	protected $loadedUsers = array();
	protected $_app;

	/**
	 * BreezeNotifications::__construct()
	 *
	 * @return
	 */
	function __construct($app)
	{

		// We kinda need all this stuff, don't' ask why, just nod your head...
		$this->_app = $app;
	}
}
