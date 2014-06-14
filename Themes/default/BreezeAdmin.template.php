<?php

/**
 * BreezeAdmin.template.php
 *
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
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
						<span class="ie6_header floatleft">', $txt['Breeze_live'] , '</span>
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
							<strong>', $txt['support_versions'], ':</strong><br />
							', $txt['Breeze_version'] , ':
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
					<span class="ie6_header floatleft">', $txt['Breeze_page_credits'] , '</span>
				</h3>
			</div>
			<div class="windowbg nopadding">
				<span class="topslice"><span></span></span>
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
				<span class="botslice"><span></span></span>
			</div>
		</div>
	</div>
	<br />';
}

function template_manage_mood()
{
	global $context, $txt;

	// The dir is not writeable, tell the admin about it
	if (!$context['mood']['isDirWritable'])
		echo '<div class="errorbox">some error text here</div><br />';

	// Show all the images the user has uploaded
	if (!empty($context['mood']['all']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">some description here</h3>
		</div>';

		echo '
			<table class="table_grid" cellspacing="0" width="100%">
				<thead>
					<tr class="catbg">
						<th scope="col" class="first_th">Image</th>
						<th scope="col" >Name</th>
						<th scope="col">Description</th>
						<th scope="col">Edit</th>
						<th scope="col" class="last_th">Delete</th>
					</tr>
				</thead>
			<tbody>';

		foreach($context['mood']['all'] as $m)
		{
			echo '
				<tr class="windowbg" style="text-align: center">
					<td>
						<img src="', $context['mood']['imageUrl'] . $m['file'] .'.'. $m['ext'],'" />
					</td>
					<td>
						', $m['name'] ,'
					</td>
					<td>
						', $m['description'] ,'
					</td>
					<td>
						edit
					</td>
					<td>
						delete
					</td>
				</tr>';
		}

		echo '
			</tbody>
		</table><hr /><br />';
	}

	else
		echo '<div class="errorbox">', $txt['Breeze_page_mood__noList'] ,'</div><br />';
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
