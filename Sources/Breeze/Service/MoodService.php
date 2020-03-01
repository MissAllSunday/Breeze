<?php

declare(strict_types=1);


namespace Breeze\Service;


class MoodService extends BaseService implements ServiceInterface
{

	public function showMoodList()
	{

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
