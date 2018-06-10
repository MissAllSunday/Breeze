<?php

/**
 * BreezeAdmin
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2017, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

namespace Breeze;

if (!defined('SMF'))
	die('No direct access...');

class BreezeAdmin
{
	public function __construct(Breeze $app)
	{
		$this->_app = $app;

		// We are gonna need some admin language strings...
		$this->_app['tools']->loadLanguage('admin');
	}

	function call()
	{
		global $txt, $context, $modSettings;

		require_once($this->_app['tools']->sourceDir . '/ManageSettings.php');
		loadLanguage('BreezeAdmin');
		loadtemplate('BreezeAdmin');

		$context['page_title'] = $txt['Breeze_page_panel'];

		$subActions = array(
			'general' => 'main',
			'settings' => 'settings',
			'moodList' => 'moodList',
			'moodEdit' => 'moodEdit',
			'permissions' => 'permissions',
			'donate' => 'donate',
			'cover' => 'cover',
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'general' => [],
				'settings' => [],
				'moodList' => [],
				'moodEdit' => [],
				'permissions' => [],
				'donate' => [],
			),
		);

		$sa = $this->_app->data('get')->get('sa');

		// Call the sub-action.
		if (isset($subActions[$sa]))
			$this->{$subActions[$sa]}();

		else
			$this->main();
	}

	function main()
	{
		global $context;

		// Get the version
		$context['Breeze']['version'] = \Breeze\Breeze::$version;

		// Set all the page stuff
		$context['page_title'] = $this->_app['tools']->text('page_main');
		$context['sub_template'] = 'admin_home';
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_welcome'),
		);

		// Get the credits.
		$context['Breeze']['credits'] = $this->_app->credits();

		// Feed news
		addInlineJavascript('
$(function(){
	var breezelive = $("#smfAnnouncements");
	$.ajax({
		type: "GET",
		url: '. JavaScriptEscape($this->_app['tools']->scriptUrl . '?action=breezefeed') .',
		cache: false,
		dataType: "xml",
		success: function(xml){
			var dl = $("<dl />");
			$(xml).find("entry").each(function () {
				var item = $(this),
					title = $("<a />", {
						text: item.find("title").text(),
						href: "//github.com" + item.find("link").attr("href")
					}),
					parsedTime = new Date(item.find("updated").text().replace("T", " ").split("+")[0]),
					updated = $("<span />").text( parsedTime.toDateString()),
					content = $("<div/>").html(item.find("content")).text(),
					dt = $("<dt />").html(title),
					dd = $("<dd />").html(content);
					updated.appendTo(dt);
					dt.appendTo(dl);
					dd.appendTo(dl);
			});

			breezelive.html(dl);
		},
		error: function (html){}
	});
});
', true);
	}

	function settings()
	{
		global $context, $txt;

		// Load stuff
		$data = $this->_app->data('request');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = \Breeze\Breeze::$name .' - '. $this->_app['tools']->text('page_settings');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_settings_desc'),
		);

		require_once($this->_app['tools']->sourceDir . '/ManageServer.php');

		$config_vars = array(
			array('title', $this->_app->txtpattern .'page_settings'),
			array('check', $this->_app->txtpattern .'master', 'subtext' => $this->_app['tools']->text('master_sub')),
			array('check', $this->_app->txtpattern .'force_enable', 'subtext' => $this->_app['tools']->text('force_enable_sub')),
			array('int', $this->_app->txtpattern .'allowed_max_num_users', 'size' => 3, 'subtext' => $this->_app['tools']->text('allowed_max_num_users_sub')),
			array('int', $this->_app->txtpattern .'allowed_maxlength_aboutMe', 'size' => 4, 'subtext' => $this->_app['tools']->text('allowed_maxlength_aboutMe_sub')),
			array('check', $this->_app->txtpattern .'mood', 'subtext' => $this->_app['tools']->text('mood_sub')),
			array('text', $this->_app->txtpattern .'mood_label', 'subtext' => $this->_app['tools']->text('mood_label_sub')),
			array('select', $this->_app->txtpattern .'mood_placement',
				array(
					$txt['custom_profile_placement_standard'],
					$txt['custom_profile_placement_icons'],
					$txt['custom_profile_placement_above_signature'],
					$txt['custom_profile_placement_below_signature'],
					$txt['custom_profile_placement_below_avatar'],
					$txt['custom_profile_placement_above_member'],
					$txt['custom_profile_placement_bottom_poster'],
				),
				'subtext' => $this->_app['tools']->text('mood_placement_sub'),
				'multiple' => false,
			),
			array('int', $this->_app->txtpattern .'flood_messages', 'size' => 3, 'subtext' => $this->_app['tools']->text('flood_messages_sub')),
			array('int', $this->_app->txtpattern .'flood_minutes', 'size' => 3, 'subtext' => $this->_app['tools']->text('flood_minutes_sub')),
		);

		$context['post_url'] = $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=settings;save';

		// Saving?
		if ($data->validate('save'))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezeadmin;sa=settings');
		}

		prepareDBSettingContext($config_vars);
	}

	function permissions()
	{
		global $context, $txt;

		// This page needs the general strings.
		loadLanguage(\Breeze\Breeze::$name);

		// Load stuff
		$data = $this->_app->data('request');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = \Breeze\Breeze::$name .' - '. $this->_app['tools']->text('page_permissions');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_permissions_desc'),
		);

		require_once($this->_app['tools']->sourceDir . '/ManageServer.php');

		$config_vars = array(
			array('title', $this->_app->txtpattern .'page_permissions'),
		);

		foreach (\Breeze\Breeze::$permissions as $p)
			$config_vars[] = ['permissions', 'breeze_'. $p, 0, $txt['permissionname_breeze_'. $p]];

		$context['post_url'] = $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=permissions;save';

		// Saving?
		if ($data->validate('save'))
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezeadmin;sa=permissions');
		}

		prepareDBSettingContext($config_vars);
	}

	public function moodList()
	{
		global $context, $txt, $smcFunc;

		// Gotta respect the master setting.
		if (!$this->_app['tools']->enable('mood'))
			redirectexit('action=admin;area=breezeadmin');

		loadLanguage('ManageSmileys');

		// Gotta know what we're going to do.
		$data = $this->_app->data('request');

		// A random session var huh? sounds legit...
		$context['mood']['notice'] = !empty($_SESSION['breeze']) ? $_SESSION['breeze'] : [];

		if (isset($_SESSION['breeze']))
			unset($_SESSION['breeze']);

		// Set all the page stuff.
		$context['page_title'] = $this->_app['tools']->text('page_mood');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_mood_desc'),
		);
		$context['sub_template'] = 'manage_mood';

		// Need to know a few things.
		$context['mood']['isDirWritable'] = $this->_app['mood']->isDirWritable();

		// Go get some...
		$context['mood']['all'] = $this->_app['mood']->read();
		$context['mood']['imagesUrl'] = $this->_app['mood']->getImagesUrl();
		$context['mood']['imagesPath'] = $this->_app['mood']->getImagesPath();
		$start = $data->get('start') ? $data->get('start') : 0;
		$maxIndex = count($context['mood']['all']);

		// Lets use SMF's createList...
		$listOptions = array(
			'id' => 'breeze_mood_list',
			'title' => $this->_app['tools']->text('page_mood'),
			'base_href' => $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodList',
			'items_per_page' => 10,
			'get_count' => array(
				'function' => function () use ($context)
				{
					return count($context['mood']['all']);
				},
			),
			'get_items' => array(
				'function' => function ($start, $maxIndex) use ($smcFunc)
				{
					$moods = [];
					$request = $smcFunc['db_query']('', '
						SELECT *
						FROM {db_prefix}breeze_moods
						LIMIT {int:start}, {int:maxindex}
						',
						array(
							'start' => $start,
							'maxindex' => $maxIndex,
						)
					);

					while ($row = $smcFunc['db_fetch_assoc']($request))
						$moods[$row['moods_id']] = $row;

					$smcFunc['db_free_result']($request);

					return $moods;
				},
				'params' => array(
					$start,
					count($context['mood']['all']),
				),
			),
			'no_items_label' => $txt['icons_no_entries'],
			'columns' => array(
				'icon' => array(
					'header' => array(
						'value' => $this->_app['tools']->text('mood_image'),
					),
					'data' => array(
						'function' => function ($rowData) use($context, $txt)
						{
							$fileUrl = $context['mood']['imagesUrl'] . $rowData['file'] .'.'. $rowData['ext'];
							$filePath = $context['mood']['imagesPath'] . $rowData['file'] .'.'. $rowData['ext'];

							if (file_exists($filePath))
								return '<img src="'. $fileUrl .'" />';

							else
								return $txt['Breeze_mood_noFile'];
						},
						'class' => 'centercol',
					),
				),
				'enable' => array(
					'header' => array(
						'value' => $this->_app['tools']->text('mood_enable'),
					),
					'data' => array(
						'function' => function ($rowData) use($txt)
						{
								$enable = !empty($rowData['enable']) ? 'enable' : 'disable';
								return $txt['Breeze_mood_'. $enable];
						},
						'class' => 'centercol',
					),
				),
				'filename' => array(
					'header' => array(
						'value' => $txt['smileys_filename'],
					),
					'data' => array(
						'sprintf' => array(
							'format' => '%1$s',
							'params' => array(
								'file' => true,
							),
						),
					),
				),
				'tooltip' => array(
					'header' => array(
						'value' => $txt['smileys_description'],
					),
					'data' => array(
						'db_htmlsafe' => 'description',
					),
				),
				'modify' => array(
					'header' => array(
						'value' => $txt['smileys_modify'],
						'class' => 'centercol',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<a href="' . $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodEdit;moodID=%1$s">' . $txt['smileys_modify'] . '</a>',
							'params' => array(
								'moods_id' => true,
							),
						),
						'class' => 'centercol',
					),
				),
				'check' => array(
					'header' => array(
						'value' => '<input type="checkbox" onclick="invertAll(this, this.form);" class="input_check">',
						'class' => 'centercol',
					),
					'data' => array(
						'sprintf' => array(
							'format' => '<input type="checkbox" name="checked_icons[]" value="%1$d" class="input_check">',
							'params' => array(
								'moods_id' => false,
							),
						),
						'class' => 'centercol',
					),
				),
			),
			'form' => array(
				'href' => $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodList;delete=1',
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="delete" value="' . $txt['quickmod_delete_selected'] . '" class="button_submit"> <a class="button_link" href="' . $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=moodEdit">' . $txt['icons_add_new'] . '</a>',
				),
			),
		);

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
			$_SESSION['breeze'] = array(
				'message' => array('success_delete'),
				'type' => 'info',
			);

			// Force a redirect.
			return redirectexit('action=admin;area=breezeadmin;sa=moodList');
		}
	}

	public function moodEdit()
	{
		global $context;

		// Gotta respect the master setting.
		if (!$this->_app['tools']->enable('mood'))
			redirectexit('action=admin;area=breezeadmin');

		$data = $this->_app->data('request');
		$context['mood'] = [];

		// If editing, pass the ID to the template.
		$context['mood']['id'] = $data->get('moodID') ? $data->get('moodID') : false;

		// Set all the page stuff
		$context['page_title'] = $this->_app['tools']->text('page_mood_edit_'.($context['mood']['id'] ? 'update' : 'create'));
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_mood_edit_'.($context['mood']['id'] ? 'update' : 'create') .'_desc'),
		);
		$context['sub_template'] = 'manage_mood_edit';
		$context['mood']['imagesUrl'] = $this->_app['mood']->getImagesUrl();
		$context['mood']['imagesPath'] = $this->_app['mood']->getImagesPath();
		$mood = [];

		// Mercury has a message for you!
		$context['mood']['notice'] = !empty($_SESSION['breeze']) ? $_SESSION['breeze'] : [];

		// Fill out the edited values so they don't get lost.
		if (!empty($_SESSION['breeze']))
		{
			$mood = $_SESSION['breeze']['data'];

			// Don't need you anymore.
			unset($_SESSION['breeze']);
		}

		// Got some?
		if ($data->get('moodID') && empty($_SESSION['breeze']))
			$mood = $this->_app['query']->getMoodByID($data->get('moodID'), true);

		// Create the form.
		$form = $this->_app['form'];

		// Group all these values into an array. Makes it easier to save the changes.
		$form->setOptions(array(
			'name' => 'mood',
			'character_set' => $context['character_set'],
			'url' => $this->_app['tools']->scriptUrl .'?action=admin;area=breezeadmin;sa=moodEdit;save=1'. (!empty($context['mood']['id']) ? ';moodID='. $context['mood']['id'] .'' : '') .'',
			'title' => $context['page_title'],
		));

		// Set the right prefix.
		$form->setTextPrefix('mood_', 'admin');
		// Name.
		$form->addText(array(
			'name' => 'name',
			'value' => !empty($mood['name']) ? $mood['name'] : '',
			'size' => 15,
			'maxlength' => 15,
		));

		// Filename.
		$form->addText(array(
			'name' => 'file',
			'value' => !empty($mood['file']) && !empty($mood['ext']) ? ($mood['file'] .'.'. $mood['ext']) : '',
			'size' => 15,
			'maxlength' => 15,
		));

		// Description.
		$form->addTextArea(array(
			'name' => 'description',
			'value' => !empty($mood['description']) ? $mood['description'] : '',
			'size' => array('rows' => 10, 'cols' => 50, 'maxLength' => 1024)
		));

		// Enable.
		$form->addCheckBox(array(
			'name' => 'enable',
			'value' => !empty($mood['enable']) ? true : false
		));

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		$form->addHr();

		$form->addButton(['name' => 'submit']);

		// Send the form to the template
		$context['mood']['form'] = $form->display();

		// Saving?
		if ($data->get('save') && $data->get('mood'))
		{
			$mood = $data->get('mood');
			$errors = [];

			// Validate time, this is really simple, ALL images must be on a single place.
			if (empty($mood['file']))
				$errors[] = 'error_file';

			// Gotta make sure the image does exists on the specified folder
			$filePath = $context['mood']['imagesPath'] . $mood['file'];

			if (!file_exists($filePath))
				$errors[] = 'error_path';

			// Do note that this isn't a real check, since all images will have to be uploaded via FTP or cPanel or similar, its assumed that you will indeed put a valid image filename.
			else
			{
				// Get some info out of this image.
				$image = pathinfo($filePath);

				// No extension? come on!
				if (empty($image) || empty($image['extension']))
					$errors[] = 'error_extension';

				else if (!in_array($image['extension'], $this->_app['mood']->allowedExtensions))
					$errors[] = 'error_extension';
			}

			// One last check, if we're adding a new mood, make sure the image associated with it hasn't been already used by another mood.
			if (!$data->get('moodID') && file_exists($filePath) && !empty($image))
				foreach ($this->_app['mood']->read() as $m)
					if ($m['file'] == $image['filename'])
						$errors[] = 'error_already';

			// Go back and do it again my darling...
			if (!empty($errors))
			{
				// Pass some useful info too...
				$_SESSION['breeze'] = array(
					'message' => $errors,
					'type' => 'error',
					'data' => $mood,
				);
				return redirectexit('action=admin;area=breezeadmin;sa=moodEdit'. ($data->get('moodID') ? ';moodID='. $data->get('moodID') : ''));
			}

			// Provide some default values as needed.
			$saveData = array(
				'name' => !empty($mood['name']) ? $mood['name'] : $mood['file'],
				'file' => $image['filename'],
				'ext' => $image['extension'],
				'description' => !empty($mood['description']) ? $mood['description'] : $mood['file'],
				'enable' => !empty($mood['enable']) ? '1' : '0',
			);

			// Editing? need the ID please!
			if ($data->get('moodID'))
				$saveData['moods_id'] = $data->get('moodID');

			// All good, Save the stuff.
			$this->_app['mood']->create($saveData, $data->get('moodID'));

			$_SESSION['breeze'] = array(
				'message' => array('success_'. ($data->get('moodID') ? 'update' : 'create')),
				'type' => 'info',
				'data' => $mood,
			);

			// Back to the list page.
			return redirectexit('action=admin;area=breezeadmin;sa=moodList');
		}
	}

	function cover()
	{
		global $context, $txt;

		// Load stuff
		$data = $this->_app->data('request');
		$context['page_title'] = \Breeze\Breeze::$name .' - '. $this->_app['tools']->text('page_cover');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_cover_desc'),
		);

		require_once($this->_app['tools']->sourceDir . '/ManageServer.php');

		$config_vars = array(
			array('check', $this->_app->txtpattern .'cover', 'subtext' => $this->_app['tools']->text('cover_sub')),
			array('int', $this->_app->txtpattern .'cover_max_image_size', 'size' => 3, 'subtext' => $this->_app['tools']->text('cover_max_image_size_sub')),
			array('int', $this->_app->txtpattern .'cover_max_image_width', 'size' => 4, 'subtext' => $this->_app['tools']->text('cover_max_image_width_sub')),
			array('int', $this->_app->txtpattern .'cover_max_image_height', 'size' => 3, 'subtext' => $this->_app['tools']->text('cover_max_image_height_sub')),
			array('text', $this->_app->txtpattern .'cover_image_types', 'size' => 25, 'subtext' => $this->_app['tools']->text('cover_image_types_sub')),
		);

		$context['post_url'] = $this->_app['tools']->scriptUrl . '?action=admin;area=breezeadmin;sa=cover;save';

		// Saving?
		if ($data->validate('save'))
		{
			checkSession();

			// Gotta make sure this is indeed a comma separated list....
			if (!empty($_POST[$this->_app->txtpattern .'cover_image_types']))
				$_POST[$this->_app->txtpattern .'cover_image_types'] = $this->_app['tools']->commaSeparated($_POST[$this->_app->txtpattern .'cover_image_types'], 'alpha');

			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezeadmin;sa=cover');
		}

		prepareDBSettingContext($config_vars);
	}

	// Pay no attention to the girl behind the curtain.
	function donate()
	{
		global $context;

		// Page stuff
		$context['page_title'] = \Breeze\Breeze::$name .' - '. $this->_app['tools']->text('page_donate');
		$context['sub_template'] = 'admin_donate';
		$context['Breeze']['donate'] = $this->_app['tools']->text('page_donate_exp');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->text('page_donate_desc'),
		);
	}
}
