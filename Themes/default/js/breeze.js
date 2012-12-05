/**
 * Breeze.js
 *
 * The purpose of this file is to handle all the client side code, the ajax call for the status, comments and other stuff
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <missallsunday@simplemachines.org>
 * @copyright Copyright (c) 2012, Jessica González
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
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

/* The status stuff goes right here... */
	jQuery(document).ready(function()
	{
		jQuery('.status_button').livequery(function()
		{
			jQuery(this).click(function()
			{
				var test = jQuery('#content').val();
				var ownerID = jQuery('#owner_id').val();
				var posterID = jQuery('#poster_id').val();
				var loadImage = '<img src="' + smf_images_url + '/breeze/loading.gif" /><br /> <span class="loading">' + ajax_notification_text + '</span>';

				/* Disable the button to prevent multiple clicks -_- */
				jQuery(this).attr('disabled', 'disabled');

				if(test=='')
				{
					alert(breeze_empty_message);

					/* Enable the button again... */
					jQuery('.status_button').removeAttr('disabled');
				}

				/* Shhh! */
				else if(test== 'about:breeze')
				{
					alert('Y es que tengo un coraz\xF3n t\xE1n necio \n que no comprende que no entiende \n que le hace da\xF1o amarte tanto \n no comprende que lo haz olvidado \n sigue aferrado a tu recuerdo y a tu amor \n Y es que tengo un coraz\xF3n t\xE1n necio \n que vive preso a las caricias de tus lindas manos \n al dulce beso de tus labios \n y aunque le hace da\xF1o \n te sigue amando igual o mucho m\xE1s que ayer \n mucho m\xE1s que ayer... \n');

					/* Enable the button again... */
					jQuery('.status_button').removeAttr('disabled');
				}

				else
				{
					jQuery('#breeze_load_image').fadeIn('slow').html(loadImage);

					jQuery.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=post',
						data: ({content : test, owner_id : ownerID, poster_id : posterID}),
						cache: false,
						dataType: 'json',
						success: function(html){
							/* The server side found an issue */
							if(html.type == 'error')
							{
								jQuery('#breeze_load_image').fadeOut('slow', 'linear', function(){
									noty({
										text: html.data,
										timeout: 3500, type: html.type,
										layout: 'top'
									});
								});
							}

							else if(html.type == 'ok'){
								jQuery('#breeze_load_image').fadeOut('slow', 'linear', function(){
									document.getElementById('content').value='';
									document.getElementById('content').focus();

									jQuery('#breeze_display_status').prepend(html.data);

									jQuery('#breeze_display_status').fadeIn('slow', 'linear', function(){
										noty({
											text: breeze_success_message,
											timeout: 3500, type: 'success',
											layout: 'top'
										});
									});
								});
							}

							/* Enable the button again... */
							jQuery('.status_button').removeAttr('disabled');
						},
						error: function (html)
						{
							/* Enable the button again... */
							jQuery('.status_button').removeAttr('disabled');

							/*
							 * Something happen while sending the request
							 *
							 * @todo identify the different errors and show more info about it to the forum admin
							 */
							jQuery('#breeze_load_image').slideUp('slow', 'linear', function(){
								showNotification(
								{
									message: breeze_error_message,
									type: 'error',
									autoClose: true,
									duration: 3
								});
							});
						},
					});
				}
				return false;
			});
		});
	});

