<?php

/**
 * Breeze.template
 *
 * The purpose of this file is
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

// User's wall.
function template_user_wall()
{
	global $txt, $context, $settings, $scripturl, $user_info, $modSettings;

	loadLanguage(Breeze::$name);

	// Start of profileview div
	echo '
<div id="profileview" class="flow_auto">';

	// Right block, tabs and block
	echo '
	<div id="Breeze_right_block">';

	// Tabs
	echo '
		<div id="Breeze_tabs">
			<ul class="dropmenu breezeTabs">
				<li class="wall"><a href="#tab-wall" class="active firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_wall'] ,'</span></a></li>
				<li class="buddies"><a href="#tab-posts" class="firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_posts'] ,'</span></a></li>';

	// Does recent activity is enable?
	if (!empty($context['member']['breezeOptions']['enable_activityLog']))
		echo '
				<li class="buddies"><a href="#tab-activity" class="firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_activity'] ,'</span></a></li>';

	echo '
			</ul>
		</div>
		<p class="clear" />';

	// Wall
	echo '
		<div id="tab-wall" class="content">';


	// A nice title bar
	echo '
		<div class="cat_bar">
			<h3 class="catbg">
					', $txt['Breeze_general_wall'] ,'
			</h3>
		</div>';

	// This is the status box,  O RLY?
	if (!empty($context['Breeze']['permissions']['postStatus']))
			echo '
			<div class="breeze_user_inner windowbg">
				<span class="topslice"><span></span></span>
				<div class="breeze_user_statusbox content">
						<form method="post" action="', $scripturl, '?action=breezeajax;sa=post', !empty($context['Breeze']['commingFrom']) ? ';rf='. $context['Breeze']['commingFrom'] : '' ,'" id="form_status" name="form_status" class="form_status">
							<textarea cols="40" rows="5" name="statusContent" id="statusContent" rel="atwhoMention"></textarea>
							<input type="hidden" value="', $user_info['id'] ,'" name="statusPoster" id="statusPoster" />
							<input type="hidden" value="', $context['member']['id'] ,'" name="statusOwner" id="statusOwner" />
							<input type="hidden" id="'. $context['session_var'] .'" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
							<br /><input type="submit" value="', $txt['post'] ,'" name="statusSubmit" class="status_button" id="statusSubmit"/>
						</form>
				</div>
				<span class="botslice"><span></span></span>
			</div>';

	// Print out the status if there are any.
	breeze_status($context['member']['status']);

	// Pagination
	if (!empty($context['page_index']))
		echo '
			<div class="pagelinks">
				', $txt['pages'], ': ', $context['page_index'], $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#profileview"><strong>' . $txt['go_up'] . '</strong></a>
			</div>';

	// Wall end
	echo '
		</div>';

	// Activity
	if (!empty($context['member']['breezeOptions']['enable_activityLog']))
	{
		echo '
		<div id="tab-activity" class="content">';

		var_dump($context['Breeze']['log']);

		echo '
		</div>';
	}
	// Activity end


	// Posts

	// Posts end



	// End of right block
	echo '
	</div>';

	// Left block, users data and info
	echo '
	<div id="Breeze_left_block">';

	// Profile owner details
	breeze_profile_owner();

	// Buddies
	if (!empty($context['member']['options']['enable_buddies']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				'. $txt['Breeze_tabs_buddies'] .'
			</h3>
		</div>';

		echo '
		<div class="windowbg2">
			<span class="topslice"><span> </span></span>
			<div class="content BreezeList">';

		if (!empty($context['member']['buddies']))
		{
			// Print a nice Ul
			echo '
				<ul class="reset">';

			// Show the profile visitors in a big, fat echo!
			foreach ($context['member']['buddies'] as $buddy)
				echo '
					<li> ', $context['Breeze']['user_info'][$buddy]['facebox'] ,' <br /> ', $context['Breeze']['user_info'][$buddy]['link'] ,'</li>';

			// End the buddies list
			echo '
				</ul>';
		}

		// No buddies :(
		else $txt['Breeze_user_modules_buddies_none'];

			echo '
			</div>
			<span class="botslice"><span> </span></span>
		</div>';

	}
	// Buddies end

	// Visitors
	if (!empty($context['member']['options']['enable_visitors']))
	{

		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				'. $txt['Breeze_tabs_views'] .'
			</h3>
		</div>';

		echo '
		<div class="windowbg2">
			<span class="topslice"><span> </span></span>
			<div class="content BreezeList">';

		if (!empty($context['Breeze']['views']))
		{
			// Print a nice Ul
			echo '
				<ul class="reset">';

			// Show the profile visitors
			foreach ($context['Breeze']['views'] as $visitor)
			{
				echo '
					<li> ', $context['Breeze']['user_info'][$visitor['user']]['facebox'];

				// The user's name, don't forget to put a nice br to force a break line...
				echo '
						<br />',  $context['Breeze']['user_info'][$visitor['user']]['link'];

				// The last visit was at...?
				echo '
						<br />',  $context['Breeze']['tools']->timeElapsed($visitor['last_view']);

				// If you're the profile owner you might want to know how many time this user has visited your profile...
				if ($context['member']['id'] == $user_info['id'])
					echo '
						<br />',  $txt['Breeze_user_modules_visitors'] . $visitor['views'];

				// Finally, close the li
				echo '
					</li>';
			}

			// End the visitors list
			echo '
				</ul>';
		}

		// No visitors :(
		else
			echo $txt['Breeze_user_modules_visitors_none'];

		echo '
			</div>
			<span class="botslice"><span> </span></span>
		</div>';
	}
	// Visitor end

	// End of left block
	echo '
		</div>';

	// End of profileview div
	echo '
</div>';

	// Don't forget to print the users data
	breeze_user_info();
}

function template_user_notifications()
{
	global $context, $txt, $scripturl, $user_info;

	// Get the message from the server
	breeze_server_response();

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'] ,'</h3>
		</div>';

	if (!empty($context['Breeze']['noti']))
	{
		echo '
		<form action="', $scripturl , '?action=breezeajax;sa=multiNoti;user=', $user_info['id'] ,'', !empty($context['Breeze']['commingFrom']) ? ';rf='. $context['Breeze']['commingFrom'] : '' ,'" method="post" name="multiNoti" id="multiNoti">
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['Breeze_noti_message'] ,'</th>
						<th scope="col">', $txt['Breeze_noti_markasread_title'] ,'</th>
						<th scope="col">', $txt['Breeze_general_delete'] ,'</th>
						<th scope="col" class="last_th">
							', $txt['Breeze_noti_checkAll'] ,' <input type="checkbox" name="check_all">
						</th>
					</tr>
				</thead>
				<tbody>';

		foreach($context['Breeze']['noti'] as $noti)
		{
			echo '
				<tr class="windowbg" style="text-align: center">
					<td>
						', $noti['message'] ,'
					</td>
					<td>
					<a href="', $scripturl ,'?action=breezeajax;sa=notimark;content=', $noti['id'] ,';user=', $user_info['id'] ,'', !empty($context['Breeze']['commingFrom']) ? ';rf='. $context['Breeze']['commingFrom'] : '' ,'">', (!empty($noti['viewed']) ? $txt['Breeze_noti_markasunread'] : $txt['Breeze_noti_markasread']) ,'</a>
					</td>
					<td>
					<a href="', $scripturl ,'?action=breezeajax;sa=notidelete;content=', $noti['id'] ,';user='. $user_info['id'] ,'', !empty($context['Breeze']['commingFrom']) ? ';rf='. $context['Breeze']['commingFrom'] : '' ,'">', $txt['Breeze_general_delete'] ,'</a>
					</td>
					<td>
						<input type="checkbox" name="idNoti[]" class="idNoti" value="', $noti['id'] ,'">
					</td>
				</tr>';
		}

		// Close the table
		echo '
				</tbody>
			</table><br />';

		// Print the select box
		echo'
			<div style="float:right;">
				', $txt['Breeze_noti_selectedOptions'] ,'
				<select id="multiNotiOption" name="multiNotiOption">
					<option value="">&nbsp;&nbsp;&nbsp;</option>
					<option value="read">', $txt['Breeze_noti_markasread']  ,'</option>
					<option value="unread">', $txt['Breeze_noti_markasunread'] ,'</option>
					<option value="delete">', $txt['Breeze_general_delete'] ,'</option>
				</select>
				<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
				<input type="submit" value="', $txt['Breeze_noti_send'] ,'" class="button_submit" />
			</div>
			<div class="clear"></div>';

		// End the form
		echo '
		</form>';
	}

	// Gotta be more social buddy...
	else
	{
		echo '
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">
				', $txt['Breeze_noti_none'] ,'
			</div>
			<span class="botslice">
				<span></span>
			</span>
		</div>';
	}

	// For some reason we need to add a br, otherwise it gets borked...
	echo '
		<br />';
}

function template_member_options()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// Get the message from the server
	breeze_server_response();

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=breezeajax;sa=usersettings;rf=profile;u=', $context['member']['id'] ,'" method="post" accept-charset="', $context['character_set'], '" name="breezeSettings" id="breezeSettings">
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">
						<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" class="icon" />
						', $context['page_desc'] , '
					</span>
				</h3>
			</div>
			<p class="windowbg description">
				', $context['page_desc'] , '
			</p>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
					<div class="content">';

		// Print the form
	echo $context['Breeze']['UserSettings']['Form'];

	// Print the save button.
	echo '<input type="submit" name="submit" value="', $txt['save'] ,'" class="button_submit floatright"/>';

	echo '
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<br />
		</form>';
}

function template_general_wall()
{
	global $context, $txt, $scripturl, $settings;

	// Print the server response
	breeze_server_response();

	echo '
	<div id="profileview" class="flow_auto">';

	// Left block, user's data and blocks
	echo '
		<div id="Breeze_left_block">
			<div class="cat_bar">
				<h3 class="catbg">
					<span id="author">
						', $txt['Breeze_general_latest_buddy_status'] ,'
				</h3>
			</div>';

		// Print the buddies status
		if (!empty($context['Breeze']['status']))
			foreach ($context['Breeze']['status'] as $status)
				breeze_status($status);

	// Pagination
	if (!empty($context['page_index']))
		echo '
		<div class="pagelinks">
			', $txt['pages'], ': ', $context['page_index'], $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#profileview"><strong>' . $txt['go_up'] . '</strong></a>
		</div>';

	echo '
		</div>';

	echo '
		<div id="Breeze_right_block">';

	echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span id="author">
						', $txt['Breeze_general_activity'] ,'
				</h3>
			</div>
			<div class="windowbg2 BreezeBlock">
				<span class="topslice">
					<span> </span>
				</span>';

	if (!empty($context['Breeze']['activity']))
		foreach ($context['Breeze']['activity'] as $activity)
			breeze_activity($activity);

	echo '
			<span class="botslice"><span></span></span>
			</div>';

	echo '
		</div>';

	echo '
	</div>';

	// Lastly, print the suers info
	breeze_user_info();
}
