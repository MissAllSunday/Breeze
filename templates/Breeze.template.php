<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

function template_admin_home()
{
	global $txt, $context;

	echo '
	<script type="text/javascript">
$(document).ready(function () {
	$(\'#Breeze_rss\').rssfeed(\'',$context['breeze']['rss_url'],'\', {
		limit: 5
	});
});
</script>
	<div class="breeze_rss_box">
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					<div id="Breeze_rss"></div>
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span>
	</div>
	<div class="breeze_admin_info">
	';

	foreach($context['breeze']['versions'] as $version)
		echo $version;
		
	echo'<br />I dont know what else I should put in here...</div>
	<div class="clear"></div>';
}


/* Boring stuff you will never see... */
function template_general_wall()
{
	global $txt;

	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					',$txt['breeze_admin_settings_donate_text'],'
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span><br />';
}

/* Boring stuff you will never see... */
function template_user_wall()
{
	global $txt, $context;

	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					',$context['breeze']['user'].$context['member']['name'].$context['member']['posts'],'
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span><br />';
}

/* Boring stuff you will never see... */
function template_admin_donate()
{
	global $txt;

	echo '
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">
					',$txt['breeze_admin_settings_donate_text'],'
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span><br />';
}