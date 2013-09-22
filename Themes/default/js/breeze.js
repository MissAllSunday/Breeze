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

// Some re-usable vars
var breeze = {};

breeze.loadImage = '<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" />';

// Helper function to show a notification instance
breeze.noti = function(params)
{
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
		status.Owner = window.breeze_profileOwner;

		// Get all the values we need
		jQuery('#form_status :input').each(function(){
			var input = jQuery(this);
			status[input.attr('name').replace('status', '')] = input.val();
		});

		// You need to type something...
		if(status.Content=='')
		{
			alert(breeze_empty_message); // @todo, perhaps fire a nice noty message instead of a nasty alert?
			return false;
		}

		else
		{
			// Shh!
			if (status.Content == 'about:Suki')
			{
				alert('Y es que tengo un coraz\xF3n t\xE1n necio \n que no comprende que no entiende \n que le hace da\xF1o amarte tanto \n no comprende que lo haz olvidado \n sigue aferrado a tu recuerdo y a tu amor \n Y es que tengo un coraz\xF3n t\xE1n necio \n que vive preso a las caricias de tus lindas manos \n al dulce beso de tus labios \n y aunque le hace da\xF1o \n te sigue amando igual o mucho m\xE1s que ayer \n mucho m\xE1s que ayer... \n');
				return false;
			}

			// Show a nice loading image so people can think we are actually doing some work...
			jQuery('#breeze_load_image').fadeIn('slow').html(breeze.loadImage);

			// The long, long ajax call...
			jQuery.ajax(
			{
				type: 'GET',
				url: smf_scripturl + '?action=breezeajax;sa=post;js=1' + window.breeze_session_var + '=' + window.breeze_session_id,
				data: status,
				cache: false,
				dataType: 'json',
				success: function(html)
				{console.log(html);
					jQuery('#breeze_load_image').fadeOut('slow', 'linear', function(){
						// Enable the button again...
						jQuery('.status_button').removeAttr('disabled');

						// Set the notification
						breeze.noti(html);

						// Do some after work...
						if (html.type == 'success')
						{
							jQuery('#statusContent').text('').focus();
							jQuery('#breeze_display_status').prepend(html.data).fadeIn('slow', 'linear', function(){})
						}
					});
				},
				error: function (html)
				{
					// Enable the button again...
					jQuery('.status_button').removeAttr('disabled');

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
	$(document).on('submit', '.form_comment', function(event){

		// Set a new object
		var comment = {};

		// The most important thing is... the ID
		comment.Status = parseInt(jQuery(this).attr('id').replace('form_comment_', ''));

		// Set the profile owner
		comment.Owner = window.breeze_profileOwner;

		// Get all the values
		jQuery('.form_comment :input').each(function(){
			var input = jQuery(this);
			status[input.attr('name').replace('status', '')] = input.val();
		});

console.log(comment);
		// Prevent normal behaviour
		return false;
	});

	// Mentioning
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

	// Facebox
	jQuery('a[rel*=facebox]').livequery(function()
	{
		jQuery(this).facebox(
		{
			loadingImage : smf_images_url + '/breeze/loading.gif',
			closeImage   : smf_images_url + '/breeze/error_close.png'
		});
	});

});


