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

	// Tabs
	echo '
		<div id="Breeze_tabs" class="generic_menu">
			<ul class="dropmenu breezeTabs">
				<li class="subsections" id="wall">
					<a href="#tab-wall" class="active">', $txt['Breeze_tabs_wall'] ,'</a>
				</li>';

	// The "About me" tab.
	if (!empty($context[Breeze::NAME]['profileSettings'][UserSettingsEntity::ABOUT_ME])) {
		echo '
				<li class="subsections" id="about">
					<a href="#tab-about">', $txt['Breeze_tabs_about'], '</a>
				</li>';
	}

	// Does recent activity is enable?
//	if (!empty($context['Breeze']['settings']['owner']['activityLog']))
		echo '
				<li class="subsections" id="activity">
					<a href="#tab-activity">', $txt['Breeze_tabs_activity'] ,'</a>
				</li>';

	echo '
			</ul>
		</div>
		<p class="clear" />';

	echo '<div id="tab-wall" class="content">';

		echo '<div id="editor_container">';
		echo  template_control_richedit(Breeze::NAME, 'smileyBox_message', 'bbcBox_message');

	echo sprintf('
		<span id="post_confirm_buttons">
			<input type="submit" value="%s" name="post" tabindex="2" id="smfEditor" accesskey="s" class="button">
		</span>', $txt['Breeze_general_send']);

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

function template_showEditor(): void
{
	echo '';
}
