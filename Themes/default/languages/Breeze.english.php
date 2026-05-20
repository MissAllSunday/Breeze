<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $txt;

// General strings
$txt['Breeze_generalWall'] = 'Wall';
$txt['Breeze_general_summary'] = 'Summary';
$txt['Breeze_load_more'] = 'Load more';
$txt['Breeze_admin'] = 'Admin panel';
$txt['Breeze_user_noti_settings_name'] = 'Alert Settings';
$txt['Breeze_general_my_wall_settings'] = 'My wall settings';
$txt['Breeze_general_delete'] = 'Delete';
$txt['Breeze_general_save'] = 'Save';
$txt['Breeze_general_send'] = 'Send';
$txt['Breeze_general_editing'] = 'Editing';
$txt['Breeze_general_previewing'] = 'Previewing';
$txt['Breeze_general_preview'] = 'Preview';
$txt['Breeze_general_close'] = 'Close';
$txt['Breeze_general_cancel'] = 'Cancel';
$txt['Breeze_general_goBack'] = 'Go Back';

// User Individual user settings
$txt['Breeze_breezeSettings_main_title'] = 'My wall settings';
$txt['Breeze_user_settings_main_desc'] = 'Configure your wall and other general settings';
$txt['Breeze_user_single_status'] = 'Single Status';
$txt['Breeze_user_settings_paginationNumber'] = 'How many status will be displayed per page';
$txt['Breeze_user_settings_paginationNumber_desc'] = 'Enter the number of status you want
 to display per page on your wall. By default is 5';
$txt['Breeze_user_settings_generalWall'] = 'Enable the general wall';
$txt['Breeze_user_settings_generalWall_desc'] = 'The general wall is a unique page where you can see the status
 and updates from your buddies. This setting allows you to enable/disable it';
$txt['Breeze_user_settings_submit'] = 'Submit';
$txt['Breeze_user_settings_wall'] = 'Enable my wall';
$txt['Breeze_user_settings_wall_desc'] = 'If you want to use your wall, you need to check this option,
otherwise your profile will show the default page';
$txt['Breeze_user_settings_aboutMe'] = 'Enable the About me tab';
$txt['Breeze_user_settings_aboutMe_desc'] = 'Leave empty to disable it. You can use BBC';
$txt['Breeze_user_settings_enableBuddiesTab'] = 'Enable the "Buddies" tab';
$txt['Breeze_user_settings_enableBuddiesTab_desc'] = 'This will show a tab showing all your buddies';
$txt['Breeze_user_settings_blockBuddyRequests'] = 'Block buddy requests from users in my ignore list';
$txt['Breeze_user_settings_blockBuddyRequests_desc'] = 'If enabled, users on your ignore list will not be able to send you buddy requests';
$txt['Breeze_user_modules_buddies_none'] = 'This user doesn\'t have any buddies';

// Likes
$txt['Breeze_error_likesLike'] = 'You aren\'t allowed to use the like feature';
$txt['Breeze_error_likesNotEnabled'] = 'Likes feature is not enable';
$txt['Breeze_error_likesTypeInvalid'] = 'Invalid like type';
$txt['Breeze_error_no_like'] = 'The content to be liked doesn\'t exists anymore';
$txt['Breeze_error_alreadyLiked'] = 'Cannot like an already liked content';
$txt['Breeze_success_likeSuccess'] = 'Your like has been given!';
$txt['Breeze_error_save_like'] = 'The like couldn\'t be inserted';
$txt['Breeze_likes_1'] = '%1$s person likes this.';
$txt['Breeze_likes_n'] = '%1$s people like this.';
$txt['Breeze_you_likes_0'] = 'You like this.';
$txt['Breeze_you_likes_1'] = 'You and %1$s other person like this.';
$txt['Breeze_you_likes_n'] = 'You and %1$s other people like this.';

// Time
$txt['Breeze_time_just_now'] = 'just now';
$txt['Breeze_time_second'] = 'second';
$txt['Breeze_time_ago'] = 'ago';
$txt['Breeze_time_minute'] = 'minute';
$txt['Breeze_time_hour'] = 'hour';
$txt['Breeze_time_day'] = 'day';
$txt['Breeze_time_month'] = 'month';
$txt['Breeze_time_year'] = 'year';

// Validate strings
$txt['cannot_view_general_wall'] = 'I\'m sorry, you are not allowed to see this user\'s wall';

// Ajax strings
$txt['Breeze_info_updated_settings'] = 'Your settings were updated successfully';
$txt['Breeze_error_deleteComments'] = 'I\'m sorry,  you aren\'t allowed to delete comments';
$txt['Breeze_error_deleteStatus'] = 'I\'m sorry,  you aren\'t allowed to delete status';
$txt['Breeze_error_server'] = 'There was an error: %s';
$txt['Breeze_error_generic'] = 'There was an error, please try again or contact your forum administrator';
$txt['Breeze_error_wrong_values'] = 'Wrong values were sent, the request could not be handled';
$txt['Breeze_error_flood'] = 'You have already reached the amount of messages you can post, please try again later';
$txt['Breeze_success_published_status'] = 'Your status was successfully published';
$txt['Breeze_success_published_comment'] = 'Your comment was successfully published!';
$txt['Breeze_success_deleted_comment'] = 'Your comment was successfully deleted!';
$txt['Breeze_error_empty'] = 'You need to type something!';
$txt['Breeze_error_malformed_data'] = 'Malformed data';
$txt['Breeze_error_incomplete_data'] = 'Incomplete data';
$txt['Breeze_error_invalid_users'] = 'Invalid user Ids';
$txt['Breeze_success_deleted_status'] = 'Your status has been deleted';
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
$txt['Breeze_error_no_valid_action'] = 'This is not a valid action';
$txt['Breeze_error_no_property'] = '%s isn\'t a valid call';
$txt['Breeze_error_no_access'] = 'I\'m sorry, you don\'t have access to this section';

// Loading text.
$txt['Breeze_profile_of_username'] = 'Profile of {name}';
$txt['Breeze_info_loading_end'] = 'There are no more status to display';
$txt['Breeze_page_no_status'] = 'There are no status to display'; // This will be replaced by conditional logic
$txt['Breeze_info_empty_data'] = 'There are no status to display'; // This will be replaced by conditional logic

// Tabs
$txt['Breeze_tabs_wall'] = 'Wall';
$txt['Breeze_tabs_about'] = 'About me';
$txt['Breeze_tabs_activity'] = 'Recent activity';
$txt['Breeze_tabs_buddies'] = 'Buddies';

// Errors
$txt['cannot_breeze_postStatus'] = $txt['Breeze_error_postStatus'];
$txt['cannot_breeze_postComments'] = $txt['Breeze_error_postComments'];
$txt['cannot_breeze_deleteStatus'] = $txt['Breeze_error_deleteStatus'] ;
$txt['cannot_breeze_deleteComments'] = $txt['Breeze_error_deleteComments'];
