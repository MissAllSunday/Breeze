

/* The status stuff goes right here... */
	$(function()
	{
		$(".status_button").click(function()
		{
			var test = $("#content").val();
			var dataString = 'content='+ test;
			var ownerID = $("#owner_id").val();
			var posterID = $("#poster_id").val();
			var loadImage = '<img src="' + smf_images_url + '/loading.gif" /> <span class="loading">' + ajax_notification_text + '</span>'

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
					data: ({content : test, owner_id : ownerID, poster_id : posterID}),
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

/* Handle the comments ajax */
	$(document).ready(function()
	{
		$(".comment_submit").click(function()
		{
			var element = $(this);
			var Id = element.attr("id");
			var commentBox = $("#textboxcontent"+Id).val();
			var dataString = 'textcontent='+ commentBox + '&com_msgid=' + Id;
			if(commentBox=='')
			{
				alert("Please Enter Some Text");
			}
			else
			{
				$("#flash"+Id).show();
				$("#flash"+Id).fadeIn(400).html('<img src="ajax-loader.gif" align="absmiddle"> loading.....');
				$.ajax({
				type: "POST",
				url: "insertajax.php",
				data: dataString,
				cache: false,
				success: function(html){
				$("#loadplace"+Id).append(html);
				$("#flash"+Id).hide();
				}
				});
			}
			return false;
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