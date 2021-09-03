<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_main(): void
{
	echo '
	<div id="breeze_app" class="breeze_main_section">
		<breeze-wall></breeze-wall>
	</div>';
}
