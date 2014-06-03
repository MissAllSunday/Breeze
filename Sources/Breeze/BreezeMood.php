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
	protected $moodFolder = 'moods';

	function __construct($app)
	{
		$this->_app = $app;
	}

	function call($app)
	{
		$data = Breeze::data('get');

		// Crud actions.
		$subActions = array(
			'create',
			'read',
			'update',
			'delete',
		);

		// Master setting is off, back off!
		if (!$this->_app['tools']->enable('mood'))
			return;

		// Does the subaction even exist?
		if (isset($subActions[$data->get('sa')]))
			$this->$subActions[$data->get('sa')]();

		else
			fatal_lang_error('Breeze_error_no_valid_action', false);
	}

	public function create()
	{
		$this->_app['query']->insertMood($data);
	}

	public function read()
	{
		$this->_app['query']->getMood($data);
	}

	public function update()
	{
		$this->_app['query']->updateMood($data);
	}

	public function delete(
	{
		$this->_app['query']->deleteMood($data);
	}

	public function checkExt($var)
	{
		if (empty($var))
			return false;

		if (!in_array(strtolower(substr(strrchr($var, '.'), 1)), $this->allowedExtensions))
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

		return file_exists($this->imagesPath .'/'. $image);
	}

	public function checkDir()
	{
		return file_exists($this->imagesPath);
	}

	public function isDirWritable()
	{
		return is_writable($this->imagesPath);
	}
}
