<?php

/**
 * BreezeAdmin.english-utf8
 *
 * @package Breeze mod
 * @version 1.0.15
 * @author Michel Mendiola <suki@missallsunday.com>
 * @copyright Copyright (c) 2011 - 2018 Jessica Gonzalez
 * @license //www.mozilla.org/MPL/MPL-1.1.html
 */

global $txt;

// Admin Settings
$txt['Breeze_page_panel'] = 'Breeze Admin Panel';
$txt['Breeze_page_welcome'] = 'This is your &quot;Breeze Admin Panel&quot;.  From here, you can edit the settings for Breeze If you have any trouble, feel free to <a href="'. Breeze::$supportSite .'" target="_blank" class="new_win">ask for support</a> on the author\'s site.';
$txt['Breeze_page_main'] = 'Main Breeze Admin Center';
$txt['Breeze_page_permissions'] = 'Permissions';
$txt['Breeze_page_permissions_desc'] = 'From here you can add/remove specific Breeze permissions.';
$txt['Breeze_page_settings'] = 'General Settings';
$txt['Breeze_page_settings_desc'] = 'This is the general settings page, from here you can enable/disable the mod as well as configuring general settings.';
$txt['Breeze_page_donate'] = 'Donate';
$txt['Breeze_page_donate_desc'] = 'Boring stuff you were curious about it but you\'ll never gonna see it again :P';
$txt['Breeze_page_donate_exp'] = 'Breeze is a free SMF modification brought to you by a PHP enthusiast on her free time.<p />If you like this modification and would like to show your appreciation, please consider making a <a href="'. Breeze::$supportSite .'">donation</a>. Your donation will be used to cover server costs and/or to buy shoes, shoes keeps the developer happy and if she is happy then there will be more updates ;)<p />You can also show your appreciation by letting me know you are using Breeze on your forum, come by, say hi and show me your shiny profile pages powered by Breeze.';
$txt['Breeze_page_credits'] = 'Credits';
$txt['Breeze_page_credits_decs'] = 'Breeze is brought to you by the following persons and/or scripts:';
$txt['Breeze_enable_general_wall'] = 'Enable General Wall';
$txt['Breeze_enable_general_wall_sub'] = 'If enable, a general wall will appear, in this general wall the user will be able to see his/her buddie\'s status and recent activity';
$txt['Breeze_menu_position'] = 'Select the position for the general Wall button.';
$txt['Breeze_menu_position_sub'] = 'By default is next to the home button.';
$txt['Breeze_master'] = 'Enable Breeze mod';
$txt['Breeze_master_sub'] = 'The master setting, this must be enable for the mod to work properly.';
$txt['Breeze_force_enable'] = 'Check to force the enabling of walls.';
$txt['Breeze_force_enable_sub'] = 'By default the wall is disable and users needs to enable it manually, if you check this option their wall will be enable, keep in mind this option will enable everyone\'s wall including inactive members and bots.<br /> They can still manually disable their wall if they want it, this option only enables it but does not really forces the wall to be always enable.';
$txt['Breeze_force_enable_on'] = 'Enable';
$txt['Breeze_force_enable_off'] = 'Disable';
$txt['Breeze_notifications'] = 'Enable notifications';
$txt['Breeze_notifications_sub'] = 'If enable your users will be able to receive notifications and will be able to enable/disable them as they see fit.';
$txt['Breeze_parseBBC'] = 'Enable parsing BBC';
$txt['Breeze_parseBBC_sub'] = 'If enable, your users will be able to use BBC code on their status/comments.<br />Do note that enabling this option on very busy forums can slow down your server.';
$txt['Breeze_mention'] = 'Enable the mention feature.';
$txt['Breeze_mention_sub'] = 'Turn this on if you want people to be able to mention other users on their status and comments.';
$txt['Breeze_mention_limit'] = 'How many users can the poster mention on a single message?';
$txt['Breeze_mention_limit_sub'] = 'If the user tries to mention more users than allowed, only the first X will be converted to mentions where X is the number you specified. If left empty, the mod will use the default value: 10';
$txt['Breeze_posts_for_mention'] = 'How many posts are required to appear on the mention list?';
$txt['Breeze_posts_for_mention_sub'] = 'To avoid having spammer/bot accounts appearing on the mention list you can set how many posts does an user needs to have in order to be mentionable, if you leave this setting empty, it will use the default value: 1.<br /> To help with the server load, the mentions list gets cached, if you change this setting, make sure to clean your forum cache to see the results.';
$txt['Breeze_version'] = 'Breeze version';
$txt['Breeze_live'] = 'Live from the support forum...';
$txt['Breeze_allowed_actions'] = 'Write the actions where you wish the notification system shows up';
$txt['Breeze_allowed_actions_sub'] = 'By default, the notification system will appear on the following actions: '. implode(', ', Breeze::$_allowedActions) .'. Plus the BoardIndex, MessageIndex, Topic and Board pages. <br /> Please add your actions in a comma separated list, example: action, action, action, action';
$txt['Breeze_feed_error_message'] = 'Breeze couldn\'t connect with the support site';
$txt['Breeze_allowed_maxlength_aboutMe'] = 'The max length for the "about me" block ';
$txt['Breeze_allowed_maxlength_aboutMe_sub'] = 'If left empty, the mod will use the default value: 1024';
