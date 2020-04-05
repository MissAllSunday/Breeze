<?php

declare(strict_types=1);

/**
 * BreezeMood
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2019, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

class BreezeMood
{
	protected $_app;

	protected $_moodFolder = 'breezeMoods/';

	public $imagesPath = '';

	public $imagesUrl = '';

	public $allowedExtensions = ['gif', 'jpg', 'png'];

	protected $_moods;

	protected static $active = [];

	function __construct(Breeze $app)
	{
		$this->_app = $app;

		$this->imagesPath = $this->_app['tools']->settings['default_theme_dir'] . '/images/' . $this->_moodFolder;
		$this->imagesUrl = $this->_app['tools']->settings['default_images_url'] . '/' . $this->_moodFolder;
		$this->placementField = $this->_app['tools']->enable(SettingsEntity::MOOD_PLACEMENT) ? $this->_app['tools']->setting(SettingsEntity::MOOD_PLACEMENT) : 0;
	}

	public function call(): void
	{
		global $context;

		loadLanguage('Help');

		// Wild MoodRepository Swings... a highly underrated album if you ask me ;)
		loadtemplate(Breeze::NAME . 'Functions');

		// Get the user.
		$context['moodUser'] = $this->_app->data()->get('user');

		// Pass the currently active moods
		$context['moods'] = $this->getActive();

		// Pass the imageUrl.
		$context['moodUrl'] = $this->imagesUrl;
		$context['template_layers'] = [];
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

	public function create($data, $update = false): void
	{
		if (empty($data))
			return;

		// Updating or creating?
		$method = ($update ? 'update' : 'insert') . 'MoodRepository';

		$this->_app['query']->{$method}($data);
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
		$this->read();
	}

	public function getUserHistory($user)
	{
		// No ID no fun!
		if (empty($user) || !is_int($user))
			return false;

		$return = [];

		$temp = $this->_app['query']->getUserSettings($user);
		$history = $temp['moodHistory'];
		unset($temp);

		// Go get 'em, Tiger!
		$this->read();

		foreach ($history as $id => $date)
			if (isset($this->_moods[$id]) && $this->_moods[$id]['enable'] && $this->checkImage($this->_moods[$id]['file']))
				$return[$id] = [
					'id' => $id,
					'date' => $this->_app['tools']->timeElapsed($date),
					'name' => $this->_moods[$id]['name'],
					'file' => $this->_moods[$id]['file'],
					'url' => $this->imagesUrl . $this->_moods[$id]['file'],
					'desc' => $this->_moods[$id]['desc'],
				];

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

		
			return false;
	}

	public function show($user)
	{
		global $context;

		// Wild MoodRepository Swings... a highly underrated album if you ask me ;)
		loadtemplate(Breeze::NAME . 'Functions');

		// Get the currently active moods.
		$moods = $this->getActive();

		// Get this user options.
		$userSettings = $this->_app['query']->getUserSettings($user);

		// Get the image.
		$currentMood = !empty($userSettings['mood']) && !empty($moods[$userSettings['mood']]) ? $moods[$userSettings['mood']] : false;

		return [
			'title' => $this->_app['tools']->enable(SettingsEntity::MOOD_LABEL) ? $this->_app['tools']->setting(SettingsEntity::MOOD_LABEL) : $this->_app['tools']->text('moodLabel'),
			'col_name' => $this->_app['tools']->text('moodLabel'),
			'value' => template_mood_image($currentMood, $user),
			'placement' => $this->placementField,
		];
	}

	public function showProfile($user, $area): void
	{
		global $context;

		// its easier to list the areas where we want this to be displayed.
		$profileAreas = ['summary', 'static'];

		// Don't do anything if the feature is disable or we are in an area we don't care...
		if (!$this->_app['tools']->enable(SettingsEntity::ENABLE_MOOD) || !in_array($area, $profileAreas))
			return;

		// Wild MoodRepository Swings... a highly underrated album if you ask me ;)
		loadtemplate(Breeze::NAME . 'Functions');

		// Get the currently active moods.
		$moods = $this->getActive();

		// Get this user options.
		$userSettings = $this->_app['query']->getUserSettings($user);

		// Get the image.
		$currentMood = !empty($userSettings['mood']) && !empty($moods[$userSettings['mood']]) ? $moods[$userSettings['mood']] : false;

		// Gotta love globals...
		$context['custom_fields'][] = [
			'name' => $this->_app['tools']->enable(SettingsEntity::MOOD_LABEL) ? $this->_app['tools']->setting(SettingsEntity::MOOD_LABEL) : $this->_app['tools']->text('moodLabel'),
			'placement' => $this->placementField,
			'output_html' => template_mood_image($currentMood, $user),
			'show_reg' => false,
		];
	}

	public function noImage()
	{
		// Gotta load our template.
		loadtemplate(Breeze::NAME . 'Functions');

		// Build the needed HTML.
		return [
			'title' => $this->_app['tools']->enable(SettingsEntity::MOOD_LABEL) ? $this->_app['tools']->setting(SettingsEntity::MOOD_LABEL) : $this->_app['tools']->text('moodLabel'),
			'col_name' => $this->_app['tools']->text('moodLabel'),
			'value' => template_mood_noImage(),
			'placement' => $this->_app['tools']->enable(SettingsEntity::MOOD_PLACEMENT) ? $this->_app['tools']->setting(SettingsEntity::MOOD_PLACEMENT) : 0,
		];
	}

	public function checkExt($var)
	{
		if (empty($var))
			return false;

		if (!in_array(strtolower(substr(strrchr($var, '.'), 1)), $this->_allowedExtensions))
			return false;

		
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
