<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $txt;

// General strings
$txt['Breeze_breeze_main_title'] = 'My wall';
$txt['Breeze_general_wall_page'] = 'page';
$txt['Breeze_general_summary'] = 'Summary';
$txt['Breeze_load_more'] = 'Load more';
$txt['Breeze_admin'] = 'Admin panel';
$txt['Breeze_general_my_wall_settings'] = 'My wall settings';
$txt['Breeze_general_loading'] = 'Loading..';
$txt['Breeze_general_like'] = 'Like';
$txt['Breeze_general_delete'] = 'Delete';
$txt['Breeze_general_save'] = 'Save';
$txt['Breeze_general_editing'] = 'Editing';
$txt['Breeze_general_close'] = 'Close';
$txt['Breeze_general_cancel'] = 'Cancel';
$txt['Breeze_general_posted_on'] = 'Posted on %s\'s User';

// User Individual user settings
$txt['Breeze_breezeSettings_main_title'] = 'My wall settings';
$txt['Breeze_user_settings_main_desc'] = 'Configure your wall and other general settings';
$txt['Breeze_user_buddysettings_name'] = 'Buddy Requests';
$txt['Breeze_user_single_status'] = 'Single Status';
$txt['Breeze_user_settings_paginationNumber'] = 'How many status will be displayed per page';
$txt['Breeze_user_settings_paginationNumber_desc'] = 'Enter the number of status you want
 to display per page on your wall. By default is 5';
$txt['Breeze_user_settings_generalWall'] = 'Enable the general wall';
$txt['Breeze_user_settings_generalWall_desc'] = 'The general wall is a unique page where you can see the status
 and updates from your buddies. This setting allows you to enable/disable it';
$txt['Breeze_user_settings_submit'] = 'Submit';
$txt['Breeze_user_settings_cancel'] = 'Cancel';
$txt['Breeze_user_settings_checkAll'] = 'select all options';
$txt['Breeze_user_settings_wall'] = 'Enable my wall';
$txt['Breeze_user_settings_wall_desc'] = 'If you want to use your wall, you need to check this option,
otherwise your profile will show the default page';
$txt['Breeze_user_settings_aboutMe'] = 'Enable the About me tab';
$txt['Breeze_user_settings_aboutMe_desc'] = 'Leave empty to disable it. You can use BBC';
$txt['Breeze_user_settings_buddies'] = 'Enable the "Buddies" block';
$txt['Breeze_user_settings_buddies_desc'] = 'This will show a block div showing all your buddies and their info';
$txt['Breeze_user_settings_kickIgnored'] = 'Do not show my wall to users in my ignore list';
$txt['Breeze_user_settings_kickIgnored_desc'] = 'If this is enable, users on your ignore list will not be able to
 see your wall or to post on it';
$txt['Breeze_user_permissions_name'] = 'Validate';
$txt['Breeze_user_modules_visitors_none'] = 'There are no recent visitors';
$txt['Breeze_user_modules_buddies_none'] = 'This user doesn\'t have any buddies';
$txt['Breeze_noti_none'] = 'You don\'t have any alerts yet';

// Time
$txt['Breeze_time_just_now'] = 'just now';
$txt['Breeze_time_second'] = 'second';
$txt['Breeze_time_ago'] = 'ago';
$txt['Breeze_time_minute'] = 'minute';
$txt['Breeze_time_hour'] = 'hour';
$txt['Breeze_time_day'] = 'day';
$txt['Breeze_time_week'] = 'week';
$txt['Breeze_time_month'] = 'month';
$txt['Breeze_time_year'] = 'year';

// Validate strings
$txt['cannot_view_general_wall'] = 'I\'m sorry, you are not allowed to see this user\'s wall';

