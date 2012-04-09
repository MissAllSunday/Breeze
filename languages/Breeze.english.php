<?php

/**
 * Breeze.english
 *
 * The purpose of this file is Provide the text strings
 * @package Breeze mod
 * @version 1.0 Beta 2
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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

/* Public/General strings */
$txt['BreezeMod_general_wall'] = 'Wall';
$txt['BreezeMod_general_summary'] = 'Summary';

$txt['BreezeMod_general_my_wall'] = 'My Wall';
$txt['BreezeMod_general_my_wall_settings'] = 'My Wall Settings';
$txt['BreezeMod_general_loading'] = 'Loading...';
$txt['BreezeMod_general_like'] = 'Like';
$txt['BreezeMod_general_delete'] = 'Delete';
$txt['BreezeMod_general_unlike'] = 'Unlike';
$txt['BreezeMod_general_plural'] = '(s)';

/* Modules */
$txt['BreezeMod_general_my_wall_modules'] = 'My Wall Modules settings';
$txt['BreezeMod_modules_enable_visitors_title'] = 'Recent Visitors';
$txt['BreezeMod_modules_enable_visitors_description'] = 'The last %1$s visitor%2$s to this page were: <p />';


/* User Individual Settings */
$txt['BreezeMod_profile'] = 'Wall Settings';
$txt['BreezeMod_user_settings_name'] = 'Wall Settings';
$txt['BreezeMod_user_buddysettings_name'] = 'Buddy Requests';
$txt['BreezeMod_user_buddyrequestmessage_name'] = 'Buddy Message Request Send';
$txt['BreezeMod_user_buddyrequestmessage_message'] = 'Your request has been submitted, the user will see your request and if he/she accepts it, you will receive a notification via private message.';
$txt['BreezeMod_user_notisettings_name'] = 'Notifications';
$txt['BreezeMod_user_settings_wall_limit'] = 'How many status per page to show?';
$txt['BreezeMod_user_settings_wall_limit_sub'] = 'This will be the number of status to show by default, max value is 30, if set it will create a pagination with the rest of your status.';
$txt['BreezeMod_user_settings_kick_ignored'] = 'Do not show my wall to users in my ignore list';
$txt['BreezeMod_user_settings_kick_ignored_sub'] = 'If this is enable, users on your ignore list will not be able to see your wall or to post on it.';
$txt['BreezeMod_user_settings_pagination_number'] = 'How many status will be displayed per page';
$txt['BreezeMod_user_settings_pagination_number_sub'] = 'Enter the number of status you want to display per page on your wall. By default is 5';
$txt['BreezeMod_user_settings_enable_wall'] = 'Enable my wall';
$txt['BreezeMod_user_settings_enable_wall_sub'] = 'If you want to use your wall, you need to check this option, otherwise your profile will show the default page.';
$txt['BreezeMod_user_settings_enable_visits_module'] = 'Enable the visits module?';
$txt['BreezeMod_user_settings_enable_visits_module_sub'] = 'If this is enable, a block with the latest user who visited your wall will appear.';
$txt['BreezeMod_user_settings_visits_module_timeframe'] = 'The time frame to count the vistis from.';
$txt['BreezeMod_user_settings_visits_module_timeframe_sub'] = 'By default is set to aweek, that means the module will show the latest visits to your wall in the last week.';


/* Modules */
$txt['BreezeMod_user_settings_enable_buddies'] = 'Enable the "Buddies" module.';
$txt['BreezeMod_user_settings_enable_buddies_sub'] = 'This will show a block showing all your buddies.';
$txt['BreezeMod_user_settings_how_many_buddies'] = 'How many buddies to display';
$txt['BreezeMod_user_settings_how_many_buddies_sub'] = 'If empty it will show the default value 10, max value is 30.';
$txt['BreezeMod_user_settings_visitors'] = 'Enable the "Latest Visitors" module.';
$txt['BreezeMod_user_settings_visitors_sub'] = 'This will show a block with the latest visitors to your wall.';
$txt['BreezeMod_user_settings_notification_pm'] = 'Send me a pm when someone else mentions me in any wall.';
$txt['BreezeMod_user_settings_notification_pm_sub'] = 'This will send a private message per status or comment.';
$txt['BreezeMod_user_settings_how_many_visitors'] = 'How many visitors to display';
$txt['BreezeMod_user_settings_how_many_visitors_sub'] = 'If empty it will show the default value 10, max value is 30.';
$txt['BreezeMod_user_settings_time_frame'] = 'The time frame to consider when fetching the visitors';
$txt['BreezeMod_user_settings_time_frame_sub'] = 'For example, if you select "Last Week" then the module will show the users who had visited your wall in the last week.';
$txt['BreezeMod_user_settings_time_hour'] = 'Last Hour';
$txt['BreezeMod_user_settings_time_day'] = 'Last Day';
$txt['BreezeMod_user_settings_time_week'] = 'Last Week';
$txt['BreezeMod_user_settings_time_month'] = 'Last Month';
$txt['BreezeMod_user_settings_time_year'] = 'Last Year';
$txt['BreezeMod_user_settings_show_avatar'] = 'Show avatar in list';
$txt['BreezeMod_user_settings_show_avatar_sub'] = 'By default it shows the user link only.';
$txt['BreezeMod_user_settings_show_last_visit'] = 'Show the last time the users visited your wall';
$txt['BreezeMod_user_settings_notification_wall'] = 'Send me a pm when someone else post a new status on my wall';
$txt['BreezeMod_user_settings_notification_wall_sub'] = 'This will only apply for new status.';
$txt['BreezeMod_user_permissions_name'] = 'Permissions';
$txt['BreezeMod_user_modules_name'] = 'Modules';


