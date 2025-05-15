<?php

declare(strict_types=1);

use Breeze\Breeze;
use Breeze\Entity\UserSettingsEntity;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */
function template_profile(): void
{
	global $context, $txt;

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
		</div>
	</div>';

	if (!empty($context[Breeze::NAME]['profileSettings'][UserSettingsEntity::ABOUT_ME])) {
		echo '
		<div id="tab-about" class="content" style="display: none;">
			' . parse_bbc($context[Breeze::NAME]['profileSettings'][UserSettingsEntity::ABOUT_ME]) . '
		</div>';
	}

	if (!empty($context[Breeze::NAME]['profileSettings'][UserSettingsEntity::ENABLE_BUDDIES_TAB])) {
		echo '
		<div id="tab-buddies" class="windowbg" style="display: none;">';

		if (!empty($context[Breeze::NAME]['buddiesData']))
		{
			echo '
				<ul class="reset buddyList">';

			foreach ($context[Breeze::NAME]['buddiesData'] as $buddy) {
				echo '
				<ul class="flow_auto">
    				<li>', $buddy['link_color'] ,'</li>
    				<li class="avatar">
    					<a href="', $buddy['href'], '">
			  				<img
								src="', $buddy['avatar']['url'], '"
								alt="', $buddy['username'], '"
								class="avatar" />
						</a>
    				</li>
  				</ul>';
			}
			echo '
				</ul>';
		}

		// No buddies :(
		else {
			echo $txt[Breeze::NAME . '_user_modules_buddies_none'];
		}

		echo '
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
