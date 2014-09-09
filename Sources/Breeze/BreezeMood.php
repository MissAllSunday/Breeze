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
	public $imagesPath = '';
	public $imagesUrl = '';
	public $allowedExtensions = array('gif', 'jpg', 'png');
	protected $_moods;
	protected static $active = array();

	function __construct($app)
	{
		global $boarddir, $boardurl;

		$this->_app = $app;

		$this->imagesPath = $boarddir . Breeze::$coversFolder . $this->_moodFolder;
		$this->imagesUrl = $boardurl . Breeze::$coversFolder . $this->_moodFolder;
	}

	public function call()
	{
		global $context;

		loadLanguage('Help');

		// Get the user.
		$context['moodUser'] = Breeze::data()->get('user');

		// Pass the currently active moods
		$context['moods'] = $this->getActive();

		// Wild Mood Swings... a highly underrated album if you ask me ;)
		loadtemplate(Breeze::$name .'Functions');
		$context['template_layers'] = array();
		$context['sub_template'] = 'mood_change';
	}

	public function getActive()
	{
		$this->read();

		// Do this only if needed.
		if (empty(static::$active))
			foreach ($this->_moods as $m)
				if (!empty($m['enable']))
					static::$active[$m['moods_id']] = $m;

		return static::$active;
	}

	public function create($data, $update = false)
	{
		if (empty($data))
			return;

		// Updating or creating?
		$method = ($update ? 'update' : 'insert') . 'Mood';

		$this->_app['query']->$method($data);
	}

	public function read()
	{
		if (empty($this->_moods))
			$this->_moods = $this->_app['query']->getAllMoods();

		return $this->_moods;
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
					'url' => $this->imagesUrl . $this->_moods[$id]['file'],
					'desc' => $this->_moods[$id]['desc'],
				);

		return $return;
	}

	public function show($mood, $user)
	{
		global $context;

		// Gotta load our template.
		loadtemplate(Breeze::$name .'Functions');

		// Pass the imageUrl and this user
		$context['moodUrl'] = $this->imagesUrl;

		return array(
			'title' => $this->_app['tools']->enable('mood_label') ? $this->_app['tools']->enable('mood_label') : $this->_app['tools']->text('moodLabel'),
			'col_name' => $this->_app['tools']->text('moodLabel'),
			'value' => template_mood_image($mood, $user),
			'placement' => $this->_app['tools']->enable('mood_placement') ? $this->_app['tools']->enable('mood_placement') : 0,
		);
	}

	public function noImage()
	{
		// Gotta load our template.
		loadtemplate(Breeze::$name .'Functions');

		// Build the needed HTML.
		return array(
			'title' => $this->_app['tools']->enable('mood_label') ? $this->_app['tools']->enable('mood_label') : $this->_app['tools']->text('moodLabel'),
			'col_name' => $this->_app['tools']->text('moodLabel'),
			'value' => template_mood_noImage(),
			'placement' => $this->_app['tools']->enable('mood_placement') ? $this->_app['tools']->enable('mood_placement') : 0,
		);
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

		return file_exists($this->imagesPath . $image);
	}

	public function checkDir()
	{
		return file_exists($this->imagesPath);
	}

	public function isDirWritable()
	{
		return is_writable($this->imagesPath);
	}

	public function getImagesPath()
	{
		return $this->imagesPath;
	}

	public function getImagesUrl()
	{
		return $this->imagesUrl;
	}
}
