<?php

declare(strict_types=1);

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

	if (!empty($context['msg']))
	{
		echo '
	<div class="', $context['msg']['type'] ,'box">
		', $context['msg']['message'] ,'
	</div>';
	}

	echo '
	<div class="roundframe">
		', $context['form'] ,'
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
