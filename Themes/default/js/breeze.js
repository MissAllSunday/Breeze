/*
 Copyright (c) 2011, 2014 Jessica Gonzalez
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

breeze.tools.loadImage = '<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" />';

window.breeze.mentions = {};

// Helper function to show a notification instance
breeze.tools.showNoti = function(params){
	noty({
		text: params.message,
		timeout: 3500, //@todo set this to a user setting
		type: params.type
	});
}

jQuery(document).ready(function(){

	// Posting a new status
	jQuery('#form_status').submit(function(event){

		event.preventDefault();

		var status = {};

		// Get the profile owner
		status.statusOwner = window.breeze_profileOwner;

		// Get all the values we need
		jQuery('#form_status :input').each(function(){
			var input = jQuery(this);
			status[input.attr('name')] = input.val();
		});

		// You need to type something...
		if(status.statusContent=='')
		{
			breeze.tools.showNoti({message: breeze.text.error_empty, type : 'error'});
			return false;
		}

		else
		{
			// Shh!
			if (status.statusContent == 'about:Suki')
			{
				alert('Y es que tengo un coraz\xF3n t\xE1n necio \n que no comprende que no entiende \n que le hace da\xF1o amarte tanto \n no comprende que lo haz olvidado \n sigue aferrado a tu recuerdo y a tu amor \n Y es que tengo un coraz\xF3n t\xE1n necio \n que vive preso a las caricias de tus lindas manos \n al dulce beso de tus labios \n y aunque le hace da\xF1o \n te sigue amando igual o mucho m\xE1s que ayer \n mucho m\xE1s que ayer... \n');
				return false;
			}

			// Show a nice loading image so people can think we are actually doing some work...
			jQuery('#breeze_load_image').fadeIn('slow').html(breeze.tools.loadImage);

			// If there are some mentions, attach them to our main object.
			if (window.breeze.mentions)
				status['mentions'] = window.breeze.mentions;

			// The long, long ajax call...
			jQuery.ajax({
				type: 'GET',
				url: smf_scripturl + '?action=breezeajax;sa=post;js=1' + breeze.session.v + '=' + breeze.session.id + ';rf=' + breeze.tools.comingFrom,
				data: status,
				cache: false,
				dataType: 'json',
				success: function(html)
				{
					jQuery('#breeze_load_image').fadeOut('slow', 'linear', function(){
						// Enable the button again...
						jQuery('.status_button').removeAttr('disabled');

						// Set the notification
						breeze.tools.showNoti(html);

						// Do some after work...
						if (html.type == 'success')
						{
							jQuery('#statusContent').val('');
							jQuery('#breeze_display_status').prepend(html.data).fadeIn('slow', 'linear', function(){})
						}
					});
				},
				error: function (html)
				{
					// Enable the button again...
					jQuery('.status_button').removeAttr('disabled');
					jQuery('#statusContent').val('');

					jQuery('#breeze_load_image').slideUp('slow', 'linear', function(){
						noty({
							text: html.message,
							timeout: 3500, type: html.type
						});
					});
				}
			});
		}

		// Prevent normal behaviour
		return false;
	});

	// Post a new comment
	jQuery(document).on('submit', '.form_comment', function(event){

		event.preventDefault();

		// The most important thing is... the ID
		var StatusID = parseInt(jQuery(this).attr('id').replace('form_comment_', ''));

		// Gather all the data we need
		var comment = {
			'commentStatus' : StatusID,
			'commentOwner' : jQuery('#commentOwner_' + StatusID).val(),
			'commentPoster' : jQuery('#commentPoster_' + StatusID).val(),
			'commentStatusPoster' : jQuery('#commentStatusPoster_' + StatusID).val(),
			'commentContent' : jQuery('#commentContent_' + StatusID).val()
		};

		// Don't be silly...
		if(comment.commentContent=='')
		{
			breeze.tools.showNoti({message: breeze.text.empty_message, type : 'error'});
			return false;
		}

		else
		{
			// Disable the button
			jQuery('#commentSubmit_' + StatusID).attr('disabled', 'disabled');

			// The usual loading image...
			jQuery('#breeze_load_image_comment_'+ StatusID).fadeIn('slow').html(breeze.tools.loadImage);

			// Any mentions? add them at once!
			if (window.breeze.mentions)
				comment['mentions'] = window.breeze.mentions;

			jQuery.ajax({
				type: 'GET',
				url: smf_scripturl + '?action=breezeajax;sa=postcomment;js=1' + breeze.session.v + '=' + breeze.session.id + ';rf=' + breeze.tools.comingFrom,
				data: comment,
				cache: false,
				dataType: 'json',
				success: function(html){

					jQuery('#breeze_load_image_comment_'+ StatusID).fadeOut('slow', 'linear', function(){

						// Send the notification
						breeze.tools.showNoti(html);

						// Everything went better than expected :)
						jQuery('#comment_loadplace_'+ StatusID).append(html.data).fadeIn('slow', 'linear', function(){});
					});

					// Enable the button again...
					jQuery('#commentSubmit_' + StatusID).removeAttr('disabled');
					jQuery('#commentContent_' + StatusID).val('');

				},
				error: function (html){

					jQuery('#breeze_load_image_comment_'+ StatusID).fadeOut('slow');
					breeze.tools.showNoti(html);
					jQuery('#commentSubmit_' + StatusID).removeAttr('disabled');
					jQuery('#commentContent_' + StatusID).val('');
				}
			});
		}

		// Prevent normal behaviour
		return false;
	});

	// Deleting a comment
	jQuery(document).on('click', '.breeze_delete_comment', function(event){

		event.preventDefault();

		// Get the ID
		commentID = parseInt(jQuery(this).attr('id').replace('deleteComment_', ''));
		commentUrl = jQuery(this).attr('href');

		// Show a confirmation message
		noty({
			text: breeze.text.confirm_delete,
			type: 'confirmation',
			dismissQueue: false,
			closeWith: ['button'],
			buttons: [{
				addClass: 'button_submit', text: breeze.text.confirm_yes, onClick: function($noty) {
					jQuery.ajax({
						type: 'GET',
						url: commentUrl + ';js=1' + breeze.session.v + '=' + breeze.session.id,
						cache: false,
						dataType: 'json',
						success: function(html){
							$noty.close();

							switch(html.type)
							{
								case 'error':
									noty({
										text: html.message,
										timeout: 3500, type: html.type
									});
								break;
								case 'success':
								jQuery('#comment_id_'+ commentID).fadeOut('slow');
								noty({
									text: html.message,
									timeout: 3500, type: html.type
								});
								break;
							}
						},
						error: function (html){
							$noty.close();
							noty({
								text: html.message,
								timeout: 3500, type: html.error
							});
						}
					});
				}
			},
				{addClass: 'button_submit', text: breeze.text.confirm_cancel, onClick: function($noty) {
					$noty.close();
				}}
			]
		});

		return false;
	});

	// Deleting a status, pretty much the same as deleting a comment :(
	jQuery(document).on('click', '.breeze_delete_status', function(event){

		event.preventDefault();

		var element = jQuery(this);
		var I = parseInt(element.attr('id').replace('deleteStatus_', ''));
		var typeMethod = 'status';
		var urlParam = element.attr('href');

		// Show a nice confirmation box
		noty({
			text: breeze.text.confirm_delete,
			type: 'confirmation',
			dismissQueue: false,
			closeWith: ['button'],
			buttons: [{
				addClass: 'button_submit', text: breeze.text.confirm_yes, onClick: function($noty) {
					jQuery.ajax({
						type: 'GET',
						url: urlParam + ';js=1' + breeze.session.v + '=' + breeze.session.id,
						cache: false,
						dataType: 'json',
						success: function(html){
							$noty.close();
							switch(html.type)
							{
								case 'error':
									noty({
										text: html.message,
										timeout: 3500,
										type: html.type
									});
								break;
								case 'success':
									jQuery('#status_id_'+I).fadeOut('slow');
									noty({
										text: html.message,
										timeout: 3500,
										type: html.type
									});
								break;
							}
						},
						error: function (html){
							$noty.close();
							noty({
								text: html.message,
								timeout: 3500,
								type: html.type
							});
						}
					});
				}
			},
				{addClass: 'button_submit', text: breeze.text.confirm_cancel, onClick: function($noty) {
					$noty.close();
				}}
			]
		});

		return false;
	});

	// Facebox
	jQuery(document).on('click', 'a[rel*=facebox]', function(event){
		jQuery(this).facebox(
		{
			loadingImage : smf_images_url + '/breeze/loading.gif',
			closeImage   : smf_images_url + '/breeze/error_close.png'
		});
	});

	// Clean the visitors log
	jQuery('.clean_log').on('click', false, function(event){

		event.preventDefault();

		jQuery.ajax({
			url: jQuery(this).attr('href') + ';js=1' + breeze.session.v + '=' + breeze.session.id,
			type: "GET",
			dataType: "json",
			success: function(data){
				breeze.tools.showNoti(data);
			},
			error: function(data){
				breeze.tools.showNoti(data);
			}
		});

		return false;
	});
});

/*
 LoadMoreJS
 Copyright (c) 2011, 2014 Jessica Gonzalez
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

jQuery(document).ready(function(){

	// Fires up the load more thingy
	if (breeze.currentSettings.load_more){

		var numberOfEvents = 0;

		// Hide the normal pagination.
		jQuery('.pagelinks').hide();

		showMoarButton();
	}

	function showMoarButton(){

	// Don't show anything if there isn't enough items to display...
	if (breeze.pagination.totalItems <= breeze.pagination.maxIndex)
		return false;

		// Add a nice button...
		jQuery('<button/>', {
			id: 'loadMoar',
			class: 'clear',
			text: breeze.text.load_more,
			click: function () {

				numberOfEvents++;

				jQuery('<ul/>', {
					id: 'tempDIV_'+ numberOfEvents,
					class: 'breeze_status',
					style: ''
				}).appendTo('#breezeAppendTo').hide();

				// Append some more data to breeze.pagination.
				breeze.pagination.numberTimes = numberOfEvents;
				breeze.pagination.comingFrom = breeze.tools.comingFrom

				jQuery.ajax(
				{
					// Send the data and oh boy there's a lot of it!
					url: smf_scripturl + '?action=breezeajax;sa=fetch;js=1;' + breeze.session.v + '=' + breeze.session.id,
					data : breeze.pagination,
					cache: false,
					dataType: 'json',
					success: function(html)
					{
						// The server response as a JSON object
						if(html.type == 'success'){

							// Append the html to our temp DIV.
							if (html.data != 'end'){

								// Create a unique UL for this very own status, isn't it great?
								jQuery('#tempDIV_'+ numberOfEvents).append(html.data).fadeIn('slow', 'linear', function(){});
							}

							// No more data:(
							else{
								noty({
									text: breeze.text.page_loading_end,
									timeout: 3500,
									type: 'success'
								});
								jQuery('#loadMoar').fadeOut('slow');
								return;
							}
						}

						else if(html.type == 'error'){
							noty({
								text: html.message,
								timeout: 3500, type: html.type,
								type: html.type
							});
						}
					},
					error: function (html){
						noty({
							text: html,
							timeout: 3500,
							type: 'error'
						});
					}
				});
			}
		}).appendTo('#tab-wall');
	}

	// Check if we are near the end of the page
	function GetScrollPercent()
	{
		 var bottom = jQuery(window).height() + jQuery(window).scrollTop();
		 var height = jQuery(document).height();

		 return Math.round(100*bottom/height);

	}
});

/*
 MentionJS
 Copyright (c) 2011, 2014 Jessica Gonzalez
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

 // Mentioning
jQuery(document).ready(function(){
	jQuery('textarea[rel*=atwhoMention]').atwho({
		at: "@",
		tpl: "<li data-value='@${name}' data-user='${id}'>${name}</li>",
		callbacks: {
			remote_filter: function(query, callback) {

				if (query.length <= 2)
					return {name: '', id:''};

				jQuery.ajax({
					url: smf_scripturl + '?action=breezeajax;sa=usersmention;js=1' + breeze.session.v + '=' + breeze.session.id,
					type: "GET",
					data: {match: query},
					dataType: "json",
					success: function(data){
						callback(data);
					},
					error: function(data){
					}
				});
			},
			before_insert: function(value, li)
			{
				var userID, name;

				userID = li.data('user');
				name = li.data('value');

				// Set a "global" var to be picked up by the posting functions
				window.breeze.mentions[userID.toString()] = {'id': userID, 'name': name.replace('@', '')};

				return value;
			}
		}
	});
});

/*
 NotiJS
 Copyright (c) 2011, 2014 Jessica Gonzalez
 @license http://www.mozilla.org/MPL/MPL-1.1.html
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

/*
 TabsJS
 Copyright (c) 2011, 2014 Jessica Gonzalez
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

 jQuery(document).ready(function(){

	var tabs = {};

	var tabChange = function (newTab){

		currentActive = getCurrentActive();

		// Tell whatever tab is active at the moment to get lost...
		tabs[currentActive].active = false;
		jQuery(tabs[currentActive].href).fadeOut('slow', function() {

			// Remove the active class and add it to the newtab
			jQuery('li.'+ currentActive +' a').removeClass('active');
			jQuery('li.'+ newTab +' a').addClass('active');

			jQuery(tabs[newTab].href).fadeIn('slow', function() {});
		});

		// And set this as the active one...
		tabs[newTab].active = true;
	}

	var getCurrentActive = function (){

		var output = null,
			key;

		for (key in tabs) {
			if (tabs.hasOwnProperty(key)) {
				if (tabs[key].active == true){
					output = tabs[key].name;
				}
			}
		}

		return output;
	};

	// Get all available <li> tags
	jQuery('ul.breezeTabs li').each(function(){

		var currentName = jQuery(this).attr('class');

		tabs[currentName] = {
			href : jQuery(this).find('a').attr('href'),
			name : jQuery(this).attr('class'),
			active : (currentName == 'wall') ? true : false
		};

		// Hide all tabs by default
		jQuery(tabs[currentName].href).hide();

		// Make the wall page the active tab...
		jQuery(tabs['wall'].href).show();

		jQuery('li.'+ currentName +' a').on('click', false, function(e){

			// Is it active already?
			if (tabs[currentName].active == true){
				return false;
			}

			else {
				tabChange(currentName);
			}

			e.preventDefault();
			return false;
		});
	});

	jQuery(window).hashchange();
 });