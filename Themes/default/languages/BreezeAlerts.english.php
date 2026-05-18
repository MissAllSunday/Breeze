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

use Breeze\LikesEnum;


// types
$txt['Breeze_alert_' . LikesEnum::Comments->value] = 'comment';
$txt['Breeze_alert_' . LikesEnum::Status->value] = 'status';

// Gender related stuff.
$txt['Breeze_alert_gender_possessive_Female'] = 'her';
$txt['Breeze_alert_gender_possessive_Male'] = 'his';
$txt['Breeze_alert_gender_possessive_None'] = 'his/her';
$txt['Breeze_alert_gender_pronoun_Male'] = 'he';
$txt['Breeze_alert_gender_pronoun_Female'] = 'she';
$txt['Breeze_alert_gender_pronoun_None'] = 'he/she';

// Comment notification.
$txt['Breeze_alert_comment_different_owner'] = '{poster} commented on the status made by {status_poster} on {wall_owner}\'s wall';
$txt['Breeze_alert_comment_status_owner'] = '{poster} commented on your status made in {wall_owner}\'s wall';

// Comment deleted.
$txt['Breeze_alert_comment_deleted_different_owner'] = '{poster} deleted a comment on the status made by {status_poster} on {wall_owner}\'s wall';
$txt['Breeze_alert_comment_deleted_status_owner'] = '{poster} deleted a comment on your status made in {wall_owner}\'s wall';

// Someone posted a status on your wall.
$txt['Breeze_alert_status_owner'] = '{poster} posted a new status on your wall';

// Likes
$txt['Breeze_alert_like'] = '{poster} liked your {type}';

// Single Status
$txt['Breeze_singleStatus_pageTitle'] = 'Single Status';

// UserSettingsController.
$txt['alert_group_breezeComponents'] = 'My wall alert settings';
$txt['alert_Breeze_status_owner'] = 'When someone post a status on my wall';
$txt['alert_Breeze_comment_status_owner'] = 'When someone comment on a status I made';
$txt['alert_Breeze_comment_profile_owner'] = 'When someone comment on a status made on my wall';
$txt['alert_Breeze_like'] = 'When someone likes a comment or status I made on any wall';
$txt['alert_Breeze_mention'] = 'When someone mentions me on a comment or status on any wall';
