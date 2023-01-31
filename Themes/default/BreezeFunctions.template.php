<?php

declare(strict_types=1);

use Breeze\Breeze;

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */


function breeze_status($data, $returnVar = false)
{
	global $context, $txt, $user_info, $scripturl;

	$echo = '';

	// Status and comments
	foreach ($data as $status)
	{
		$echo .= '
			<li class="windowbg stripes breezeStatus" id ="status_id_' . $status['id'] . '">';

		// If we're on the general wall, show a nice bar indicating where this status come from...
		if (!empty($context['Breeze']['comingFrom']) && 'wall' == $context['Breeze']['comingFrom'])
			$echo .= '
				<div class="cat_bar">
					<h3 class="catbg">
						<span class="author">
							' . sprintf($txt['Breeze_general_posted_on'], $context['Breeze']['user_info'][$status['profile_id']]['link']) . '
						</span>
					</h3>
				</div>';

		$echo .= '
				<div class="user_avatar">
					' . $context['Breeze']['user_info'][$status['poster_id']]['breezeFacebox'] . '<br />
					' . $context['Breeze']['user_info'][$status['poster_id']]['link'] . '
				</div>
				<div class="status">
					<div class="post">
						' . $status['body'] . '
					</div>
					<div class="options">';

		// Likes.
		if ($context['Breeze']['tools']->modSettings('enable_likes') && !empty($status['likes']) && ($status['likes']['can_view_like'] || $status['likes']['can_like']))
		{
			$echo .=
						'<ul class="floatleft">';

			if (!empty($status['likes']['can_like']))
				$echo .= '
							<li class="like_button"><a href="' . $scripturl . '?action=likes;ltype=breSta;sa=like;like=' . $status['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . (!empty($context['Breeze']['comingFrom']) ? ';extra=' . $context['Breeze']['comingFrom'] : '') . '" class="breSta_like"><span class="generic_icons ' . ($status['likes']['already'] ? 'unlike' : 'like') . '"></span>' . ($status['likes']['already'] ? $txt['unlike'] : $txt['like']) . '</a></li>';

			// Likes count
			if (!empty($status['likes']['count']) && !empty($status['likes']['can_view_like']))
			{
				$context['some_likes'] = true;
				$count = $status['likes']['count'];
				$base = 'likes_';
				if ($status['likes']['already'])
				{
					$base = 'you_' . $base;
					$count--;
				}
				$base .= (isset($txt[$base . $count])) ? $count : 'n';

				$echo .= '
							<li class="like_count smalltext">' . sprintf($txt[$base], $scripturl . '?action=likes;sa=view;ltype=breSta;like=' . $status['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . (!empty($context['Breeze']['comingFrom']) ? ';extra=' . $context['Breeze']['comingFrom'] : ''), comma_format($count)) . '</li>';
			}

			$echo .=
						'</ul>';
		}

		// Time.
		$echo .= '
						<div class="floatright">
							<span class="time_elapsed" title="' . timeformat($status['time_raw'], false) . '" data-livestamp="' . $status['time_raw'] . '">' . $status['time'] . ' </span>';

		// Delete status.
		if ($status['canHas']['delete'])
			$echo .=
								' | <a href="' . $scripturl . '?action=breezeajax;sa=delete;bid=' . $status['id'] . ';type=status;profileOwner=' . $status['profile_id'] . ';poster=' . $status['poster_id'] . '' . (!empty($context['Breeze']['comingFrom']) ? ';rf=' . $context['Breeze']['comingFrom'] : '') . ';' . $context['session_var'] . '=' . $context['session_id'] . '" id="deleteStatus_' . $status['id'] . '" class="breeze_delete" data-bid="' . $status['id'] . '">' . $txt['Breeze_general_delete'] . '</a>';

		// Modify? maybe someday...

		$echo .= '
						</div>
						<div class="clear"></div>
					</div>
					<hr />';

		$echo .= '
					<ul class="breeze_comments_list" id="comment_loadplace_' . $status['id'] . '">';

		// Print out the comments
		if (!empty($status['comments']))
				$echo .= breeze_comment($status['comments'], true);

		$echo .= '
					</ul>';

		$echo .= '
					<div id="breeze_display_comment_' . $status['id'] . '"></div>';

		// Post a new comment
		if ($status['canHas']['postComments'])
		{
			$echo .= '
						<div class="post_comment">';

			// Show a nice avatar next to the post form
			if (!empty($context['Breeze']['user_info'][$user_info['id']]['breezeFacebox']))
				$echo .=  '<div class="user_avatar">' . $context['Breeze']['user_info'][$user_info['id']]['breezeFacebox'] . '</div>';

			// The actual post form
				$echo .= '
							<div class="comment">
								<form action="' . $scripturl . '?action=breezeajax;sa=postcomment' . (!empty($context['Breeze']['comingFrom']) ? ';rf=' . $context['Breeze']['comingFrom'] : '') . '" method="post" name="form_comment_' . $status['id'] . '" id="form_comment_' . $status['id'] . '" class="form_comment">
									<textarea name="message" id="commentContent_' . $status['id'] . '" rel="atwhoMention"></textarea>
									<input type="hidden" value="' . $status['poster_id'] . '" name="statusPoster" id="commentStatusPoster_' . $status['id'] . '" />
									<input type="hidden" value="' . $status['id'] . '" name="statusID" id="commentStatus_' . $status['id'] . '" />
									<input type="hidden" value="' . $status['profile_id'] . '" name="owner" id="commentOwner_' . $status['profile_id'] . '" />
									<input type="hidden" id="' . $context['session_var'] . '" name="' . $context['session_var'] . '" value="' . $context['session_id'] . '" /><br>
									<input type="submit" value="' . $txt['post'] . '" class="button_submit clear" name="commentSubmit" id="commentSubmit_' . $status['id'] . '" />
								</form>
							</div>';

			// End of div post_comment
				$echo .= '
						</div>';
		}

			$echo .= '
				</div>
				<div class="clear"></div>
			</li>';
	}

	// What are we gonna do?
	if ($returnVar)
		return $echo;


		echo $echo;
}

