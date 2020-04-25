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
		<span v-if="loading">loading...</span>
		<status
		v-else
		v-for="status_item in status"
		v-bind:status_item="status_item"
		v-bind:poster_data="getUserData(status_item.status_poster_id)"
		v-bind:comments="getComments(status_item.status_id)"
		v-bind:key="status_item.status_id"
		></status>
	</div>
<br />';

	echo '	
	<script>
      var statusURL = smf_scripturl + "?action=breezeStatus;u=" + smf_member_id;
    </script>';
}