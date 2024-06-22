<?php

declare(strict_types=1);

use Breeze\Breeze;
use Breeze\Entity\UserSettingsEntity;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */
function template_profile(): void
{
	global $txt, $context;

	echo '<hr />';

	echo '
		<p class="clear" />';

	echo '<div id="tab-wall" class="content">';

		echo '<div id="editor_container">';
		controlEditorVars(Breeze::NAME);
	echo '</div>';

	echo '
	<div id="root" class="breeze_main_section" wallType="profile">
	</div>';

	echo '</div>';

	echo '
		<div id="tab-activity" class="content" style="display: none;">
			activity tab
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

function controlEditorVars(string $editorId = ''): void
{
	global $context, $settings, $modSettings, $smcFunc;

	$editor_context = &$context['controls']['richedit'][$editorId];

	echo '
		', !empty($context['bbcodes_handlers']) ? $context['bbcodes_handlers'] : '', '
	';

	echo 'let scOptions = ' . $smcFunc['json_encode']($editor_context['sce_options'], \JSON_PRETTY_PRINT);
}