/* Admin Settings */
$txt['BreezeMod_admin_settings_admin_panel'] = 'Breeze Admin Panel';
$txt['BreezeMod_admin_welcome'] = 'This is your &quot;%1$s&quot;.  From here, you can edit the settings for Breeze If you have any trouble, feel free to <a href="http://missallsunday.com" target="_blank" class="new_win">ask for support</a> on the author\'s site.';
$txt['BreezeMod_admin_settings_main'] = 'Main breeze Admin Center';
$txt['BreezeMod_admin_settings_settings'] = 'breeze General Settings';
$txt['BreezeMod_admin_settings_donate'] = 'Donate';
$txt['BreezeMod_admin_settings_server_needs'] = '<div class="breeze_error_message">Your Server does not support %s, contact your hosting provider and ask for it to be enable, otherwise you won\'t be able to use this mod at full.</div>';
$txt['BreezeMod_admin_settings_json'] = 'JSON library';
$txt['BreezeMod_admin_settings_php'] = '<div class="breeze_error_message">Your PHP version: ( %s ) is lower than the minimum required by this mod: 5.3, you won\'t be able to use this mod at full.</div>';
$txt['BreezeMod_admin_settings_php_ok'] = '<div class="breeze_ok_message">Your PHP version is supported.</div>';
$txt['BreezeMod_admin_settings_server_needs_ok'] = '<div class="breeze_ok_message"> %s is supported by your server.</div>';
$txt['BreezeMod_admin_settings_tab_server_specs'] = 'Server Requirements';
$txt['BreezeMod_admin_settings_tab_admin_logs'] = 'Admin Logs';
$txt['BreezeMod_admin_settings_donate'] = 'Donate';
$txt['BreezeMod_admin_settings_donate_text'] = 'Boring stuff you wil never see...';
$txt['BreezeMod_admin_settings_enablegeneralwall'] = 'Enable General Wall';
$txt['BreezeMod_admin_settings_enablegeneralwall_sub'] = 'If enable, a generai wall will apear, in this general wall the user will be able to see his/her buddie\'s status and recent activity';
$txt['BreezeMod_admin_settings_menuposition'] = 'Select the position for the general Wall button.';
$txt['BreezeMod_admin_settings_menuposition_sub'] = 'By default is next to the home button.';
$txt['BreezeMod_admin_settings_enable'] = 'Enable the Breeze mod';
$txt['BreezeMod_admin_settings_enable_sub'] = 'The master setting, this must be enable for the mod to work properly.';
$txt['BreezeMod_admin_settings_home'] = 'Home';
$txt['BreezeMod_admin_settings_help'] = 'Help';
$txt['BreezeMod_admin_settings_profile'] = 'Profile';
$txt['BreezeMod_admin_enable_limit'] = 'Enable the query limit';
$txt['BreezeMod_admin_enable_limit_sub'] = 'If you have problems with resources, you can enable the query limit, this will load only a fraction of the total of status/comments and can help to reduce server stress.';
$txt['BreezeMod_admin_limit_timeframe'] = 'Set the limit in time to fetch status and comments from';
$txt['BreezeMod_admin_limit_timeframe_sub'] = 'This will fetch the status/comments only from the past selected option, for example, the last week, the last month, etc.';
$txt['BreezeMod_admin_breeze_version'] = 'Breeze version';
$txt['BreezeMod_admin_live'] = 'Live from the suport forum...';


