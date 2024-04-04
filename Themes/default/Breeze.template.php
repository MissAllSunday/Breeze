<?php

declare(strict_types=1);

use Breeze\Breeze;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */
function template_profile(): void
{
	global $txt;

	echo '<div id="editor_container">';
	echo  template_control_richedit(Breeze::NAME, 'smileyBox_message', 'bbcBox_message');

	echo sprintf('
		<span id="post_confirm_buttons">
			<input type="submit" value="%s" name="post" tabindex="2" id="smfEditor" accesskey="s" class="button">
		</span>', $txt['Breeze_general_save']);

	echo '</div>';
	echo '
	<div id="root" class="breeze_main_section" wallType="profile">
	</div>';
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

function template_showEditor(): void
{
	echo '';
}
