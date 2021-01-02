<?php

declare(strict_types=1);


// The admin panel where the news and other very useful stuff is displayed
use Breeze\Breeze;

function template_breezeAdmin_main(): void
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

	// Display the "live news"
	echo '
			<div id="live_news" class="floatleft">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['Breeze_live'] , '
					</h3>
				</div>
				<div id="smfAnnouncements" class="information">
					<span v-if="errored">{{releasesNotFound}}</span>
					<releases-feed
						v-else
						v-for="release in releases"
						v-bind:release="release"
						v-bind:key="release.id"
					></releases-feed>
				</div>
			</div>';

	// Show the Breeze version.
	echo '
			<div id="support_info" class="floatright">
				<div class="cat_bar">
					<h3 class="catbg">
						', $txt['support_title'], '
					</h3>
				</div>
				<div class="information">
					<div class="content">
						<div id="version_details" class="padding">
							<strong>', $txt['support_versions'], ':</strong>
							<br>
							', $txt['Breeze_version'] , ':
							<em>
								', $context[Breeze::NAME]['version'] , '
							</em>
							<br>
							', $txt['Breeze_vue_version'] , ':
							<em>
								', $context[Breeze::NAME]['vue'] , '
							</em>';

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
	if (!empty($context[Breeze::NAME]['credits'])) {
		foreach ($context[Breeze::NAME]['credits'] as $credit) {
			echo '
					<dl>
						<dt>
							<strong>', $credit['name'], ':</strong>
						</dt>';

			foreach ($credit['users'] as $user) {
				echo '
						<dd>
							<a href="', $user['site'] ,'">', $user['name'] ,'</a>
						</dd>';
			}

			echo '
					</dl>';
		}
	}

	echo '
				</div>
			</div>
		</div>
	</div>
	<br />';

	echo '
	<script>
      var feedURL = "'. Breeze::FEED .'";
      var releasesNotFound = "'. $txt['Breeze_feed_error_message'] .'";
    </script>

';
}

function template_breezeAdmin_moodList(): void
{
	global $context;

	if (!empty($context[Breeze::NAME]['notice'])) {
		echo '
		<div class="' . $context[Breeze::NAME]['notice']['type'] . 'box">
		', $context[Breeze::NAME]['notice']['message'] ,'
		</div><br />';
	}

	echo '
		<div id="moodList">
			<span v-if="errored">There was a problem getting the moods</span>
			<mood
				v-for ="mood in localMoods"
				v-bind:mood="mood"
			></mood>
		</div>';
}

// Boring stuff you will never see...
function template_breezeAdmin_donate(): void
{
	global $context, $txt;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'] ,'</h3>
		</div>
		<div class="information">
			', $txt['Breeze_page_donate_exp'] ,'
			<a href="', Breeze::SUPPORT_URL ,'">', $txt['Breeze_page_donate_link'] ,'</a>.
		</div>
		<br />';
}
