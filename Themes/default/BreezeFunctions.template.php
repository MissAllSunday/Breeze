<?php

/**
 * BreezeFunctions.template.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014, Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

function breeze_status($data, $returnVar = false)
{
	global $context, $txt, $user_info, $scripturl;

	$echo = '';

	// Status and comments
	foreach ($data as $status)
	{
		$echo .= '
			<li class="windowbg stripes status_breeze" id ="status_id_'. $status['id'] .'">';

		// If we're on the general wall, show a nice bar indicating where this status come from...
		if (!empty($context['Breeze']['comingFrom']) && $context['Breeze']['comingFrom'] == 'wall')
			$echo .= '
				<div class="cat_bar">
					<h3 class="catbg">
						<span id="author">
							'. sprintf($txt['Breeze_general_posted_on'], $context['Breeze']['user_info'][$status['owner_id']]['link']) .'
					</h3>
				</div>';

		$echo .= '
				<span class="topslice"><span></span></span>
					<div class="breeze_user_inner">
						<div class="user_avatar">
							'. $context['Breeze']['user_info'][$status['poster_id']]['breezeFacebox'] .'<br />
							'. $context['Breeze']['user_info'][$status['poster_id']]['link'] .'
						</div>
						<div class="status">
							<div class="body">
								'. $status['body'] .'
							</div>
							<div class="options">';

		// Likes.
		if ($context['Breeze']['tools']->enable('likes') && $status['likes']['can_like'])
		{
			$echo .=
								'<ul class="floatleft">
									<li class="like_button"><a href="'. $scripturl .'?action=likes;ltype=breSta;sa=like;like='. $status['id'] .';'. $context['session_var'] .'='. $context['session_id'] . (!empty($context['Breeze']['comingFrom']) ? ';extra='. $context['Breeze']['comingFrom'] : '') .'" class="breSta_like"><span class="'. ($status['likes']['already'] ? 'unlike' : 'like') .'"></span>'. ($status['likes']['already'] ? $txt['unlike'] : $txt['like']) .'</a></li>';

			// Likes count
			if (!empty($status['likes']['count']))
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
									<li class="like_count smalltext">'. sprintf($txt[$base], $scripturl . '?action=likes;sa=view;ltype=breSta;like=' . $status['id'] .';'. $context['session_var'] .'='. $context['session_id']. (!empty($context['Breeze']['comingFrom']) ? ';extra='. $context['Breeze']['comingFrom'] : '') , comma_format($count)).'</li>';
			}

			$echo .=
								'</ul>';
		}

		// Time.
		$echo .= '
								<div class="floatright">
									<span class="time_elapsed" title="'. timeformat($status['time_raw'], false) .'" data-livestamp="'. $status['time_raw'] .'">'. $status['time'] .' </span>';

		// Delete status.
		if ($status['canHas']['delete'])
			$echo .=
								' | <a href="'. $scripturl .'?action=breezeajax;sa=delete;bid='. $status['id'] .';type=status;profileOwner='. $status['owner_id'] .';poster='. $status['poster_id'] .''. (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') .';'. $context['session_var'] .'='. $context['session_id'] .'" id="deleteStatus_'. $status['id'] .'" class="breeze_delete" data-bid="'. $status['id'] .'">'. $txt['Breeze_general_delete'] .'</a>';

		// Modify? maybe someday...

		$echo .= '
								</div>
								<div class="clear"></div>
							</div>
							<hr />
							<div id="breeze_display_comment_'. $status['id'] .'"></div>';

		$echo .= '
								<ul class="breeze_comments_list" id="comment_loadplace_'. $status['id'] .'">';

		// Print out the comments
		if (!empty($status['comments']))
				$echo .= breeze_comment($status['comments'], true);

		$echo .= '
								</ul>';

		$echo .= '
								<div id="breeze_load_image_comment_'. $status['id'] .'" style="margin:auto; text-align:center;"></div>';

		// Post a new comment
		if ($status['canHas']['postComments'])
		{
			$echo .= '
								<div class="post_comment">';

			// Show a nice avatar next to the post form
			if (!empty($context['Breeze']['user_info'][$user_info['id']]['breezeFacebox']))
				$echo .=  '<div class="user_avatar">'. $context['Breeze']['user_info'][$user_info['id']]['breezeFacebox'] .'</div>';

			// The actual post form
				$echo .= '
									<form action="'. $scripturl .'?action=breezeajax;sa=postcomment'. (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') .'" method="post" name="form_comment_'. $status['id'] .'" id="form_comment_'. $status['id'] .'" class="form_comment">
										<textarea name="content" id="commentContent_'. $status['id'] .'" cols="40" rows="2" rel="atwhoMention"></textarea>
										<input type="hidden" value="'. $status['poster_id'] .'" name="statusPoster" id="commentStatusPoster_'. $status['id'] .'" />
										<input type="hidden" value="'. $user_info['id'] .'" name="poster" id="commentPoster_'. $status['id'] .'" />
										<input type="hidden" value="'. $status['id'] .'" name="statusID" id="commentStatus_'. $status['id'] .'" />
										<input type="hidden" value="'. $status['owner_id'] .'" name="owner" id="commentOwner_'. $status['id'] .'" /><br />
										<input type="hidden" id="'. $context['session_var'] .'" name="'. $context['session_var'] .'" value="'. $context['session_id'] .'" />
										<input type="submit" value="'. $txt['post'] .'" class="comment_submit" name="commentSubmit" id="commentSubmit_'. $status['id'] .'" />
									</form>';

			// End of div post_comment
				$echo .= '
								</div>';
		}

			$echo .= '
						</div>
						<div class="clear"></div>
					</div>
				<span class="botslice"><span></span></span>
			</li>';
	}

	// What are we gonna do?
	if ($returnVar)
		return $echo;

	else
		echo $echo;
}

function breeze_comment($comments, $returnVar = false)
{
	global $context, $txt, $scripturl, $user_info;

	$echo = '';

	foreach ($comments as $comment)
	{
		$echo .= '
		<li class="windowbg2 stripes" id ="comments_id_'. $comment['id'] .'">
			<div class="user_avatar">
					'. $context['Breeze']['user_info'][$comment['poster_id']]['breezeFacebox'] .'<br />
					'. $context['Breeze']['user_info'][$comment['poster_id']]['link'] .'
			</div>
			<div class="comment">
				<div class="body">
				'. $comment['body'] .'
				</div>
				<div class="options">';

		// Likes.
		if ($context['Breeze']['tools']->setting('likes') && $comment['likes']['can_like'])
		{
			$echo .=
					'<ul class="floatleft">
						<li class="like_button">
							<a href="'. $scripturl .'?action=likes;ltype=breCom;sa=like;like='. $comment['id'] .';'. $context['session_var'] .'='. $context['session_id'] . (!empty($context['Breeze']['comingFrom']) ? ';extra='. $context['Breeze']['comingFrom'] : '') .'" class="breCom_like">
								<span class="'. ($comment['likes']['already'] ? 'unlike' : 'like') .'"></span>'. ($comment['likes']['already'] ? $txt['unlike'] : $txt['like']) .'
							</a>
						</li>';

			// Likes count.
			if (!empty($comment['likes']['count']))
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
						<li class="like_count smalltext">'. sprintf($txt[$base], $scripturl . '?action=likes;sa=view;ltype=breCom;like=' . $comment['id'] .';'. $context['session_var'] .'='. $context['session_id']. (!empty($context['Breeze']['comingFrom']) ? ';extra='. $context['Breeze']['comingFrom'] : '') , comma_format($count)).'</li>';
			}

			$echo .=
					'</ul>';
		}

		// Time.
		$echo .= '
					<div class="floatright">
						<span class="time_elapsed" title="'. timeformat($comment['time_raw'], false) .'">'. $comment['time'] .'</span>';

		// Delete comment.
		if ($comment['canHas']['delete'])
			$echo .= '| <a href="'. $scripturl .'?action=breezeajax;sa=delete;bid='. $comment['id'] .';type=comments;poster='. $comment['poster_id'] .';profileOwner='. $comment['profile_id'] .''. (!empty($context['Breeze']['comingFrom']) ? ';rf='. $context['Breeze']['comingFrom'] : '') .';'. $context['session_var'] .'='. $context['session_id'] .'" id="deleteComment_'. $comment['id'] .'" class="breeze_delete" data-bid="'. $comment['id'] .'">'. $txt['Breeze_general_delete'] .'</a>';

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

	else
		echo $echo;
}

function breeze_profile_owner()
{
	global $context, $txt, $settings, $scripturl;

	// User info, details
	echo '
	<div class="cat_bar">
		<h3 class="catbg">
			<span id="author">
				', $txt['Breeze_tabs_pinfo'], '
		</h3>
	</div>
	<div class="windowbg BreezeBlock">
		<span class="topslice">
		<span> </span>
		</span>
		<div class="content BreezeInfoBlock">';
	echo '
				<div class="username"><h4>', $context['member']['name'] ,'<br/><span class="position">', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</span></h4></div>
				', $context['member']['avatar']['image'], '
				<ul class="reset">';

	// What about if we allow email only via the forum??
	if ($context['member']['show_email'] === 'yes' || $context['member']['show_email'] === 'no_through_forum' || $context['member']['show_email'] === 'yes_permission_override')
		echo '
					<li><a href="', $scripturl, '?action=emailuser;sa=email;uid=', $context['member']['id'], '" title="', $context['member']['show_email'] == 'yes' || $context['member']['show_email'] == 'yes_permission_override' ? $context['member']['email'] : '', '" rel="nofollow"><img src="', $settings['images_url'], '/email_sm.gif" alt="', $txt['email'], '" /></a></li>';

	// Don't show an icon if they haven't specified a website.
	if ($context['member']['website']['url'] !== '' && !isset($context['disabled_fields']['website']))
		echo '
					<li><a href="', $context['member']['website']['url'], '" title="' . $context['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<img src="' . $settings['images_url'] . '/www_sm.gif" alt="' . $context['member']['website']['title'] . '" />' : $txt['www']), '</a></li>';

	// Are there any custom profile fields for the summary?
	if (!empty($context['custom_fields']))
	{
		foreach ($context['custom_fields'] as $field)
			if (($field['placement'] == 1 || empty($field['output_html'])) && !empty($field['value']))
				echo '
					<li class="custom_field">', $field['output_html'], '</li>';
	}

	echo '
			</ul>
			<span id="userstatus">', $context['can_send_pm'] ? $context['member']['online']['link'] : $context['member']['online']['text'];

	// Can they add this member as a buddy?
	if (!empty($context['can_have_buddy']) && !$context['member']['is_owner'])
		echo '
			<br /><a href="', $scripturl, '?action=buddy;u=', $context['id_member'], ';', $context['session_var'], '=', $context['session_id'], '">[', $txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')], ']</a>';

	if (!$context['member']['is_owner'] && $context['can_send_pm'])
		echo '
			<br /><a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], '">', $txt['profile_sendpm_short'], '</a>';
	echo '
			<br /><a href="', $scripturl, '?action=profile;area=showposts;u=', $context['id_member'], '">', $txt['showPosts'], '</a><br />
			<a href="', $scripturl, '?action=profile;area=statistics;u=', $context['id_member'], '">', $txt['statPanel'], '</a>';

	echo '
			<br /></span>';

	echo'
		</div>
		<span class="botslice">
		<span> </span>
		</span>
	</div>';
}

function breeze_activity($data)
{
	global $context, $txt;

	if (empty($data))
		return false;

	$counter = 1;

	echo '
		<div class="content">
			<ul class="reset">';

	foreach ($data as $act)
	{
		echo '
				<li class="activityLI">
						<div class="activityContent windowbg', ($counter = !$counter ? '2' : '') ,'">
							<span class="topslice">
								<span> </span>
							</span>
							<div class="activityInner">
								<span class="activityIcon ', $act['type'] ,'"></span>
								<span class="time_elapsed" title="'. timeformat($act['time_raw'], false) .'" data-livestamp="'. $act['time_raw'] .'">', $act['time'] ,'</span><br />
								', $act['content']['message'] ,'<br />
								', $act['content']['link'] ,'
							</div>
							<span class="botslice">
								<span></span>
							</span>
						</div>
				</li>';
	}

	// Close the ul
	echo '
			</ul>
		</div>';
}

function breeze_user_list($list, $type = 'buddy')
{
	global $context, $user_info, $txt;

	// You have too many buddies/visitors pal!
	if ($context['Breeze']['compact'][$type])
	{

		echo '<ol>';

		foreach ($list as $u)
		{
			// Trickery...
			$user = $type == 'visitors' ? $u['user'] : $u;

			echo '<li>', $context['Breeze']['user_info'][$user]['link'] ,'</li>';
		}

		echo '</ul>';
	}

	// Print a nice Ul
	else
	{
		echo '
			<ul class="reset">';

		// Show the profile visitors in a big, fat echo!
		foreach ($list as $u)
		{
			// Trickery...
			$user = $type == 'visitors' ? $u['user'] : $u;

			echo '
				<li> ', $context['Breeze']['user_info'][$user]['breezeFacebox'] ,' <br /> ', $context['Breeze']['user_info'][$user]['link'];

			// Are we showing the visitors? if so, show some more info!
			if ($type == 'visitors')
			{
				echo '
						<br />',  $context['Breeze']['tools']->timeElapsed($u['last_view']);

				// If you're the profile owner you might want to know how many time this user has visited your profile...
				if ($context['member']['id'] == $user_info['id'])
					echo '
						<br />',  $txt['Breeze_user_modules_visitors'] . $u['views'];
			}

			// close the li
			echo '</li>';
		}

		// End the buddies list
		echo '
			</ul>';
	}
}

function breeze_server_response()
{
	global $txt;

	// Just to be sure...
	loadLanguage(Breeze::$name);

	// Get the message from the server, FUGLY.
	$serverResponse = !empty($_SESSION['Breeze']['response']) ? $_SESSION['Breeze']['response'] : array('type' => false, 'message' => false);

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

function template_userDiv()
{
	global $context, $settings, $modSettings, $txt;

	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta charset="', $context['character_set'], '">
		<meta name="robots" content="noindex">
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css', $modSettings['browser_cache'] ,'">
		<script src="', $settings['default_theme_url'], '/scripts/script.js', $modSettings['browser_cache'] ,'"></script>
	</head>
	<body id="likes_popup">
		<div class="windowbg">';

		if (!empty($context['BreezeUser']))
			print_r($context['BreezeUser']);

		// else
			// some error string here.

	echo '
			<br class="clear">
			<a href="javascript:self.close();">', $txt['close_window'], '</a>
		</div>
	</body>
</html>';
}

function template_mood_image($mood, $user, $currentUser)
{
	global $scripturl, $txt, $context;


	// First case, no mood and no link.
	if (empty($mood) && $currentUser)
		return '<a href="'. $scripturl .'?action=breezemood;user='. $user .'" rel="breezeMood" data-name="'. $txt['Breeze_moodChange'] .'" data-user="'. $user .'">'. $txt['Breeze_moodChange'] .'</a>';

	// Got a mood, show it!
	elseif ($currentUser)
		return '<a href="'. $scripturl .'?action=breezemood;user='. $user .'" rel="breezeMood" data-name="'. $txt['Breeze_moodChange'] .'" data-user="'. $user .'">'. $mood['image_html'] .'</a>';

	// Just show the image...
	else
		return $mood['image_html'];
}


function template_mood_change()
{
	global $context, $settings, $modSettings, $txt, $scripturl;

	$count = 0;

	echo '<!DOCTYPE html>
<html', $context['right_to_left'] ? ' dir="rtl"' : '', '>
	<head>
		<meta charset="', $context['character_set'], '">
		<meta name="robots" content="noindex">
		<title>', $context['page_title'], '</title>
		<link rel="stylesheet" type="text/css" href="', $settings['theme_url'], '/css/index', $context['theme_variant'], '.css', $modSettings['browser_cache'] ,'">
		<script src="', $settings['default_theme_url'], '/scripts/script.js', $modSettings['browser_cache'] ,'"></script>
	</head>
	<body id="breeze_mood_popup">
		<div class="windowbg">
			<table class="bbc_table">
				<tr>';

		foreach ($context['moods'] as $m)
		{
			$count++;

			if ($count % 5 == 1)
				echo '
				</tr>
				<tr>';

			echo '
					<td>
						<a href="'. $scripturl .'?action=breezeajax;sa=moodchange;user='. $context['moodUser'] .';moodID='. $m['moods_id'] .'" rel="breezeMoodSave" data-id="'. $m['moods_id'] .'">'. $m['image_html'] .'</a>
						'. (!empty($m['name']) ? '<p>'. $m['name'] .'</p>' : '') .'
					</td>';
		}

	echo '
			</tr>
			</table>
			<br class="clear">
			<a href="javascript:self.close();">', $txt['close_window'], '</a>
		</div>
	</body>
</html>';
}

function template_top()
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
						echo $context['user']['name'], ' &#9660;</a>
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
				<option value="all"', ($selected == 'all' ? ' selected' : ''), '>', $txt['search_entireforum'], ' </option>';

		// Can't limit it to a specific topic if we are not in one
		if (!empty($context['current_topic']))
			echo '
				<option value="topic"', ($selected == 'current_topic' ? ' selected' : ''), '>', $txt['search_thistopic'], '</option>';

		// Can't limit it to a specific board if we are not in one
		if (!empty($context['current_board']))
			echo '
					<option value="board"', ($selected == 'current_board' ? ' selected' : ''), '>', $txt['search_thisbrd'], '</option>';
			echo '
					<option value="members"', ($selected == 'members' ? ' selected' : ''), '>', $txt['search_members'], ' </option>
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