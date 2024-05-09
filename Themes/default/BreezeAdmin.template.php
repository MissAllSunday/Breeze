<?php

declare(strict_types=1);

use Breeze\Breeze;

function template_main(): void
{
	global $txt, $context;

	echo '
	<div id="admincenter">';

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
	<script defer="defer">
      let feedURL = "' . Breeze::FEED . '";
      let releasesNotFound = "' . $txt['Breeze_feed_error_message'] . '";
      let app = document.querySelector("#smfAnnouncements");
      app.append(releasesNotFound);

      fetch(feedURL).then(function (response) {
		return response.json();
	  }).then(function (data) {
		addReleases(data, app);
	  });

    function addReleases(releases, app)
    {
	  let dl = document.createElement("dl");
	  app.innerHTML = ""

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
    </script>';
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
