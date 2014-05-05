<?php

/**
 * Breeze.template.php
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */
// User's wall.
function template_user_wall()
{
	global $txt, $context, $settings, $scripturl, $user_info, $modSettings;

	// Get the message from the server
	breeze_server_response();

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
				<li class="wall"><a href="#tab-wall" class="active firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_wall'] ,'</span></a></li>';

	// The "About me" tab.
	if (!empty($context['Breeze']['settings']['owner']['aboutMe']))
		echo '
				<li class="about"><a href="#tabs-about" class="firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_about'] ,'</span></a></li>';

	// Does recent activity is enable?
	if (!empty($context['Breeze']['settings']['owner']['activityLog']))
		echo '
				<li class="activity"><a href="#tab-activity" class="firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_activity'] ,'</span></a></li>';

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
	if ($context['member']['is_owner'] || allowedTo('breeze_postStatus'))
		echo '
			<div class="breeze_user_inner windowbg">
				<span class="topslice"><span></span></span>
				<div class="breeze_user_statusbox content">
						<form method="post" action="', $scripturl, '?action=breezeajax;sa=post', !empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '' ,'" id="form_status" name="form_status" class="form_status">
							<textarea cols="40" rows="5" name="statusContent" id="statusContent" rel="atwhoMention"></textarea>
							<input type="hidden" value="', $user_info['id'] ,'" name="statusPoster" id="statusPoster" />
							<input type="hidden" value="', $context['member']['id'] ,'" name="statusOwner" id="statusOwner" />
							<input type="hidden" id="'. $context['session_var'] .'" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
							<br /><input type="submit" value="', $txt['post'] ,'" name="statusSubmit" class="status_button" id="statusSubmit"/>
						</form>
				</div>
				<span class="botslice"><span></span></span>
			</div>';

	// New ajax status here DO NOT MODIFY THIS UNLESS YOU KNOW WHAT YOU'RE DOING and even if you do, DON'T MODIFY THIS
	echo '
			<div id="breeze_load_image"></div>
				<ul class="breeze_status" id="breeze_display_status">';

	// Print out the status if there are any.
	if (!empty($context['member']['status']))
		breeze_status($context['member']['status']);

	// End of list
	echo '
				</ul>';

	// An empty div to append the loaded status via AJAX.
	echo '
			<div id="breezeAppendTo" style="display:hide;"></div>';

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
	if (!empty($context['Breeze']['settings']['owner']['activityLog']))
	{

		echo '
		<div id="tab-activity" class="content">';

		// A nice title bar
		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['Breeze_tabs_activity'] ,'
				</h3>
			</div>';

		if (empty($context['Breeze']['log']))
			echo
			'<span class="upperframe"><span></span></span>
			<div class="roundframe">', $txt['Breeze_tabs_activity_none'] ,'</div>
			<span class="lowerframe"><span></span></span><br />';

		else
			breeze_activity($context['Breeze']['log']);

		echo '
		</div>';
	}
	// Activity end

	// About me
	if (!empty($context['Breeze']['settings']['owner']['aboutMe']))
	{
		echo '
		<div id="tabs-about" class="content">';

		echo '
		<div class="cat_bar">
			<h3 class="catbg">
					', $txt['Breeze_tabs_about'] ,'
			</h3>
		</div>';

		echo '
			<div class="breeze_user_inner windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">';

		echo parse_bbc($context['Breeze']['settings']['owner']['aboutMe']);

		echo '
				</div>
				<span class="botslice"><span></span></span>
			</div>';

		echo '
		</div>';
	}
	// About me end

	// End of right block
	echo '
	</div>';

	// Left block, users data and info
	echo '
	<div id="Breeze_left_block">';

	// Profile owner details
	breeze_profile_owner();

	// Buddies
	if (!empty($context['Breeze']['settings']['owner']['buddies']))
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
			breeze_user_list($context['member']['buddies'], 'buddy');

		// No buddies :(
		else
			echo $txt['Breeze_user_modules_buddies_none'];

			echo '
			</div>
			<span class="botslice"><span> </span></span>
		</div>';

	}
	// Buddies end

	// Visitors
	if (!empty($context['Breeze']['settings']['owner']['visitors']))
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
			breeze_user_list($context['Breeze']['views'], 'visitors');

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
		<form action="', $scripturl , '?action=breezeajax;sa=multiNoti;user=', $user_info['id'] ,'', (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') ,'', ($context['Breeze']['is_log'] ? ';log' : '') ,'" method="post" name="multiNoti" id="multiNoti">
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['Breeze_noti_message'] ,'</th>
						', (!$context['Breeze']['is_log'] ? '<th scope="col">'. $txt['Breeze_noti_markasread_title'] .'</th>' : '') ,'
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
						', ($context['Breeze']['is_log'] ? $noti['content']['message'] : $noti['message']) ,'
					</td>
					', (!$context['Breeze']['is_log'] ? '<td>
					<a href="'. $scripturl .'?action=breezeajax;sa=notimark;content='. $noti['id'] .';user='. $user_info['id'] .''. (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') .'">'. (!empty($noti['viewed']) ? $txt['Breeze_noti_markasunread'] : $txt['Breeze_noti_markasread']) .'</a>' : '') ,'
					</td>
					<td>
					<a href="', $scripturl ,'?action=breezeajax;sa=notidelete;content=', $noti['id'] ,';user='. $user_info['id'] ,'', (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') ,'', ($context['Breeze']['is_log'] ? ';log' : '') ,'">', $txt['Breeze_general_delete'] ,'</a>
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
					', (!$context['Breeze']['is_log'] ? '<option value="">&nbsp;&nbsp;&nbsp;</option>
					<option value="read">'. $txt['Breeze_noti_markasread']  .'</option>
					<option value="unread">'. $txt['Breeze_noti_markasunread'] .'</option>' : '') ,'
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
		<form action="', $scripturl, '?action=breezeajax;sa=usersettings;rf=profile;u=', $context['member']['id'] ,';area='. (!empty($context['Breeze_redirect']) ? $context['Breeze_redirect'] : 'breezesettings') .'" method="post" accept-charset="', $context['character_set'], '" name="breezeSettings" id="breezeSettings">
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">
						<img src="', $settings['images_url'], '/icons/profile_sm.gif" alt="" class="icon" />
						', $context['page_title'] , '
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
	echo '<input type="submit" name="submit" value="', $txt['save'] ,'" class="button_submit"/>';

	echo '
				</div>
				<span class="botslice"><span></span></span>
			</div>
			<br />
		</form>';
}

