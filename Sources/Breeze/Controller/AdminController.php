<?php

declare(strict_types=1);

namespace Breeze\Controller;

use Breeze\Breeze;
use Breeze\Service\AdminService;
use Breeze\Traits\Persistence as Persistence;

class Admin extends BaseController implements ControllerInterface
{
	use Persistence;

	public const SUB_ACTIONS = [
	    'general',
	    'settings',
	    'permissions',
	    'cover',
	    'donate',
	    'moodList',
	    'moodEdit',
	];

	/**
	 * @var AdminService
	 */
	protected $adminService;

	public function dispatch(): void
    {
        $this->service->initSettingsPage($this->getSubActions());

        $this->subActionCall();
    }

	public function general(): void
	{
		$this->service->setSubActionContent(__FUNCTION__);
		$this->render('admin_home', [
		    'credits' => Breeze::credits(),
		]);
	}

	public function settings(): void
	{
		$scriptUrl = $this->adminService->global('scripturl');

		$this->service->setSubActionContent(__FUNCTION__);
		$this->render(__FUNCTION__, [
		    'post_url' => $scriptUrl . '?' . AdminService::POST_URL . __FUNCTION__ . ';save',
		]);

		$this->adminService->configVars();

		if ($this->request->get('save'))
		{
			$this->adminService->saveConfigVars();
			$this->adminService->redirect(AdminService::POST_URL . __FUNCTION__);
		}
	}

	public function permissions(): void
	{
		$scriptUrl = $this->adminService->global('scripturl');

		$this->service->setSubActionContent(__FUNCTION__);
		$this->render(__FUNCTION__, [
		    'post_url' => $scriptUrl . '?' . AdminService::POST_URL . __FUNCTION__ . ';save',
		]);

		$this->adminService->permissionsConfigVars();

		if ($this->request->get('save'))
		{
			$this->adminService->saveConfigVars();
			$this->adminService->redirect( AdminService::POST_URL . __FUNCTION__ );
		}
	}

	public function moodList()
	{
		$this->service->isEnableFeature('mood', __FUNCTION__ . 'general');

		$this->service->setSubActionContent(__FUNCTION__);
		$this->render(__FUNCTION__, [
		    'notice' => $this->getMessage(),
		]);


		$this->service->setSubActionContent(__FUNCTION__);


		// Go get some...
		$context['mood']['all'] = $this->_app['mood']->read();
		$context['mood']['imagesUrl'] = $this->_app['mood']->getImagesUrl();
		$context['mood']['imagesPath'] = $this->_app['mood']->getImagesPath();
		$start = $data->get('start') ? $data->get('start') : 0;
		$maxIndex = count($context['mood']['all']);

		// Lets use SMF's createList...
		$listOptions = [
		    'id' => 'breeze_mood_list',
		    'title' => $this->_app['tools']->text('page_mood'),
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
		];

		require_once($this->_app['tools']->sourceDir . '/Subs-List.php');
		createList($listOptions);

		// So, are we deleting?
		if ($data->get('delete') && $data->get('checked_icons'))
		{
			// Get the icons to delete.
			$toDelete = $data->get('checked_icons');

			// They all are IDs right?
			$toDelete = array_map('intval', (array) $toDelete);

			// Call BreezeQuery here.
			$this->_app['query']->deleteMood($toDelete);

			// set a nice session message.
			$_SESSION['breeze'] = [
			    'message' => ['success_delete'],
			    'type' => 'info',
			];

			// Force a redirect.
			return redirectexit('action=admin;area=breezeadmin;sa=moodList');
		}
	}

	public function render(string $subTemplate, array $params): void
	{
		$context = $this->adminService->global('context');

		$context[$subTemplate] = $params;

		$this->adminService->setGlobal('context', $context);

		$this->adminService->setSubActionContent($subTemplate);
	}
}
