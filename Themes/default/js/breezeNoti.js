/**
 * breeze.js
 *
 * The purpose of this file is to fetch notifications for the current user and display them.
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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
 * Portions created by the Initial Developer are Copyright (c) 2012, 2013
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

breezeNotifications = {};

breezeNotifications.stream = function(currentUser)
{
	// Make an ajax call to get all notifications for this user
	jQuery.ajax({
		type: 'GET',
		url: smf_scripturl + '?action=breezeajax;sa=fetchNoti;js=1;' + window.breeze_session_var + '=' + window.breeze_session_id + ';u=' + currentUser,
		cache: false,
		dataType: 'json',
		success: function(object)
		{
		},
		error: function (object)
		{
		},
	});
}
