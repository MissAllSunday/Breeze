<?php

declare(strict_types=1);

use Breeze\Breeze;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */
function template_profile(): void
{
	global $context, $txt, $scripturl;

	$aboutMe = $context[Breeze::NAME]['profileSettings']->getAboutMe();
	$enableBuddiesTab = $context[Breeze::NAME]['profileSettings']->getEnableBuddiesTab();

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

	if (!empty($aboutMe)) {
		echo '
		<div id="tab-about" class="content" style="display: none;">
			' . parse_bbc($aboutMe) . '
		</div>';
	}

	if (!empty($enableBuddiesTab)) {
		echo '
		<div id="tab-buddies" class="windowbg" style="display: none;">';

		if (!empty($context[Breeze::NAME]['buddiesData']))
		{
			echo '
				<ul class="reset buddyList">';

			foreach ($context[Breeze::NAME]['buddiesData'] as $buddy) {
				$buddyIcon = $buddy['is_buddy'] ? 'delete' : 'plus';
				$buddyText = $buddy['is_buddy'] ? 'remove' : 'add';
				echo '
				<ul class="flow_auto">
    				<li class="avatar">
    					<a href="', $buddy['href'], '">
			  				<img
								src="', $buddy['avatar']['url'], '"
								alt="', $buddy['username'], '"
								class="avatar" />
						</a>
    				</li>
    				<li>
    					', $buddy['link_color'] ,'
    					<a href="', $scripturl , '?action=buddy;u=', $buddy['id'], ';', $context['session_var'], '=', $context['session_id'], '">
    						<span class="main_icons ', $buddyIcon , '" title="', $txt['buddy_' . $buddyText] ,'" /></a>
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
