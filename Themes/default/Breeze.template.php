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
<div id="breeze_app" class="breeze_main_section">
	<div class="breeze_summary floatleft">
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
			<p class="breeze_mood">
				<set-mood
					:current-mood-id="currentMoodId"
					:user-id="wallData.ownerId"
					:mood-txt="txtMood"
					:is-current-user-owner="isCurrentUserOwner"
					:use-mood="useMood"
				></set-mood>
			</p>
		</div>
	</div>

	<div class="breeze_wall floatright">
		<tabs>
    		<tab :name="\'' . $txt['Breeze_tabs_wall'] . '\'" :selected="true">
				<editor
					editor_id="\'breeze_status\'"
					@get-content="postStatus($event)">
				</editor>
				<ul class="status">
					<status
						v-if="errored !== null"
						v-for="status_item in status"
						v-bind:item="status_item"
						v-bind:key="status_item.status_id"
						:users="users"
						@remove-status="onRemoveStatus($event)"
						@set-new-users="onSetNewUsers($event)">
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

//	Activity tab
//	echo '
//			<tab :name="\'' . $txt['Breeze_tabs_activity'] . '\'">
//
//    		</tab>';

	echo '
  		</tabs>
	</div>
<br />';
}
