<?php

declare(strict_types=1);

use Breeze\Breeze;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_main(): void
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

	if (!empty($context[Breeze::NAME]['msg'])) {
		echo '
	<div class="', $context[Breeze::NAME]['msg']['type'] ,'box">
		', $context[Breeze::NAME]['msg']['message'] ,'
	</div>';
	}

	echo '
	<div class="roundframe">
		', $context[Breeze::NAME]['form'] ,'
	</div>
</div>
<br />';
}

function template_breezeSettings_error(): void
{
	global $context;

	echo '
	<div class="errorbox">
		', $context['errorMessage'] ,'
	</div>';
}
