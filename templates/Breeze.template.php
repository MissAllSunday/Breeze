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


/* General wall... */
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

/* User's wall. */
function template_user_wall()
{
	global $txt, $context, $settings, $scripturl;

	echo '
	<div class="breeze_user_left">
		<span class="clear upperframe">
			<span></span>
		</span>
		<div class="roundframe rfix">
			<div class="innerframe">
				<div class="content">';
				/* This section holds two divs, on for the avatar and the other for the user info (post, name, gender, etc) oh, and a clear div below all of that */
				echo'<div class="breeze_user_left_avatar">',$context['member']['avatar']['image'],'</div>
					<div class="breeze_user_left_info">';

			echo '<ul class="breeze_user_left_info">';

					// Show the member's primary group (like 'Administrator') if they have one.
		if (!empty($context['member']['group']))
			echo '
								<li class="membergroup">', $context['member']['group'], '</li>';

					// Don't show these things for guests.
		if (!$context['member']['is_guest'])
		{
			// Show the post group if and only if they have no other group or the option is on, and they are in a post group.
			if ((empty($settings['hide_post_group']) || $context['member']['group'] == '') && $context['member']['post_group'] != '')
				echo '
								<li class="postgroup">', $context['member']['post_group'], '</li>';
			echo '
								<li class="stars">', $context['member']['group_stars'], '</li>';

			// Show how many posts they have made.
			if (!isset($context['disabled_fields']['posts']))
				echo '
								<li class="postcount">', $txt['member_postcount'], ': ', $context['member']['posts'], '</li>';

			// Show the member's gender icon?
			if (!empty($settings['show_gender']) && $context['member']['gender']['image'] != '' && !isset($context['disabled_fields']['gender']))
				echo '
								<li class="gender">', $txt['gender'], ': ', $context['member']['gender']['image'], '</li>';

			// Show their personal text?
			if (!empty($settings['show_blurb']) && $context['member']['blurb'] != '')
				echo '
								<li class="blurb">', $context['member']['blurb'], '</li>';

			// Any custom fields to show as icons?
			if (!empty($context['member']['custom_fields']))
			{
				$shown = false;
				foreach ($context['member']['custom_fields'] as $custom)
				{
					if ($custom['placement'] != 1 || empty($custom['value']))
						continue;
					if (empty($shown))
					{
						$shown = true;
						echo '
								<li class="im_icons">
									<ul>';
					}
					echo '
										<li>', $custom['value'], '</li>';
				}
				if ($shown)
					echo '
									</ul>
								</li>';
			}

			// Show the profile, website, email address, and personal message buttons.
			if ($settings['show_profile_buttons'])
			{
				echo '
								<li class="profile">
									<ul>';

				// Don't show an icon if they haven't specified a website.
				if ($context['member']['website']['url'] != '' && !isset($context['disabled_fields']['website']))
					echo '
										<li><a href="', $context['member']['website']['url'], '" title="' . $context['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $context['member']['website']['title'] . '" />' : $txt['www']), '</a></li>';

				// Don't show the email address if they want it hidden.
				if (in_array($context['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
					echo '
										<li><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $context['member']['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a></li>';

				echo '</ul>
				</li>';
			}

			// Any custom fields for standard placement?
			if (!empty($context['member']['custom_fields']))
			{
				foreach ($context['member']['custom_fields'] as $custom)
					if (empty($custom['placement']) || empty($custom['value']))
						echo '
								<li class="custom">', $custom['title'], ': ', $custom['value'], '</li>';
			}
		}
		// Otherwise, show the guest's email.
		elseif (!empty($context['member']['email']) && in_array($context['member']['show_email'], array('yes', 'yes_permission_override', 'no_through_forum')))
			echo '
								<li class="email"><a href="', $scripturl, '?action=emailuser;sa=email;msg=', $context['id'], '" rel="nofollow">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/email_sm.gif" alt="' . $txt['email'] . '" title="' . $txt['email'] . '" />' : $txt['email']), '</a></li>';

		echo '</ul>';

			/* End of the user info */
			echo'	</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
		<span class="lowerframe">
			<span></span>
		</span>
	</div>';
	
	/* This is the status box,  O RLY? */
	echo '<div class="breeze_user_right">
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
				<div class="breeze_user_inner">
					<div class="breeze_user_statusbox">
						<form method="post" action="', $scripturl, '?action=breezeajax;sa=post" id="status" name="form">
							<textarea cols="30" rows="2" name="content" id="content" maxlength="145" ></textarea><br />
							<input type="submit" value="Update" name="submit" class="comment_button"/>
						</form>
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>';

		/* New ajax status here */
	echo '<div id="breeze_load_image"></div>
 <div id="breeze_display_status"></div>';
				

/* End of the status/comments */
echo '</div>
	<div class="clear"></div>';
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