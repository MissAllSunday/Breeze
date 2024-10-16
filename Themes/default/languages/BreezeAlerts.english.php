<?php

declare(strict_types=1);

/**
 * BreezeAlerts.english
 *
 * @package Breeze mod
 * @version 1.1
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Michel Mendiola
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $txt;

// Notifications
$txt['Breeze_noti_markasread'] = 'Mark as read';
$txt['Breeze_noti_markasunread'] = 'Mark as unread';
$txt['Breeze_noti_markasread_title'] = 'Mark as read/unread';
$txt['Breeze_noti_markasread_viewed'] = 'Already marked as read';
$txt['Breeze_noti_close'] = 'Close';
$txt['Breeze_noti_delete'] = 'Delete';
$txt['Breeze_noti_cancel'] = 'Cancel';
$txt['Breeze_noti_checkAll'] = 'CheckType all';
$txt['Breeze_noti_closeAll'] = 'Close all';
$txt['Breeze_noti_check'] = 'check';
$txt['Breeze_noti_selectedOptions'] = 'With the selected options do: ';
$txt['Breeze_noti_send'] = 'Send';

// Gender related stuff.
$txt['Breeze_alert_gender_possessive_Female'] = 'her';
$txt['Breeze_alert_gender_possessive_Male'] = 'his';
$txt['Breeze_alert_gender_possessive_None'] = 'his/her';
$txt['Breeze_alert_gender_pronoun_Male'] = 'he';
$txt['Breeze_alert_gender_pronoun_Female'] = 'she';
$txt['Breeze_alert_gender_pronoun_None'] = 'he/she';

// Comment notification.
$txt['Breeze_alert_comment_different_owner'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on the status made by {status_poster} on {wall_owner}\'s wall';
$txt['Breeze_alert_comment_different_owner_own_wall'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on the status made by {status_poster} on your own wall';
$txt['Breeze_alert_comment_status_owner'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on your status made in {wall_owner}\'s wall';
$txt['Breeze_alert_comment_status_owner_buddy'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on {status_owner}\'s status made in {wall_owner}\'s wall';
$txt['Breeze_alert_comment_status_owner_own_wall'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on your status made on your own wall';
$txt['Breeze_alert_comment_poster_own_wall'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on {gender_possessive} status on {gender_possessive} own wall';

// Someone posted a status on your wall.
$txt['Breeze_alert_status_owner'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">posted a new status on your wall</a>';
$txt['Breeze_alert_status_owner_buddy'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">posted a new status</a> on {wall_owner}\'s wall';

// Likes
$txt['Breeze_alert_like'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">liked</a> your {type}';
$txt['Breeze_alert_like_buddy'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">liked</a> {contentOwner}\'s {type}';

// CoverController
$txt['Breeze_alert_cover'] = '{poster} changed {gender_possessive} cover image<br>{image}';

// New topic.
$txt['Breeze_alert_topic'] = '{poster} created a new topic:<br><a href="{href}" class="bbc_link" target="_blank">{subject}</a>';

// Mentions.
$txt['Breeze_alert_mention_status'] = '<a href="{href}" class="bbc_link" target="_blank"> You have been mentioned</a> by {poster} on {wall_owner}\'s wall!';
$txt['Breeze_alert_mention_own_status'] = '<a href="{href}" class="bbc_link" target="_blank">You have been mentioned</a> on your own wall by {poster}!';
$txt['Breeze_alert_mention_own_comment'] = '<a href="{href}" class="bbc_link" target="_blank"> You have been mentioned on a comment</a> by {poster} on {wall_owner}\'s wall!';
$txt['Breeze_alert_mention_own_comment'] = '<a href="{href}" class="bbc_link" target="_blank">You have been mentioned</a> on a comment on your own wall by {poster}!';

// Buddy request.
$txt['Breeze_alert_buddy_confirm'] = '{sender} wants to be your buddy!<br><a href="{href}">Confirm/Deny the invitation</a>';
$txt['Breeze_alert_buddy_confirmed'] = '{receiver} confirmed your buddy invitation!';
$txt['Breeze_alert_buddy_done'] = '{receiver} and {sender} have become buddies!.';

// Single Status
$txt['Breeze_singleStatus_pageTitle'] = 'Single Status';

// Log
$txt['Breeze_log__topic'] = 'created a new topic:';
$txt['Breeze_log_Register'] = 'has just registered!';
$txt['Breeze_log_Comment'] = 'made a new comment on %s\'s wall';
$txt['Breeze_log_Comment_own_0'] = 'made a comment on his/her own wall';
$txt['Breeze_log_Comment_own_1'] = 'made a comment on his own wall';
$txt['Breeze_log_Comment_own_2'] = 'made a comment on her own wall';
$txt['Breeze_log_Comment_view'] = 'View comment';
$txt['Breeze_log_Status'] = 'made a new status on %s\'s wall';
$txt['Breeze_log_Status_own_0'] = 'made a new status on his/her own wall';
$txt['Breeze_log_Status_own_1'] = 'made a new status on his own wall';
$txt['Breeze_log_Status_own_2'] = 'made a new status on her own wall';
$txt['Breeze_log_Status_view'] = 'View status';

// UserSettingsController.
$txt['alert_group_breezeComponents'] = 'My wall alert settings';
$txt['alert_Breeze_status_owner'] = 'When someone post a status on my wall';
$txt['alert_Breeze_comment_status_owner'] = 'When someone comment on a status I made';
$txt['alert_Breeze_comment_profile_owner'] = 'When someone comment on a status made on my wall';
$txt['alert_Breeze_like'] = 'When someone likes a comment or status I made on any wall';
$txt['alert_Breeze_mention'] = 'When someone mentions me on a comment or status on any wall';