function template_general_wall()
{
	global $txt, $context, $settings, $scripturl, $user_info, $modSettings;

	// Print the server response
	breeze_server_response();

	// Start of profileview div
	echo '
<div id="profileview" class="flow_auto">';

	// Tabs
	echo '
		<div id="Breeze_tabs">
			<ul class="dropmenu breezeTabs">
				<li class="wall"><a href="#tab-wall" class="active firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_wall'] ,'</span></a></li>';

	echo '
				<li class="activity"><a href="#tab-activity" class="firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_activity'] ,'</span></a></li>';

	echo '
			</ul>
		</div>
		<p class="clear" />';

	// General wall
	echo '
		<div id="tab-wall" class="content">';

	// A nice title bar
	echo '
			<div class="cat_bar">
				<h3 class="catbg">
						', $txt['Breeze_general_wall'] ,'
				</h3>
			</div>';

	// Start the list
	echo '
			<ul class="breeze_status" id="breeze_display_status">';

	// Display the status...
	if (!empty($context['Breeze']['status']))
		breeze_status($context['Breeze']['status']);

	else
		echo '<li>', $txt['Breeze_page_no_status'] ,'</li>';

	// End of list
	echo '
			</ul>';

		// An empty div to append the loaded status via AJAX.
	echo '
			<div id="breezeAppendTo" style="display:hide;"></div>';

	// Pagination
	if (!empty($context['page_index']))
		echo '
			<div class="pagelinks">
				', $txt['pages'], ': ', $context['page_index'], $context['menu_separator'] . ' &nbsp;&nbsp;<a href="#profileview"><strong>' . $txt['go_up'] . '</strong></a>
			</div>';

	// Wall end
	echo '
		</div>';

	echo '
		<div id="tab-activity" class="content">';

	// A nice title bar
	echo '
			<div class="cat_bar">
				<h3 class="catbg">
						', $txt['Breeze_tabs_activity'] ,'
				</h3>
			</div>';

	if (!empty($context['Breeze']['log']))
		breeze_activity($context['Breeze']['log']);

	else
		echo
			'<span class="upperframe"><span></span></span>
			<div class="roundframe">', $txt['Breeze_tabs_activity_buddies_none'] ,'</div>
			<span class="lowerframe"><span></span></span><br />';

	// End of activity
	echo '
		</div>';

	// End of profileview div
	echo '
</div>';

	// Don't forget to print the users data
	breeze_user_info();
}
