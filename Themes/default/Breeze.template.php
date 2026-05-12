<?php

declare(strict_types=1);

use Breeze\Breeze;
use Breeze\Entity\UserSettingsEntity;

function display_content(string $type = Breeze::ACTION_PROFILE): string
{
	global $context;

	return '
		<div id="editor_container">
		 	<script>'. (!empty($context['bbcodes_handlers']) ? $context['bbcodes_handlers'] : '') . '</script>
			<div id="root" class="breeze_main_section" wallType="'. $type .'">
			</div>
		</div>';
}
function template_profile(): void
{
	global $context, $scripturl, $txt;

	$profileSettings = $context[Breeze::NAME]['profileSettings'];
	$buddiesData = $context[Breeze::NAME]['buddiesData'];
	$aboutMe = $profileSettings[UserSettingsEntity::ABOUT_ME] ?? '';
	$enableBuddiesTab = $profileSettings[UserSettingsEntity::ENABLE_BUDDIES_TAB] ?? 0;

	echo '
	<hr />
	<p class="clear" />
	<div id="tab-wall" class="content">
		', display_content() ,'
	</div>';

	if (!empty($aboutMe)) {
		echo '
		<div id="tab-about" class="content" style="display: none;">
			' . parse_bbc($aboutMe) . '
		</div>';
	}

	if ($enableBuddiesTab)
	{
		echo '
		<div id="tab-buddies" class="windowbg" style="display: none;">';
		if (!empty($buddiesData))
		{
			$buddyToken = !empty($context['buddy_token_var'])
				? ['buddy_token_var' => $context['buddy_token_var'], 'buddy_token' => $context['buddy_token']]
				: createToken('buddy', 'get');
			echo '
				<ul class="reset buddyList">';

			foreach ($buddiesData as $buddy) {
				$buddyIcon = ($buddy['buddy_status'] ?? '') === 'confirmed' ? 'delete' : 'plus';
				$buddyText = ($buddy['buddy_status'] ?? '') === 'confirmed' ? 'remove' : 'add';
				echo '
				<ul class="flow_auto">
    				<li class="avatar">
    					<a href="', $buddy['href'], '">
			  				<img
								src="', $buddy['avatar']['url'], '"
								alt="', $buddy['username'], '"
								class="avatar" />
						</a>
    				</li>
    				<li>
    					', $buddy['link_color'] ,'
    					<a href="', $scripturl , '?action=buddy;u=', $buddy['id'], ';', $context['session_var'], '=', $context['session_id'], ';', $buddyToken['buddy_token_var'], '=', $buddyToken['buddy_token'], '">
    						<span class="main_icons ', $buddyIcon , '" title="', $txt['buddy_' . $buddyText] ,'" /></a>
					</li>
  				</ul>';
			}
			echo '
				</ul>';
		}

		// No buddies :(
		else {
			echo $txt[Breeze::NAME . '_user_modules_buddies_none'];
		}

		echo '
		</div>';
	}

	echo template_javascript(true);
}

function template_wall(): void
{
	echo '
	', display_content(Breeze::ACTION_WALL);
}

function template_buddyRequests(): void
{
	global $context, $scripturl, $txt;

	$pendingRequests = $context[Breeze::NAME]['pendingRequests'] ?? [];
	$buddyToken = $context[Breeze::NAME]['buddyToken'] ?? [];
	$sessionVar = $context[Breeze::NAME]['sessionVar'] ?? 'sc';
	$sessionId = $context[Breeze::NAME]['sessionId'] ?? '';
	$currentUserId = $context['user']['id'] ?? 0;

	echo '
	<div class="cat_bar">
		<h3 class="catbg">', $txt['Breeze_user_buddy_requests_title'] ?? 'Buddy Requests', '</h3>
	</div>
	<div class="windowbg noup">
		<div class="padding">
			<ul class="quickbuttons">
				<li>
					<a href="', $scripturl, '?action=profile;area=lists;sa=buddies" class="button">', $txt['buddies'] ?? 'Buddies', '</a>
				</li>
				<li>
					<a href="', $scripturl, '?action=profile;area=lists;sa=ignore;u=', $currentUserId, '" class="button">', $txt['ignore'] ?? 'Ignore List', '</a>
				</li>
			</ul>';

	if (!empty($pendingRequests)) {
		echo '
			<ul class="reset buddyList">';

		foreach ($pendingRequests as $request) {
			$sender = $request['sender'] ?? [];
			$req = $request['request'] ?? [];
			$senderId = $sender['id'] ?? 0;
			$senderName = $sender['name'] ?? 'Unknown';
			$senderAvatar = $sender['avatar']['url'] ?? '';
			$senderLink = $sender['link'] ?? '';

			echo '
				<li class="flow_auto">
					<div class="avatar">
						<a href="', $senderLink, '">
							<img src="', $senderAvatar, '" alt="', $senderName, '" class="avatar" />
						</a>
					</div>
					<div class="user_info">
						', $senderLink, '
						<div class="action_links">
							<a href="', $scripturl, '?action=buddy;sa=confirm;u=', $senderId, ';', $sessionVar, '=', $sessionId, ';', $buddyToken['buddy_token_var'], '=', $buddyToken['buddy_token'], '" class="button">
								<span class="main_icons check" title="', $txt['Breeze_user_accept'] ?? 'Accept', '"></span> ', $txt['Breeze_user_accept'] ?? 'Accept', '
							</a>
							<a href="', $scripturl, '?action=buddy;sa=decline;u=', $senderId, ';', $sessionVar, '=', $sessionId, ';', $buddyToken['buddy_token_var'], '=', $buddyToken['buddy_token'], '" class="button">
								<span class="main_icons delete" title="', $txt['Breeze_user_decline'] ?? 'Decline', '"></span> ', $txt['Breeze_user_decline'] ?? 'Decline', '
							</a>
						</div>
					</div>
				</li>';
		}

		echo '
			</ul>';
	} else {
		echo '
			<p class="information">', $txt['Breeze_user_buddy_requests_empty'] ?? 'You have no pending buddy requests.', '</p>';
	}

	echo '
		</div>
	</div>';
}
