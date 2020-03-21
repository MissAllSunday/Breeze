<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Entity\MoodEntity;
use Breeze\Traits\PersistenceTrait;

class MoodService extends BaseService implements ServiceInterface
{
	use PersistenceTrait;

	public function createMoodList(array $listParams, int $start = 0): void
	{
		if (empty($listParams))
			return;

		$this->setLanguage('ManageSmileys');
		$numItemsPerPage = 10;
		$scriptUrl = $this->global('scripturl');
		$maxIndex = $this->repository->getCount();
		$chunkedItems = $this->repository->getChunk($start, $numItemsPerPage);

		$listParams =  array_merge([
			'id' => '',
			'title' => $this->getText('page_' . $listParams['id'] . '_title'),
			'base_href' => '',
			'items_per_page' => $numItemsPerPage,
			'get_count' => [
				'function' => function() use($maxIndex){
					return $maxIndex;
				},
			],
			'get_items' => [
				'function' => function() use($chunkedItems){
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
						'function' => function ($rowData)
						{
							return $rowData['emoji'];
						},
						'class' => 'centercol',
					],
				],
				'enable' => [
					'header' => [
						'value' => $this->getText('mood_enable'),
					],
					'data' => [
						'function' => function ($rowData)
						{
							return $this->getText('mood_' . $rowData['status']);
						},
						'class' => 'centercol',
					],
				],
				'description' => [
					'header' => [
						'value' => $this->getText('mood_description'),
					],
					'data' => [
						'function' => function ($rowData)
						{
							return $rowData['description'];
						},
						'class' => 'centercol',
					],
				],
				'modify' => [
					'header' => [
						'value' => $this->getSmfText('smileys_modify'),
						'class' => 'centercol',
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
				'href' => $scriptUrl . '?action=admin;area=' . AdminService::AREA . ';sa=' . $listParams['id'] . ';delete',
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '
						<input type="submit" name="delete" value="' .
						$this->getSmfText('quickmod_delete_selected') .
						'" class="button you_sure"> 
						<a class="button" href="' .
						$scriptUrl . '?action=admin;area=' . AdminService::AREA . ';sa=moodEdit">' .
						$this->getText('page_mood_create') . '</a>',
					'class' => 'titlebg',
				],
			],
		], $listParams);

		$this->requireOnce('Subs-List');

		createList($listParams);
	}

	public function getPlacementField(): int
	{
		return (int) $this->getSetting('mood_placement', 0);
	}

	public function displayMood(array &$data, int $userId): void
	{
		if (!$this->getSetting('master') || !$this->getSetting('mood'))
			return;

		$data['custom_fields'][] =  $this->repository->getActiveMoods();
	}

	public function moodProfile(int $memID, array $area): void
	{
		if (!$this->getSetting('master'))
			return;

		$this->repository->getMoodProfile($memID, $area);
	}

	public function deleteMoods(array $toDeleteMoodIds): bool
	{
		$wasDeleted = $this->repository->deleteByIds($toDeleteMoodIds);
		$messageKey = $wasDeleted ? 'info' : 'error';

		$this->setMessage($this->getText('mood_' . $messageKey . '_delete'), $messageKey);

		return $wasDeleted;
	}

	public function getMoodById(int $moodId): array
	{
		$moods = $this->repository->getModel()->getMoodByIDs($moodId);

		return $moods[$moodId] ?? [];
	}

	public function saveMood(array $mood, int $moodId): bool
	{
		$errors = [];

		if (!empty($moodId))
		{
			$activeMoods = $this->repository->getActiveMoods();

			if (!isset($activeMoods[$moodId]))
				$errors[] = $this->getText('mood_error_invalid');
		}

		if (!isset($mood[MoodEntity::COLUMN_EMOJI]) || empty($mood[MoodEntity::COLUMN_EMOJI]))
			$errors[] = $this->getText('mood_error_empty_emoji');

		if (!empty($errors))
		{
			$this->setMessage(sprintf(
				$this->getText('mood_errors'),
				implode(' ', $errors)
			));

			return false;
		}

		if (!empty($moodId))
			$result = $this->repository->getModel()->update($mood, $moodId);

		else
			$result = $this->repository->getModel()->insert($mood);

		return (bool) $result;
	}
}
