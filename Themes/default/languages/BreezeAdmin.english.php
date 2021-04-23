<?php

declare(strict_types=1);

/**
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $txt;

// Admin UserSettingsController
$txt['Breeze_breezeAdmin_main_title'] = 'Breeze Admin Panel';
$txt['Breeze_breezeAdmin_main_description'] = 'This is your &quot;Breeze Admin Panel&quot;.  From here, you can edit the settings for Breeze If you have any trouble, feel free to <a href="https://missallsunday.com" target="_blank" class="new_win">ask for support</a> on the author\'s site.';
$txt['Breeze_breezeAdmin_permissions_title'] = 'Permissions';
$txt['Breeze_breezeAdmin_permissions_description'] = 'From here you can add/remove specific Breeze permissions.';
$txt['Breeze_breezeAdmin_settings_title'] = 'General settings';
$txt['Breeze_breezeAdmin_settings_description'] = 'This is the general settings page, from here you can enable/disable the mod as well as configuring general settings.';
$txt['Breeze_breezeAdmin_donate_title'] = 'Donate';
$txt['Breeze_breezeAdmin_donate_description'] = 'Boring stuff you were curious about it but you\'ll never gonna see it again :P';
$txt['Breeze_breezeAdmin_moodList_title'] = 'Mood List';
$txt['Breeze_breezeAdmin_moodList_description'] = 'A list showing all the current moods available, from here you can add/edit and delete moods.
<br>A mod can be any text-based emoji that can be copy/pasted as text string. Some emojis aren\'t supported by all browsers.';
$txt['Breeze_mood_noFile'] = 'Image mood was not found';
$txt['Breeze_page_mood_create'] = 'Add a new mood';
$txt['Breeze_page_mood_edit_create'] = 'Creating a new mood';
$txt['Breeze_page_mood_edit_update'] = 'Editing a mood';
$txt['Breeze_page_mood__noList'] = 'There aren\'t any moods to display';
$txt['Breeze_page_donate_exp'] = 'Breeze is a free and Open Source SMF modification. If you like this modification and would like to show your appreciation, please consider making a';
$txt['Breeze_page_donate_link'] = 'donation';
$txt['Breeze_page_credits'] = 'Credits';
$txt['Breeze_page_credits_decs'] = 'Breeze is brought to you by the following persons and/or scripts:';
$txt['Breeze_enable_general_wall'] = 'Enable UserSettingsController User';
$txt['Breeze_enable_general_wall_sub'] = 'If enable, a general wall will appear, in this general wall the user will be able to see his/her buddie\'s status and recent activity';
$txt['Breeze_menu_position'] = 'SelectType the position for the general User button.';
$txt['Breeze_menu_position_sub'] = 'By default is next to the home button.';

// SettingsTrait
$txt['Breeze_master'] = 'Enable Breeze mod';
$txt['Breeze_master_sub'] = 'The master setting, this must be enable for the mod to work properly.';
$txt['Breeze_forceWall'] = 'Check to force enable user\'s walls.';
$txt['Breeze_forceWall_sub'] = 'By default the wall is disable and users needs to enable it manually, if you check this option their wall will be enable, keep in mind this option will enable everyone\'s wall including inactive members and bots.<br /> They can still manually disable their wall if they want it, this option only enables it but does not really forces the wall to be always enable.';
$txt['Breeze_maxBuddiesNumber'] = 'How many buddies does an user can show on their buddies block?';
$txt['Breeze_maxBuddiesNumber_sub'] = 'If the user has more users than the specified setting, their entire list will be converted to a more compact links list. Leave at 0 to disable this option.';
$txt['Breeze_aboutMeMaxLength'] = 'The max length for the "about me" block ';
$txt['Breeze_aboutMeMaxLength_sub'] = 'If left empty, the mod will use the default value: 1024';
$txt['Breeze_enableMood'] = 'Enable the my mood feature';
$txt['Breeze_enableMood_sub'] = 'The user must have JavaScript enable on their machines for this to work properly for them.';
$txt['Breeze_moodLabel'] = 'A label used when showing the current user\'s mood';
$txt['Breeze_moodDefault'] = 'mood: ';
$txt['Breeze_moodLabel_sub'] = 'If left empty the mod will use the default value: "'. $txt['Breeze_moodDefault'] .'"';
$txt['Breeze_moodPlacement'] = 'The place inside the display area where the moods will be showed';
$txt['Breeze_moodPlacement_sub'] = 'The mod will display all possible locations but not all of them will be suitable for showing a mood.';
$txt['Breeze_maxFloodNum'] = 'How many messages can an user make';
$txt['Breeze_maxFloodNum_sub'] = 'The amount of total messages, including status and messages across all walls an user can make. Defaults to 10';
$txt['Breeze_maxFloodMinutes'] = 'The time frame for the amount of messages an user can make';
$txt['Breeze_maxFloodMinutes_sub'] = 'Defaults to 5 minutes. If you leave both options empty it will mean an user cannot make more than 10 messages in 5 minutes across all walls';

$txt['Breeze_version'] = 'Breeze version';
$txt['Breeze_vue_version'] = 'Vue version';
$txt['Breeze_live'] = 'Live from the support forum...';
$txt['Breeze_feed_error_message'] = 'Breeze couldn\'t connect with the support site';

// My mood feature.
$txt['Breeze_mood_info_create'] = 'The mood has been created.';
$txt['Breeze_mood_info_update'] = 'The mood has been updated.';
$txt['Breeze_mood_info_delete'] = 'The moods have been deleted.';
$txt['Breeze_mood_error_delete'] = 'The was a problem while trying to delete the moods.';
$txt['Breeze_mood_errors'] = 'Some errors were identified while trying to save this mood.<br/><ul>%1s</ul> ';
$txt['Breeze_mood_error_empty_emoji'] = '<li>The field emoji is required</li>';
$txt['Breeze_mood_error_invalid'] = '<li>The you are trying to edit does not exists</li>';
$txt['Breeze_mood_1'] = 'Active';
$txt['Breeze_mood_0'] = 'Inactive';
$txt['Breeze_mood_file'] = 'Filename';
$txt['Breeze_mood_emoji'] = 'Name';
$txt['Breeze_mood_name_sub'] = 'Optional. A name to help identify the icon. If left empty, the mod will use the filename.';
$txt['Breeze_mood_description'] = 'Description';
$txt['Breeze_mood_description_sub'] = 'Optional. A description that will appear whenever an user hover over the image, Don\'t use HTML. If left empty, the mod will use the filename.';
$txt['Breeze_mood_enable'] = 'Enable';
$txt['Breeze_mood_enable_sub'] = 'To enable/disable this mood, if let empty the user won\'t be able to select this mood.';
$txt['Breeze_mood_submit'] = 'Submit';
