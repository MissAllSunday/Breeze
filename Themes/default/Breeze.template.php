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
			$buddyToken = createToken('buddy', 'get');
			echo '
				<ul class="reset buddyList">';

			foreach ($buddiesData as $buddy) {
				$buddyIcon = $buddy['is_buddy'] ? 'delete' : 'plus';
				$buddyText = $buddy['is_buddy'] ? 'remove' : 'add';
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
