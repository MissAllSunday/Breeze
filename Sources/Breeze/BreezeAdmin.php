<?php

/**
 * BreezeAdmin
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

if (!defined('SMF'))
	die('No direct access...');

class BreezeAdmin
{
	public function __construct($app)
	{
		$this->_app = $app;
	}

	function call()
	{
		global $txt, $scripturl, $context, $sourcedir, $settings;
		global $modSettings;

		require_once($sourcedir . '/ManageSettings.php');
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
		);

		loadGeneralSettingParameters($subActions, 'general');

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'tabs' => array(
				'general' => array(),
				'settings' => array(),
				'moodList' => array(),
				'permissions' => array(),
				'donate' => array(),
			),
		);

		// Admin bits
		loadJavascriptFile('jquery.zrssfeed.js', array('local' => true, 'default_theme' => true));
		addInlineJavascript('
		var breeze_feed_error_message = '. JavaScriptEscape($this->_app['tools']->adminText('feed_error_message')) .';', true);

		addInlineJavascript('
		$(document).ready(function (){
			$(\'#breezelive\').rssfeed(\''. Breeze::$supportSite .'\',
			{
				limit: 5,
				header: false,
				date: true,
				linktarget: \'_blank\',
				errormsg: breeze_feed_error_message,
				'.(!empty($modSettings['setting_secureCookies']) ? 'ssl: true,' : '').'
		   });
		});', true);

		$sa = Breeze::data('get')->get('sa');

		// Call the sub-action.
		if (isset($subActions[$sa]))
			$this->$subActions[$sa]();

		else
			$this->main();
	}

	function main()
	{
		global $scripturl, $context;

		// Get the version
		$context['Breeze']['version'] = Breeze::$version;

		// The support site RSS feed
		$context['Breeze']['support'] = Breeze::$supportSite;

		// Set all the page stuff
		$context['page_title'] = $this->_app['tools']->adminText('page_main');
		$context['sub_template'] = 'admin_home';
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->adminText('page_welcome'),
		);

		// Get the credits.
		$context['Breeze']['credits'] = $this->_app->credits();
	}

	function settings()
	{
		global $scripturl, $context, $sourcedir;

		// Load stuff
		$data = Breeze::data('request');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Breeze::$name .' - '. $this->_app['tools']->adminText('page_settings');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->adminText('page_settings_desc'),
		);

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
			array('title', Breeze::$txtpattern .'page_settings'),
			array('check', Breeze::$txtpattern .'master', 'subtext' => $this->_app['tools']->adminText('master_sub')),
			array('check', Breeze::$txtpattern .'force_enable', 'subtext' => $this->_app['tools']->adminText('force_enable_sub')),
			array('check', Breeze::$txtpattern .'notifications', 'subtext' => $this->_app['tools']->adminText('notifications_sub')),
			array('text', Breeze::$txtpattern .'allowed_actions', 'size' => 60, 'subtext' => $this->_app['tools']->adminText('allowed_actions_sub')),
			array('check', Breeze::$txtpattern .'mention', 'subtext' => $this->_app['tools']->adminText('mention_sub')),
			array('int', Breeze::$txtpattern .'mention_limit', 'size' => 3, 'subtext' => $this->_app['tools']->adminText('mention_limit_sub')),
			array('int', Breeze::$txtpattern .'allowed_max_num_users', 'size' => 3, 'subtext' => $this->_app['tools']->adminText('allowed_max_num_users_sub')),
			array('check', Breeze::$txtpattern .'parseBBC', 'subtext' => $this->_app['tools']->adminText('parseBBC_sub')),
			array('int', Breeze::$txtpattern .'allowed_maxlength_aboutMe', 'size' => 4, 'subtext' => $this->_app['tools']->adminText('allowed_maxlength_aboutMe_sub')),
			array('check', Breeze::$txtpattern .'cover', 'subtext' => $this->_app['tools']->adminText('cover_sub')),
			array('check', Breeze::$txtpattern .'likes', 'subtext' => $this->_app['tools']->adminText('likes_sub')),
			array('check', Breeze::$txtpattern .'mood', 'subtext' => $this->_app['tools']->adminText('mood_sub')),
		);

		$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=settings;save';

		// Saving?
		if ($data->validate('save') == true)
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezeadmin;sa=settings');
		}

		prepareDBSettingContext($config_vars);
	}

	function permissions()
	{
		global $scripturl, $context, $sourcedir, $txt;

		// This page needs the general strings.
		loadLanguage(Breeze::$name);

		// Load stuff
		$data = Breeze::data('request');
		$context['sub_template'] = 'show_settings';
		$context['page_title'] = Breeze::$name .' - '. $this->_app['tools']->adminText('page_permissions');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->adminText('page_permissions_desc'),
		);

		require_once($sourcedir . '/ManageServer.php');

		$config_vars = array(
			array('title', Breeze::$txtpattern .'page_permissions'),
		);

		foreach (Breeze::$permissions as $p)
			$config_vars[] = array('permissions', 'breeze_'. $p, 0, $txt['permissionname_breeze_'. $p]);

		$context['post_url'] = $scripturl . '?action=admin;area=breezeadmin;sa=permissions;save';

		// Saving?
		if ($data->validate('save') == true)
		{
			checkSession();
			saveDBSettings($config_vars);
			redirectexit('action=admin;area=breezeadmin;sa=permissions');
		}

		prepareDBSettingContext($config_vars);
	}

	public function moodList()
	{
		global $context, $sourcedir, $txt, $scripturl, $smcFunc;

		loadLanguage('ManageSmileys');

		// Gotta know what we're going to do.
		$data = Breeze::data('request');

		// A random session var huh? sounds legit...
		$context['breeze']['response'] = isset($_SESSION['breeze']) ? $txt['Breeze_mood_deleted'] : '';

		if (isset($_SESSION['breeze']))
			unset($_SESSION['breeze']);

		// Set all the page stuff.
		$context['page_title'] = $this->_app['tools']->adminText('page_mood');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->adminText('page_mood_desc'),
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
			'title' => $this->_app['tools']->adminText('page_mood'),
			'base_href' => $scripturl . '?action=admin;area=breezeadmin;sa=moodList',
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
					$moods = array();
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
							'format' => '<a href="' . $scripturl . '?action=admin;area=breezeadmin;sa=moodEdit;mood=%1$s">' . $txt['smileys_modify'] . '</a>',
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
				'href' => $scripturl . '?action=admin;area=breezeadmin;sa=moodList;delete=1',
			),
			'additional_rows' => array(
				array(
					'position' => 'below_table_data',
					'value' => '<input type="submit" name="delete" value="' . $txt['quickmod_delete_selected'] . '" class="button_submit"> <a class="button_link" href="' . $scripturl . '?action=admin;area=breezeadmin;sa=moodEdit">' . $txt['icons_add_new'] . '</a>',
				),
			),
		);

		require_once($sourcedir . '/Subs-List.php');
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
			$_SESSION['breeze'] = 'done_delete';

			// Force a redirect.
			return redirectexit('action=admin;area=breezeadmin;sa=moodList');
		}
	}

	public function moodEdit()
	{
		global $context;

		// Set all the page stuff
		$context['page_title'] = $this->_app['tools']->adminText('page_mood');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->adminText('page_mood_desc'),
		);
		$context['sub_template'] = 'manage_mood_edit';

		$data = Breeze::data('request');
		$context['mood'] = array();
		$context['mood']['imagesUrl'] = $this->_app['mood']->getImagesUrl();
		$context['mood']['imagesPath'] = $this->_app['mood']->getImagesPath();
		$mood = array();

		// Errors you say? madness!
		$context['mood']['errors'] = !empty($_SESSION['breeze']) ? $_SESSION['breeze']['errors'] : array();

		// Fill out the edited values so they don't get lost.
		if (!empty($_SESSION['breeze']))
		{
			$mood = $_SESSION['breeze']['data'];

			// Don't need you anymore
			unset($_SESSION['breeze']);
		}

		// Got some?
		if ($data->get('mood') && empty($_SESSION['breeze']))
			$mood = $this->_app['query']->getMoodByID($data->get('mood'), true);

		// Create the form.
		$form = $this->_app['form'];

		// Group all these values into an array. Makes it easier to save the changes.
		$form->setFormName('mood');

		// Session stuff.
		$form->addHiddenField($context['session_var'], $context['session_id']);

		// Set the right prefix.
		$form->setTextPrefix('mood_', true);

		// Name.
		$form->addText(
			'name',
			!empty($mood['name']) ? $mood['name'] : '',
			15,15
		);

		// Filename.
		$form->addText(
			'file',
			!empty($mood['file']) && !empty($mood['ext']) ? ($mood['file'] .'.'. $mood['ext']) : '',
			15,15
		);

		// Description.
		$form->addTextArea(
			'description',
			!empty($mood['description']) ? $mood['description'] : '',
			array('rows' => 10, 'cols' => 50, 'maxLength' => 1024)
		);

		// Enable.
		$form->addCheckBox(
			'enable',
			!empty($mood['enable']) ? true : false
		);

		$form->addHr();

		// Send the form to the template
		$context['mood']['form'] = $form->display();

		// Saving?
		if ($data->get('save'))
		{
			$mood = $data->get('mood');
			$errors = array();

			// Validate time, this is really simple, ALL images must be on a single place.
			if (empty($mood['file']))
				$errors[] = 'file';

			// Gotta make sure the image does exists on the specified folder
			$filePath = $context['mood']['imagesPath'] . $mood['file'];

			if (!file_exists($filePath))
				$errors[] = 'path';

			// Do note that this isn't a real check, since all images will have to be uploaded via FTP or cPanel or similar, its assumed that you will indeed put a valid image filename.
			else
			{
				// Get some info out of this image.
				$image = pathinfo($filePath);

				// No extension? come on!
				if (empty($image) || empty($image['extension']))
					$errors[] = 'extension';

				else if (!in_array($image['extension'], $this->_app['mood']->allowedExtensions))
					$errors[] = 'extension';
			}

			// Go back and do it again my darling...
			if (!empty($errors))
			{
				// Pass some useful info too...
				$_SESSION['breeze'] = array(
					'errors' => $errors,
					'data' => $mood,
				);
				return redirectexit('action=admin;area=breezeadmin;sa=moodEdit'. ($data->get('mood') ? ';mood='. $data->get('mood') : ''));
			}

			// All good, Save the stuff, provide some default values too.
			$this->_app['mood']->create(array(
				'name' => !empty($mood['name']) ? $mood['name'] : $mood['file'],
				'file' => $image['filename'],
				'ext' => $image['extension'],
				'description' => !empty($mood['description']) ? $mood['description'] : $mood['file'],
				'enable' => !empty($mood['enable']) ? '1' : '0',
			), !$data->get('mood'));
		}
	}

	// Pay no attention to the girl behind the curtain.
	function donate()
	{
		global $context;

		// Page stuff
		$context['page_title'] = Breeze::$name .' - '. $this->_app['tools']->adminText('page_donate');
		$context['sub_template'] = 'admin_donate';
		$context['Breeze']['donate'] = $this->_app['tools']->adminText('page_donate_exp');
		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $context['page_title'],
			'description' => $this->_app['tools']->adminText('page_donate_desc'),
		);
	}
}
