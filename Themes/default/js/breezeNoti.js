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
	var number = 0;

	// Make an ajax call to get all notifications for this user
	jQuery.ajax({
		type: 'GET',
		url: smf_scripturl + '?action=breezeajax;sa=fetchNoti;js=1;' + window.breeze_session_var + '=' + window.breeze_session_id + ';u=' + currentUser,
		cache: false,
		dataType: 'json',
		success: function(noti)
		{
			if (jQuery.isEmptyObject(noti))
				return;

			// Loops for everyone!!
			jQuery.each(noti.data, function(i, item){

				number++;

				noty({
					text: item.message,
					timeout: 3500,
					type: 'notification',
					dismissQueue: true,
					layout: 'topRight',
					closeWith: ['button'],
					buttons: [
						{addClass: 'button_submit', text: breeze_noti_markasread, onClick: function($noty){
							jQuery.ajax({
								type: 'POST',
								url: smf_scripturl + '?action=breezeajax;sa=notimark;js=1;' + window.breeze_session_var + '=' + window.breeze_session_id,
								data: ({content : noti.id, user : noti.user}),
								cache: false,
								dataType: 'json',
								success: function(html){

									if(html.type == 'error'){
										noty({text: breeze_error_message, timeout: 3500, type: 'error'});
									}

									else if(html.type == 'ok'){
											noty({text: breeze_noti_markasread_after, timeout: 3500, type: 'success'});
									}
								},
								error: function (html){
										noty({text: breeze_error_message, timeout: 3500, type: 'error'});
								},
							});

								$noty.close();
						}},
						{addClass: 'button_submit', text: breeze_noti_delete, onClick: function($noty){
							jQuery.ajax({
								type: 'POST',
								url: smf_scripturl + '?action=breezeajax;sa=notidelete;js=1;' + window.breeze_session_var + '=' + window.breeze_session_id,
								data: ({content : noti.id, user : noti.user}),
								cache: false,
								dataType: 'json',
								success: function(html){
									if(html.type == 'error'){
										noty({text: html.data, timeout: 3500, type: 'error'});
									}

									else if(html.type == 'deleted'){
										noty({text: html.data, timeout: 3500, type: 'error'});
									}

									else if(html.type == 'ok'){
										noty({text: html.data, timeout: 3500, type: 'success'});
									}
								},
								error: function (html){
										noty({text: breeze_error_message, timeout: 3500, type: 'error'});
								},
							});
								$noty.close();
						}},
						{addClass: 'button_submit', text: breeze_noti_cancel, onClick: function($noty){
								$noty.close();
						}}
					]
				});
			});

			// Show a close all button
			noty({
				text: noti_closeAll,
				type: 'warning',
				dismissQueue: true,
				layout: 'topRight',
				closeWith: ['click'],
				callback: {
					afterClose: function() {
							jQuery.noty.closeAll();
					},
				onShow: function() {window.setTimeout("jQuery.noty.closeAll()", 5 * 1000 );},
				},
					});

			// Append the number of notifications to the wall button
			jQuery('#button_wall  a.firstlevel span').append(' ['+ number +']');
		},
		error: function (noti){
		},
	});
}