function breeze_comment($comments, $returnVar = false)
{
	global $context, $txt, $scripturl, $user_info;

	$echo = '';

	foreach ($comments as $comment)
	{
		$echo .= '
		<li class="windowbg2 stripes breezeComment" id ="comments_id_' . $comment['id'] . '">
			<div class="user_avatar">
					' . $context['Breeze']['user_info'][$comment['poster_id']]['breezeFacebox'] . '<br />
					' . $context['Breeze']['user_info'][$comment['poster_id']]['link'] . '
			</div>
			<div class="comment">
				<div class="post">
				' . $comment['body'] . '
				</div>
				<div class="options clear">';

		// Likes.
		if ($context['Breeze']['tools']->modSettings('enable_likes') && !empty($comment['likes']) && ($comment['likes']['can_view_like'] || $comment['likes']['can_like']))
		{
			$echo .= '
					<ul class="floatleft">';

			if (!empty($comment['likes']['can_like']))
				$echo .= '
						<li class="like_button">
							<a href="' . $scripturl . '?action=likes;ltype=breCom;sa=like;like=' . $comment['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . (!empty($context['Breeze']['comingFrom']) ? ';extra=' . $context['Breeze']['comingFrom'] : '') . '" class="breCom_like">
								<span class="generic_icons ' . ($comment['likes']['already'] ? 'unlike' : 'like') . '"></span>' . ($comment['likes']['already'] ? $txt['unlike'] : $txt['like']) . '
							</a>
						</li>';

			// Likes count.
			if (!empty($comment['likes']['count']) && !empty($comment['likes']['can_view_like']))
			{
				$context['some_likes'] = true;
				$count = $comment['likes']['count'];
				$base = 'likes_';
				if ($comment['likes']['already'])
				{
					$base = 'you_' . $base;
					$count--;
				}
				$base .= (isset($txt[$base . $count])) ? $count : 'n';

				$echo .= '
						<li class="like_count smalltext">' . sprintf($txt[$base], $scripturl . '?action=likes;sa=view;ltype=breCom;like=' . $comment['id'] . ';' . $context['session_var'] . '=' . $context['session_id'] . (!empty($context['Breeze']['comingFrom']) ? ';extra=' . $context['Breeze']['comingFrom'] : ''), comma_format($count)) . '</li>';
			}

			$echo .=
					'</ul>';
		}

		// Time.
		$echo .= '
					<div class="floatright">
						<span class="time_elapsed" title="' . timeformat($comment['time_raw'], false) . '">' . $comment['time'] . '</span>';

		// Delete comment.
		if ($comment['canHas']['delete'])
			$echo .= '| <a href="' . $scripturl . '?action=breezeajax;sa=delete;bid=' . $comment['id'] . ';type=comments;poster=' . $comment['poster_id'] . ';profileOwner=' . $comment['profile_id'] . '' . (!empty($context['Breeze']['comingFrom']) ? ';rf=' . $context['Breeze']['comingFrom'] : '') . ';' . $context['session_var'] . '=' . $context['session_id'] . '" id="deleteComment_' . $comment['id'] . '" class="breeze_delete" data-bid="' . $comment['id'] . '">' . $txt['Breeze_general_delete'] . '</a>';

		$echo .= '
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="clear"></div>
		</li>';
	}

	// What are we going to do?
	if ($returnVar)
		return $echo;


		echo $echo;
}

