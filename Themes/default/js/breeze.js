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

// The status stuff goes right here...
jQuery(document).ready(function(){


	// Posting a new status
	jQuery('#form_status').submit(function(event){

		var status = {};

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
				type: 'POST',
				url: smf_scripturl + '?action=breezeajax;sa=post;js=1' + window.breeze_session_var + '=' + window.breeze_session_id,
				data: status,
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

		// Temp
		return false;
	});

});
