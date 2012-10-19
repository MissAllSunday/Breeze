<?php

/**
 * BreezeAdmin.template
 *
 * The purpose of this file is to show the admin section for the mod's settings
 * @package Breeze mod
 * @version 1.0 Beta 2
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

	/* The admin panel where the news and other very useful stuff is displayed*/
function template_admin_home()
{
	global $txt, $context;

	/* Welcome message for the admin. */
	echo '
	<div id="admincenter">
		<div class="cat_bar">
			<h3 class="catbg">';

	echo $txt['Breeze_admin_settings_admin_panel'], '
			</h3>
		</div>
		<span class="upperframe"><span></span></span>
		<div class="roundframe">
			<div id="welcome">
				<strong>', $txt['hello_guest'], ' ', $context['user']['name'], '!</strong>
				', sprintf($txt['Breeze_admin_welcome'], $txt['Breeze_admin_settings_admin_panel']), '
			</div>
		</div>
		<span class="lowerframe"><span></span></span>';

	/* Is there an update available? */
	echo '
		<div id="update_section"></div>';

	echo '
		<div id="admin_main_section">';

	/* Display the "live news" from missallsunday.com. */
	echo '
			<div id="live_news" class="floatleft">
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="ie6_header floatleft">', $txt['Breeze_admin_live'] , '</span>
					</h3>
				</div>
				<div class="windowbg nopadding">
					<span class="topslice"><span></span></span>
					<div class="content" id="breezelive">
						<div id="breezelive"></div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>';

	/* Show the Breeze version. */
	echo '
			<div id="supportVersionsTable" class="floatright">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['support_title'], '
					</h3>
				</div>
				<div class="windowbg nopadding">
					<span class="topslice"><span></span></span>
					<div class="content">
						<div id="version_details">
							<strong>', $txt['support_versions'], ':</strong><br />
							', $txt['Breeze_admin_breeze_version'] , ':
							<em id="yourVersion" style="white-space: nowrap;">', $context['Breeze']['version'] , '</em><br />';

		/* Some more stuff will be here... eventually */

	echo '
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
		</div>
	</div>';
}

/* Boring stuff you will never see... */
function template_admin_donate()
{
	global $txt;

	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					',$txt['Breeze_admin_settings_donate_text'],'
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span><br />';
}