function breeze_activity($data, $returnVar = false)
{
	global $context, $txt;

	if (empty($data))
		return false;

	$counter = 1;
	$echo = '';

	$echo .= '
			<ul class="reset breezeActivity">';

	foreach ($data as $act)
		$echo .= '
				<li class="windowbg">
						<div class="activityIcon floatleft">
							<span class="fa fa-' . (!empty($act['icon']) ? $act['icon'] : 'envelope') . ' fa-3x"></span>
						</div>
						<div class="activityContent">
								<span class="time_elapsed" title="' . $act['time'] . '" data-livestamp="' . $act['time_raw'] . '">' . $act['time'] . '</span><br />
								' . $act['text'] . '<br />
						</div>
						<div class="clear"></div>
				</li>';

	// Close the ul
	$echo .= '
			</ul>';

	// What are we going to do?
	if ($returnVar)
		return $echo;


		echo $echo;
}

function breeze_server_response(): void
{
	global $txt;

	// Just to be sure...
	loadLanguage(Breeze::NAME);

	// Get the message from the server, FUGLY.
	$serverResponse = !empty($_SESSION['Breeze']['response']) ? $_SESSION['Breeze']['response'] : ['type' => false, 'message' => false];

	$type = $serverResponse['type'];
	$message = $serverResponse['message'];

	// Show a nice confirmation message for those without JavaScript.
	if (!empty($type) && !empty($message))
		echo
		'<div class="', $type ,'box">
			', $message ,'
		</div>';

	// Ugly, I know...
	unset($_SESSION['Breeze']['response']);
}

