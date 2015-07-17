/*
 Copyright (c) 2011, 2015, Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
*/

// The main breeze JS object.
var breeze = {
	text : {},
	settings : {},
	ownerSettings : {},
	currentSettings : {},
	tools : {},
	pagination : {},
};

breeze.tools.showNoti = function(params){
	noty({
		text: params.message,
		timeout: 3500, //@todo set this to an user setting.
		type: params.type,
		theme: 'relax',
		layout: (typeof(params.layout) === 'undefined' ? 'top' : params.layout),
	});
};

breeze.tools.findWord = function(string, word){
	return string.match('@' + word) !== null;
};

$(function() {

	// Posting a new status
	$(document).on('submit', '#form_status', function(event){

		// Prevent normal behaviour.
		event.preventDefault();

		var breezeStatus = new breezePost('status', this);

		// Validate everything.
		valid = breezeStatus.validate();

		// Leeloo Dallas multipass...
		if (valid != false)
			breezeStatus.save();

		return false;
	});

	// Post a new comment.
	$(document).on('submit', '.form_comment', function(event){

		// Prevent normal behaviour.
		event.preventDefault();

		var breezeComment = new breezePost('comment', this);

		// Validate everything.
		valid = breezeComment.validate();

		if (valid != false && breezeComment.data){
			breezeComment.save();
		}

		// Prevent normal behaviour.
		return false;
	});

	// Deleting a status/comment.
	$(document).on('click', '.breeze_delete', function(event){

		event.preventDefault();

		var thisObject = $(this);

		// Get the ID
		postId = DOMPurify.sanitize(parseInt(thisObject.data('bid')));
		postUrl = DOMPurify.sanitize(thisObject.attr('href'));

		// Show a confirmation message
		noty({
			text: breeze.text.confirm_delete,
			type: 'confirm',
			theme: 'relax',
			layout: 'center',
			dismissQueue: false,
			closeWith: ['button'],
			buttons: [{
				addClass: 'button_submit', text: breeze.text.confirm_yes, onClick: function($noty) {
					$.ajax({
						type: 'GET',
						url: postUrl + ';js=1',
						cache: false,
						dataType: 'json',
						success: function(html){
							$noty.close();

							if (typeof(html.type) !== 'undefined' && html.type == 'info') {
								$('#' + html.data).fadeOut('slow');
							}

							// Show a message.
							noty({
								text: html.message,
								type: html.type,
								theme: 'relax',
								timeout: 3500
							});
						},
						error: function (html){
							$noty.close();
							noty({
								text: html.message,
								type: 'error',
								theme: 'relax',
								timeout: 3500
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

	// Likes.
	$(document).on('click', '.breSta_like, .breCom_like', function(event){
		var obj = $(this);
		event.preventDefault();
		ajax_indicator(true);
		$.ajax({
			type: 'GET',
			url: DOMPurify.sanitize(obj.attr('href') + ';js=1;'),
			cache: false,
			dataType: 'html',
			success: function(html)
			{
				ajax_indicator(false);
				obj.closest('ul').replaceWith(html);
			},
			error: function (html)
			{
				ajax_indicator(false);
			}
		});

		return false;
	});

	// Likes count.
	$(function() {
		$(document).on('click', '.like_count a', function(e){
			e.preventDefault();
			var title = $(this).parent().text(),
				url = $(this).attr('href') + ';js=1';
			return reqOverlayDiv(url, title);
		});
	});

	// User div.
	$(document).on('click', 'a[rel*=breezeFacebox]', function(event){
		event.preventDefault();
		var title = DOMPurify.sanitize($(this).data('name')),
			url = $(this).attr('href') + ';js=1';

		return reqOverlayDiv(url, title);
	});

	// Clean the visitors log/ delete cover image.
	$('.clean_log, .cover_delete').on('click', false, function(event){

		event.preventDefault();

		$.ajax({
			url: DOMPurify.sanitize($(this).attr('href') + ';js=1'),
			type: "GET",
			dataType: "json",
			success: function(data){
				breeze.tools.showNoti(data);

				// Hide the current cover preview.
				$('.current_cover').fadeOut();
			},
			error: function(data){
				breeze.tools.showNoti(data);

				// Hide the current cover preview.
				$('.current_cover').fadeOut();
			}
		});

		return false;
	});

	// My mood!
	$(document).one('click', 'a[rel*=breezeMood]', function(event){
		event.preventDefault();
		var title = DOMPurify.sanitize($(this).data('name')),
			url = $(this).attr('href') + ';js=1';
		return reqOverlayDiv(url, title);
	});

	// Changing moods.
	$(document).one('click', 'a[rel*=breezeMoodSave]', function(event){
		event.preventDefault();
		var moodID = DOMPurify.sanitize($(this).data('id')),
			url = $(this).attr('href') + ';js=1';

		// Lets make a quick ajax call here...
		$.ajax({
			type: 'GET',
			url: url,
			data: {},
			cache: false,
			dataType: 'json',
			success: function(response){
				breeze.tools.showNoti(response);
				response.data = $.parseJSON(response.data);
				// Find all mood images from this user.
				$(document).find('[data-user=' + response.data.user + ']').html(response.data.image);

				// Close the pop up.
				$('#smf_popup').fadeOut(300, function(){ $(this).remove(); });
			},
			error: function(response, textStatus, errorThrown){

				// Close the pop up.
				$('#smf_popup').fadeOut(300, function(){ $(this).remove(); });
			}
		});
	});
});
