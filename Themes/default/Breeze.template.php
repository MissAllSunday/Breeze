<?php

declare(strict_types=1);

use Breeze\Entity\UserSettingsEntity;
use Breeze\Util\Parser;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_main(): void
{
	global $txt, $context, $user_info;

	$memberOnline = sprintf(
		'%1$s%2$s%3$s',
		($context['can_send_pm'] ?
			'<a href="' . $context['member']['online']['href'] . '" title="' .
			$context['member']['online']['text'] . '" rel="nofollow">' : ''),
		'<span class="' . (1 == $context['member']['online']['is_online'] ? 'on' : 'off') .
		'" title="' . $context['member']['online']['text'] . '"></span>',
		($context['can_send_pm'] ?
			'</a>' : '')
	);

	echo '
<div id="breeze_main" class="breeze_main_section">
	<div id="breeze_summary" class="floatleft">
		<div class="roundframe flow_auto">
			<div class="breeze_avatar"
				style="background-image: url(' . $context['member']['avatar']['url'] . ')">
			</div>
			<h3 class="breeze_name">
				' . $memberOnline . '
				' . $context['member']['name_color'] . '
			</h3>
			<p class="breeze_title">
				' . (!empty($context['member']['primary_group']) ? $context['member']['primary_group'] :
			$context['member']['post_group']) . '
			</p>
			<p class="breeze_title">
				' . $context['member']['group_icons'] . '
			</p>
			<p class="breeze_description">
				' . $context['member']['blurb'] . '
			</p>
		</div>
	</div>

	<div id="breeze_app" class="breeze_wall floatright">
		<tabs v-if="loading !== true">
    		<tab :name="\'' . $txt['Breeze_tabs_wall'] . '\'" :selected="true">
				<editor
      			v-bind:editor_id="editorId()"
      			v-on:get-content="postStatus">
				</editor>
				<ul class="status">
					<status
						v-if="errored !== null"
						v-for="status_item in status"
						v-bind:status_item="status_item"
						v-bind:poster_data="getUserData(status_item.status_poster_id)"
						v-bind:key="status_item.status_id"
						v-bind:users="users"
						@remove_status="onRemoveStatus">
					</status>
				</ul>
    		</tab>';

	if (!empty($context['userSettings']['aboutMe'])) {
		echo '
    		<tab :name="\'' . $txt['Breeze_tabs_about'] . '\'">
      			<div class="windowbg">
      				<div class="content">
      					' . Parser::parse($context['userSettings'][UserSettingsEntity::ABOUT_ME]) . '
      				</div>
				</div>
    		</tab>';
	}

	echo '
			<tab :name="\'' . $txt['Breeze_tabs_activity'] . '\'">
      			profile owner recent activity
    		</tab>
  		</tabs>
	</div>
<br />';
}
