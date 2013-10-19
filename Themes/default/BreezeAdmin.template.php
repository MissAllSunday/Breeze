<?php

/**
 * BreezeAdmin.template
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

	// The admin panel where the news and other very useful stuff is displayed
function template_admin_home()
{
	global $txt, $context;

	// Welcome message for the admin.
	echo '
	<div id="admincenter">';

	// Is there an update available?
	echo '
		<div id="update_section"></div>';

	echo '
		<div id="admin_main_section">';

	// Display the "live news" from missallsunday.com.
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

	// Show the Breeze version.
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
							<strong>', $txt['Breeze_admin_breeze_version'] , '</strong> :
							<em id="yourVersion" style="white-space: nowrap;">', $context['Breeze']['version'] , '</em><br />';

		// Some more stuff will be here... eventually

	echo '
						</div>
					</div>
					<span class="botslice"><span></span></span>
				</div>
			</div>
		<br class="clear" />
			<div class="cat_bar">
				<h3 class="catbg">
					<span class="ie6_header floatleft">', $txt['Breeze_admin_general_credits_title'] , '</span>
				</h3>
			</div>
			<div class="windowbg nopadding">
				<span class="topslice"><span></span></span>
				<div class="content" id="breezelive">
					<p>', $txt['Breeze_admin_general_credits_decs'] ,'</p>';

	// Print the credits array
	if (!empty($context['Breeze']['credits']))
		foreach ($context['Breeze']['credits'] as $c)
		{
			echo '
					<dl>
						<dt>
							<strong>', $c['name'], ':</strong>
						</dt>';

			foreach ($c['users'] as $u)
				echo '
						<dd>
							<a href="', $u['site'] ,'">', $u['name'] ,'</a>
						</dd>';

			echo '
					</dl>';
		}

	echo '
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>
	</div>
	<br />';
}

function template_admin_maintenance()
{
	global $context, $txt, $scripturl, $settings;

	echo '
	<div id="manage_maintenance" style="margin:auto;">';

	// Mass delete status
	echo '
		<div class="floatright" style="width:49%;">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['Breeze_maintenance_status_tools'] , '</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>', $txt['Breeze_maintenance_delete_status_since'] , '</dt>
						<dd>
							<form action="', $scripturl , '?action=admin;area=breezeadmin;sa=maintenance;do=status_since" method="post" accept-charset="', $context['character_set'], '">
							<select id="status_since" name="since">
								<option value="">&nbsp;&nbsp;&nbsp;</option>
								<option value="week">', $txt['Breeze_maintenance_delete_week']  ,'</option>
								<option value="month">', $txt['Breeze_maintenance_delete_month'] ,'</option>
								<option value="year">', $txt['Breeze_maintenance_delete_year'] ,'</option>
								<option value="all">', $txt['Breeze_maintenance_delete_all'] ,'</option>
							</select>
							<span><input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							</form>
						</dd>
						<dt>', $txt['Breeze_maintenance_delete_status_user'] , '<br/>
							<span class="smalltext">', $txt['Breeze_maintenance_delete_status_user_desc'] , '</span></dt>
						<dd>
							<form action="', $scripturl , '?action=admin;area=breezeadmin;sa=maintenance;do=status_user" method="post" accept-charset="', $context['character_set'], '">
									<input type="text" name="user" id="status_user">
									<span><input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" /><br />
									<div id="to_item_list_container_status"></div>
							</form>
						</dd>
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>';

	// Comment tools
	echo '
		<div class="floatleft" style="width:49%;">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['Breeze_maintenance_comments_tools'] , '</h3>
			</div>
			<div class="windowbg">
				<span class="topslice"><span></span></span>
				<div class="content">
					<dl class="settings">
						<dt>', $txt['Breeze_maintenance_delete_comments'] , '</dt>
						<dd>
							<form action="', $scripturl , '?action=admin;area=breezeadmin;sa=maintenance;do=comment_since" method="post" accept-charset="', $context['character_set'], '">
							<select id="comment_since" name="since">
								<option value="">&nbsp;&nbsp;&nbsp;</option>
								<option value="week">', $txt['Breeze_maintenance_delete_week']  ,'</option>
								<option value="month">', $txt['Breeze_maintenance_delete_month'] ,'</option>
								<option value="year">', $txt['Breeze_maintenance_delete_year'] ,'</option>
								<option value="all">', $txt['Breeze_maintenance_delete_all'] ,'</option>
							</select>
							<span><input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
							<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
							</form>
						</dd>
						<dt>', $txt['Breeze_maintenance_delete_comment_user'] , '<br/>
							<span class="smalltext">', $txt['Breeze_maintenance_delete_status_user_desc'] , '</span></dt>
						<dd>
							<form action="', $scripturl , '?action=admin;area=breezeadmin;sa=maintenance;do=comment_user" method="post" accept-charset="', $context['character_set'], '">
									<input type="text" name="user" id="comment_user">
									<span><input type="submit" value="', $txt['maintain_run_now'], '" class="button_submit" /></span>
									<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" /><br/>
									<div id="to_item_list_container_comment"></div>
							</form>
						</dd>
					</dl>
				</div>
				<span class="botslice"><span></span></span>
			</div>
		</div>';

	echo '
		<div class="clear"></div>
	</div>
	<br />';

	// Auto-suggest script
	echo '<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/PersonalMessage.js?fin20"></script>
<script type="text/javascript" src="'. $settings['default_theme_url'] .'/scripts/suggest.js?fin20"></script>
<script type="text/javascript"><!-- // --><![CDATA[
	var comment_user = new smc_AutoSuggest({
		sSelf: \'comment_user\',
		sSessionId: \'', $context['session_id'], '\',
		sSessionVar: \'', $context['session_var'], '\',
		sSuggestId: \'comment_user\',
		sControlId: \'comment_user\',
		sSearchType: \'member\',
		sPostName: \'comment_user_suggest\',
		sURLMask: \'action=profile;u=%item_id%\',
		sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
		bItemList: true,
		sItemListContainerId: \'to_item_list_container_comment\',
	});

	var status_user = new smc_AutoSuggest({
		sSelf: \'status_user\',
		sSessionId: \'', $context['session_id'], '\',
		sSessionVar: \'', $context['session_var'], '\',
		sSuggestId: \'status_user\',
		sControlId: \'status_user\',
		sSearchType: \'member\',
		sPostName: \'status_user_suggest\',
		sURLMask: \'action=profile;u=%item_id%\',
		sTextDeleteItem: \'', $txt['autosuggest_delete_item'], '\',
		bItemList: true,
		sItemListContainerId: \'to_item_list_container_status\',
	});
// ]]></script>';
}

// Boring stuff you will never see...
function template_admin_donate()
{
	global $context;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'] ,'</h3>
		</div>
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					',$context['Breeze']['donate'],'
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span><br />';
}
