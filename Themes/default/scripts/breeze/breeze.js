/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
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
		timeout: 3500, //@todo set this to a user setting
		type: params.type,
		theme: 'relax',
		layout: (typeof(params.layout) === 'undefined' ? 'top' : params.layout),
	});
};

breeze.tools.findWord = function(string, word){
	return string.match('@' + word) !== null;
};

jQuery(document).ready(function(){

	// Posting a new status
	jQuery(document).on('submit', '#form_status', function(event){

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
	jQuery(document).on('submit', '.form_comment', function(event){

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
	jQuery(document).on('click', '.breeze_delete', function(event){

		event.preventDefault();

		var thisObject = jQuery(this);

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
					jQuery.ajax({
						type: 'GET',
						url: postUrl + ';js=1',
						cache: false,
						dataType: 'json',
						success: function(html){
							$noty.close();
console.log(html);
							if (typeof(html.type) !== 'undefined' && html.type == 'info') {
								jQuery('#' + html.data).fadeOut('slow');
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
	jQuery(document).on('click', '.breSta_like, .breCom_like', function(event){
		var obj = jQuery(this);
		event.preventDefault();
		ajax_indicator(true);
		jQuery.ajax({
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
	jQuery(function() {
		jQuery(document).on('click', '.like_count a', function(e){
			e.preventDefault();
			var title = jQuery(this).parent().text(),
				url = DOMPurify.sanitize(jQuery(this).attr('href') + ';js=1');
			return reqOverlayDiv(url, title);
		});
	});

	// User div.
	jQuery(document).on('click', 'a[rel*=breezeFacebox]', function(event){
		event.preventDefault();
		var title = DOMPurify.sanitize(jQuery(this).data('name')),
			url = DOMPurify.sanitize(jQuery(this).attr('href') + ';js=1');
		return reqOverlayDiv(url, title);
	});

	// Clean the visitors log/ delete cover image.
	jQuery('.clean_log, .cover_delete').on('click', false, function(event){

		event.preventDefault();

		jQuery.ajax({
			url: DOMPurify.sanitize(jQuery(this).attr('href') + ';js=1'),
			type: "GET",
			dataType: "json",
			success: function(data){
				console.log(data);
				breeze.tools.showNoti(data);

				// Hide the current cover preview.
				jQuery('.current_cover').fadeOut();
			},
			error: function(data){
				console.log(data);
				breeze.tools.showNoti(data);

				// Hide the current cover preview.
				jQuery('.current_cover').fadeOut();
			}
		});

		return false;
	});

	// My mood!
	jQuery(document).on('click', 'a[rel*=breezeMood]', function(event){
		event.preventDefault();
		var title = DOMPurify.sanitize(jQuery(this).data('name')),
			url = DOMPurify.sanitize(jQuery(this).attr('href') + ';js=1');
		return reqOverlayDiv(url, title);
	});

	// Changing moods.
	jQuery(document).on('click', 'a[rel*=breezeMoodSave]', function(event){
		event.preventDefault();
		var moodID = DOMPurify.sanitize(jQuery(this).data('id')),
			url = DOMPurify.sanitize(jQuery(this).attr('href') + ';js=1');

		// Lets make a quick ajax call here...
		jQuery.ajax({
			type: 'GET',
			url: url,
			data: {},
			cache: false,
			dataType: 'json',
			success: function(response){
				breeze.tools.showNoti(response);
				response.data = jQuery.parseJSON(response.data);
				// Find all mood images from this user.
				jQuery(document).find('[data-user=' + response.data.user + ']').html(response.data.image);
			},
			error: function(response){
				breeze.tools.showNoti(response);
			}
		});
	});
});
