

/* The ajax stuff goes right here... */
	$(function()
	{
		$(".comment_button").click(function()
		{
			var test = $("#content").val();
			var dataString = 'content='+ test;
			var loadImage = '<img src="' + smf_images_url + '/loading.gif" align="absmiddle"> <span class="loading">' + ajax_notification_text + '</span>'

			if(test=='')
			{
				alert('Please Enter Some Text');
			}
			else
			{
				$("#breeze_load_image").show();
				$("#breeze_load_image").fadeIn(400).html(loadImage);

				$.ajax(
				{
					type: 'POST',
					url: smf_scripturl + '?action=breezeajax;sa=post',
					data: dataString,
					cache: false,
					success: function(html)
					{
						$("#breeze_display_status").after(html);
						document.getElementById('content').value='';
						document.getElementById('content').focus();
						$("#breeze_load_image").hide();
					}
				});
			}
			return false;
		});
	});
