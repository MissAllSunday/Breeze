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
		theme: 'breezeNoti'
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
		breezeComment.validate();

		if (breezeComment.data){
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
		postId = parseInt(thisObject.data('bid'));
		postUrl = thisObject.attr('href');

		// Show a confirmation message
		noty({
			text: breeze.text.confirm_delete,
			type: 'confirmation',
			theme: 'breezeNoti',
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

							switch(html.type) {
								case 'error':
									noty({
										text: html.message,
										type: 'error',
										theme: 'breezeNoti',
										timeout: 3500, type: html.type
									});
								break;
								case 'success':
								thisObject.closest('li').fadeOut('slow');
								noty({
									text: html.message,
									type: 'success',
									theme: 'breezeNoti',
									timeout: 3500, type: html.type
								});
								break;
							}
						},
						error: function (html){
							$noty.close();
							noty({
								text: html.message,
								type: 'error',
								theme: 'breezeNoti',
								timeout: 3500, type: html.error
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
			url: obj.attr('href') + ';js=1;',
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
				url = jQuery(this).attr('href') + ';js=1';
			return reqOverlayDiv(url, title);
		});
	});

	// User div.
	jQuery(document).on('click', 'a[rel*=breezeFacebox]', function(event){
		event.preventDefault();
		var title = jQuery(this).data('name'),
			url = jQuery(this).attr('href') + ';js=1';
		return reqOverlayDiv(url, title);
	});

	// Clean the visitors log/ delete cover image
	jQuery('.clean_log, .cover_delete').on('click', false, function(event){

		event.preventDefault();

		jQuery.ajax({
			url: jQuery(this).attr('href') + ';js=1',
			type: "GET",
			dataType: "json",
			success: function(data){
				breeze.tools.showNoti(data);
			},
			error: function(data){
				breeze.tools.showNoti(data);
			}
		});

		return false;
	});
});
