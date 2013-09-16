/**
 * breeze.js
 *
 * The purpose of this file is to handle all the client side code, the ajax call for the status, comments and other stuff
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
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

// The status stuff goes right here...
	jQuery(document).ready(function()
	{
		jQuery('.status_button').livequery(function()
		{
			jQuery(this).click(function()
			{
				var test = jQuery('#content').val();
				var ownerID = jQuery('#owner_id').val();
				var posterID = jQuery('#poster_id').val();
				var loadImage = '<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" /><br /> <span class="loading">' + ajax_notification_text + '</span>';

				// Disable the button to prevent multiple clicks -_-
				jQuery(this).attr('disabled', 'disabled');

				if(test=='')
				{
					alert(breeze_empty_message);

					// Enable the button again...
					jQuery('.status_button').removeAttr('disabled');
				}

				// Shhh!
				else
					if(test== 'about:breeze')
					{
						alert('Y es que tengo un coraz\xF3n t\xE1n necio \n que no comprende que no entiende \n que le hace da\xF1o amarte tanto \n no comprende que lo haz olvidado \n sigue aferrado a tu recuerdo y a tu amor \n Y es que tengo un coraz\xF3n t\xE1n necio \n que vive preso a las caricias de tus lindas manos \n al dulce beso de tus labios \n y aunque le hace da\xF1o \n te sigue amando igual o mucho m\xE1s que ayer \n mucho m\xE1s que ayer... \n');

						// Enable the button again...
						jQuery('.status_button').removeAttr('disabled');
					}

				else
				{
					jQuery('#breeze_load_image').fadeIn('slow').html(loadImage);

					jQuery.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=post;js=1' + window.breeze_session_var + '=' + window.breeze_session_id,
						data: ({content : test, owner_id : ownerID, poster_id : posterID}),
						cache: false,
						dataType: 'json',
						success: function(html)
						{
							// The server side found an issue
							if(html.type == 'error')
							{
								jQuery('#breeze_load_image').fadeOut('slow', 'linear', function(){
									noty({
										text: html.message,
										timeout: 3500, type: html.type,
									});
								});
							}

							else if(html.type == 'success'){
								jQuery('#breeze_load_image').fadeOut('slow', 'linear', function(){
									document.getElementById('content').value='';
									document.getElementById('content').focus();

									jQuery('#breeze_display_status').prepend(html.data);

									jQuery('#breeze_display_status').fadeIn('slow', 'linear', function(){
										noty({
											text: html.message,
											timeout: 3500, type: html.type,
										});
									});
								});
							}

							// Enable the button again...
							jQuery('.status_button').removeAttr('disabled');
						},
						error: function (html)
						{
							// Enable the button again...
							jQuery('.status_button').removeAttr('disabled');

							/*
							 * Something happen while sending the request
							 *
							 * @todo identify the different errors and show more info about it to the forum admin
							 */
							jQuery('#breeze_load_image').slideUp('slow', 'linear', function(){
								noty({
									text: html.message,
									timeout: 3500, type: html.type,
								});
							});
						},
					});
				}
				return false;
			});
		});
	});

