/*
 Copyright (c) 2011 - 2018 Jessica González
 @license //www.mozilla.org/MPL/MPL-1.1.html
*/

breeze.tools.stream = function(currentUser)
{
	var number = 0;

	// Make an ajax call to get all notifications for this user.
	jQuery.ajax({
		type: 'GET',
		url: smf_scripturl + '?action=breezeajax;sa=fetchNoti;js=1;' + breeze.session.v + '=' + breeze.session.id + ';u=' + currentUser,
		cache: false,
		dataType: 'json',
		success: function(noti)
		{
			if (noti.data == '')
				return;

			// (Froot) Loops for everyone!!
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
						{addClass: 'button_submit', text: breeze.text.noti_markasread, onClick: function($noty){
							jQuery.ajax({
								type: 'POST',
								url: smf_scripturl + '?action=breezeajax;sa=notimark;js=1;' + breeze.session.v + '=' + breeze.session.id,
								data: ({content : item.id, user : item.user}),
								cache: false,
								dataType: 'json',
								success: function(html){
										noty({text: html.message, timeout: 3500, type: html.type});
								},
								error: function (html){
										noty({text: breeze.text.error_wrong_values, timeout: 3500, type: 'error'});
								}
							});

								$noty.close();
						}},
						{addClass: 'button_submit', text: breeze.text.noti_delete, onClick: function($noty){
							jQuery.ajax({
								type: 'POST',
								url: smf_scripturl + '?action=breezeajax;sa=notidelete;js=1;' + breeze.session.v + '=' + breeze.session.id,
								data: ({content : item.id, user : item.user}),
								cache: false,
								dataType: 'json',
								success: function(html){
										noty({text: html.message, timeout: 3500, type: html.type});
								},
								error: function (html){
										noty({text: breeze.text.error_wrong_values, timeout: 3500, type: 'error'});
								}
							});
								$noty.close();
						}},
						{addClass: 'button_submit', text: breeze.text.noti_cancel, onClick: function($noty){
								$noty.close();
						}}
					]
				});
			});

			// Show a close all button
			noty({
				text: breeze.text.noti_closeAll,
				type: 'warning',
				dismissQueue: true,
				layout: 'topRight',
				closeWith: ['click'],
				callback: {
					afterClose: function() {
						jQuery.noty.closeAll();
					},
					onShow: function() {window.setTimeout("jQuery.noty.closeAll()", ((breeze.currentSettings.clear_noti) ? breeze.currentSettings.clear_noti : 5) * 1000 );}
				}
			});

			// Append the number of notifications to the wall button.
			jQuery('#button_wall  a.firstlevel span').append(' ['+ number +']');

			// And to the title tag.
			jQuery('title').append(' ['+ number +']');
		},
		error: function (noti){
		}
	});
}
