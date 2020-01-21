<?php

declare(strict_types=1);

/**
 * Breeze.template.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

// Dummy template function is dummy!
function template_user_wall_dummy(): void{}

function template_user_wall_above(): void
{
	global $context, $scripturl;

	// This little fella replaces template_body_above()
	template_top();

	echo '
	<div class="header">
		<div class="container">
			<div class="avatar windowbg">
				<a href="', $scripturl, '?action=profile;area=static;u=', $context['member']['id'] ,'">', $context['member']['avatar']['image'] ,'</a>
			</div>
			<div class="username">
				<h1 class="forumtitle"><a href="', $scripturl, '?action=profile;area=static;u=', $context['member']['id'] ,'">', $context['member']['name'] ,'</a></h1>
				<div>';
}

// User's wall.
function template_user_wall_below(): void
{
	global $txt, $context, $settings, $scripturl, $user_info, $modSettings;

	// Close the div
	echo '
				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>';

	echo '
	<div id="wrapper">';

	theme_linktree();

	// Begins block's div.
	echo '
		<div class="blocks">';

	template_breeze_info();

	if (!empty($context['Breeze']['settings']['owner']['buddies']))
		template_breeze_buddies();

	if (!empty($context['Breeze']['settings']['owner']['visitors']))
		template_breeze_visitors();

	// Ends block's div.
	echo '
		</div>';

	// Tabs
	echo '
		<div class="wallContent">
			<div id="Breeze_tabs">
				<ul class="breezeTabs dropmenu">
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
		<div id="tab-wall">';
	// A nice title bar
	echo '
		<div class="cat_bar">
			<h3 class="catbg">
					', $txt['Breeze_general_wall'] ,'
			</h3>
		</div>';
	// This is the status box,  O RLY?
	if ($context['user']['is_owner'] || allowedTo('breeze_postStatus'))
		echo '
			<div class="inside information">
				<div class="breeze_user_statusbox">
						<form method="post" action="', $scripturl, '?action=breezeajax;sa=post', !empty($context['Breeze']['comingFrom']) ? ';rf=' . $context['Breeze']['comingFrom'] : '' ,'" id="form_status" name="form_status" class="form_status">
							<textarea name="message" id="statusContent" rel="atwhoMention"></textarea>
							<input type="hidden" value="', $context['member']['id'] ,'" name="statusOwner" id="statusOwner" />
							<input type="hidden" id="' . $context['session_var'] . '" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
							<br><input type="submit" value="', $txt['post'] ,'" name="statusSubmit" class="button_submit" id="statusSubmit"/>
						</form>
				</div>
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
			<div id="breezeAppendTo"></div>';

	// Pagination
	if (!empty($context['page_index']))
			echo '
			<div class="floatleft">
				<div class="pagesection">
					', $context['page_index'], ' &nbsp;&nbsp;<a href="#wrapper"><strong>' . $txt['go_up'] . '</strong></a>
				</div>
			</div>
			<br>';
	// Wall end
	echo '
		</div>';

	if (!empty($context['Breeze']['settings']['owner']['activityLog']))
	{
		echo '
		<div id="tab-activity">';

		// A nice title bar
		echo '
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['Breeze_tabs_activity'] ,'
				</h3>
			</div>';

		if (empty($context['Breeze']['log']))
			echo '
			<div class="information">'
				, $txt['Breeze_tabs_activity_none'] ,'
			</div><br />';

		else
			breeze_activity($context['Breeze']['log']);

	// An empty div to append the loaded status via AJAX.
	echo '
			<div id="breezeAppendToLog"></div>';

		echo '
		</div>';
	}

	// About me
	if (!empty($context['Breeze']['settings']['owner']['aboutMe']))
	{
		echo '
		<div id="tabs-about">
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['Breeze_tabs_about'] ,'
				</h3>
			</div>
			<div class="inside information">
				<div class="content">';

		echo parse_bbc($context['Breeze']['settings']['owner']['aboutMe']);

		echo '
				</div>
			</div>
		</div>';
	}

	echo '
		</div>
		<div style="margin:auto; text-align:center" class="clear"><a href="http://missallsunday.com" title="Free SMF Mods">Breeze mod &copy Suki</a></div>
	</div>';

	// Yep, ugly, I know...
	template_body_below();
}


function template_user_notifications(): void
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
		<form action="', $scripturl , '?action=breezeajax;sa=multiNoti;user=', $user_info['id'] ,'', (!empty($context['Breeze']['comingFrom']) ? ';rf=' . $context['Breeze']['comingFrom'] : '') ,'', ($context['Breeze']['is_log'] ? ';log' : '') ,'" method="post" name="multiNoti" id="multiNoti">
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['Breeze_noti_message'] ,'</th>
						', (!$context['Breeze']['is_log'] ? '<th scope="col">' . $txt['Breeze_noti_markasread_title'] . '</th>' : '') ,'
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
					<a href="' . $scripturl . '?action=breezeajax;sa=notimark;content=' . $noti['id'] . ';user=' . $user_info['id'] . '' . (!empty($context['Breeze']['comingFrom']) ? ';rf=' . $context['Breeze']['comingFrom'] : '') . '">' . (!empty($noti['viewed']) ? $txt['Breeze_noti_markasunread'] : $txt['Breeze_noti_markasread']) . '</a>' : '') ,'
					</td>
					<td>
					<a href="', $scripturl ,'?action=breezeajax;sa=notidelete;content=', $noti['id'] ,';user=' . $user_info['id'] ,'', (!empty($context['Breeze']['comingFrom']) ? ';rf=' . $context['Breeze']['comingFrom'] : '') ,'', ($context['Breeze']['is_log'] ? ';log' : '') ,'">', $txt['Breeze_general_delete'] ,'</a>
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
			<div class="floatright">
				', $txt['Breeze_noti_selectedOptions'] ,'
				<select id="multiNotiOption" name="multiNotiOption">
					', (!$context['Breeze']['is_log'] ? '<option value="">&nbsp;&nbsp;&nbsp;</option>
					<option value="read">' . $txt['Breeze_noti_markasread'] . '</option>
					<option value="unread">' . $txt['Breeze_noti_markasunread'] . '</option>' : '') ,'
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
			<div class="content">
				', $txt['Breeze_noti_none'] ,'
			</div>
		</div>';
	}

	// For some reason we need to add a br, otherwise it gets borked...
	echo '
		<br />';
}

function template_member_options(): void
{
	global $context, $txt;

	// Get the message from the server
	breeze_server_response();

	// Print the form
	echo $context['Breeze']['UserSettings']['Form'];

	// SMF autosuggest
	echo "
	<script>
		var oBlockSuggest = new smc_AutoSuggest({
			sSelf: 'oBlockSuggest',
			sSessionId: smf_session_id,
			sSessionVar: smf_session_var,
			sSuggestId: 'breezeSettings[blockList]',
			sControlId: 'blockList',
			sSearchType: 'member',
			sPostName: 'breezeSettings[blockListIDs]',
			sURLMask: 'action=profile;u=%item_id%',
			sTextDeleteItem: '" . $txt['autosuggest_delete_item'] . "',
			bItemList: true,
			sItemListContainerId: 'to_item_list_container'";

	if (!empty($context['Breeze']['UserSettings']['blockListUserData']))
	{
		echo ",
			aListItems: [";

		$blockIds = array_keys($context['Breeze']['UserSettings']['blockListUserData']);
		$lastUserID = key($blockIds);

		foreach ($context['Breeze']['UserSettings']['blockListUserData'] as $id => $userData)
			echo '
				{
					sItemId: ', JavaScriptEscape($id), ',
					sItemName: ', JavaScriptEscape($userData['name']), '
				}', $id == $lastUserID ? '' : ',';

		echo "
			]";
	}


	echo "
		});
	</script>";
}

// This is pretty much the same as template_showAlerts()
function template_alert_edit(): void
{
	global $context, $txt, $scripturl;

	// Do we have an update message?
	if (!empty($context['update_message']))
		echo '
			<div class="infobox">
				', $context['update_message'], '.
			</div>';

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
			', $txt['alerts'], ' - ', $context['member']['name'], '
			</h3>
		</div>';

	if (empty($context['alerts']))
		echo '
		<div class="information">
			', $txt['Breeze_noti_none'] ,'
		</div>';

	else
	{
		// Start the form.
		echo '
		<form action="', $scripturl, '?action=profile;u=', $context['id_member'], ';area=alerts;sa=edit;save=1" method="post" accept-charset="', $context['character_set'], '" id="mark_all">
			<table id="alerts" class="table_grid">';

		foreach ($context['alerts'] as $id => $alert)
		{
			echo '
				<tr class="windowbg">
					<td>', $alert['text'], '</td>
					<td>', $alert['time'], '</td>
					<td>
						<ul class="quickbuttons">
							<li><a href="', $scripturl, '?action=profile;u=', $context['id_member'], ';area=alerts;sa=edit;delete=1;aid=', $id ,';', $context['session_var'], '=', $context['session_id'], '" class="remove_button you_sure">', $txt['delete'] ,'</a></li>
						<li><input type="checkbox" name="mark[', $id ,']" value="', $id ,'"></li>
						</ul>
					</td>
				</tr>';
		}

		echo '
			</table>
			<div class="pagesection">
				<div class="floatleft">
					', $context['pagination'] ,'
				</div>
				<div class="floatright">
					', $txt['check_all'] ,': <input type="checkbox" name="select_all" id="select_all">
					<select name="mark_as">
						<option value="remove">', $txt['quick_mod_remove'] ,'</option>
					</select>
					<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '">
					<input type="submit" name="req" value="', $txt['quick_mod_go'] ,'" class="button_submit you_sure">
				</div>
			</div>
		</form>';
	}
}

// @todo move this to its own template file. Abstract some things...
function template_general_wall(): void
{
	global $txt, $context, $settings, $scripturl, $user_info, $modSettings;

	// Print the server response
	breeze_server_response();

	// Start of profileview div.
	echo '
<div id="profileview" class="flow_auto">';

	// Tabs
	echo '
			<div id="Breeze_tabs">
				<ul class="breezeTabs dropmenu">
					<li class="wall"><a href="#tab-wall" class="active firstlevel"><span class="firstlevel">', $txt['Breeze_tabs_wall'] ,'</span></a></li>';


	// Your buddies activity.
	if (!isset($context['Breeze']['disableTabs']))
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
		echo '<li class="information">', $txt['Breeze_page_no_status'] ,'</li>';

	// End of list
	echo '
			</ul>';

		// An empty div to append the loaded status via AJAX.
	echo '
			<div id="breezeAppendTo" style="display:none;"></div>';

	// Pagination
	if (!empty($context['page_index']))
		echo '
			<div class="floatleft">
				<div class="pagesection">
					', $context['page_index'], ' &nbsp;&nbsp;<a href="#profileview"><strong>' . $txt['go_up'] . '</strong></a>
				</div>
			</div>
			<br>';

	// Wall end
	echo '
		</div>';

	if (!isset($context['Breeze']['disableTabs']))
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

		if (!empty($context['Breeze']['log']))
		{
			echo '
		<div class="content">';
			breeze_activity($context['Breeze']['log']);
			echo '
		</div>';
		}

		else
			echo '
			<div class="information">
				', $txt['Breeze_tabs_activity_buddies_none'] ,'
			</div>
			<br />';

		// End of activity
		echo '
		</div>';
	}

	// End of profileview div
	echo '
</div>';
}
