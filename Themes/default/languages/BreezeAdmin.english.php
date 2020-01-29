<?php

declare(strict_types=1);

/**
 * BreezeAdmin.english
 *
 * @package Breeze mod
 * @version 1.1
 * @author Jessica Gonzalez <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2015, Jessica Gonzalez
 * @license http://www.mozilla.org/MPL/ MPL 2.0
 */

global $txt;

// Admin General
$txt['Breeze_page_panel'] = 'Breeze Admin Panel';
$txt['Breeze_page_welcome'] = 'This is your &quot;Breeze Admin Panel&quot;.  From here, you can edit the settings for Breeze If you have any trouble, feel free to <a href="http://missallsunday.com" target="_blank" class="new_win">ask for support</a> on the author\'s site.';
$txt['Breeze_page_main'] = 'Main Breeze Admin Center';
$txt['Breeze_page_permissions'] = 'Validate';
$txt['Breeze_page_permissions_desc'] = 'From here you can add/remove specific Breeze permissions.';
$txt['Breeze_page_settings'] = 'General General';
$txt['Breeze_page_settings_desc'] = 'This is the general settings page, from here you can enable/disable the mod as well as configuring general settings.';
$txt['Breeze_page_donate'] = 'Donate';
$txt['Breeze_page_donate_desc'] = 'Boring stuff you were curious about it but you\'ll never gonna see it again :P';
$txt['Breeze_page_cover'] = 'Cover settings';
$txt['Breeze_page_cover_desc'] = 'Cover settings page';
$txt['Breeze_page_mood'] = 'Mood List';
$txt['Breeze_page_mood_desc'] = 'A list showing all the current moods available, from here you can add/edit and delete moods.<br>Remember that you need to upload your images to the correct folder <strong>before</strong> adding new moods.';
$txt['Breeze_page_mood_create'] = 'Add a new mood';
$txt['Breeze_page_mood_edit_create'] = 'Creating a new mood';
$txt['Breeze_page_mood_edit_create_desc'] = 'Remember that you need to upload your images to the correct folder <strong>before</strong> adding new moods';
$txt['Breeze_page_mood_edit_update'] = 'Editing a mood';
$txt['Breeze_page_mood_edit_update_desc'] = 'Remember that you need to upload your images to the correct folder <strong>before</strong> adding new moods';
$txt['Breeze_page_mood__noList'] = 'There aren\'t any moods to display';
$txt['Breeze_page_donate_exp'] = 'Breeze is a free SMF modification brought to you by a PHP enthusiast on her free time.<p />If you like this modification and would like to show your appreciation, please consider making a <a href="http://missallsunday.com/">donation</a>. Your donation will be used to cover server costs and/or to buy shoes, shoes keeps the developer happy and if she is happy then there will be more updates ;)<p />You can also show your appreciation by letting me know you are using Breeze on your forum, come by, say hi and show me your shiny profile pages powered by Breeze.';
$txt['Breeze_page_credits'] = 'Credits';
$txt['Breeze_page_credits_decs'] = 'Breeze is brought to you by the following persons and/or scripts:';
$txt['Breeze_enable_general_wall'] = 'Enable General User';
$txt['Breeze_enable_general_wall_sub'] = 'If enable, a general wall will appear, in this general wall the user will be able to see his/her buddie\'s status and recent activity';
$txt['Breeze_menu_position'] = 'Select the position for the general User button.';
$txt['Breeze_menu_position_sub'] = 'By default is next to the home button.';
$txt['Breeze_master'] = 'Enable Breeze mod';
$txt['Breeze_master_sub'] = 'The master setting, this must be enable for the mod to work properly.';
$txt['Breeze_force_enable'] = 'Check to force the enabling of walls.';
$txt['Breeze_force_enable_sub'] = 'By default the wall is disable and users needs to enable it manually, if you check this option their wall will be enable, keep in mind this option will enable everyone\'s wall including inactive members and bots.<br /> They can still manually disable their wall if they want it, this option only enables it but does not really forces the wall to be always enable.';
$txt['Breeze_force_enable_on'] = 'Enable';
$txt['Breeze_force_enable_off'] = 'Disable';
$txt['Breeze_mood'] = 'Enable the my mood feature';
$txt['Breeze_mood_sub'] = 'The user must have JavaScript enable on their machines for this to work properly for them.';
$txt['Breeze_mood_label'] = 'A label used when showing the current user\'s mood';
$txt['Breeze_mood_label_sub'] = 'If left empty the mod will use the default value "mood"';
$txt['Breeze_mood_placement'] = 'The place inside the display area where the moods will be showed';
$txt['Breeze_mood_placement_sub'] = 'The mod will display all possible locations but not all of them will be suitable for showing a mood.';
$txt['Breeze_cover'] = 'Enable the image cover on wall feature.';
$txt['Breeze_cover_sub'] = 'Your users will be able to upload an image to serve as a cover on their own wall, there is a separate permission for this so be sure to enable this feature and then give the appropriate permissions for your users.';
$txt['Breeze_cover_max_image_size'] = 'The max file size an uploaded image can has.';
$txt['Breeze_cover_max_image_size_sub'] = 'In kB (kilobyte, 1 kB = 1000 bytes), if no size is given, the mod will use the default value: 250.';
$txt['Breeze_cover_max_image_width'] = 'The max width size an uploaded image can has.';
$txt['Breeze_cover_max_image_width_sub'] = 'In pixels, a number without the px bit, If no value is given, the mod will use the default value: 1500';
$txt['Breeze_cover_max_image_height'] = 'The max height size an uploaded image can has.';
$txt['Breeze_cover_max_image_height_sub'] = 'In pixels, a number without the px bit, If no value is given, the mod will use the default value: 500';
$txt['Breeze_cover_image_types'] = 'A list of allowed image types';
$txt['Breeze_cover_image_types_sub'] = 'A comma separated list of valid image types, if left empty the mod will use the default value: jpg,jpeg,png';
$txt['Breeze_posts_for_mention'] = 'How many posts are required to appear on the mention list?';
$txt['Breeze_posts_for_mention_sub'] = 'To avoid having spammer/bot accounts appearing on the mention list you can set how many posts does an user needs to have in order to be mentionable, if you leave this setting empty, it will use the default value: 1.<br /> To help with the server load, the mentions list gets cached, if you change this setting, make sure to clean your forum cache to see the results.';
$txt['Breeze_version'] = 'Breeze version';
$txt['Breeze_live'] = 'Live from the support forum...';
$txt['Breeze_feed_error_message'] = 'Breeze couldn\'t connect with the support site';
$txt['Breeze_allowed_max_num_users'] = 'How many users does an user can show on their visitors and buddies block?';
$txt['Breeze_allowed_max_num_users_sub'] = 'If the user has more users than the specified setting, their entire list will be converted to a more compact links list. Leave at 0 to disable this option.';
$txt['Breeze_allowed_maxlength_aboutMe'] = 'The max length for the "about me" block ';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'If left empty, the mod will use the default value: 1024';
$txt['Breeze_flood_messages'] = 'How many messages can an user make';
$txt['Breeze_flood_messages_sub'] = 'The amount of total messages, including status and messages across all walls an user can make. Defaults to 10';
$txt['Breeze_flood_minutes'] = 'The time frame for the amount of messages an user can make';
$txt['Breeze_flood_minutes_sub'] = 'Defaults to 5 minutes. If you leave both options empty it will mean an user cannot make more than 10 messages in 5 minutes across all walls';

