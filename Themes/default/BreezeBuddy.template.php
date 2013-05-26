<?php

/**
 * BreezeBuddy.template
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

	// Show a message to let the user know his/her request must be approved by the user
function template_Breeze_request_buddy_message_send()
{
	global $txt, $context;

	// Welcome message for the admin.
	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">'
				, $txt['Breeze_user_buddyrequestmessage_name'], '
			</h3>
		</div>
		<span class="upperframe">
			<span></span>
		</span>
		<div class="roundframe">
			<div id="welcome">
				', $txt['Breeze_user_buddyrequestmessage_message'] ,'
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span>
	</div>';
}

function template_Breeze_buddy_list()
{
	global $txt, $context, $scripturl;

	// If there is an inner_message, show it here
	if (!empty($context['Breeze']['inner_message']))
		echo '<div class="windowbg" id="profile_success">', $context['Breeze']['inner_message'] ,'</div>';

	// Welcome message for the admin.
	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $txt['Breeze_buddy_title'] ,'
			</h3>
		</div>
		<span class="upperframe">
			<span></span>
		</span>
		<div class="roundframe">
			<div id="welcome">
				', $txt['Breeze_buddy_desc'] ,'
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span>
		<div id="admin_main_section">';

	// Buddy request
	if (!empty($context['Breeze']['Buddy_Request']))
	{
		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">', $txt['Breeze_buddyrequest_list_message'] ,'</th>
						<th scope="col">', $txt['Breeze_buddyrequest_list_confirm'] ,'</th>
						<th scope="col" class="last_th">', $txt['Breeze_buddyrequest_list_decline'] ,'</th>
					</tr>
				</thead>
			<tbody>';

			foreach($context['Breeze']['Buddy_Request'] as $request)
			{
				echo '
						<tr class="windowbg" style="text-align: center">
							<td>
							', $request['content']['message'] ,'
							</td>
							<td>
							<a href="', $scripturl ,'?action=profile;area=breezebuddies;from=', $request['user'] ,';to=', $request['id'] ,';message=confirm" target="_self">', $txt['Breeze_buddyrequest_list_confirm'] ,'</a>
							</td>
							<td>
							<a href="', $scripturl ,'?action=profile;area=breezebuddies;from=', $request['user'] ,';to=', $request['id'] ,';message=decline" target="_self">', $txt['Breeze_buddyrequest_list_decline'] ,'</a>
							</td>
						</tr>';
			}

		echo '</tbody>
		</table><br />';
	}

	else
		echo '<p />
<div class="windowbg">
	<span class="topslice">
		<span></span>
	</span>
	<div class="content flow_auto">
		', $txt['Breeze_buddyrequest_noBuddies'] ,'
	</div>
	<span class="botslice">
		<span></span>
	</span>
	</div>';

		// end of admin div
	echo '
		</div>';
}
