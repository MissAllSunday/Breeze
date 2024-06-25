<?php

declare(strict_types=1);

use Breeze\Breeze;
use Breeze\Entity\UserSettingsEntity;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */
function template_profile(): void
{
	global $context;

	echo '
	<hr />
	<p class="clear" />
	<div id="tab-wall" class="content">
	<div id="editor_container">
		 <script>
		', (!empty($context['bbcodes_handlers']) ? $context['bbcodes_handlers'] : '') ,
		'</script>
		<div id="root" class="breeze_main_section" wallType="profile">
		</div>
	</div>';

	if (!empty($context[Breeze::NAME]['profileSettings'][UserSettingsEntity::ABOUT_ME])) {
		echo '
		<div id="tab-about" class="content" style="display: none;">
			' . parse_bbc($context[Breeze::NAME]['profileSettings'][UserSettingsEntity::ABOUT_ME]) . '
		</div>';
	}

	echo template_javascript(true);
}

function template_wall(): void
{
	echo '<div id="editor_container">';
	echo  template_control_richedit(Breeze::NAME, 'smileyBox_message', 'bbcBox_message');
	echo '</div>';

	echo '
	<div id="root" class="breeze_main_section" wallType="general">
	</div>';
}

