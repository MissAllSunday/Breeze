<?php

/**
 * Breeze.template.php
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014, Jessica González
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

function template_breeze_info()
{
	global $context, $txt, $scripturl, $settings;

	// Some info from the wall owner.
	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				'. $txt['summary'] .'
			</h3>
		</div>
		<div class="roundframe flow_auto" id="profileview">
			<div class="infoList">';

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
				<div class="username clear">
					<h4>', $context['member']['name'], '<span class="position">', (!empty($context['member']['group']) ? $context['member']['group'] : $context['member']['post_group']), '</span></h4>
				</div>';

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
				<ul class="reset clear">';
	// Email is only visible if it's your profile or you have the moderate_forum permission
	if ($context['member']['show_email'])
		echo '
					<li><a href="mailto:', $context['member']['email'], '" title="', $context['member']['email'], '" rel="nofollow"><span class="generic_icons mail" title="' . $txt['email'] . '"></span></a></li>';

	// Don't show an icon if they haven't specified a website.
	if ($context['member']['website']['url'] !== '' && !isset($context['disabled_fields']['website']))
		echo '
					<li><a href="', $context['member']['website']['url'], '" title="' . $context['member']['website']['title'] . '" target="_blank" class="new_win">', ($settings['use_image_buttons'] ? '<span class="generic_icons www" title="' . $context['member']['website']['title'] . '"></span>' : $txt['www']), '</a></li>';

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
				<span id="userstatus">', $context['can_send_pm'] ? '<a href="' . $context['member']['online']['href'] . '" title="' . $context['member']['online']['text'] . '" rel="nofollow">' : '', $settings['use_image_buttons'] ? '<span class="' . ($context['member']['online']['is_online'] == 1 ? 'on' : 'off') . '" title="' . $context['member']['online']['text'] . '"></span>' : $context['member']['online']['label'], $context['can_send_pm'] ? '</a>' : '', $settings['use_image_buttons'] ? '<span class="smalltext"> ' . $context['member']['online']['label'] . '</span>' : '';

	// Can they add this member as a buddy?
	if (!empty($context['can_have_buddy']) && !$context['user']['is_owner'])
		echo '
					<br><a href="', $scripturl, '?action=buddy;u=', $context['id_member'], ';', $context['session_var'], '=', $context['session_id'], '">[', $txt['buddy_' . ($context['member']['is_buddy'] ? 'remove' : 'add')], ']</a>';

	echo '
				</span>';

	if (!$context['user']['is_owner'] && $context['can_send_pm'])
		echo '
				<a href="', $scripturl, '?action=pm;sa=send;u=', $context['id_member'], '" class="infolinks">', $txt['profile_sendpm_short'], '</a>';

	echo '
				<a href="', $scripturl, '?action=profile;area=showposts;u=', $context['id_member'], '" class="infolinks">', $txt['showPosts'], '</a>';

	if ($context['user']['is_owner'] && !empty($modSettings['drafts_post_enabled']))
		echo '
				<a href="', $scripturl, '?action=profile;area=showdrafts;u=', $context['id_member'], '" class="infolinks">', $txt['drafts_show'], '</a>';

	echo '
				<a href="', $scripturl, '?action=profile;area=statistics;u=', $context['id_member'], '" class="infolinks">', $txt['statPanel'], '</a>';

	// Are there any custom profile fields for bottom?
	if (!empty($context['print_custom_fields']['bottom_poster']))
	{
		echo '
				<div class="custom_fields_bottom">
					<ul class="reset nolist">';

		foreach ($context['print_custom_fields']['bottom_poster'] as $field)
			if (!empty($field['output_html']))
				echo '
					<li>', $field['output_html'], '</li>';

		echo '
				</ul>
			</div>';
	}

	echo '
		</div>
	</div>';
}

function template_breeze_buddies()
{
	global $context, $txt;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				'. $txt['Breeze_tabs_buddies'] .'
			</h3>
		</div>';

	echo '
		<div class="information">
			<div class="BreezeList">';

	if (!empty($context['member']['buddies']))
	{
			echo '
				<ul class="reset user_list">';

			foreach ($context['member']['buddies'] as $id)
				if (!empty($context['Breeze']['user_info'][$id]['breezeFacebox']))
					echo '
					<li>', $context['Breeze']['user_info'][$id]['breezeFacebox'] ,'</li>';

			echo '
				</ul>';
	}

	// No buddies :(
	else
		echo $txt['Breeze_user_modules_buddies_none'];

		echo '
			</div>
		</div>';
}

function template_breeze_visitors()
{
	global $context, $txt;

	echo '
		<div class="cat_bar">
			<h3 class="catbg">
				'. $txt['Breeze_tabs_views'] .'
			</h3>
		</div>';

	echo '
		<div class="information">
			<div class="BreezeList">';

	if (!empty($context['Breeze']['views']))
	{
		echo '
				<ul class="reset user_list">';

		foreach ($context['Breeze']['views'] as $id => $data)
			if (!empty($context['Breeze']['user_info'][$id]['breezeFacebox']))
				echo '
					<li>', $context['Breeze']['user_info'][$id]['breezeFacebox'] ,'</li>';

		echo '
				</ul>';
	}

	// No visitors :(
	else
		echo $txt['Breeze_user_modules_visitors_none'];

	echo '
			</div>
		</div>';
}
