<?php

/**
 * Breeze_
 *
 * The purpose of this file is to create proper html based on the type and the info it got.
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2011, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

if (!defined('SMF'))
	die('Hacking attempt...');

class Breeze_Display
{
	private $ReturnArray;
	private $params;
	private $time;
	private $user_info;

	function __construct($params, $type)
	{
		if (empty($params))
			$this->ReturnArray = '';

		else
			$this->params = $params;
	}

	public function HTML()
	{
		global $scripturl;

		/* Load stuff */
		Breeze::Load(array(
			'UserInfo',
			'Subs',
			'Parser'
		));

		$this->user_info = Breeze_UserInfo::Profile($profile_id, true);
		$this->time = new Breeze_Subs();

		$parse = new Breeze_Parser($this->params['body']);
		$this->params['body'] = $parse->Display();

		switch ($type)
		{
			case 'status':
				$this->ReturnArray .= '
		<li class="windowbg" id ="status_id_'. $this->params['id'] .'">
			<span class="topslice"><span></span></span>
				<div class="breeze_user_inner">
					<div class="breeze_user_status_avatar">
						'. $this->user_info .'
					</div>
					<div class="breeze_user_status_comment">
						'. $this->params['body'] .'
						<div class="breeze_options"><span class="time_elapsed">'. $this->time->Time_Elapsed($data['time']) .'</span>  <a href="javascript:void(0)" id="'. $this->params['id'] .'" class="breeze_delete_status">Delete</a> </div>
						<hr />
						<div id="comment_flash_'. $this->params['id'] .'"></div>';

					$this->ReturnArray .= '<ul class="breeze_comments_list">';

						/* New status don't have comments... */

						$this->ReturnArray .= '<li id="comment_loadplace_'. $this->params['id'] .'"></li>

							<li><form action="'. $scripturl. '?action=breezeajax;sa=postcomment" method="post" name="formID_'. $this->params['id'] .'" id="formID_'. $this->params['id'] .'">
								<textarea id="textboxcontent_'. $this->params['id'] .'" cols="40" rows="2"></textarea>
								<input type="hidden" value="'. $this->params['poster_id'] .'" name="status_owner_id'. $this->params['id'] .'" id="status_owner_id'. $this->params['id'] .'" />
								<input type="hidden" value="'.$context['member']['id'] .'" name="profile_owner_id'. $this->params['id'] .'" id="profile_owner_id'. $this->params['id'] .'" />
								<input type="hidden" value="'. $this->params['id'] .'" name="status_id'. $this->params['id'] .'" id="status_id'. $this->params['id'] .'" />
								<input type="hidden" value="'.$user_info['id'] .'" name="poster_comment_id'. $this->params['id'] .'" id="poster_comment_id'. $this->params['id'] .'" /><br />
								<input type="submit" value="Comment" class="comment_submit" id="'. $this->params['id'] .'" />
							</form></li>';
				break;
			case 'comment':
				$this->ReturnArray = '
					<li class="description" id ="comment_id_'. $this->params['id'] .'">
						<div class="breeze_user_comment_avatar">
							'. $this->user_info .'<br />
						</div>
						<div class="breeze_user_comment_comment">
							'. $this->params['body'] .'
							<div class="breeze_options">
								<span class="time_elapsed">'. $this->time->Time_Elapsed($data['time']).'</span> | <a href="javascript:void(0)" id="'. $this->params['id'] .'" class="breeze_delete_comment">Delete</a>
							</div>
						</div>
						<div class="clear"></div>
					</li>';
				break;
		}

		return $this->ReturnArray;
	}
}