<?php

declare(strict_types=1);

use Breeze\Util\Parser;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_main(): void
{
	global $txt, $context;

	$memberOnline = sprintf('%1$s%2$s%3$s',
		($context['can_send_pm'] ?
			'<a href="' . $context['member']['online']['href'] . '" title="' .
			$context['member']['online']['text'] . '" rel="nofollow">' : ''),
		'<span class="' . ($context['member']['online']['is_online'] == 1 ? 'on' : 'off') .
		'" title="' . $context['member']['online']['text'] . '"></span>',
		($context['can_send_pm'] ?
			'</a>' : '')
	);

	echo '
<div id="breeze_main" class="breeze_main_section">
	<div id="breeze_summary" class="floatleft">
		<div class="roundframe flow_auto">
			<div class="breeze_avatar" 
			style="background-image: url('. $context['member']['avatar']['url'] .')">		
			</div>
			<h3 class="breeze_name">
				'. $memberOnline .'
				'. $context['member']['name_color'] .'
			</h3>
			<p class="breeze_title">
				'. (!empty($context['member']['primary_group']) ? $context['member']['primary_group'] :
			$context['member']['post_group']) .'
			</p>
			<p class="breeze_title">
				'. $context['member']['group_icons'] .'
			</p>
			<p class="breeze_description">
				'. $context['member']['blurb'] .'
			</p>
		</div>
	</div>

	<div id="breeze_app" class="breeze_wall floatright">
		<tabs v-if="loading !== true">
    		<tab :name="tabs_name.wall" :selected="true">
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

	if (!empty($context['userSettings']['aboutMe']))
		echo '
    		<tab :name="tabs_name.about">
      			<div class="windowbg">
      				<div class="content">
      					'. Parser::parse($context['userSettings']['aboutMe']) .'
      				</div>
				</div>
    		</tab>';

	echo '
			<tab :name="tabs_name.activity">
      			profile owner recent activity
    		</tab>
  		</tabs>
	</div>
<br />';

	echo '
	<script>
		// TODO move these to a service
		var statusURL = smf_scripturl + "?action=breezeStatus;";
		var wall_owner_id = '. $context['member']['id'] .';
		var tabs_wall = "'. $txt['Breeze_tabs_wall'] .'";
		var tabs_post = "'. $txt['Breeze_tabs_post'] .'";
		var tabs_about = "'. $txt['Breeze_tabs_about'] .'";
		var tabs_activity = "'. $txt['Breeze_tabs_activity'] .'";
		var generic_error = "'. $txt['Breeze_error_wrong_values'] .'";
    </script>';
}