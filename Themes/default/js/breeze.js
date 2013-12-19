/**
 * breeze.js
 *
 * The purpose of this file is to handle all the client side code, the ajax call for the status, comments and other stuff
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

breeze.tools.loadImage = '<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" />';

// Helper function to show a notification instance
breeze.tools.showNoti = function(params){
	noty({
		text: params.message,
		timeout: 3500, //@todo set this to a user setting
		type: params.type,
	});
}

jQuery(document).ready(function(){

	// Posting a new status
	jQuery('#form_status').submit(function(event){

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
			breeze.tools.showNoti({message: breeze.text.empty_message, type : 'error'});
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
							timeout: 3500, type: html.type,
						});
					});
				},
			});
		}

		// Prevent normal behaviour
		return false;
	});

	// Post a new comment
	jQuery(document).on('submit', '.form_comment', function(event){

		// The most important thing is... the ID
		var StatusID = parseInt(jQuery(this).attr('id').replace('form_comment_', ''));

		// Gather all the data we need
		var comment = {
			'commentStatus' : StatusID,
			'commentOwner' : jQuery('#commentOwner_' + StatusID).val(),
			'commentPoster' : jQuery('#commentPoster_' + StatusID).val(),
			'commentStatusPoster' : jQuery('#commentStatusPoster_' + StatusID).val(),
			'commentContent' : jQuery('#commentContent_' + StatusID).val(),
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
				},
			});
		}

		// Prevent normal behaviour
		return false;
	});

	// Deleting a comment
	jQuery(document).on('click', '.breeze_delete_comment', function(event){

		// Get the ID
		commentID = parseInt(jQuery(this).attr('id').replace('deleteComment_', ''));
		commentUrl = jQuery(this).attr('href');

		// Show a confirmation message
		noty({
			text: breeze_confirm_delete,
			type: 'confirmation',
			dismissQueue: false,
			closeWith: ['button'],
			buttons: [{
				addClass: 'button_submit', text: breeze.text.confirm_yes, onClick: function($noty) {
					jQuery.ajax({
						type: 'GET',
						url: commentUrl + ';js=1' + breeze.session.v + '=' + breeze.session.id + ';type=Comments',
						cache: false,
						dataType: 'json',
						success: function(html){
							$noty.close();

							switch(html.type)
							{
								case 'error':
									noty({
										text: html.message,
										timeout: 3500, type: html.type,
									});
								break;
								case 'success':
								jQuery('#comment_id_'+ commentID).fadeOut('slow');
								noty({
									text: html.message,
									timeout: 3500, type: html.type,
								});
								break;
							}
						},
						error: function (html){
							$noty.close();
							noty({
								text: html.message,
								timeout: 3500, type: html.error,
							});
						},
					});
				}
			},
				{addClass: 'button_submit', text: breeze_confirm_cancel, onClick: function($noty) {
					$noty.close();
				}}
			]
		});

		return false;
	});

	// Deleting a status, pretty much the same as deleting a comment :(
	jQuery(document).on('click', '.breeze_delete_status', function(event){

		var element = jQuery(this);
		var I = parseInt(element.attr('id').replace('deleteStatus_', ''));
		var typeMethod = 'status';
		var urlParam = element.attr('href');

		// Show a nice confirmation box
		noty({
			text: breeze_confirm_delete,
			type: 'confirmation',
			dismissQueue: false,
			closeWith: ['button'],
			buttons: [{
				addClass: 'button_submit', text: breeze.text.confirm_yes, onClick: function($noty) {
					jQuery.ajax({
						type: 'GET',
						url: urlParam + ';js=1' + breeze.session.v + '=' + breeze.session.id + ';type=Status',
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
										type: html.type,
									});
								break;
								case 'success':
									jQuery('#status_id_'+I).fadeOut('slow');
									noty({
										text: html.message,
										timeout: 3500,
										type: html.type,
									});
								break;
							}
						},
						error: function (html){
							$noty.close();
							noty({
								text: html.message,
								timeout: 3500,
								type: html.type,
							});
						},
					});
				}
			},
				{addClass: 'button_submit', text: breeze_confirm_cancel, onClick: function($noty) {
					$noty.close();
				}}
			]
		});

		return false;
	});

	// Mentioning
	jQuery('textarea[rel*=atwhoMention]').bind("focus", function(event){
		jQuery.ajax({
			url: smf_scripturl + '?action=breezeajax;sa=usersmention;js=1' + breeze.session.v + '=' + breeze.session.id,
			type: "GET",
			dataType: "json",
			success: function(result)
			{
				jQuery('textarea[rel*=atwhoMention]').atwho('@', {
					search_key: "name",
					tpl: "<li data-value='(${name}, ${id})'>${name} <small>${id}</small></li>",
					data: result,
					limit: breeze.currentSettings.how_many_mentions,
					callback: {
						filter: function (query, data, search_key) {
							return jQuery.map(data, function(item, i) {
								return item[search_key].toLowerCase().indexOf(query) < 0 ? null : item
							})
						},
					}
				});
			},
		});
	});

	// Facebox
	jQuery(document).on('click', 'a[rel*=facebox]', function(event){
		jQuery(this).facebox(
		{
			loadingImage : smf_images_url + '/breeze/loading.gif',
			closeImage   : smf_images_url + '/breeze/error_close.png'
		});
	});
});