/* Handle the comments */
	jQuery(document).ready(function()
	{
		jQuery('.comment_submit').livequery(function()
		{
			jQuery(this).click(function()
			{
				var element = jQuery(this);
				var Id = element.attr('id');
				var commentBox = jQuery('#textboxcontent_'+Id).val();
				var loadcommentImage = '<img src="' + smf_images_url + '/breeze/loading.gif" /> <span class="loading">' + ajax_notification_text + '</span>';
				var status_owner_id = jQuery('#status_owner_id'+Id).val();
				var poster_comment_id = jQuery('#poster_comment_id'+Id).val();
				var profile_owner_id = jQuery('#profile_owner_id'+Id).val();
				var status_id = jQuery('#status_id'+Id).val();
				var loadImage = '<img src="' + smf_images_url + '/breeze/loading.gif" /><br /> <span class="loading">' + ajax_notification_text + '</span>';

				if(commentBox=='')
					alert(breeze_empty_message);

				else
				{
					jQuery('#breeze_load_image_comment_'+Id).fadeIn('slow').html(loadImage);

					jQuery.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=postcomment',
						data: ({content : commentBox, status_owner_id : status_owner_id, poster_comment_id : poster_comment_id, profile_owner_id: profile_owner_id, status_id : status_id}),
						cache: false,
						success: function(html){
							/* The server side found an issue */
							if(html.type == 'error')
							{
								jQuery('#breeze_load_image_comment_'+Id).fadeOut('slow', 'linear', function(){
									noty({
										text: html.data,
										timeout: 3500, type: html.type,
										layout: 'top'
									});
								});
							}

							else if(html.type == 'ok'){
								jQuery('#breeze_load_image_comment_'+Id).fadeOut('slow', 'linear', function(){
									document.getElementById('textboxcontent_'+Id).value='';
									document.getElementById('textboxcontent_'+Id).focus();

									jQuery('#comment_loadplace_'+Id).append(html);

									jQuery('#comment_loadplace_'+Id).fadeIn('slow', 'linear', function(){
										noty({
											text: breeze_success_message,
											timeout: 3500, type: 'success',
											layout: 'top'
										});
									});
								});
							}

							/* Enable the button again... */
							jQuery('.status_button').removeAttr('disabled');
						},
						error: function (html)
						{
							jQuery('#breeze_load_image_comment_'+Id).fadeOut('slow', 'linear', function()
							{
								showNotification({
									message: breeze_error_message,
									type: 'error',
									autoClose: true,
									duration: 3
								});
							});
						},
					});
				}
				return false;
			});
		});
	});

	/* Delete a comment */
	jQuery(document).ready(function()
	{
		jQuery('.breeze_delete_comment').livequery(function()
		{
			jQuery(this).click(function()
			{
				var element = jQuery(this);
				var I = element.attr('id');
				var Type = 'comment';

				jQuery.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=delete',
						data: ({id : I, type : Type}),
						cache: false,
						success: function(html)
						{
							if(html.type == 'error')
							{
								showNotification({
									message: html.data,
									type: 'error',
									autoClose: true,
									duration: 3
								});
							}
							else if(html.type == 'deleted')
							{
								showNotification({
									message: html.data,
									type: 'error',
									autoClose: true,
									duration: 3
								});
							}
							else
							{
								jQuery('#comment_id_'+I).hide('slow');
								showNotification({
									message: html.data,
									type: 'success',
									autoClose: true,
									duration: 3
								});
							}
						},
						error: function (html)
						{
							showNotification({
								message: breeze_error_message,
								type: 'error',
								autoClose: true,
								duration: 3
							});
							jQuery('#comment_id_'+I).hide('slow');
						},
					});
				return false;
			});
		});

		// The confirmation message
		jQuery('.breeze_delete_comment').livequery(function()
		{
			jQuery(this).confirm(
			{
				msg: breeze_confirm_delete + '<br />',
				buttons:
				{
					ok: breeze_confirm_yes,
					cancel: breeze_confirm_cancel,
					separator:' | '
				}

			});
		});

		var b_c = C_C('PGRpdiBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyIgY2xhc3M9InNtYWxsdGV4dCI+QnJlZXplIKkgMjAxMiwgPGEgaHJlZj0iaHR0cDovL21pc3NhbGxzdW5kYXkuY29tIiB0aXRsZT0iRnJlZSBTTUYgbW9kcyIgdGFyZ2V0PSJibGFuayI+U3VraTwvYT48L2Rpdj4=');

		jQuery('#admin_content').append(b_c);
	});

	/* Toggle the comment box */
	jQuery(document).ready(function()
	{
		$(".comment_button").click(function()
		{
			var element = jQuery(this);
			var I = element.attr('id');

			$("#slidepanel"+I).slideToggle(300);
			jQuery(this).toggleClass("active");

			return false;
		});
	});

	/* Delete a status */
	jQuery(document).ready(function()
	{
		jQuery('.breeze_delete_status').livequery(function()
		{
			jQuery(this).click(function()
			{
				var element = jQuery(this);
				var I = element.attr('id');
				var Type = 'status';

				jQuery.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=delete',
						data: ({id : I, type : Type}),
						cache: false,
						success: function(html)
						{
							if(html == 'error_')
							{
								showNotification(
								{
									message: breeze_error_message,
									type: 'error',
									autoClose: true,
									duration: 3
								});
							}
							else
							{
								jQuery('#status_id_'+I).hide('slow');
								showNotification({
									message: breeze_success_delete,
									type: 'success',
									autoClose: true,
									duration: 3
								});
							}
						},
						error: function (html)
						{
							showNotification(
							{
								message: breeze_error_message,
								type: 'error',
								autoClose: true,
								duration: 3
							});
							jQuery('#status_id_'+I).hide('slow');
						},
					});
				return false;
			});
		});

		// The confirmation message
		jQuery('.breeze_delete_status').livequery(function()
		{
			jQuery(this).confirm(
			{
				msg: breeze_confirm_delete + '<br />',
				buttons:
				{
					ok: breeze_confirm_yes,
					cancel: breeze_confirm_cancel,
					separator:' | '
				}
			});
		});
	});

	/* Fun! */
	function C_C(data)
	{
		var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
		var o1, o2, o3, h1, h2, h3, h4, bits, i = 0,
			ac = 0,
			dec = "",
			tmp_arr = [];

		if (!data) {
			return data;
		}

		data += '';

		do {
			h1 = b64.indexOf(data.charAt(i++));
			h2 = b64.indexOf(data.charAt(i++));
			h3 = b64.indexOf(data.charAt(i++));
			h4 = b64.indexOf(data.charAt(i++));

			bits = h1 << 18 | h2 << 12 | h3 << 6 | h4;

			o1 = bits >> 16 & 0xff;
			o2 = bits >> 8 & 0xff;
			o3 = bits & 0xff;

			if (h3 == 64) {
				tmp_arr[ac++] = String.fromCharCode(o1);
			} else if (h4 == 64) {
				tmp_arr[ac++] = String.fromCharCode(o1, o2);
			} else {
				tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);
			}
		} while (i < data.length);

		dec = tmp_arr.join('');

		return dec;
	}

/* Facebox */
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