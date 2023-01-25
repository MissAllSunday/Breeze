<?php

declare(strict_types=1);

use Breeze\Breeze;

function template_main(): void
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
							', $txt['Breeze_react_version'] , ':
							<em>
								', $context[Breeze::NAME]['react'] , '
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
      let feedURL = "'. Breeze::FEED .'";
      
      fetch(feedURL).then(function (response) {
		return response.json();
	  }).then(function (data) {
		addReleases(data)(data);
	  });
    
    function addReleases(releases)
    {
	  let releasesNotFound = "'. $txt['Breeze_feed_error_message'] .'";
      let smfAnnouncements = releasesNotFound;
      let app = document.querySelector("#smfAnnouncements");
	  let dl = document.createElement("dl");
	  
	  for (const [key, release] of Object.entries(releases).slice(0, 5)) {
 			let dt = document.createElement("dt");	
 			let dd = document.createElement("dd");
 			let anchor = document.createElement("a");
 			let date = new Date(release.published_at)
 			
 			anchor.innerText = 	release.name;
            anchor.href = release.html_url;
            dt.append(anchor)
            dt.append(" " + date.toLocaleString("en-US"))
            dd.innerText = release.body;
 			dl.append(dt);
 			dl.append(dd);
		}
      
      app.append(dl);
    }
    </script>

';
}

function template_moodList(): void
{
	global $context, $txt;

	if (!empty($context[Breeze::NAME]['notice'])) {
		echo '
		<div class="' . $context[Breeze::NAME]['notice']['type'] . 'box">
		', $context[Breeze::NAME]['notice']['message'] ,'
		</div><br />';
	}

	echo '
<div id="moodList">
	<div class="cat_bar">&nbsp;</div>
	<div class="windowbg">
		<span v-if="errored">' . $txt['Breeze_error_moodGet'] . '</span>
		<ul>
			<mood-admin
				v-for ="mood in localMoods"
				:key="mood.id"
				v-bind:mood="mood"
				@remove-mood="removeMood($event)"
				@update-list="updateList($event)"
			></mood-admin>
		</ul>
	</div>
	<div class="additional_row">
		<input type="submit" value="' . $txt['Breeze_mood_newMood'] . '" class="button" @click="creating()">
		<modal v-if="showModal" @close="showModal = false" @click.stop>
			<div slot="header">
				{{ $root.txt.mood.newMood }}
			</div>
			<div slot="body">
				<mood-form
					v-bind:mood="[]"
					@delete="showModal = false"
					@save="onSave($event)"
				></mood-form>
			</div>
		</modal>
	</div>
</div>';
}

// Boring stuff you will never see...
function template_donate(): void
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
