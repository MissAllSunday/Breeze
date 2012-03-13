<?php

/**
 * BreezeBuddy.template
 *
 * The purpose of this file is to show the admin section for the mod's settings
 * @package Breeze mod
 * @version 1.0 Beta 1
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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

	/* Show a message to let the user know his/her request must be approved by the user */
function template_Breeze_request_buddy_message_send()
{
	global $txt, $context;

	/* Welcome message for the admin. */
	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">'
				, $txt['BreezeMod_user_buddyrequestmessage_name'], '
			</h3>
		</div>
		<span class="upperframe">
			<span></span>
		</span>
		<div class="roundframe">
			<div id="welcome">
				Description message here
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span>
	</div>';
}

function template_Breeze_buddy_list()
{
	global $txt, $context;

	/* Welcome message for the admin. */
	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				Buddy list
			</h3>
		</div>
		<span class="upperframe">
			<span></span>
		</span>
		<div class="roundframe">
			<div id="welcome">
				Description message here
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span>
		<div id="admin_main_section">';

	/* Show the Buddy request list. */
	echo '
			<div id="supportVersionsTable" >
				<div class="cat_bar">
					<h3 class="catbg">
						buddy list
					</h3>
				</div>
				<div class="windowbg nopadding">
					<span class="topslice">
						<span></span>
					</span>
					<div class="content">
						buddy lisy here
					</div>
					<span class="botslice">
						<span></span>
					</span>
				</div>
			</div>
		</div>';
}