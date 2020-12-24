<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\MoodEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\PersistenceTrait;
use Breeze\Util\Components;

class MoodService extends BaseService implements MoodServiceInterface
{
	use PersistenceTrait;

	private UserRepositoryInterface $userRepository;

	private MoodRepositoryInterface $moodRepository;

	private Components $components;

	public function __construct(
		MoodRepositoryInterface $moodRepository,
		UserRepositoryInterface $userRepository,
		Components $components
	) {
		$this->moodRepository = $moodRepository;
		$this->userRepository = $userRepository;
		$this->components = $components;
	}

	public function createMoodList(): void
	{
		$this->setLanguage('ManageSmileys');
		$scriptUrl = $this->global('scripturl');
		$context = $this->global('context');
		$allMoods = $this->moodRepository->getAllMoods();
		$context['allMoods'] = $allMoods;

		$this->setGlobal('context', $context);

		$this->components->loadComponents(['moodMain', 'textArea']);
	}

	public function getPlacementField(): int
	{
		return (int) $this->getSetting(SettingsEntity::MOOD_PLACEMENT, 0);
	}

	public function displayMood(array &$data, int $userId): void
	{
		if (!$this->getSetting(SettingsEntity::MASTER) ||
			!$this->getSetting(SettingsEntity::ENABLE_MOOD)) {
			return;
		}

		$activeMoods = $this->moodRepository->getActiveMoods();
		$userSettings = $this->userRepository->getUserSettings($userId);

		if (empty($userSettings[UserSettingsEntity::MOOD]) ||
			empty($activeMoods[$userSettings[UserSettingsEntity::MOOD]])) {
			return;
		}

		$userMood = $userSettings[UserSettingsEntity::MOOD];

		$data['custom_fields'][] =  $userMood;
	}

	public function moodProfile(int $memID, array $area): void
	{
		if (!$this->getSetting(SettingsEntity::MASTER)) {
			return;
		}

		$this->moodRepository->getMoodProfile($memID, $area);
	}

	public function deleteMoods(array $toDeleteMoodIds): bool
	{
		$wasDeleted = $this->moodRepository->deleteByIds($toDeleteMoodIds);
		$messageKey = $wasDeleted ? 'info' : 'error';

		$this->setMessage($this->getText('mood_' . $messageKey . '_delete'), $messageKey);

		return $wasDeleted;
	}

	public function getMoodById(int $moodId): array
	{
		$moods = $this->moodRepository->getModel()->getMoodByIDs([$moodId]);

		return $moods[$moodId] ?? [];
	}

	public function saveMood(array $mood, int $moodId): bool
	{
		$errors = [];

		if (!empty($moodId)) {
			$activeMoods = $this->moodRepository->getActiveMoods();

			if (!isset($activeMoods[$moodId])) {
				$errors[] = $this->getText('mood_error_invalid');
			}
		}

		if (!isset($mood[MoodEntity::COLUMN_EMOJI]) || empty($mood[MoodEntity::COLUMN_EMOJI])) {
			$errors[] = $this->getText('mood_error_empty_emoji');
		}

		if (!empty($errors)) {
			$this->setMessage(sprintf(
				$this->getText('mood_errors'),
				implode(' ', $errors)
			));

			return false;
		}

		if (!empty($moodId)) {
			$result = $this->moodRepository->getModel()->update($mood, $moodId);
		} else {
			$result = $this->moodRepository->getModel()->insert($mood);
		}

		return (bool) $result;
	}

	public function showMoodOnCustomFields(int $userId): void
	{
		$context = $this->global('context');

		$activeMoods = $this->moodRepository->getActiveMoods();
		$userSettings = $this->userRepository->getUserSettings($userId);
		$placementField = $this->getSetting(SettingsEntity::MOOD_PLACEMENT, 0);
		$moodLabel = $this->getSetting(
			SettingsEntity::MOOD_LABEL,
			$this->getText(SettingsEntity::MOOD_LABEL)
		);

		$currentMood = !empty($userSettings['mood']) && !empty($activeMoods[$userSettings['mood']]) ?
			$activeMoods[$userSettings['mood']] : '';

		// Wild Mood Swings... a highly underrated album if you ask me ;)
		$context['custom_fields'][] = [
			'name' => $moodLabel,
			'placement' => $placementField,
			'output_html' => $currentMood,
			'show_reg' => false,
		];

		$this->setGlobal('context', $context);
	}
}
