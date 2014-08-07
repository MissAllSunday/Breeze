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
	tools : {
		showNoti = function(params){
			noty({
				text: params.message,
				timeout: 3500, //@todo set this to a user setting
				type: params.type
			});
		},
		findWord = function(string, word){
			return string.match('@' + word) !== null;
		},
	},
	pagination : {},
};

jQuery(document).ready(function(){

	// Posting a new status
	jQuery('#form_status').submit(function(event){

		event.preventDefault();

		var breezeStatus.prototype = new breezePost('status', this);

		// Validate everything.
		breezeStatus.validate();

		if (breezeStatus.data){
			breezeStatus.save();
		}

		// Prevent normal behaviour.
		return false;
	});

	// Post a new comment
	jQuery(document).on('submit', '.form_comment', function(event){

		event.preventDefault();

		var breezeComment.prototype = new breezePost('comment', this);

		// Validate everything.
		breezeComment.validate();

		if (breezeComment.data){
			breezeComment.save();
		}

		// Prevent normal behaviour.
		return false;
	});

	// Deleting a comment
	jQuery(document).on('click', '.breeze_delete', function(event){

		event.preventDefault();

		// Get the ID
		postId = parseInt(jQuery(this).data('bid'));
		postUrl = jQuery(this).attr('href');

		// Show a confirmation message
		noty({
			text: breeze.text.confirm_delete,
			type: 'confirmation',
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

							switch(html.type)
							{
								case 'error':
									noty({
										text: html.message,
										timeout: 3500, type: html.type
									});
								break;
								case 'success':
								jQuery('#comment_id_'+ postId).fadeOut('slow');
								noty({
									text: html.message,
									timeout: 3500, type: html.type
								});
								break;
							}
						},
						error: function (html){
							$noty.close();
							noty({
								text: html.message,
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

	// Facebox
	jQuery(document).on('click', 'a[rel*=facebox]', function(event){
		jQuery(this).facebox(
		{
			loadingImage : smf_images_url + '/breeze/loading.gif',
			closeImage   : smf_images_url + '/breeze/error_close.png'
		});
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
