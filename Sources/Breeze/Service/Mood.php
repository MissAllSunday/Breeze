<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\Mood as MoodRepository;

class Mood extends Base
{
	/**
	 * @var MoodRepository
	 */
	protected $moodRepository;

	/**
	 * @var Settings
	 */
	protected $settings;

	public function __construct(MoodRepository $moodRepository, Settings $settings)
	{
		$this->moodRepository = $moodRepository;
		$this->settings = $settings;
	}

	public function displayMood(array &$data, int $userId): void
	{
		if (!$this->settings->enable('master') || !$this->settings->enable('mood'))
			return;

		$data['custom_fields'][] =  $this->moodRepository->getMood($userId);
	}

	public function moodProfile($memID, $area): void
	{
		// Don't do anything if the mod is off
		if (!$this['tools']->enable('master'))
			return;

		// Let BreezeMood handle this...
		$this['mood']->showProfile($memID, $area);
	}
}
