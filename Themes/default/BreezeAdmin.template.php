<?php

/**
 * BreezeAdmin.template.php
 *
 * @package Breeze mod
 * @version 1.0.14
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2018, Jessica González
 * @license //www.mozilla.org/MPL/MPL-1.1.html
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
						<div id="smfAnnouncements">',
							$txt['Breeze_feed_error_message'] , '
						</div>
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
	<br />
	<script>
	$(document).ready(function(){
		$.ajax({
			type: "GET",
			url: ', JavaScriptEscape(Breeze::$supportFeed) ,',
			cache: false,
			dataType: "xml",
			success: function(xml){

				var breezelive = $("#smfAnnouncements");
				var dl = $("<dl />").appendTo(breezelive);
				breezelive.html(dl);
				$(xml).find("entry").each(function () {
					var item = $(this),
						title = $("<a />", {
							text: item.find("title").text(),
							href: "//github.com" + item.find("link").attr("href")
						}),
						parsedTime = new Date(item.find("updated").text().replace("T", " ").split("+")[0]),
						updated = $("<span />").text(" | " + parsedTime.toDateString()),
						content = $("<div/>").html(item.find("content")).text(),
						dt = $("<dt />").html(title),
						dd = $("<dd />").html(content);

						updated.appendTo(dt);
						dt.appendTo(dl);
						dd.appendTo(dl);
				});
			},
			error: function (html){}
		});
	});
	</script>';
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