// Ajax strings
$txt['Breeze_info_updated_settings'] = 'Your settings were updated successfully';
$txt['Breeze_error_deleteComments'] = 'I\'m sorry,  you aren\'t allowed to delete comments';
$txt['Breeze_error_deleteStatus'] = 'I\'m sorry,  you aren\'t allowed to delete status';
$txt['Breeze_error_server'] = 'There was an error: %s';
$txt['Breeze_error_wrong_values'] = 'Wrong values were sent, the request couldn\'t be handled';
$txt['Breeze_error_flood'] = 'You have already reached the amount of messages you can post, please try again later';
$txt['Breeze_info_published_status'] = 'Your status was successfully published';
$txt['Breeze_info_published_comment'] = 'Your comment was successfully published!';
$txt['Breeze_info_deleted_comment'] = 'Your comment was successfully deleted!';
$txt['Breeze_error_empty'] = 'You need to type something!';
$txt['Breeze_error_malformed_data'] = 'Malformed data';
$txt['Breeze_error_incomplete_data'] = 'Incomplete data';
$txt['Breeze_error_invalid_users'] = 'Invalid user Ids';
$txt['Breeze_info_deleted_status'] = 'Your status has been deleted';
$txt['Breeze_error_no_status'] = 'The status doesn\'t exists anymore';
$txt['Breeze_error_no_comment'] = 'The comment doesn\'t exists anymore';
$txt['Breeze_error_save_comment'] = 'The comment couldn\'t be inserted';
$txt['Breeze_error_save_status'] = 'The status couldn\'t be inserted';
$txt['Breeze_error_already_deleted_status'] = 'This status was already deleted. Try refreshing your browser';
$txt['Breeze_error_already_deleted_comment'] = 'This comment was already deleted. Try refreshing your browser';
$txt['Breeze_error_already_deleted_noti'] = 'This notification was already deleted. Try refreshing your browser';
$txt['Breeze_error_already_marked_noti'] = 'This notification was marked as read already. Try refreshing your browser';
$txt['Breeze_error_no_validator'] = 'There isn\'t a validator registered for this call';
$txt['Breeze_error_postStatus'] = 'You aren\'t allowed to post status';
$txt['Breeze_error_postComments'] = 'You aren\'t allowed to post comments';
$txt['Breeze_error_deleteStatus'] = 'I\'m sorry,  you aren\'t allowed to delete status';
$txt['Breeze_error_deleteComments'] = 'I\'m sorry,  you aren\'t allowed to delete comments';
$txt['Breeze_error_no_valid_action'] = 'This is not a valid action';
$txt['Breeze_error_no_property'] = '%s isn\'t a valid call';
$txt['Breeze_error_no_access'] = 'I\'m sorry, you don\'t have access to this section';
$txt['Breeze_info_noti_unmarkasread_after'] = 'You have successfully marked this notification as unread';
$txt['Breeze_info_noti_markasread_after'] = 'You have successfully marked this notification as read';
$txt['Breeze_error_noti_markasreaddeleted_after'] = 'This notification was already deleted or is not a valid entry';
$txt['Breeze_error_noti_markasreaddeleted'] = 'This notification was already deleted or is not a valid entry';
$txt['Breeze_info_noti_delete_after'] = 'You have successfully deleted this notification';
$txt['Breeze_info_noti_visitors_clean'] = 'You have successfully cleaned your visitors log';
$txt['Breeze_info_notiMulti_delete_after'] = 'You have successfully deleted all notifications';
$txt['Breeze_info_notiMulti_markasread_after'] = 'You have successfully marked as read all notifications';
$txt['Breeze_info_notiMulti_unmarkasread_after'] = 'You have successfully marked as unread all notifications';

// Loading text.
$txt['Breeze_profile_of_username'] = 'Profile of {name}';
$txt['Breeze_info_loading_end'] = 'There are no more status to display';
$txt['Breeze_info_loadingAlerts_end'] = 'There are no more alerts to display';
$txt['Breeze_page_no_status'] = 'There are no status to display';

