<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Repository\Mood as MoodRepository;

class Mood extends BaseService implements ServiceInterface
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

	public function moodProfile(int $memID, array $area): void
	{
		if (!$this->settings->enable('master'))
			return;

		$this->moodRepository->getMoodProfile($memID, $area);
	}
}
