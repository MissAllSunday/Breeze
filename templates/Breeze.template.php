<?php

/**
 * @package breeze mod
 * @version 1.0
 * @author Suki <missallsunday@simplemachines.org>
 * @copyright 2011 Suki
 * @license http://creativecommons.org/licenses/by-nc-sa/3.0/ CC BY-NC-SA 3.0
 */

	/* This will be moved to its own template... eventually */
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
	/* This will be moved to its own template... eventually */
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
	global $txt, $context, $settings, $scripturl, $user_info, $memberContext;

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
		</span>';

		/* Modules */

		echo '<div class="description" >modules goes here</div>';

		echo '<div class="description" > <a href="javascript:void(0)" id="1" class="breeze_delete">another one</a></div>';


	/* end of left div */
	echo '</div>';


	/* This is the status box,  O RLY? */
	echo '<div class="breeze_user_right">
		<div class="windowbg2">
			<span class="topslice"><span></span></span>
				<div class="breeze_user_inner">
					<div class="breeze_user_statusbox">
						<form method="post" action="', $scripturl, '?action=breezeajax;sa=post" id="status" name="form">
							<textarea cols="40" rows="5" name="content" id="content" ></textarea>
							<input type="hidden" value="',$context['member']['id'],'" name="owner_id" id="owner_id" />
							<input type="hidden" value="',$user_info['id'],'" name="poster_id" id="poster_id" /><br />
							<input type="submit" value="Update" name="submit" class="status_button"/>
						</form>
					</div>
				</div>
			<span class="botslice"><span></span></span>
		</div>';

		/* New ajax status here */
	echo '<div id="breeze_load_image"></div>
 <div id="breeze_display_status"></div>';


	foreach ($context['member']['status'] as $k => $status)
	{

		echo '<div class="windowbg" id ="status_id_',$status['id'],'">
			<span class="topslice"><span></span></span>
				<div class="breeze_user_inner">
					<div class="breeze_user_status_avatar">
						',$status['breeze_user_info'],'
					</div>
					<div class="breeze_user_status_comment">
						',$status['body'],'<br />
						<a href="javascript:void(0)" id="',$status['id'],'" class="breeze_delete_status">Delete</a>
						<p /><hr />
						|like|delete|',$status['time'],'<p />
						<div id="comment_flash_',$status['id'],'"></div>';

						/* Print out the comments */
						foreach($status['comments'] as $comment)
							echo'<div class="description" id ="comment_id_',$comment['id'],'">
									<div class="breeze_user_inner">
										<div class="breeze_user_status_avatar">
											',$comment['comment_user_info'],'<br />
											',$comment['time'],'
										</div>
										<div class="breeze_user_status_comment">
											',$comment['body'],'<br />
											<a href="javascript:void(0)" id="',$comment['id'],'" class="breeze_delete_comment">Delete</a>
										</div>
										<div class="clear"></div>
									</div>
								</div>';

						echo'<div id="comment_loadplace_',$status['id'],'"></div>

							<form action="', $scripturl, '?action=breezeajax;sa=postcomment" method="post" name="formID_',$status['id'],'" id="formID_',$status['id'],'">
								<textarea id="textboxcontent_',$status['id'],'" cols="40" rows="2"></textarea>
								<input type="hidden" value="',$status['poster_id'],'" name="status_owner_id',$status['id'],'" id="status_owner_id',$status['id'],'" />
								<input type="hidden" value="',$context['member']['id'],'" name="profile_owner_id',$status['id'],'" id="profile_owner_id',$status['id'],'" />
								<input type="hidden" value="',$status['id'],'" name="status_id',$status['id'],'" id="status_id',$status['id'],'" />
								<input type="hidden" value="',$user_info['id'],'" name="poster_comment_id',$status['id'],'" id="poster_comment_id',$status['id'],'" /><br />
								<input type="submit" value="Comment" class="comment_submit" id="',$status['id'],'" />
							</form>
					</div>
					<div class="clear"></div>
				</div>
			<span class="botslice"><span></span></span>
			</div>';
	}


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