// Tabs
$txt['Breeze_tabs_wall'] = 'Wall';
$txt['Breeze_error_wall_none'] = 'This user doesn\'t have any status yet!';
$txt['Breeze_tabs_post'] = 'Leave a message';
$txt['Breeze_tabs_about'] = 'About me';
$txt['Breeze_tabs_activity'] = 'Recent activity';
$txt['Breeze_tabs_buddies'] = 'Buddies';
$txt['Breeze_tabs_views'] = 'Profile Visitors';
$txt['Breeze_tabs_pinfo'] = 'Profile Info';
$txt['Breeze_tabs_activity'] = 'Recent activity';
$txt['Breeze_tabs_activity_none'] = 'This user doesn\'t have any activities recorded';
$txt['Breeze_tabs_activity_buddies_none'] = 'Your buddies doesn\'t have any activities recorded';
$txt['Breeze_tabs_about'] = 'About me';
$txt['Breeze_goTop'] = 'Go to top';

// Mood feature.
$txt['Breeze_mood_emoji'] = 'Emoji';
$txt['Breeze_mood_description'] = 'Description';
$txt['Breeze_mood_enable'] = 'Enable';
$txt['Breeze_mood_newMood'] = 'New mood';
$txt['Breeze_mood_createNewMood'] = 'Create a new mood';
$txt['Breeze_moodLabel'] = 'mood';
$txt['Breeze_moodChange'] = 'Change your mood';
$txt['Breeze_info_moodChanged'] = 'Your mood has been changed';
$txt['Breeze_info_moodCreated'] = 'The new mood has been successfully created!';
$txt['Breeze_info_moodDeleted'] = 'Your mood was successfully deleted!';
$txt['Breeze_error_moodCreated'] = 'You aren\'t allowed to create moods';
$txt['Breeze_error_moodGet'] = 'There was a problem getting the moods';
$txt['Breeze_error_no_mood'] = 'The mood doesn\'t exists anymore';
$txt['Breeze_error_validEmoji'] = 'Please use a valid emoji';
$txt['Breeze_error_emptyEmoji'] = 'Emoji field cannot be empty';

// Buddy request.
$txt['Breeze_buddy_title'] = 'Buddy request';
$txt['Breeze_buddy_confirm'] = 'The invitation has been sent, {receiver} will soon receive an alert';
$txt['Breeze_buddy_sender_message_title'] = '{sender} sent the following message';
$txt['Breeze_buddy_chose_title'] = '{sender} wants to be your buddy!';
$txt['Breeze_buddy_chose'] = 'Please <a href="{href_confirm}"><i class="fa fa-user-plus fa-2x"></i> confirm</a> or
 <a href="{href_decline}"><i class="fa fa-user-times fa-2x"></i> decline</a> the invitation';
$txt['Breeze_buddy_already_buddy'] = 'You and {receiver} are already buddies';
$txt['Breeze_buddy_blocked'] = 'You cannot send an invitation to this user';
$txt['Breeze_buddy_already_blocked'] = 'You already added this user to your block list';
$txt['Breeze_buddy_decline'] = 'You have declined the invitation.<br>
Do you want to block this person from sending you more invites? this WILL NOT put this person on your ignore list,
 it will merely prevent this user from sending you a buddy invite<br><a href="{href}">yes, block this user</a>';
$txt['Breeze_buddy_blocked_done'] = 'You have successfully blocked this user';
$txt['Breeze_buddy_delete_done'] = 'You and {receiver} are no longer buddies';
$txt['Breeze_buddy_confirmed_done'] = 'You have successfully confirmed the buddy request';
$txt['Breeze_buddy_already_sent'] = 'You already sent an invitation, please wait for {receiver} to respond';
$txt['Breeze_buddy_error'] = 'There was an error, please try again';
$txt['Breeze_buddy_message'] = 'Buddy message';
$txt['Breeze_buddy_message_desc'] = 'You can send {receiver} a message along with your buddy invite. <br>
 no HTML or BBC is allowed. Leave the field empty if you do not want to send a message';

// Errors
$txt['cannot_breeze_postStatus'] = $txt['Breeze_error_postStatus'];
$txt['cannot_breeze_postComments'] = $txt['Breeze_error_postComments'];
$txt['cannot_breeze_deleteStatus'] = $txt['Breeze_error_deleteStatus'] ;
$txt['cannot_breeze_deleteComments'] = $txt['Breeze_error_deleteComments'];
$txt['cannot_breeze_moodCreated'] = $txt['Breeze_error_moodCreated'];
