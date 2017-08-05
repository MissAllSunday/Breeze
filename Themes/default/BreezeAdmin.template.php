<?php

/**
 * BreezeAdmin.template.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
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
						', $txt['Breeze_live'] , '
					</h3>
				</div>
				<div class="information">
					<div class="windowbg nopadding">
						<div id="smfAnnouncements">',
							$txt['Breeze_feed_error_message'] , '
						</div>
					</div>
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
				<div class="information">
					<div class="content">
						<div id="version_details">
							<strong>', $txt['support_versions'], ':</strong><br />
							', $txt['Breeze_version'] , ':
							<em id="yourVersion" style="white-space: nowrap;">', $context['Breeze']['version'] , '</em><br />';

		// Some more stuff will be here... eventually

	echo '
						</div>
					</div>
				</div>
			</div>
		<div class="clear" />
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['Breeze_page_credits'] , '
				</h3>
			</div>
			<div class="information">
				<div class="content" id="breezelive">
					<p>', $txt['Breeze_page_credits_decs'] ,'</p>';

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
			</div>
		</div>
	</div>
	<br />';
}

function template_manage_mood()
{
	global $context, $txt;

	// Success YAY!
	if (!empty($context['mood']['notice']))
	{
		echo '
		<div class="'. $context['mood']['notice']['type'] .'box">';

		foreach ($context['mood']['notice']['message'] as $m)
			echo '', $txt['Breeze_mood_'. $m] ,'';

		echo '
		</div><br />';
	}

	template_show_list('breeze_mood_list');
}

function template_manage_mood_edit()
{
	global $context, $txt, $scripturl;

	// There were some errors.
	if (!empty($context['mood']['notice']))
	{
		echo '
		<div class="errorbox">
		<p>'. $txt['Breeze_mood_errors'] .'</p>';

		foreach ($context['mood']['notice']['message'] as $e)
			echo '<li>', $txt['Breeze_mood_'. $e] ,'</li>';

		echo '
		</div><br />';
	}

	// Print the form.
	echo
	''
		, $context['mood']['form'] ,
	'';
}

// Boring stuff you will never see...
function template_admin_donate()
{
	global $context;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'] ,'</h3>
		</div>
		<div class="information">
			',$context['Breeze']['donate'],'
		</div>
		<br />';
}
