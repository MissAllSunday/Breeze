<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */
function template_profile(): void
{
	echo '
	<div id="root" class="breeze_main_section" wallType="profile">
	</div>';
}

function template_wall(): void
{
	echo '
	<div id="root" class="breeze_main_section" wallType="general">
	</div>';
}
