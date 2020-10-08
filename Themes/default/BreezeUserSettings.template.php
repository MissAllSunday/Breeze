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
	', $context['form'] ,'	
</div>
<br />';
}