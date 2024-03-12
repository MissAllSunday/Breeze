<?php

declare(strict_types=1);

use Breeze\Breeze;

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

function template_editor(): void
{
	echo  template_control_richedit(Breeze::NAME, 'smileyBox_message', 'bbcBox_message');
}