function template_userDiv(): void
{
	global $context, $settings, $modSettings, $txt, $scripturl, $modSettings;

	// Since this is a popup of its own we need to start the html, etc.
	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta charset="', $context['character_set'], '">
		<meta name="robots" content="noindex">
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css', $modSettings['browser_cache'] ,'">
		<script src="', $settings['default_theme_url'], '/scripts/script.js', $modSettings['browser_cache'] , '"></script>
	</head>
	<body; id="help_popup">
		<div; class="description breezeInfo">
			<div; id="basicinfo">';

	// Are there any custom profile fields for above the name?
	if (!empty($context['print_custom_fields']['above_member']))
	{
		echo '
			<div class="custom_fields_above_name">
				<ul >';

		foreach ($context['print_custom_fields']['above_member'] as $field)
			if (!empty($field['output_html']))
				echo '
					<li>', $field['output_html'], '</li>';

		echo '
				</ul>
			</div>
			<br>';
	}

	echo '
				<div class="username">
					<h4>', $context['BreezeUser']['link_color'], '<span class="position">', (!empty($context['BreezeUser']['group']) ? $context['BreezeUser']['group'] : $context['BreezeUser']['post_group']), '</span></h4>
				</div>
				', $context['BreezeUser']['group_icons'] ,'<br>
				', $context['BreezeUser']['avatar']['image'];

	// Are there any custom profile fields for below the avatar?
	if (!empty($context['print_custom_fields']['below_avatar']))
	{
		echo '
			<div class="custom_fields_below_avatar">
				<ul >';

		foreach ($context['print_custom_fields']['below_avatar'] as $field)
			if (!empty($field['output_html']))
				echo '
					<li>', $field['output_html'], '</li>';

		echo '
				</ul>
			</div>
			<br>';
	}

	echo '
				<ul class="reset">';
		// Email is only visible if it's your profile or you have the moderate_forum permission
		if ($context['BreezeUser']['show_email'])
			echo '
					<li><a href="mailto:', $context['BreezeUser']['email'], '" title="', $context['BreezeUser']['email'], '" rel="nofollow"><span class="generic_icons mail" title="' . $txt['email'] . '"></span></a></li>';

		// Don't show an icon if they haven't specified a website.
		if ('' !== $context['BreezeUser']['website']['url'] && !isset($context['disabled_fields']['website']))
			echo '
					<li><a href="', $context['BreezeUser']['website']['url'], '" title="' . $context['BreezeUser']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<span class="generic_icons www" title="' . $context['BreezeUser']['website']['title'] . '"></span>' : $txt['www']), '</a></li>';

		// Are there any custom profile fields as icons?
		if (!empty($context['print_custom_fields']['icons']))
		{
			foreach ($context['print_custom_fields']['icons'] as $field)
				if (!empty($field['output_html']))
					echo '
						<li class="custom_field">', $field['output_html'], '</li>';
		}
		echo '
				</ul>
				<span id="userstatus">', $settings['use_image_buttons'] ? '<span class="' . (1 == $context['BreezeUser']['online']['is_online'] ? 'on' : 'off') . '" title="' . $context['BreezeUser']['online']['text'] . '"></span>' : $context['BreezeUser']['online']['label'], $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['BreezeUser']['online']['label'] . '</span>' : '';
		echo '
				</span>
			</div>
			<div id="detailedinfo">
				<dl>';
	if ($context['user']['is_owner'] || $context['user']['is_admin'])
		echo '
					<dt>', $txt['username'], ': </dt>
					<dd>', $context['BreezeUser']['username'], '</dd>';
	if (!isset($context['disabled_fields']['posts']))
		echo '
					<dt>', $txt['profile_posts'], ': </dt>
					<dd>', $context['BreezeUser']['posts'], '</dd>';
	if (!empty($modSettings['titlesEnable']) && !empty($context['BreezeUser']['title']))
		echo '
					<dt>', $txt['custom_title'], ': </dt>
					<dd>', $context['BreezeUser']['title'], '</dd>';
	if (!empty($context['BreezeUser']['blurb']))
		echo '
					<dt>', $txt['personal_text'], ': </dt>
					<dd>', $context['BreezeUser']['blurb'], '</dd>';
	echo '
				</dl>';
	// Any custom fields for standard placement?
	if (!empty($context['print_custom_fields']['standard']))
	{
		echo '
				<dl>';

		foreach ($context['print_custom_fields']['standard'] as $field)
			if (!empty($field['output_html']))
				echo '
					<dt>', $field['name'], ':</dt>
					<dd>', $field['output_html'], '</dd>';

		echo '
				</dl>';
	}
	echo '
				<dl class="noborder">';
	echo '
					<dt>', $txt['date_registered'], ': </dt>
					<dd>', $context['BreezeUser']['registered'], '</dd>';
	echo '
					<dt>', $txt['local_time'], ':</dt>
					<dd>', $context['BreezeUser']['local_time'], '</dd>';
	if ($context['BreezeUser']['online']['is_online'])
		echo '
					<dt>', $txt['lastLoggedIn'], ': </dt>
					<dd>', $context['BreezeUser']['last_login'], (!empty($context['BreezeUser']['is_hidden']) ? ' (' . $txt['hidden'] . ')' : ''), '</dd>';
	echo '
				</dl>';

	// Are there any custom profile fields for above the signature?
	if (!empty($context['print_custom_fields']['above_signature']))
	{
		echo '
				<div class="custom_fields_above_signature">
					<ul class="reset nolist">';

		foreach ($context['print_custom_fields']['above_signature'] as $field)
			if (!empty($field['output_html']))
				echo '
						<li>', $field['output_html'], '</li>';

		echo '
					</ul>
				</div>';
	}

	// Show the users signature.
	if (!empty($context['BreezeUser']['signature']))
		echo '
				<div class="signature">
					<h5>', $txt['signature'], ':</h5>
					', $context['BreezeUser']['signature'] ,'
				</div>';

	// Are there any custom profile fields for below the signature?
	if (!empty($context['print_custom_fields']['below_signature']))
	{
		echo '
				<div class="custom_fields_below_signature">
					<ul class="reset nolist">';

		foreach ($context['print_custom_fields']['below_signature'] as $field)
			if (!empty($field['output_html']))
				echo '
						<li>', $field['output_html'], '</li>';

		echo '
					</ul>
				</div>';
	}

	echo '
			</div>
		</div>
		<div class="clear"></div>
			<br>
			<a href="javascript:self.close();">', $txt['close_window'], '</a>
		</div>
	</body>
</html>';
}

