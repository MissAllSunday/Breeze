<?php

/**
 * BreezeMood
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeMood
{
	protected $_app;
	protected $_moodFolder = 'moods/';
	protected $_imagesPath = '';
	protected $_imagesUrl = '';
	protected $allowedExtensions = array('gif');
	protected $_moods;

	function __construct($app)
	{
		global $boarddir, $boardurl;

		$this->_app = $app;

		$this->_imagesPath = $boarddir . Breeze::$coversFolder . $this->_moodFolder;
		$this->_imageUrl = $boardurl . Breeze::$coversFolder . $this->_moodFolder;
	}

	public function create()
	{
		$this->_app['query']->insertMood($data);
	}

	public function read($all = false)
	{
		$this->_moods = $this->_app['query']->getMoods($all);
	}

	public function update()
	{
		$this->_app['query']->updateMood($data);
	}

	public function delete($id)
	{
		if (empty($id) || !is_int($id))
			return false;

		$this->_app['query']->deleteMood($id);
	}

	public function getUserMood($user)
	{
		// No ID no fun!
		if (empty($user) || !is_int($user))
			return false;

		// Go get 'em, Tiger!
		$this->read(false);
	}

	public function getUserHistory($user)
	{
		// No ID no fun!
		if (empty($user) || !is_int($user))
			return false;

		$return = array();

		$temp = $this->_app['query']->getUserSettings($user);
		$history = $temp['moodHistory'];
		unset($temp);

		// Go get 'em, Tiger!
		$this->read(false);

		foreach ($history as $id => $date)
			if (isset($this->_moods[$id]) && $this->_moods[$id]['enable'] && $this->checkImage($this->_moods[$id]['file']))
				$return[$id] = array(
					'id' => $id,
					'date' => $this->_app['tools']->timeElapsed($date),
					'name' => $this->_moods[$id]['name'],
					'file' => $this->_moods[$id]['file'],
					'url' => $this->_imageUrl . $this->_moods[$id]['file'],
					'desc' => $this->_moods[$id]['desc'],
				);

		return $return;
	}

	public function checkExt($var)
	{
		if (empty($var))
			return false;

		if (!in_array(strtolower(substr(strrchr($var, '.'), 1)), $this->_allowedExtensions))
			return false;

		else
			return true;
	}

	public function checkImage($image)
	{
		if (empty($image))
			return false;

		if (!$this->checkExt($image))
			return false;

		return file_exists($this->_imagesPath . $image);
	}

	public function checkDir()
	{
		return file_exists($this->_imagesPath);
	}

	public function isDirWritable()
	{
		return is_writable($this->_imagesPath);
	}

	public function getImagesPath()
	{
		return $this->_imagesPath;
	}

	public function getImagesUrl()
	{
		return $this->_imagesUrl;
	}
}
