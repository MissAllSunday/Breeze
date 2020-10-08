<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breezeSettings_main(): void
{
	global $txt, $context;

	echo '
<div id="breeze_main_section">
	<div class="cat_bar">
		<h3 class="catbg profile_hd">
			', $context['page_title'] ,'
		</h3>
	</div>
	<p class="information">
		', $txt['Breeze_user_settings_main_desc'] ,'
	</p>';

	echo '
	<div class="roundframe">
		', $context['form'] ,'
	</div>	
</div>
<br />';
}