function template_top(): void
{
	global $context, $scripturl, $txt;

	// Wrapper div now echoes permanently for better layout options. h1 a is now target for "Go up" links.
	echo '
	<div id="top_section">
		<div class="frame">';

	// If the user is logged in, display some things that might be useful.
	if ($context['user']['is_logged'])
	{
		// Firstly, the user's menu
		echo '
			<ul class="floatleft" id="top_info">
				<li>
					<a href="', $scripturl, '?action=profile"', !empty($context['self_profile']) ? ' class="active"' : '', ' id="profile_menu_top" onclick="return false;">';
						if (!empty($context['user']['avatar']))
							echo $context['user']['avatar']['image'];
						echo $context['user']['name'], '</a>
					<div id="profile_menu" class="top_menu"></div>
				</li>';

		// Secondly, PMs if we're doing them
		if ($context['allow_pm'])
		{
			echo '
				<li>
					<a href="', $scripturl, '?action=pm"', !empty($context['self_pm']) ? ' class="active"' : '', ' id="pm_menu_top">', $txt['pm_short'], !empty($context['user']['unread_messages']) ? ' <span class="amt">' . $context['user']['unread_messages'] . '</span>' : '', '</a>
					<div id="pm_menu" class="top_menu scrollable"></div>
				</li>';
		}

		// Thirdly, alerts
		echo '
				<li>
					<a href="', $scripturl, '?action=alerts"', !empty($context['self_alerts']) ? ' class="active"' : '', ' id="alerts_menu_top">', $txt['alerts'], !empty($context['user']['alerts']) ? ' <span class="amt">' . $context['user']['alerts'] . '</span>' : '', '</a>
					<div id="alerts_menu" class="top_menu scrollable"></div>
				</li>';

		// And now we're done.
		echo '
			</ul>';
	}
	// Otherwise they're a guest. Ask them to either register or login.
	else
		echo '
			<ul class="floatleft welcome">
				<li>', sprintf($txt[$context['can_register'] ? 'welcome_guest_register' : 'welcome_guest'], $txt['guest_title'], $context['forum_name_html_safe'], $scripturl . '?action=login', 'return reqOverlayDiv(this.href, ' . JavaScriptEscape($txt['login']) . ');', $scripturl . '?action=signup'), '</li>
			</ul>';

	if (!empty($context['languages']))
	{
		echo '
			<form id="languages_form" action="" method="get" class="floatright">
				<select id="language_select" name="language" onchange="this.form.submit()">';

		foreach ($context['languages'] as $language)
			echo '
					<option value="', $language['filename'], '"', isset($context['user']['language']) && $context['user']['language'] == $language['filename'] ? ' selected="selected"' : '', '>', str_replace('-utf8', '', $language['name']), '</option>';

		echo '
				</select>
				<noscript>
					<input type="submit" value="', $txt['quick_mod_go'], '" />
				</noscript>
			</form>';
	}

	if ($context['allow_search'])
	{
		echo '
			<form id="search_form" class="floatright" action="', $scripturl, '?action=search2" method="post" accept-charset="', $context['character_set'], '">
				<input type="search" name="search" value="" class="input_text">&nbsp;';

		// Using the quick search dropdown?
		$selected = !empty($context['current_topic']) ? 'current_topic' : (!empty($context['current_board']) ? 'current_board' : 'all');

		echo '
			<select name="search_selection">
				<option value="all"', ('all' == $selected ? ' selected' : ''), '>', $txt['search_entireforum'], ' </option>';

		// Can't limit it to a specific topic if we are not in one
		if (!empty($context['current_topic']))
			echo '
				<option value="topic"', ('current_topic' == $selected ? ' selected' : ''), '>', $txt['search_thistopic'], '</option>';

		// Can't limit it to a specific board if we are not in one
		if (!empty($context['current_board']))
			echo '
					<option value="board"', ('current_board' == $selected ? ' selected' : ''), '>', $txt['search_thisbrd'], '</option>';
			echo '
					<option value="members"', ('members' == $selected ? ' selected' : ''), '>', $txt['search_members'], ' </option>
				</select>';

		// Search within current topic?
		if (!empty($context['current_topic']))
			echo '
				<input type="hidden" name="sd_topic" value="', $context['current_topic'], '">';
		// If we're on a certain board, limit it to this board ;).
		elseif (!empty($context['current_board']))
			echo '
				<input type="hidden" name="sd_brd[', $context['current_board'], ']" value="', $context['current_board'], '">';

		echo '
				<input type="submit" name="search2" value="', $txt['search'], '" class="button_submit">
				<input type="hidden" name="advanced" value="0">
			</form>';
	}

	echo '
		</div>
	</div>';
}

function template_buddy_message(): void
{
	global $context;

		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['page_title'] ,'
			</h3>
		</div>';

		echo '
		<div class="information">
			', $context['response'] ,'
		</div>';

	if (!empty($context['buddyMessage']))
	{
		echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['buddy_message_title'] ,'
			</h3>
		</div>';

		echo '
		<div class="information">
			', $context['buddyMessage'] ,'
		</div>';
	}
}

function template_buddy_request(): void
{
	global $context;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				', $context['page_title'] ,'
			</h3>
		</div>';

	echo '
		<div class="information">';

	echo $context['response'];

		echo '
		</div>';
}
