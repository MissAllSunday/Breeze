<?php

declare(strict_types=1);


namespace Breeze\Service;

class MoodService extends BaseService implements ServiceInterface
{
	public function getMoodList(array $listParams, $start = 0): array
	{

		if (empty($listParams))
			return [];
		
		return  array_merge([
		    'id' => 'breeze_mood_list',
		    'title' => $this->getText('page_mood'),
		    'base_href' => $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodList',
		    'items_per_page' => 10,
		    'get_count' => [
		        'function' => function () use ($context)
		        {
		        	return count($context['mood']['all']);
		        },
		    ],
		    'get_items' => [
		        'function' => function ($start, $maxIndex) use ($smcFunc)
		        {
		        	$moods = [];
		        	$request = $smcFunc['db_query'](
		        	    '',
		        	    '
						SELECT *
						FROM {db_prefix}breeze_moods
						LIMIT {int:start}, {int:maxindex}
						',
		        	    [
		        	        'start' => $start,
		        	        'maxindex' => $maxIndex,
		        	    ]
		        	);

		        	while ($row = $smcFunc['db_fetch_assoc']($request))
		        		$moods[$row['moods_id']] = $row;

		        	$smcFunc['db_free_result']($request);

		        	return $moods;
		        },
		        'params' => [
		            $start,
		            count($context['mood']['all']),
		        ],
		    ],
		    'no_items_label' => $txt['icons_no_entries'],
		    'columns' => [
		        'icon' => [
		            'header' => [
		                'value' => $this->_app['tools']->text('mood_image'),
		            ],
		            'data' => [
		                'function' => function ($rowData) use($context, $txt)
		                {
		                	$fileUrl = $context['mood']['imagesUrl'] . $rowData['file'] . '.' . $rowData['ext'];
		                	$filePath = $context['mood']['imagesPath'] . $rowData['file'] . '.' . $rowData['ext'];

		                	if (file_exists($filePath))
		                		return '<img src="' . $fileUrl . '" />';


		                	return $txt['Breeze_mood_noFile'];
		                },
		                'class' => 'centercol',
		            ],
		        ],
		        'enable' => [
		            'header' => [
		                'value' => $this->_app['tools']->text('mood_enable'),
		            ],
		            'data' => [
		                'function' => function ($rowData) use($txt)
		                {
		                	$enable = !empty($rowData['enable']) ? 'enable' : 'disable';

		                	return $txt['Breeze_mood_' . $enable];
		                },
		                'class' => 'centercol',
		            ],
		        ],
		        'filename' => [
		            'header' => [
		                'value' => $txt['smileys_filename'],
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
		                'value' => $txt['smileys_description'],
		            ],
		            'data' => [
		                'db_htmlsafe' => 'description',
		            ],
		        ],
		        'modify' => [
		            'header' => [
		                'value' => $txt['smileys_modify'],
		                'class' => 'centercol',
		            ],
		            'data' => [
		                'sprintf' => [
		                    'format' => '<a href="' . $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodEdit;moodID=%1$s">' . $txt['smileys_modify'] . '</a>',
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
		                    'format' => '<input type="checkbox" name="checked_icons[]" value="%1$d" class="input_check">',
		                    'params' => [
		                        'moods_id' => false,
		                    ],
		                ],
		                'class' => 'centercol',
		            ],
		        ],
		    ],
		    'form' => [
		        'href' => $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodList;delete=1',
		    ],
		    'additional_rows' => [
		        [
		            'position' => 'below_table_data',
		            'value' => '<input type="submit" name="delete" value="' . $txt['quickmod_delete_selected'] . '" class="button_submit"> <a class="button_link" href="' . $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodEdit">' . $txt['icons_add_new'] . '</a>',
		        ],
		    ],
		], $listParams);
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