// Handle the comments
	jQuery(document).ready(function()
	{
		jQuery('.comment_submit').livequery(function()
		{
			jQuery(this).click(function()
			{
				var element = jQuery(this);
				var Id = element.attr('id');
				var commentBox = jQuery('#textboxcontent_'+Id).val();
				var loadcommentImage = '<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" /> <span class="loading">' + ajax_notification_text + '</span>';
				var status_owner_id = jQuery('#status_owner_id'+Id).val();
				var poster_comment_id = jQuery('#poster_comment_id'+Id).val();
				var profile_owner_id = jQuery('#profile_owner_id'+Id).val();
				var status_id = Id;
				var loadImage = '<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" /><br /> <span class="loading">' + ajax_notification_text + '</span>';

				if(commentBox=='')
					alert(breeze_empty_message);

				else
				{
					jQuery('#breeze_load_image_comment_'+Id).fadeIn('slow').html(loadImage);

					jQuery.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=postcomment;js=1' + window.breeze_session_var + '=' + window.breeze_session_id,
						data: ({content : commentBox, status_owner_id : status_owner_id, poster_comment_id : poster_comment_id, profile_owner_id: profile_owner_id, status_id : status_id}),
						cache: false,
						dataType: 'json',
						success: function(html){
							// The server side found an issue
							if(html.type == 'error')
							{
								jQuery('#breeze_load_image_comment_'+Id).fadeOut('slow', 'linear', function(){
									noty({
										text: html.message,
										timeout: 3500, type: html.type,
									});
								});
							}

							else if(html.type == 'success'){
								jQuery('#breeze_load_image_comment_'+Id).fadeOut('slow', 'linear', function(){
									document.getElementById('textboxcontent_'+Id).value='';
									document.getElementById('textboxcontent_'+Id).focus();
									jQuery('#comment_loadplace_'+Id).append(html.data);
									jQuery('#comment_loadplace_'+Id).fadeIn('slow', 'linear', function(){
										noty({
											text: html.message,
											timeout: 3500, type: html.type,
										});
									});
								});
							}

							// Enable the button again...
							jQuery('.status_button').removeAttr('disabled');
						},
						error: function (html)
						{
							jQuery('#breeze_load_image_comment_'+Id).fadeOut('slow');
							noty({
								text: html.message,
								timeout: 3500, type: html.type,
							});
						},
					});
				}
				return false;
			});
		});
	});

	// Delete a comment
	jQuery(document).ready(function()
	{
		jQuery('.breeze_delete_comment').livequery(function()
		{
			jQuery(this).click(function()
			{
				var element = jQuery(this);
				var I = element.attr('id');
				var typeMethod = 'comment';
				var urlParam = element.attr('href');

				// Show a nice confirmation box
				noty({
					text: breeze_confirm_delete,
					type: 'confirmation',
					dismissQueue: false,
					closeWith: ['button'],
					buttons: [{
						addClass: 'button_submit', text: breeze_confirm_yes, onClick: function($noty) {
							jQuery.ajax({
								type: 'POST',
								url: urlParam + ';js=1' + window.breeze_session_var + '=' + window.breeze_session_id,
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
										jQuery('#comment_id_'+I).fadeOut('slow');
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
		});
	});

	// Delete a status
	jQuery(document).ready(function()
	{
		jQuery('.breeze_delete_status').livequery(function()
		{
			jQuery(this).click(function()
			{
				var element = jQuery(this);
				var I = element.attr('id');
				var typeMethod = 'status';
				var urlParam = element.attr('href');

				// Show a nice confirmation box
				noty({
					text: breeze_confirm_delete,
					type: 'confirmation',
					dismissQueue: false,
					closeWith: ['button'],
					buttons: [{
						addClass: 'button_submit', text: breeze_confirm_yes, onClick: function($noty) {
							jQuery.ajax({
								type: 'POST',
								url: urlParam + ';js=1' + window.breeze_session_var + '=' + window.breeze_session_id,
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
											jQuery('#status_id_'+I).fadeOut('slow');
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
										timeout: 3500, type: html.type,
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
		});
	});

// Facebox
jQuery(document).ready(function()
{
	jQuery('a[rel*=facebox]').livequery(function()
	{
		jQuery(this).facebox(
		{
			loadingImage : smf_images_url + '/breeze/loading.gif',
			closeImage   : smf_images_url + '/breeze/error_close.png'
		});
	});
});

// Scroll to top
jQuery(document).ready(function(){

	// hide #breezeTop first
	jQuery("#breezeTop").hide();

	jQuery(function ()
	{
		jQuery(window).scroll(function ()
		{
			if (jQuery(this).scrollTop() > 100)
			{
				jQuery('#breezeTop').fadeIn();
			}
			else
			{
				jQuery('#breezeTop').fadeOut();
			}
		});

		jQuery('#breezeTop a').click(function ()
		{
			jQuery('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
		});
	});
});

jQuery(document).ready(function(){
	jQuery('textarea[rel*=atwhoMention]').bind("focus", function(event){
		jQuery.ajax({
			url: smf_scripturl + '?action=breezeajax;sa=usersmention;js=1' + window.breeze_session_var + '=' + window.breeze_session_id,
			type: "GET",
			dataType: "json",
			success: function(result)
			{
				jQuery('textarea[rel*=atwhoMention]').atwho('@', {
					search_key: "name",
					tpl: "<li data-value='(${name}, ${id})'>${name} <small>${id}</small></li>",
					data: result,
					limit: breeze_how_many_mentions_options,
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
});
