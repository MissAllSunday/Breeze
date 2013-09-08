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

	// Print the server response
	breeze_server_response();

	// Start of profileview div
	echo '
	<div id="profileview" class="flow_auto">';

	// Left block, user's data and blocks
	echo '
		<div id="Breeze_left_block">';

	echo '
			<div class="cat_bar">
				<h3 class="catbg">
					<span id="author">
						'. $txt['Breeze_tabs_wall'] .'
				</h3>
			</div>';

	// Wall div
	echo '
			<div id="tabs_wall">
				<div class="windowbg2">
					<span class="topslice">
						<span></span>
					</span>
				<div class="content">';

			// This is the status box,  O RLY?
			if (!empty($context['Breeze']['permissions']['post_status']))
				echo '<div class="breeze_user_inner">
						<div class="breeze_user_statusbox">
							<form method="post" action="', $scripturl, '?action=breezeajax;sa=post', !empty($context['Breeze']['commingFrom']) ? ';rf='. $context['Breeze']['commingFrom'] : '' ,'" id="status" name="form_status" class="form_status">
								<textarea cols="40" rows="5" name="content" id="content" rel="atwhoMention"></textarea>
								<input type="hidden" value="',$context['member']['id'],'" name="owner_id" id="owner_id" />
								<input type="hidden" value="',$user_info['id'],'" name="poster_id" id="poster_id" /><br />
								<input type="submit" value="', $txt['post'] ,'" name="submit" class="status_button"/>
								<input type="hidden" id="', $context['session_var'], '" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							</form>
						</div>
					</div>';

		echo'
				</div>
				<span class="botslice">
					<span></span>
				</span>
				</div>';
		// End of the status textarea

	// Print the status and comments
	breeze_status($context['member']['status']);

	// Pagination
	if (!empty($context['page_index']))
		echo '
		<div class="pagelinks">
			', $txt['pages'], ': ', $context['page_index'], $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#profileview"><strong>' . $txt['go_up'] . '</strong></a>
		</div>';

	// End of Wall div
	echo '
			</div>';

	// End of left side
	echo '
		</div>';

	// Right block, user's data and blocks
	echo '
	<div id="Breeze_right_block">';

	// Profile owner details
	breeze_profile_owner();

	// Profile visitors
	if (!empty($context['Breeze']['views']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span id="author">
					'. $txt['Breeze_tabs_views'] .'
			</h3>
		</div>
		<div class="windowbg2 BreezeBlock">
			<span class="topslice">
			<span> </span>
			</span>
			<div class="content BreezeList">';

		// Print a nice Ul
		echo '
				<ul class="reset">';

		// Show the profile visitors
		foreach ($context['Breeze']['views'] as $visitor)
		{

			echo '<li> ', $context['Breeze']['user_info'][$visitor['user']]['facebox'];

			// The user's name, don't forget to put a nice br to force a break line...
			echo '<br />',  $context['Breeze']['user_info'][$visitor['user']]['link'];

			// The last visit was at...?
			echo '<br />',  $context['Breeze']['tools']->timeElapsed($visitor['last_view']);

			// If you're the profile owner you might want to know how many time this user has visited your profile...
			if ($context['member']['id'] == $user_info['id'])
				echo '<br />',  $txt['Breeze_user_modules_visits'] . $visitor['views'];

			// Finally, close the li
			echo '</li>';
		}

		// End the visitors list
		echo '
				</ul>';

		echo '
			</div>
			<span class="botslice">
			<span> </span>
			</span>
		</div>';
	}

	// Buddy list
	if (!empty($context['member']['options']['Breeze_enable_buddies_tab']) && !empty($context['member']['buddies']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				<span id="author">
					'. $txt['Breeze_tabs_buddies'] .'
			</h3>
		</div>
		<div class="windowbg2 BreezeBlock">
			<span class="topslice">
			<span> </span>
			</span>
			<div class="content BreezeList">';

		// Print a nice Ul
		echo '
				<ul class="reset">';

		// Show the profile visitors in a big, fat echo!
		foreach ($context['member']['buddies'] as $buddy)
			echo '<li> ', $context['Breeze']['user_info'][$buddy]['facebox'] ,' <br /> ', $context['Breeze']['user_info'][$buddy]['link'] ,'</li>';

		// End the visitors list
		echo '
				</ul>';

		echo '
			</div>
			<span class="botslice">
			<span> </span>
			</span>
		</div>';
	}

	// End of right block
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
		</div>
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
			<div class="content">';

	if (!empty($context['Breeze']['noti']))
	{
		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['Breeze_noti_message'] ,'</th>
						<th scope="col">', $txt['Breeze_noti_markasread_title'] ,'</th>
						<th scope="col" class="last_th">', $txt['Breeze_general_delete'] ,'</th>
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
					<a href="'. $scripturl .'?action=breezeajax;sa=notimark;content='. $noti['id'] .';user='. $user_info['id'] .'">'. (!empty($noti['viewed']) ? $txt['Breeze_noti_markasunread'] : $txt['Breeze_noti_markasread']) .'</a>
					</td>
					<td>
					<a href="'. $scripturl .'?action=breezeajax;sa=notidelete;content='. $noti['id'] .';user='. $user_info['id'] .'">', $txt['Breeze_general_delete'] ,'</a>
					</td>
				</tr>';
		}

		// Close the table
		echo '
				</tbody>
			</table><br />';
	}

	// Gotta be more social buddy...
	else
		echo $txt['Breeze_noti_none'];

		echo'
			</div>
			<span class="botslice">
				<span></span>
			</span>
		</div><br />';
}

function template_member_options()
{
	global $context, $settings, $options, $scripturl, $modSettings, $txt;

	// Get the message from the server
	breeze_server_response();

	// The main containing header.
	echo '
		<form action="', $scripturl, '?action=profile;area=breezesettings;save" method="post" accept-charset="', $context['character_set'], '" name="creator" id="creator" enctype="multipart/form-data" onsubmit="return checkProfileSubmit();">
			<h3 class="catbg">
				<span class="left"></span>
				<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" class="icon" />
				', $context['page_desc'] , '
			</h3>
			<p class="windowbg description">
				', $context['page_desc'] , '
			</p>
			<div class="windowbg2">
				<span class="topslice"><span></span></span>
					<div class="content">';

		// Print the form
		echo $context['Breeze']['UserSettings']['Form'];

	// Show the standard "Save Settings" profile button.
	template_profile_save();

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
