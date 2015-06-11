<?php

/**
 * BreezeMood
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
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
		$this->_app = $app;

		$this->imagesPath = $this->_app['tools']->boardDir . Breeze::$coversFolder . $this->_moodFolder;
		$this->imagesUrl = $this->_app['tools']->boardUrl . Breeze::$coversFolder . $this->_moodFolder;
		$this->placementField = $this->_app['tools']->enable('mood_placement') ? $this->_app['tools']->setting('mood_placement') : 0;
	}

	public function call()
	{
		global $context;

		loadLanguage('Help');

		// Wild Mood Swings... a highly underrated album if you ask me ;)
		loadtemplate(Breeze::$name .'Functions');

		// Get the user.
		$context['moodUser'] = Breeze::data()->get('user');

		// Pass the currently active moods
		$context['moods'] = $this->getActive();

		// Pass the imageUrl.
		$context['moodUrl'] = $this->imagesUrl;
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

	public function getSingleMood($id)
	{
		if (empty($id))
			return false;

		$mood = $this->_app['query']->getMoodByID($id, true);

		if (!empty($mood) && $mood['enable'])
		{
			$mood['url'] = $this->imagesUrl . $mood['file'];

			return $mood;
		}

		else
			return false;
	}

	public function show($user)
	{
		global $context;

		// Don't do anything if the feature is disable.
		if (!$this->_app['tools']->enable('mood'))
			return;

		// Wild Mood Swings... a highly underrated album if you ask me ;)
		loadtemplate(Breeze::$name .'Functions');

		// Get the currently active moods.
		$moods = $this->getActive();

		// Get this user options.
		$userSettings = $this->_app['query']->getUserSettings($user);

		// Get the image.
		$currentMood = !empty($userSettings['mood']) && !empty($moods[$userSettings['mood']]) ? $moods[$userSettings['mood']] : false;

		return array(
			'title' => $this->_app['tools']->enable('mood_label') ? $this->_app['tools']->setting('mood_label') : $this->_app['tools']->text('moodLabel'),
			'col_name' => $this->_app['tools']->text('moodLabel'),
			'value' => template_mood_image($currentMood, $user),
			'placement' => $this->placementField,
		);
	}

	public function showProfile($user, $area)
	{
		global $context;

		// its easier to list the areas where we want this to be displayed.
		$profileAreas = array('summary', 'static');

		// Don't do anything if the feature is disable or we are in an area we don't care...
		if (!$this->_app['tools']->enable('mood') || !in_array($area, $profileAreas))
			return;

		// Wild Mood Swings... a highly underrated album if you ask me ;)
		loadtemplate(Breeze::$name .'Functions');

		// Get the currently active moods.
		$moods = $this->getActive();

		// Get this user options.
		$userSettings = $this->_app['query']->getUserSettings($user);

		// Get the image.
		$currentMood = !empty($userSettings['mood']) && !empty($moods[$userSettings['mood']]) ? $moods[$userSettings['mood']] : false;

		// Gotta love globals...
		$context['custom_fields'][] = array(
			'name' => $this->_app['tools']->enable('mood_label') ? $this->_app['tools']->setting('mood_label') : $this->_app['tools']->text('moodLabel'),
			'placement' => $this->placementField,
			'output_html' => template_mood_image($currentMood, $user),
			'show_reg' => false,
		);
	}

	public function noImage()
	{
		// Gotta load our template.
		loadtemplate(Breeze::$name .'Functions');

		// Build the needed HTML.
		return array(
			'title' => $this->_app['tools']->enable('mood_label') ? $this->_app['tools']->setting('mood_label') : $this->_app['tools']->text('moodLabel'),
			'col_name' => $this->_app['tools']->text('moodLabel'),
			'value' => template_mood_noImage(),
			'placement' => $this->_app['tools']->enable('mood_placement') ? $this->_app['tools']->setting('mood_placement') : 0,
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
