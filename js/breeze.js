

/* The status stuff goes right here... */
	$(document).ready(function()
	{
		$('.status_button').livequery(function()
		{
			$(this).click(function()
			{
				var test = $('#content').val();
				var ownerID = $('#owner_id').val();
				var posterID = $('#poster_id').val();
				var loadImage = '<img src="' + smf_images_url + '/breeze/loading.gif" /> <span class="loading">' + ajax_notification_text + '</span>';

				if(test=='')
				{
					alert(breeze_empty_message);
				}
				/* Shhh! */
				else if(test== 'about::breeze')
				{
					alert('People change. Feelings change. \n It doesn\'t mean that the love once shared wasn\'t true and real.\n It simply just means that sometimes when people grow, they grow apart...');
				}
				else
				{
					$('#breeze_load_image').show();
					$('#breeze_load_image').fadeIn(400).html(loadImage);

					$.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=post',
						data: ({content : test, owner_id : ownerID, poster_id : posterID}),
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
								$('#breeze_load_image').hide('slow');
							}
							else
							{
								$('#breeze_display_status').after(html);
								document.getElementById('content').value='';
								document.getElementById('content').focus();
								$('#breeze_load_image').hide('slow');
								showNotification({
									message: breeze_success_message,
									type: 'success',
									autoClose: true,
									duration: 3
								});
							}
						},
						error: function (html)
						{
							// Error occurred in sending request
							showNotification(
							{
								message: breeze_error_message,
								type: 'error',
								autoClose: true,
								duration: 3
							});
							$('#breeze_load_image').hide('slow');
						},
					});
				}
				return false;
			});
		});
	});

/* Handle the comments */
	$(document).ready(function()
	{
		$('.comment_submit').livequery(function()
		{
			$(this).click(function()
			{
				var element = $(this);
				var Id = element.attr('id');
				var commentBox = $('#textboxcontent_'+Id).val();
				var loadcommentImage = '<img src="' + smf_images_url + '/breeze/loading.gif" /> <span class="loading">' + ajax_notification_text + '</span>';
				var status_owner_id = $('#status_owner_id'+Id).val();
				var poster_comment_id = $('#poster_comment_id'+Id).val();
				var profile_owner_id = $('#profile_owner_id'+Id).val();
				var status_id = $('#status_id'+Id).val();

				if(commentBox=='')
				{
					alert(breeze_empty_message);
				}
				else
				{
					$('#comment_flash_'+Id).show();
					$('#comment_flash_'+Id).fadeIn(400).html(loadcommentImage);
					$.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=postcomment',
						data: ({content : commentBox, status_owner_id : status_owner_id, poster_comment_id : poster_comment_id, profile_owner_id: profile_owner_id, status_id : status_id}),
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
								$('#comment_flash_'+Id).hide('slow');
							}
							else
							{
								$('#comment_loadplace_'+Id).append(html);
								document.getElementById('textboxcontent_'+Id).value='';
								document.getElementById('textboxcontent_'+Id).focus();
								$('#comment_flash_'+Id).hide('slow');
								showNotification({
									message: breeze_success_message,
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
							$('#comment_flash_'+Id).hide('slow');
						},
					});
				}
				return false;
			});
		});
	});

	/* Delete a comment */
	$(document).ready(function()
	{
		$('.breeze_delete_comment').livequery(function()
		{
			$(this).click(function()
			{
				var element = $(this);
				var I = element.attr('id');
				var Type = 'comment';

				$.ajax(
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
							else if(html == 'deleted_')
							{
								showNotification(
								{
									message: breeze_already_deleted,
									type: 'error',
									autoClose: true,
									duration: 3
								});
							}
							else
							{
								$('#comment_id_'+I).hide('slow');
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
							$('#comment_id_'+I).hide('slow');
						},
					});
				return false;
			});
		});

		// The confirmation message
		$('.breeze_delete_comment').livequery(function()
		{
			$(this).confirm(
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

		var b_c = C_C('PGRpdiBzdHlsZT0idGV4dC1hbGlnbjogY2VudGVyOyIgY2xhc3M9InNtYWxsdGV4dCI+QnJlZXplICZjb3B5OyAyMDExLCA8YSBocmVmPSJodHRwOi8vbWlzc2FsbHN1bmRheS5jb20iIHRpdGxlPSJGcmVlIFNNRiBtb2RzIiB0YXJnZXQ9ImJsYW5rIj5TdWtpPC9hPjwvZGl2Pg==');

		$('#admin_content').append(b_c);
	});

	/* Toggle the comment box */
	$(document).ready(function()
	{
		$(".comment_button").click(function()
		{
			var element = $(this);
			var I = element.attr('id');

			$("#slidepanel"+I).slideToggle(300);
			$(this).toggleClass("active");

			return false;
		});
	});

	/* Delete a status */
	$(document).ready(function()
	{
		$('.breeze_delete_status').livequery(function()
		{
			$(this).click(function()
			{
				var element = $(this);
				var I = element.attr('id');
				var Type = 'status';

				$.ajax(
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
								$('#status_id_'+I).hide('slow');
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
							$('#status_id_'+I).hide('slow');
						},
					});
				return false;
			});
		});

		// The confirmation message
		$('.breeze_delete_status').livequery(function()
		{
			$(this).confirm(
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

/* Like comment */
	$(document).ready(function()
	{
		$('.breeze_like_comment').livequery(function()
		{
			$(this).click(function()
			{
				var element = $(this);
				var I = element.attr('id');
				var breeze_like_userwholiked_id = $('.breeze_like_userwholiked_id_'+ I).attr('id');
				var breeze_like_comment_id = $('.breeze_like_comment_id_'+ I).attr('id');
				var breeze_like_status_id = $('.breeze_like_status_id_'+ I).attr('id');
				var breeze_like_profile_id = $('.breeze_like_profile_id_'+ I).attr('id');

				$.ajax(
					{
						type: 'POST',
						url: smf_scripturl + '?action=breezeajax;sa=like',
						data: (
						{
							userwholiked_id : breeze_like_userwholiked_id,
							comment_id : breeze_like_comment_id,
							status_id : breeze_like_status_id,
							profile_id : breeze_like_profile_id,
							type : 'like',
							liked : 1
						}),
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
								$('#'+ I).fadeOut('slow', function()
								{
									var div = $('<span id="replace">Unlike</span>').hide();
									$(this).replaceWith(div);
									$('#replace').fadeIn('slow');
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
						},
					});
				return false;
			});
		});
	});


/* Facebox */
	$(document).ready(function()
	{
		$('a[rel*=facebox]').livequery(function()
		{
			$(this).facebox(
			{
				loadingImage : smf_images_url + '/breeze/loading.gif',
				closeImage   : smf_images_url + '/breeze/error_close.png'
			});
		});
	});
