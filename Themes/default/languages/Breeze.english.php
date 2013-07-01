<?php

/**
 * Breeze.english
 *
 * The purpose of this file is Provide the text strings
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

global $txt;

// Public/General strings
$txt['Breeze_general_wall'] = 'Wall';
$txt['Breeze_general_summary'] = 'Summary';

$txt['Breeze_general_my_wall'] = 'My Wall';
$txt['Breeze_general_my_wall_settings'] = 'My Wall Settings';
$txt['Breeze_general_loading'] = 'Loading...';
$txt['Breeze_general_like'] = 'Like';
$txt['Breeze_general_last_view'] = 'Last view';
$txt['Breeze_general_delete'] = 'Delete';
$txt['Breeze_general_unlike'] = 'Unlike';
$txt['Breeze_general_plural'] = '(s)';

// Modules
$txt['Breeze_general_my_wall_modules'] = 'My Wall Modules settings';
$txt['Breeze_modules_enable_visitors_title'] = 'Recent Visitors';
$txt['Breeze_modules_enable_visitors_description'] = 'The last %1$s visitor%2$s to this page were: <p />';


// User Individual Settings
$txt['Breeze_profile'] = 'Wall Settings';
$txt['Breeze_user_settings_name'] = 'Wall Settings';
$txt['Breeze_user_buddysettings_name'] = 'Buddy Requests';
$txt['Breeze_user_single_status'] = 'Single Status';
$txt['Breeze_user_buddyrequestmessage_name'] = 'Buddy message request send';
$txt['Breeze_user_notisettings_name'] = 'Notifications';
$txt['Breeze_user_settings_enable_desc'] = 'From this page you will be able to setup the different settings for your wall.';
$txt['Breeze_user_settings_wall_limit'] = 'How many status per page to show?';
$txt['Breeze_user_settings_wall_limit_sub'] = 'This will be the number of status to show by default, max value is 30, if set it will create a pagination with the rest of your status.';
$txt['Breeze_user_settings_kick_ignored'] = 'Do not show my wall to users in my ignore list';
$txt['Breeze_user_settings_kick_ignored_sub'] = 'If this is enable, users on your ignore list will not be able to see your wall or to post on it.';
$txt['Breeze_user_settings_infinite_scroll'] = 'Use infinite scroll';
$txt['Breeze_user_settings_infinite_scroll_sub'] = 'This setting will allow you to load previous status on the same page by scrolling down.';
$txt['Breeze_user_settings_pagination_number'] = 'How many status will be displayed per page';
$txt['Breeze_user_settings_pagination_number_sub'] = 'Enter the number of status you want to display per page on your wall. By default is 5';
$txt['Breeze_user_settings_enable_wall'] = 'Enable my wall';
$txt['Breeze_user_settings_enable_wall_sub'] = 'If you want to use your wall, you need to check this option, otherwise your profile will show the default page.';
$txt['Breeze_user_settings_enable_buddies_tab'] = 'Enable the Buddies tab?';
$txt['Breeze_user_settings_enable_buddies_tab_sub'] = 'If this is enable, a new tab will be visible, this tab will contain all your confirmed buddies';
$txt['Breeze_user_settings_how_many_mentions_options'] = 'How many users will be displayed as options when mentioning an user';
$txt['Breeze_user_settings_how_many_mentions_options_sub'] = 'for example, if you set this on 5 then when mentioning you will see 5 possible options to chose from.';
$txt['Breeze_user_settings_enable_visits_tab'] = 'Enable the visits tab?';
$txt['Breeze_user_settings_enable_visits_tab_sub'] = 'This tab will show the latest visitors to your wall.';
$txt['Breeze_user_settings_visits_module_timeframe'] = 'The time frame to count the visits from.';
$txt['Breeze_user_settings_visits_module_timeframe_sub'] = 'By default is set to a week, that means the module will show the latest visits to your wall in the last week.';
$txt['Breeze_user_settings_visits_module_timeframe_hour'] = 'Hour';
$txt['Breeze_user_settings_visits_module_timeframe_day'] = 'Day';
$txt['Breeze_user_settings_visits_module_timeframe_week'] = 'Week';
$txt['Breeze_user_settings_visits_module_timeframe_month'] = 'Month';
$txt['Breeze_user_settings_clean_visits'] = 'Clean the visits log';
$txt['Breeze_user_settings_clean_visits_sub'] = 'Removes all visits from your visits tab.';
$txt['Breeze_user_settings_clear_noti'] = 'How many seconds do you want the notifications to be displayed before been automatically closed';
$txt['Breeze_user_settings_clear_noti_sub'] = 'In seconds, If you leave this empty the notifications won\'t be automatically closed and you must manually click the "close all notifications" button.';

// Modules
$txt['Breeze_user_settings_enable_buddies'] = 'Enable the "Buddies" module.';
$txt['Breeze_user_settings_enable_buddies_sub'] = 'This will show a block showing all your buddies.';
$txt['Breeze_user_settings_how_many_buddies'] = 'How many buddies to display';
$txt['Breeze_user_settings_how_many_buddies_sub'] = 'If empty it will show the default value 10, max value is 30.';
$txt['Breeze_user_settings_visitors'] = 'Enable the "Latest Visitors" module.';
$txt['Breeze_user_settings_visitors_sub'] = 'This will show a block with the latest visitors to your wall.';
$txt['Breeze_user_settings_notification_pm'] = 'Send me a pm when someone else mentions me in any wall.';
$txt['Breeze_user_settings_notification_pm_sub'] = 'This will send a private message per status or comment.';
$txt['Breeze_user_settings_how_many_visitors'] = 'How many visitors to display';
$txt['Breeze_user_settings_how_many_visitors_sub'] = 'If empty it will show the default value 10, max value is 30.';
$txt['Breeze_user_settings_time_frame'] = 'The time frame to consider when fetching the visitors';
$txt['Breeze_user_settings_time_frame_sub'] = 'For example, if you select "Last Week" then the module will show the users who had visited your wall in the last week.';
$txt['Breeze_user_settings_time_hour'] = 'Last Hour';
$txt['Breeze_user_settings_time_day'] = 'Last Day';
$txt['Breeze_user_settings_time_week'] = 'Last Week';
$txt['Breeze_user_settings_time_month'] = 'Last Month';
$txt['Breeze_user_settings_time_year'] = 'Last Year';
$txt['Breeze_user_settings_show_avatar'] = 'Show avatar in list';
$txt['Breeze_user_settings_show_avatar_sub'] = 'By default it shows the user link only.';
$txt['Breeze_user_settings_show_last_visit'] = 'Show the last time the users visited your wall';
$txt['Breeze_user_settings_notification_wall'] = 'Send me a pm when someone else post a new status on my wall';
$txt['Breeze_user_settings_notification_wall_sub'] = 'This will only apply for new status.';
$txt['Breeze_user_permissions_name'] = 'Permissions';
$txt['Breeze_user_modules_name'] = 'Modules';
$txt['Breeze_user_modules_visits_none'] = 'There are no recent visits';
$txt['Breeze_user_modules_buddies_none'] = 'This user currently doesn\'t have any buddies';


// Admin Settings
$txt['Breeze_admin_settings_admin_panel'] = 'Breeze Admin Panel';
$txt['Breeze_admin_welcome'] = 'This is your &quot;Breeze Admin Panel&quot;.  From here, you can edit the settings for Breeze If you have any trouble, feel free to <a href="http://missallsunday.com" target="_blank" class="new_win">ask for support</a> on the author\'s site.';
$txt['Breeze_admin_settings_main'] = 'Main Breeze Admin Center';
$txt['Breeze_admin_settings_sub_permissions'] = 'Permissions';
$txt['Breeze_admin_settings_sub_style'] = 'Style and Layout';
$txt['Breeze_admin_settings_sub_style_desc'] = 'Some description here';
$txt['Breeze_admin_settings_settings'] = 'General Settings';
$txt['Breeze_admin_settings_settings_desc'] = 'This is the general settings page, from here you can enable/disable the mod as well as configuring general settings.';
$txt['Breeze_admin_settings_permissions'] = 'Permissions';
$txt['Breeze_admin_settings_permissions_desc'] = 'From here you can add/remove specific Breeze permissions.';
$txt['Breeze_admin_settings_donate'] = 'Donate';
$txt['Breeze_admin_settings_donate_desc'] = 'Boring stuff you had curious about it but you\'ll never gonna see it again :P';
$txt['Breeze_admin_settings_server_needs'] = '<div class="breeze_error_message">Your Server does not support %s, contact your hosting provider and ask for it to be enable, otherwise you won\'t be able to use this mod at full.</div>';
$txt['Breeze_admin_settings_json'] = 'JSON library';
$txt['Breeze_admin_settings_php'] = '<div class="breeze_error_message">Your PHP version: ( %s ) is lower than the minimum required by this mod: 5.3, you won\'t be able to use this mod at full.</div>';
$txt['Breeze_admin_settings_php_ok'] = '<div class="breeze_ok_message">Your PHP version is supported.</div>';
$txt['Breeze_admin_settings_server_needs_ok'] = '<div class="breeze_ok_message"> %s is supported by your server.</div>';
$txt['Breeze_admin_settings_tab_server_specs'] = 'Server Requirements';
$txt['Breeze_admin_settings_tab_admin_logs'] = 'Admin Logs';
$txt['Breeze_admin_settings_donate'] = 'Donate';
$txt['Breeze_admin_general_credits_title'] = 'Credits';
$txt['Breeze_admin_general_credits_decs'] = 'Breeze is brought to you thanks to the following persons and/or scripts:'; 
$txt['Breeze_admin_settings_enablegeneralwall'] = 'Enable General Wall';
$txt['Breeze_admin_settings_enablegeneralwall_sub'] = 'If enable, a general wall will appear, in this general wall the user will be able to see his/her buddie\'s status and recent activity';
$txt['Breeze_admin_settings_menuposition'] = 'Select the position for the general Wall button.';
$txt['Breeze_admin_settings_menuposition_sub'] = 'By default is next to the home button.';
$txt['Breeze_admin_settings_enable'] = 'Enable Breeze mod';
$txt['Breeze_admin_settings_enable_sub'] = 'The master setting, this must be enable for the mod to work properly.';
$txt['Breeze_admin_settings_home'] = 'Home';
$txt['Breeze_admin_settings_help'] = 'Help';
$txt['Breeze_admin_settings_profile'] = 'Profile';
$txt['Breeze_admin_enable_limit'] = 'Enable the query limit';
$txt['Breeze_admin_enable_limit_sub'] = 'If you have problems with resources, you can enable the query limit, this will load only a fraction of the total of status/comments and can help to reduce server stress.';
$txt['Breeze_admin_mention_limit'] = 'How many users can the poster mention on a single message?';
$txt['Breeze_admin_mention_limit_sub'] = 'Leave it blank to not have any restriction, if the user tries to mention more users than allowed, only the first X will be converted to mentions where X is the number you specified. If left empty, the mod will use the default value: 10';
$txt['Breeze_admin_limit_timeframe'] = 'Set the limit in time to fetch status and comments from';
$txt['Breeze_admin_limit_timeframe_sub'] = 'This will fetch the status/comments only from the past selected option, for example, the last week, the last month, etc.';
$txt['Breeze_admin_posts_for_mention'] = 'How many posts are required to appear on the mention list?';
$txt['Breeze_admin_posts_for_mention_sub'] = 'To avoid having a huge list of users to chose from when mentioning, you can set how many posts does an user needs to have in order to be mentionable, if you leave this setting empty, it will use the default value: 1.<br /> To help with the server load, the mentions list gets cached and it only sets a new list every 2 hours, if you change this setting, make sure to clean your forum cache to see the results.';
$txt['Breeze_admin_breeze_version'] = 'Breeze version';
$txt['Breeze_admin_live'] = 'Live from the suport forum...';
$txt['Breeze_allowedActions'] = 'Write the actions where you wish the notification system shows up';
$txt['Breeze_allowedActions_sub'] = 'By default, the notification system will appear on the following actions: '. implode(', ', Breeze::$_allowedActions) .'. Plus the BoardIndex, MessageIndex, Topic and Board pages. <br /> Please add your actions in a comma separated list, example: action, action, action, action';

// Time
$txt['Breeze_time_just_now'] = 'just now.';
$txt['Breeze_time_second'] = 'second';
$txt['Breeze_time_ago'] = 'ago.';
$txt['Breeze_time_minute'] = 'minute';
$txt['Breeze_time_hour'] = 'hour';
$txt['Breeze_time_day'] = 'day';
$txt['Breeze_time_month'] = 'month';
$txt['Breeze_time_year'] = 'year';

// Permissions strings
$txt['cannot_view_general_wall'] = 'I\'m sorry, you are not allowed to this the Wall.';
$txt['permissiongroup_simple_breeze_per_simple'] = 'Breeze mod permissions';
$txt['permissiongroup_breeze_per_classic'] = 'Breeze mod permissions';
$txt['permissionname_breeze_deleteStatus'] = 'Delete all status on any wall';
$txt['permissionname_breeze_deleteComments'] = 'Delete all comments on any wall';
$txt['permissionname_breeze_postStatus'] = 'Post new Status on any wall';
$txt['permissionname_breeze_postComments'] = 'Post new Comments on any wall';
$txt['permissionname_breeze_edit_settings_any'] = 'Edit the user settings of any wall';

// Ajax strings
$txt['Breeze_feed_error_message'] = 'Breeze couldn\'t connect with the support site';
$txt['Breeze_error_message'] = 'There was an error, please try again or contact the forum admin.';
$txt['Breeze_success_message'] = 'Your message was successfully published';
$txt['Breeze_empty_message'] = 'You need to type something in the textbox.';
$txt['Breeze_success_delete'] = 'Your comment has been deleted';
$txt['Breeze_confirm_delete'] = 'Do you really want to delete this?';
$txt['Breeze_confirm_yes'] = 'Yes';
$txt['Breeze_confirm_cancel'] = 'Cancel';
$txt['Breeze_already_deleted'] = 'This comment/status was already deleted. Try refreshing your browser.';
$txt['Breeze_already_deleted_noti'] = 'This notification was already deleted. Try refreshing your browser.';
$txt['Breeze_cannot_postStatus'] = 'I\'m sorry,  you aren\'t allowed to post new Status.';
$txt['Breeze_cannot_postComments'] = 'I\'m sorry,  you aren\'t allowed to post new Comments.';
$txt['Breeze_error_no_valid_action'] = 'This is not a valid action.';

// Errors
$txt['cannot_breeze_postStatus'] = $txt['Breeze_cannot_postStatus'];
$txt['cannot_breeze_postComments'] = $txt['Breeze_cannot_postComments'];
$txt['cannot_breeze_deleteStatus'] = 'I\'m sorry,  you aren\'t allowed to delete Status/Comments.';

// Pagination
$txt['Breeze_pag_previous'] = 'previous';
$txt['Breeze_pag_next'] = 'next';
$txt['Breeze_pag_first'] = 'First';
$txt['Breeze_pag_last'] = 'Last';
$txt['Breeze_pag_pages'] = 'Pages :';
$txt['Breeze_pag_page'] = '- page ';
$txt['Breeze_profile_of_username'] = 'Profile of %1$s %2$s';
$txt['Breeze_page_loading'] = 'Loading more messages...';
$txt['Breeze_page_loading_end'] = '<span class="breeze_center">There are no more messages, go to top</span>';

// Tabs
$txt['Breeze_tabs_wall'] = 'Wall';
$txt['Breeze_tabs_buddies'] = 'Buddies';
$txt['Breeze_tabs_views'] = 'Profile Visitors';
$txt['Breeze_goTop'] = 'Go to top';

// Notifications
$txt['Breeze_noti_title'] = 'Notifications';
$txt['Breeze_noti_message'] = 'Message';
$txt['Breeze_noti_buddy_title'] = 'Buddy notification';
$txt['Breeze_noti_buddy_message'] = 'User %s has added you as his/her buddy, please confirm this request.';
$txt['Breeze_noti_markasread'] = 'Mark as read';
$txt['Breeze_noti_markasunread'] = 'Mark as unread';
$txt['Breeze_noti_markasread_title'] = 'Mark as read/unread';
$txt['Breeze_noti_markasread_viewed'] = 'Already marked as read';
$txt['Breeze_noti_close'] = 'Close';
$txt['Breeze_noti_closeAll'] = 'Close all notifications';
$txt['Breeze_noti_unmarkasread_after'] = 'You have successfully marked this notification as unread';
$txt['Breeze_noti_markasread_after'] = 'You have successfully marked this notification as read';
$txt['Breeze_noti_markasreaddeleted_after'] = 'This notification was already deleted or is not a valid entry.';
$txt['Breeze_noti_delete_after'] = 'You have successfully deleted this notification';
$txt['Breeze_noti_visits_clean'] = 'You have successfully cleaned your visitors log';
$txt['Breeze_noti_novalid_after'] = 'This isn\'t a valid action.';
$txt['Breeze_noti_none'] = 'You don\'t have any notifications';

$txt['Breeze_noti_buddy_message_1_title'] = 'Confirmation required';
$txt['Breeze_noti_buddy_message_1_message'] = 'User %s hasn\'t either denied or confirmed your buddy request, do you want to wait or force the buddy removal';
$txt['Breeze_noti_buddy_message_2_title'] = 'Buddy request sent';
$txt['Breeze_noti_buddy_message_2_message'] = 'Your request has been submitted, the user will see your request and if he/she accepts it, you will receive a notification via private message.';

// Comment notification
$txt['Breeze_noti_comment_message'] = '%1$s commented on the status made by %2$s on %3$s\'s wall,<br/> <a href="" class="bbc_link" target="_blank">see the comment</a>';
$txt['Breeze_noti_comment_message_statusOwner'] = '%1$s commented on your status made in %2$s\'s wall';
$txt['Breeze_noti_comment_message_wallOwner'] = '%1$s commented on the status made by %2$s on your wall';

// Mentions
$txt['Breeze_mention_message_status'] = '<a href="%3$s" class="bbc_link" target="_blank" id="noti_%4$s"> You have been mentioned</a> by %1$s on %2$s\'s wall!';
$txt['Breeze_mention_message_own_wall_status'] = '<a href="%1$s" class="bbc_link" target="_blank">You have been mentioned</a> on your own wall by %2$s!';
$txt['Breeze_mention_message_comment'] = '<a href="%3$s" class="bbc_link" target="_blank" id="noti_%4$s"> You have been mentioned on a comment</a> by %1$s on %2$s\'s wall!';
$txt['Breeze_mention_message_own_wall_comment'] = '<a href="%1$s" class="bbc_link" target="_blank" id="noti_%3$s">You have been mentioned</a> on a comment on your own wall by %2$s!';

// Buddy List
$txt['Breeze_buddyrequest_error_doublerequest'] = 'You already sent a buddy request, please wait for the user\'s response.';
$txt['Breeze_buddyrequest_error_dunno'] = 'Something went wrong, please contact the forum admin.';
$txt['Breeze_buddy_messagerequest_message'] = '%1$s wants to be your buddy! <span id="noti_%2$s"></span>';
$txt['Breeze_buddy_title'] = 'Buddy List';
$txt['Breeze_buddy_desc'] = 'From here you can confirm or decline your buddy request. If you confirm the buddy request, a pm on your behalf will be send to the user, if you decine the request the user will not receive anything';
$txt['Breeze_buddyrequest_title'] = 'Buddy requests';
$txt['Breeze_buddyrequest_noBuddies'] = 'You currently don\'t have any buddy requests';
$txt['Breeze_buddyrequest_list_status'] = 'Status';
$txt['Breeze_buddyrequest_list_message'] = 'Message';
$txt['Breeze_buddyrequest_list_confirm'] = 'Confirm';
$txt['Breeze_buddyrequest_list_decline'] = 'Decline';
$txt['Breeze_buddyrequest_confirmed_subject'] = 'Buddy request accepted.';
$txt['Breeze_buddyrequest_confirmed_message'] ='I have confirmed and accepted your buddy request';
$txt['Breeze_buddyrequest_confirmed_inner_message'] = 'You successfully confirmed the request';
$txt['Breeze_buddyrequest_confirmed_inner_message_de'] = 'You successfully declined the request';

// Single Status
$txt['Breeze_singleStatus_pageTitle'] = 'Single Status';

// Donate
$txt['Breeze_donate'] = 'Breeze is a free SMF modification brought to you by a PHP enthusiast on her free time.<p />If you like this modification and would like to show your appreciation, please consider making a <a href="http://missallsunday.com/">donation</a>. Your donation will be used to cover server costs and/or to buy shoes, shoes keeps the developer happy and if she is happy then there will be more updates ;)<p />You can also show your appreciation by letting me know you are using Breeze on your forum, come by, say hi and show me your shiny profile pages powered by Breeze.';
