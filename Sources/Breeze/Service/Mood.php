<?php


namespace Breeze\Service;


use Breeze\Repository\Mood as MoodRepository $moodRepository;

class Mood extends Base
{
	public function __construct(MoodRepository $moodRepository)
	{
	}

	public function displayMood(array &$data, int $userId): void
	{
		if (!$this['tools']->enable('master') || !$this['tools']->enable('mood'))
			return;

		// Append the result to the custom fields array.
		$data['custom_fields'][] =  $this['mood']->show($user);
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