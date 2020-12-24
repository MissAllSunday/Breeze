<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\MoodEntity;
use Breeze\Entity\SettingsEntity;
use Breeze\Entity\UserSettingsEntity;
use Breeze\Repository\User\MoodRepositoryInterface;
use Breeze\Repository\User\UserRepositoryInterface;
use Breeze\Service\Actions\AdminService;
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

	public function createMoodList(array $listParams, int $start = 0): void
	{
		if (empty($listParams)) {
			return;
		}

		$this->setLanguage('ManageSmileys');
		$numItemsPerPage = 10;
		$scriptUrl = $this->global('scripturl');
		$maxIndex = $this->moodRepository->getCount();
		$chunkedItems = $this->moodRepository->getChunk($start, $numItemsPerPage);

		$listParams =  array_merge([
			'id' => 'moodList',
			'title' => $this->getText('page_' . $listParams['id'] . '_title'),
			'base_href' => '',
			'items_per_page' => $numItemsPerPage,
			'get_count' => [
				'function' => function () use ($maxIndex) {
					return $maxIndex;
				},
			],
			'get_items' => [
				'function' => function () use ($chunkedItems) {
					return $chunkedItems;
				},
			],
			'no_items_label' => $this->getSmfText('icons_no_entries'),
			'columns' => [
				'icon' => [
					'header' => [
						'value' => $this->getText('mood_image'),
					],
					'data' => [
						'function' => function ($rowData) {
							return '<text-area
								v-bind:content=""
								></text-area>';
						},
						'class' => 'centercol mood_emoji',
					],
				],
				'description' => [
					'header' => [
						'value' => $this->getText('mood_description'),
					],
					'data' => [
						'function' => function ($rowData) {
							return '<text-area
								v-bind:content=""
								></text-area>';
						},
						'class' => 'centercol mood_desc',
					],
				],
				'modify' => [
					'header' => [
						'value' => $this->getSmfText('smileys_modify'),
						'class' => 'centercol mood_modify',
					],
					'data' => [
						'sprintf' => [
							'format' => '<a href="' . $scriptUrl .
								'?action=admin;area=' . AdminService::AREA . ';sa=moodEdit;moodID=%1$s">' .
								$this->getSmfText('smileys_modify') . '</a>',
							'params' => [
								'moods_id' => true,
							],
						],
						'class' => 'centercol',
					],
				],
				'check' => [
					'header' => [
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check">',
						'class' => 'centercol',
					],
					'data' => [
						'sprintf' => [
							'format' =>
							'<input type="checkbox" name="checked_icons[]" value="%1$d" class="input_check">',
							'params' => [
								'moods_id' => false,
							],
						],
						'class' => 'centercol',
					],
				],
			],
			'form' => [
				'href' => $scriptUrl .
					'?action=admin;area=' . AdminService::AREA . ';sa=' . $listParams['id'] . ';delete',
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="submit" name="delete" value="' .
						$this->getSmfText('quickmod_delete_selected') .
						'" class="button you_sure">',
					'class' => 'titlebg',
				],
			],
		], $listParams);

		$this->requireOnce('Subs-List');

		$this->components->loadComponents(['moodMain', 'textArea']);

		createList($listParams);
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
