<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_main(): void
{
	global $txt;

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
		<tabs>
    		<tab :name="tabs_name.wall" :selected="true">
    			<span v-if="loading">loading...</span>
				<status
					v-else
					v-for="status_item in status"
					v-bind:status_item="status_item"
					v-bind:poster_data="getUserData(status_item.status_poster_id)"
					v-bind:key="status_item.status_id"
					v-bind:users="users"
					@removeStatus="onRemoveStatus">			
				</status>
    		</tab>
    		<tab :name="tabs_name.post">
      			post a new status
    		</tab>
    		<tab :name="tabs_name.about">
      			about me 
    		</tab>
			<tab :name="tabs_name.activity">
      			profile owner recent activity
    		</tab>
  		</tabs>
	</div>
<br />';

	echo '	
	<script>
		// TODO use the correct profile Id
		var statusURL = smf_scripturl + "?action=breezeStatus;u=" + smf_member_id;
		
		// TODO move these to a service
		var tabs_wall = "'. $txt['Breeze_tabs_wall'] .'";
		var tabs_post = "'. $txt['Breeze_tabs_post'] .'";
		var tabs_about = "'. $txt['Breeze_tabs_about'] .'";
		var tabs_activity = "'. $txt['Breeze_tabs_activity'] .'";
    </script>';
}