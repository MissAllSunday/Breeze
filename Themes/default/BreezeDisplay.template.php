<?php

/**
 * BreezeDisplay.template
 *
 * The purpose of this file is to show the admin section for the mod's settings
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

function template_main()
{
	global $scripturl, $user_info, $context;

	switch ($context['Breeze']['type'])
	{
		case 'status':
			$return = '
	<li class="windowbg2" id ="status_id_'. $context['Breeze']['params']['id'] .'">
		<span class="topslice">
			<span></span>
		</span>
		<div class="breeze_user_inner">
			<div class="breeze_user_status_avatar">
				'. $context['Breeze']['user_info'][$context['Breeze']['params']['poster_id']]['facebox'] .'
			</div>
			<div class="breeze_user_status_comment">
				'. $context['Breeze']['params']['body'] .'
				<div class="breeze_options"><span class="time_elapsed">'. $context['Breeze']['params']['time'] .' </span>';

				// Delete link
				if ($context['Breeze']['permissions']['deleteStatus'])
					$return .= '| <a href="javascript:void(0)" id="'. $context['Breeze']['params']['id'] .'" class="breeze_delete_status">'. $context['Breeze']['text']->getText('general_delete') .'</a>';

				$return .= '</div>
				<hr />
				<div id="comment_flash_'. $context['Breeze']['params']['id'] .'"></div>';

				$return .= '<ul class="breeze_comments_list" id="comment_loadplace_'. $context['Breeze']['params']['id'] .'">';

					// New status don't have comments...

					// display the new comments ^o^
					$return .= '
					<li id="breeze_load_image_comment_'. $context['Breeze']['params']['id'] .'" style="margin:auto; text-align:center;"></li>';

					// Close the list
					$return .= '</ul>';

					// display the form for new comments
					if ($context['Breeze']['permissions']['postcomments'])
						$return .= '
						<span><form action="'. $scripturl. '?action=breezeajax;sa=postcomment" method="post" name="formID_'. $context['Breeze']['params']['id'] .'" id="formID_'. $context['Breeze']['params']['id'] .'">
							<textarea id="textboxcontent_'. $context['Breeze']['params']['id'] .'" cols="40" rows="2"></textarea>
							<input type="hidden" value="'. $context['Breeze']['params']['poster_id'] .'" name="status_owner_id'. $context['Breeze']['params']['id'] .'" id="status_owner_id'. $context['Breeze']['params']['id'] .'" />
							<input type="hidden" value="'. $context['Breeze']['params']['owner_id'] .'" name="profile_owner_id'. $context['Breeze']['params']['id'] .'" id="profile_owner_id'. $context['Breeze']['params']['id'] .'" />
							<input type="hidden" value="'. $context['Breeze']['params']['id'] .'" name="status_id'. $context['Breeze']['params']['id'] .'" id="status_id'. $context['Breeze']['params']['id'] .'" />
							<input type="hidden" value="'. $user_info['id'] .'" name="poster_comment_id'. $context['Breeze']['params']['id'] .'" id="poster_comment_id'. $context['Breeze']['params']['id'] .'" /><br />
							<input type="submit" value="Comment" class="comment_submit" id="'. $context['Breeze']['params']['id'] .'" />
						</form></span>';


				// Close the div
				$return .= '</div>
				<div class="clear"></div>
			</div>
		<span class="botslice">
			<span></span>
		</span>
		</li>';
			break;
		case 'comment':
			$return = '
				<li id ="comment_id_'. $context['Breeze']['params']['id'] .'">
					<div class="breeze_user_comment_avatar">
						'. $context['Breeze']['user_info'][$context['Breeze']['params']['poster_id']]['facebox'] .'<br />
					</div>
					<div class="breeze_user_comment_comment">
						'. $context['Breeze']['params']['body'] .'
						<div class="breeze_options">
							<span class="time_elapsed">'. $context['Breeze']['params']['time'] .'</span> | <a href="javascript:void(0)" id="'. $context['Breeze']['params']['id'] .'" class="breeze_delete_comment">Delete</a>
						</div>
					</div>
					<div class="clear"></div>
				</li>';
			break;
	}

	return $return;
}
