<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_main(): void
{
	global $txt, $context;


	echo '
<div id="breeze_main_section">
	<div id="breeze_blocks" class="floatleft">
		<div class="cat_bar">
			<h3 class="catbg">
				some block
			</h3>
		</div>
		<div class="windowbg">
			some content
		</div>
	</div>
	<div id="breeze_wall" class="floatright">
	<div id="breeze_avatar" class="floatleft"></div>
	<div class="windowbg" class="floatright">

	</div>
</div>
<br />';
}