/* Time */
$txt['BreezeMod_time_just_now'] = 'just now.';
$txt['BreezeMod_time_second'] = 'second';
$txt['BreezeMod_time_ago'] = 'ago.';
$txt['BreezeMod_time_minute'] = 'minute';
$txt['BreezeMod_time_hour'] = 'hour';
$txt['BreezeMod_time_day'] = 'day';
$txt['BreezeMod_time_month'] = 'month';
$txt['BreezeMod_time_year'] = 'year';

/* Permissions strings */
$txt['cannot_view_general_wall'] = 'I\'m sorry, you are not allowed to view the Wall.';
$txt['permissiongroup_simple_breeze_per_simple'] = 'Breeze mod permissions';
$txt['permissiongroup_breeze_per_classic'] = 'Breeze mod permissions';
$txt['permissionname_breeze_deleteStatus'] = 'Delete all status/comments on any wall';
$txt['permissionname_breeze_postStatus'] = 'Post new Status on any wall';
$txt['permissionname_breeze_postComments'] = 'Post new Comments on any wall';
$txt['permissionname_breeze_edit_settings_any'] = 'Edit the user settings of any wall';

/* Ajax strings */
$txt['BreezeMod_feed_error_message'] = 'Breeze couldn\'t connect with the support site';
$txt['BreezeMod_error_message'] = 'There was an error, please try again or contact the forum admin.';
$txt['BreezeMod_success_message'] = 'Your message was successfully published';
$txt['BreezeMod_empty_message'] = 'You need to type something in the textbox.';
$txt['BreezeMod_success_delete'] = 'Your comment has been deleted';
$txt['BreezeMod_confirm_delete'] = 'Do you really want to delete this?';
$txt['BreezeMod_confirm_yes'] = 'Yes';
$txt['BreezeMod_confirm_cancel'] = 'Cancel';
$txt['BreezeMod_already_deleted'] = 'This comment/status was already deleted.';
$txt['BreezeMod_cannot_postStatus'] = 'I\'m sorry,  you aren\'t allowed to post new Status.';
$txt['BreezeMod_cannot_postComments'] = 'I\'m sorry,  you aren\'t allowed to post new Comments.';

/* Errors */
$txt['cannot_breeze_postStatus'] = $txt['BreezeMod_cannot_postStatus'];
$txt['cannot_breeze_postComments'] = $txt['BreezeMod_cannot_postComments'];
$txt['cannot_breeze_deleteStatus'] = 'I\'m sorry,  you aren\'t allowed to delete Status/Comments.';

/* Pagination */
$txt['BreezeMod_pag_previous'] = 'previous';
$txt['BreezeMod_pag_next'] = 'next';
$txt['BreezeMod_pag_first'] = 'First';
$txt['BreezeMod_pag_last'] = 'Last';

/* Notifications */
$txt['BreezeMod_noti_title'] = 'Notifications';
$txt['BreezeMod_noti_buddy_title'] = 'Buddy notification';
$txt['BreezeMod_noti_buddy_message'] = 'The user %s has added you as his/her buddy, please confirm this request.';

/* Mentions */
$txt['BreezeMod_mention_message'] = 'You have been mentioned by %1$s on %2$s\'s wall!';
$txt['BreezeMod_mention_message_own_wall'] = 'You have been mentioned on %s own wall!';

/* Buddy List */
$txt['BreezeMod_buddyrequest_error_doublerequest'] = 'You already sent a buddy request, please wait for the user\'s response.';
$txt['BreezeMod_buddyrequest_error_dunno'] = 'Something went wrong, please contact the forum admin.';
$txt['BreezeMod_buddy_messagerequest_message'] = '%s wants to be your buddy!';
$txt['BreezeMod_buddy_title'] = 'Buddy List';
$txt['BreezeMod_buddy_desc'] = 'From here you can confirm or decline your buddy request. If you confirm the buddy request, a pm on your behalf wil be send to the user, if you decine the request the user will not receive anything';
$txt['BreezeMod_buddyrequest_title'] = 'Buddy requests';
$txt['BreezeMod_buddyrequest_list_status'] = 'Status';
$txt['BreezeMod_buddyrequest_list_message'] = 'Message';
$txt['BreezeMod_buddyrequest_list_confirm'] = 'Confirm';
$txt['BreezeMod_buddyrequest_list_decline'] = 'Decline';
$txt['BreezeMod_buddyrequest_confirmed_subject'] = 'Buddy request accepted.';
$txt['BreezeMod_buddyrequest_confirmed_message'] ='%s has confirmed your buddy request';
$txt['BreezeMod_buddyrequest_confirmed_inner_message'] = 'You successfully confirmed the request';
$txt['BreezeMod_buddyrequest_confirmed_inner_message_de'] = 'You successfully declined the request';