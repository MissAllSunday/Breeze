<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_main(): void
{
	global $txt, $context;

	echo '
<div id="breeze_main" class="breeze_main_section">
	<div id="breeze_blocks" class="floatleft">
		<div class="cat_bar">
			<h3 class="catbg">
				some block
			</h3>
		</div>
		<div class="windowbg">
			some content
		</div>
	</div>
	<div id="breeze_app" class="breeze_wall floatright">
		<tabs v-if="loading !== true">
    		<tab :name="tabs_name.wall" :selected="true">
				<editor
      			v-bind:editor_id="editorId()"
      			v-on:get-content="postStatus">
				</editor>
				<p></p>
				<status
					v-if="errored !== null"
					v-for="status_item in status"
					v-bind:status_item="status_item"
					v-bind:poster_data="getUserData(status_item.status_poster_id)"
					v-bind:key="status_item.status_id"
					v-bind:users="users"
					@removeStatus="onRemoveStatus">			
				</status>
    		</tab>
    		<tab :name="tabs_name.about">
      			'. $context['member']['name'] .'
    		</tab>
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