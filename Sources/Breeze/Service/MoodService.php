<?php

declare(strict_types=1);


namespace Breeze\Service;

use Breeze\Traits\PersistenceTrait;

class MoodService extends BaseService implements ServiceInterface
{
	use PersistenceTrait;

	public const FOLDER = 'breezeMoods';

	public function getMoodList(array $listParams, $start = 0): array
	{
		if (empty($listParams))
			return [];

		$scriptUrl = $this->global('scripturl');
		$maxIndex = $this->repository->getCount();
		$itemsPerPage = $this->repository->getChunk($start, $maxIndex);

		return  array_merge([
			'id' => '',
			'title' => '',
			'base_href' => '',
			'items_per_page' => 10,
			'get_count' => [
				'function' => $maxIndex,
			],
			'get_items' => [
				'function' => $itemsPerPage,
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
							$fileUrl = $this->getMoodsUrl() . $rowData['file'] . '.' . $rowData['ext'];
							$filePath = $this->getMoodsPath() . $rowData['file'] . '.' . $rowData['ext'];

							if (file_exists($filePath))
								return '<img src="' . $fileUrl . '" />';

							return $this->getText('mood_noFile');
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
							$enable = !empty($rowData['enable']) ? 'enable' : 'disable';

							return $this->getText('mood_' . $enable);
						},
						'class' => 'centercol',
					],
				],
				'filename' => [
					'header' => [
						'value' => $this->getSmfText('smileys_filename'),
					],
					'data' => [
						'sprintf' => [
							'format' => '%1$s',
							'params' => [
								'file' => true,
							],
						],
					],
				],
				'tooltip' => [
					'header' => [
						'value' => $this->getText('smileys_description'),
					],
					'data' => [
						'db_htmlsafe' => 'description',
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
								'?action=admin;area=breezeadmin;sa=moodEdit;moodID=%1$s">' .
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
				'href' => $scriptUrl . '?action=admin;area=breezeadmin;sa=moodList;delete=1',
			],
			'additional_rows' => [
				[
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="delete" value="' .
						$this->getSmfText('quickmod_delete_selected') .
						'" class="button_submit"> <a class="button_link" href="' .
						$scriptUrl . '?action=admin;area=breezeadmin;sa=moodEdit">' .
						$this->getSmfText('icons_add_new') . '</a>',
				],
			],
		], $listParams);
	}

	public function getMoodsPath(): string
	{
		$smfSettings = $this->global('settings');

		return $smfSettings['default_theme_dir'] . '/images/' . self::FOLDER;
	}

	public function getMoodsUrl(): string
	{
		$smfSettings = $this->global('settings');

		return $smfSettings['default_images_url'] . '/' . self::FOLDER;
	}

	public function getPlacementField(): int
	{
		return (int) $this->getSetting('mood_placement', 0);
	}

	public function displayMood(array &$data, int $userId): void
	{
		if (!$this->getSetting('master') || !$this->getSetting('mood'))
			return;

		$data['custom_fields'][] =  $this->repository->getMood($userId);
	}

	public function moodProfile(int $memID, array $area): void
	{
		if (!$this->getSetting('master'))
			return;

		$this->repository->getMoodProfile($memID, $area);
	}

	public function deleteMoods(array $toDeleteMoodIds)
	{
		$deleted = $this->repository->deleteByIds($toDeleteMoodIds);

		$this->setMessage($this->getText('mood_success_delete'), ($deleted ? 'info' : 'error'));

		return $deleted;
	}
}
