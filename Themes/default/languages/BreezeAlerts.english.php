<?php

/**
 * BreezeAlerts.english
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica Gonzalez
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt;

// Notifications
$txt['Breeze_noti_title'] = 'Notifications';
$txt['Breeze_noti_title_settings'] = 'Notifications settings';
$txt['Breeze_noti_title_settings_desc'] = 'Enable/disable individual notifications.';
$txt['Breeze_noti_message'] = 'Message';
$txt['Breeze_noti_buddy_title'] = 'Buddy notification';
$txt['Breeze_noti_buddy_message'] = 'User %s has added you as his/her buddy, please confirm this request.';
$txt['Breeze_noti_markasread'] = 'Mark as read';
$txt['Breeze_noti_markasunread'] = 'Mark as unread';
$txt['Breeze_noti_markasread_title'] = 'Mark as read/unread';
$txt['Breeze_noti_markasread_viewed'] = 'Already marked as read';
$txt['Breeze_noti_close'] = 'Close';
$txt['Breeze_noti_delete'] = 'Delete';
$txt['Breeze_noti_cancel'] = 'Cancel';
$txt['Breeze_noti_closeAll'] = 'Close all notifications';
$txt['Breeze_noti_novalid_after'] = 'This isn\'t a valid notification.';
$txt['Breeze_noti_none'] = 'You don\'t have any notifications';
$txt['Breeze_noti_checkAll'] = 'Check all';
$txt['Breeze_noti_check'] = 'check';
$txt['Breeze_noti_selectedOptions'] = 'With the selected options do: ';
$txt['Breeze_noti_send'] = 'Send';
$txt['Breeze_alert_gender_possessive_Female'] = 'her';
$txt['Breeze_alert_gender_possessive_Male'] = 'his';
$txt['Breeze_alert_gender_possessive_None'] = 'his/her';
$txt['Breeze_alert_gender_pronoun_Male'] = 'he';
$txt['Breeze_alert_gender_pronoun_Female'] = 'she';
$txt['Breeze_alert_gender_pronoun_None'] = 'he/she';

// Comment notification.
$txt['Breeze_alert_comment_different_owner'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on the status made by {status_poster} on {wall_owner}\'s wall';
$txt['Breeze_alert_comment_status_owner'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on your status made in {wall_owner}\'s wall';
$txt['Breeze_alert_comment_status_owner_buddy'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on {status_poster}\'s status made in {wall_owner}\'s wall';
$txt['Breeze_alert_comment_status_owner_own_wall'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on your status made on your own wall';
$txt['Breeze_alert_comment_poster_own_wall'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">commented</a> on {gender_possessive} status on {gender_possessive} own wall';

// Someone posted a status on your wall.
$txt['Breeze_alert_status_owner'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">posted a new status on your wall</a>';
$txt['Breeze_alert_status_owner_buddy'] = '{poster} <a href="{href}" class="bbc_link" target="_blank">posted a new status</a> on {wall_owner}\'s wall';

// Someone commented your status on your own wall
$txt['Breeze_noti_posted_comment'] = '%1$s <a href="%2$s" class="bbc_link" target="_blank">commented on your status</a> on %3$s\'s wall';
$txt['Breeze_noti_posted_comment_own_wall'] = '%1$s <a href="%2$s" class="bbc_link" target="_blank">commented on your status</a> on your own wall';

// Someone left a comment on your wall.
$txt['Breeze_noti_posted_comment_owner'] = '%1$s <a href="%2$s" class="bbc_link" target="_blank">commented on a status</a> on your own wall.';

// Mentions
$txt['Breeze_mention_message_status'] = '<a href="%3$s" class="bbc_link" target="_blank" id="noti_%4$s"> You have been mentioned</a> by %1$s on %2$s\'s wall!';
$txt['Breeze_mention_message_own_wall_status'] = '<a href="%1$s" class="bbc_link" target="_blank">You have been mentioned</a> on your own wall by %2$s!';
$txt['Breeze_mention_message_comment'] = '<a href="%3$s" class="bbc_link" target="_blank" id="noti_%4$s"> You have been mentioned on a comment</a> by %1$s on %2$s\'s wall!';
$txt['Breeze_mention_message_own_wall_comment'] = '<a href="%1$s" class="bbc_link" target="_blank" id="noti_%3$s">You have been mentioned</a> on a comment on your own wall by %2$s!';

// Single Status
$txt['Breeze_singleStatus_pageTitle'] = 'Single Status';

// Log
$txt['Breeze_logTopic'] = 'created a new topic:';
$txt['Breeze_logRegister'] = 'has just registered!';
$txt['Breeze_logComment'] = 'made a new comment on %s\'s wall';
$txt['Breeze_logComment_own_0'] = 'made a comment on his/her own wall';
$txt['Breeze_logComment_own_1'] = 'made a comment on his own wall';
$txt['Breeze_logComment_own_2'] = 'made a comment on her own wall';
$txt['Breeze_logComment_view'] = 'View comment';
$txt['Breeze_logStatus'] = 'made a new status on %s\'s wall';
$txt['Breeze_logStatus_own_0'] = 'made a new status on his/her own wall';
$txt['Breeze_logStatus_own_1'] = 'made a new status on his own wall';
$txt['Breeze_logStatus_own_2'] = 'made a new status on her own wall';
$txt['Breeze_logStatus_view'] = 'View status';

// Settings.
$txt['alert_group_breeze'] = 'Breeze alert settings';
$txt['alert_Breeze_status_owner'] = 'When someone post a status on my wall';
$txt['alert_Breeze_comment_status_owner'] = 'When someone comment on a status I made';
$txt['alert_Breeze_comment_profile_owner'] = 'When someone comment on a status made on my wall';