// My mood feature.
$txt['Breeze_mood_success_create'] = 'The mood has been created.';
$txt['Breeze_mood_success_update'] = 'The mood has been updated.';
$txt['Breeze_mood_success_delete'] = 'The moods have been deleted.';
$txt['Breeze_mood_errors'] = 'Some errors were identified while trying to save this mood. ';
$txt['Breeze_mood_error_file'] = 'file doesn\'t exists';
$txt['Breeze_mood_error_already'] = 'Theres already a mood associated with this filename. Please use another image.';
$txt['Breeze_mood_error_path'] = 'The path to the file is incorrect';
$txt['Breeze_mood_error_extension'] = 'The file doesn\'t have an extension or it has an invalid one';
$txt['Breeze_mood_image'] = 'Image';
$txt['Breeze_mood_enable'] = 'Enable';
$txt['Breeze_mood_disable'] = 'Disable';
$txt['Breeze_mood_file'] = 'Filename';
$txt['Breeze_mood_file_sub'] = 'The exact filename including the extension, remember, the file already has to be uploaded to your moods folder. This field is mandatory';
$txt['Breeze_mood_name'] = 'Name';
$txt['Breeze_mood_name_sub'] = 'Optional. A name to help identify the icon. If left empty, the mod will use the filename.';
$txt['Breeze_mood_description'] = 'Description';
$txt['Breeze_mood_description_sub'] = 'Optional. A description that will appear whenever an user hover over the image, Don\'t use HTML. If left empty, the mod will use the filename.';
$txt['Breeze_mood_enable'] = 'Enable';
$txt['Breeze_mood_enable_sub'] = 'To enable/disable this mood, if let empty the user won\'t be able to select this mood.';
$txt['Breeze_mood_submit'] = 'Submit';
