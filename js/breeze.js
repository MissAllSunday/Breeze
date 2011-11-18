

/* The status stuff goes right here... */
	$(document).ready(function()
	{
		$(".status_button").click(function()
		{
			var test = $("#content").val();
			var ownerID = $("#owner_id").val();
			var posterID = $("#poster_id").val();
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
				$("#breeze_load_image").show();
				$("#breeze_load_image").fadeIn(400).html(loadImage);

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
								duration: 5
							});
							$("#breeze_load_image").hide();
						}
						else
						{
							$("#breeze_display_status").after(html);
							document.getElementById('content').value='';
							document.getElementById('content').focus();
							$("#breeze_load_image").hide();
							showNotification({
								message: breeze_success_message,
								type: 'success',
								autoClose: true,
								duration: 5
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
							duration: 5
						});
						$("#breeze_load_image").hide();
					},
				});
			}
			return false;
		});
	});

/* Handle the comments */
	$(document).ready(function()
	{
		$(".comment_submit").click(function()
		{
			var element = $(this);
			var Id = element.attr("id");
			var commentBox = $("#textboxcontent_"+Id).val();
			var loadcommentImage = '<img src="' + smf_images_url + '/breeze/loading.gif" /> <span class="loading">' + ajax_notification_text + '</span>';
			var status_owner_id = $("#status_owner_id"+Id).val();
			var poster_comment_id = $("#poster_comment_id"+Id).val();
			var profile_owner_id = $("#profile_owner_id"+Id).val();
			var status_id = $("#status_id"+Id).val();

			if(commentBox=='')
			{
				alert(breeze_empty_message);
			}
			else
			{
				$("#comment_flash_"+Id).show();
				$("#comment_flash_"+Id).fadeIn(400).html(loadcommentImage);
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
								duration: 1
							});
							$("#comment_flash_").hide();
						}
						else
						{
							$("#comment_loadplace_"+Id).append(html);
							document.getElementById('textboxcontent_'+Id).value='';
							document.getElementById('textboxcontent_'+Id).focus();
							$("#comment_flash_"+Id).hide();
							showNotification({
								message: breeze_success_message,
								type: 'success',
								autoClose: true,
								duration: 5
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
							duration: 5
						});
						$("#comment_flash_"+Id).hide();
					},
				});
			}
			return false;
		});
	});

	/* Delete a comment */
	$(document).ready(function()
	{
		$('.breeze_delete_comment').click(function()
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
								duration: 1
							});
						}
						else
						{
							$('#comment_id_'+I).hide();
							showNotification({
								message: breeze_success_delete,
								type: 'success',
								autoClose: true,
								duration: 5
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
							duration: 5
						});
						$('#comment_id_'+I).hide();
					},
				});
			return false;
		});

		// The confirmation message
		$('.breeze_delete_comment').confirm(
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

	/* Toggle the comment box */
	$(document).ready(function()
	{
		$(".comment_button").click(function()
		{
			var element = $(this);
			var I = element.attr("id");

			$("#slidepanel"+I).slideToggle(300);
			$(this).toggleClass("active");

			return false;
		});
	});

	/* Delete a status */
	$(document).ready(function()
	{
		$('.breeze_delete_status').click(function()
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
								duration: 1
							});
						}
						else
						{
							$('#status_id_'+I).hide();
							showNotification({
								message: breeze_success_delete,
								type: 'success',
								autoClose: true,
								duration: 5
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
							duration: 5
						});
						$('#status_id_'+I).hide();
					},
				});
			return false;
		});

		// The confirmation message
		$('.breeze_delete_status').confirm(
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

/* Facebox */
$(document).ready(function() {

		  $('a[rel*=facebox]').facebox({
			loadingImage : smf_images_url + '/breeze/loading.gif',
			closeImage   : smf_images_url + '/breeze/error_close.png'
		  });
    });

