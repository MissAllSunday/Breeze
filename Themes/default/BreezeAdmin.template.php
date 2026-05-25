<?php

declare(strict_types=1);

use Breeze\Breeze;
use Breeze\Service\Actions\AdminServiceInterface;

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
				<div id="smfAnnouncements" class="information"></div>
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

	echo '
						</div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<div class="cat_bar">
				<h3 class="catbg">
					', $txt['Breeze_page_credits'] , '
				</h3>
			</div>
			<div class="information">
				<div class="content" id="breezelive">
					<p>', $txt['Breeze_page_credits_decs'] ,'</p>';

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
	</div>';
}

function template_maintenance(): void
{
	global $context, $txt;

	if (!empty($context['settings_message'])) {
		echo '
		<', $context['settings_message']['tag'], ' class="', $context['settings_message']['class'], '">',
			$context['settings_message']['label'],
		'</', $context['settings_message']['tag'], '>';
	}

	echo '
		<div class="cat_bar">
			<h3 class="catbg">', $context['page_title'] ,'</h3>
		</div>
		<div class="information">
			<div class="content">
				<dl class="settings">
					<dt>', $txt['Breeze_maintenance_orphan_comments'] ,'</dt>
					<dd>
						', $context[Breeze::NAME]['maintenance_stats']['orphan_comments'] ,'
						', $context[Breeze::NAME]['maintenance_stats']['orphan_comments'] > 0 ? '
						<form action="' . $context['post_url'] . ';type=comments" method="post" class="breeze-inline-form">
							<input type="submit" value="' . $txt['Breeze_maintenance_fix_comments'] . '" class="button" />
							<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
							<input type="hidden" name="' . $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token_var'] . '" value="' . $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token'] . '" />
						</form>' : '', '
					</dd>
					<dt>', $txt['Breeze_maintenance_orphan_likes'] ,'</dt>
					<dd>
						', $context[Breeze::NAME]['maintenance_stats']['orphan_likes'] ,'
						', $context[Breeze::NAME]['maintenance_stats']['orphan_likes'] > 0 ? '
						<form action="' . $context['post_url'] . ';type=likes" method="post" class="breeze-inline-form">
							<input type="submit" value="' . $txt['Breeze_maintenance_fix_likes'] . '" class="button" />
							<input type="hidden" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" />
							<input type="hidden" name="' . $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token_var'] . '" value="' . $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token'] . '" />
						</form>' : '', '
					</dd>
				</dl>';

	if ($context[Breeze::NAME]['maintenance_stats']['orphan_comments'] > 0 || $context[Breeze::NAME]['maintenance_stats']['orphan_likes'] > 0) {
		echo '
				<div class="righttext">
					<form action="', $context['post_url'] ,';type=all" method="post">
						<input type="submit" value="', $txt['Breeze_maintenance_fix_all'] ,'" class="button" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="', $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token_var'], '" value="', $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token'], '" />
					</form>
				</div>';
	} else {
		echo '
				<div class="righttext">
					', $txt['Breeze_maintenance_no_issues'] ,'
				</div>';
	}

	echo '
			</div>
		</div>
		<div class="cat_bar">
			<h3 class="catbg">', $txt['Breeze_maintenance_enable_all_walls'] ,'</h3>
		</div>
		<div class="information">
			<div class="content">
				<p>', $txt['Breeze_maintenance_enable_all_walls_desc'] ,'</p>
				<div class="righttext">
					<form action="', $context['post_url'] ,';type=walls" method="post">
						<input type="submit" value="', $txt['Breeze_maintenance_enable_all_walls'] ,'" class="button" />
						<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
						<input type="hidden" name="', $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token_var'], '" value="', $context[AdminServiceInterface::MAINTENANCE_TOKEN . '_token'], '" />
					</form>
				</div>
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
		</div>';
}
