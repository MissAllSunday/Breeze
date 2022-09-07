<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\MoodEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Exceptions\InvalidMoodException;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Traits\PersistenceTrait;
use Breeze\Util\Components;
use Breeze\Util\Permissions;

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

	public function moodList(): array
	{
		$moods = $this->moodRepository->getAllMoods();

		$this->components->loadTxtVarsFor(['general', 'mood']);
		$this->components->loadComponents(['moodForm', 'utils', 'modal', 'moodAdmin', 'moodListAdmin']);

		return $moods;
	}

	public function getAll(): array
	{
		return $this->moodRepository->getAllMoods();
	}

	public function getActiveMoods(): array
	{
		return $this->moodRepository->getActiveMoods();
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
		$placementField = $this->getSetting(SettingsEntity::MOOD_PLACEMENT, 0);
		$moodLabel = $this->getSetting(
			SettingsEntity::MOOD_LABEL,
			$this->getText(SettingsEntity::MOOD_DEFAULT)
		);

		$currentMood = !empty($userSettings[UserSettingsEntity::MOOD]) &&
		!empty($activeMoods[$userSettings[UserSettingsEntity::MOOD]]) ?
			$activeMoods[$userSettings[UserSettingsEntity::MOOD]] : '';

		// Wild Mood Swings... a highly underrated album if you ask me ;)
		$data['custom_fields'][] = [
			'title' => $this->tokenTxtReplace($moodLabel),
			'col_name' => $this->tokenTxtReplace($moodLabel),
			'value' => $this->setMoodComponent($userId, $currentMood),
			'raw' => $currentMood[MoodEntity::EMOJI],
			'placement' => $placementField,
		];
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
		return $this->moodRepository->deleteByIds($toDeleteMoodIds);
	}

	/**
	 * @throws InvalidMoodException
	 */
	public function getMoodById(int $moodId): array
	{
		return $this->moodRepository->getById($moodId);
	}

	public function saveMood(array $mood): array
	{
		$moodId = $mood[MoodEntity::ID] ?? 0;

		if (!empty($moodId)) {
			$result = $this->moodRepository->updateMood($mood, $moodId);
		} else {
			$result = $this->moodRepository->insertMood($mood);
		}

		return $result;
	}

	protected function setMoodComponent(int $userId, array $currentMood): string
	{
		$currentUserInfo = $this->global('user_info');

		$this->components->loadTxtVarsFor(['general', 'mood']);
		$this->components->loadComponents(
			['contentSection', 'setMood']
		);

		return '
		<set-mood
		:current-mood-id="' . $currentMood[MoodEntity::ID] . '"
		:user-id="' . $userId . '"
		:mood-txt="window.breezeTxtMood"
		:is-current-user-owner="' . ($userId === (int) $currentUserInfo['id']) . '"
		:use-mood="' . (Permissions::isAllowedTo(Permissions::USE_MOOD)) . '"
	></set-mood>';
	}
}
