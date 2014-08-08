/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

var breezePost = function(type, form) {

	this.type = type;
	this.form = form:
	this.data = {};
};

breezePost.prototype.before = function(){
	// Show the loading image.
	jQuery('#breeze_load_image').fadeIn('slow');

	// Disable the submit button.
	jQuery('input[type=submit]', this.form).attr('disabled', 'disabled');
};

breezePost.prototype.after = function()
{
	// Enable the button again...
	jQuery('input[type=submit]', this.form).removeAttr('disabled');

	// Hide the loading image.
	jQuery('#breeze_load_image').fadeOut('slow', 'linear');
};

breezePost.prototype.validate = function()
{
	// Get all the values we need
	jQuery(this.form).filter(':input').each(function(){
		var input = jQuery(this);
		this.data[input.attr('name')] = input.val();
	});

	// You need to type something...
	if(this.data.content=='')
	{
		breeze.tools.showNoti({message: breeze.text.error_empty, type : 'error'});
		return false;
	}

	// Are we posting a comment? if so, get the status ID.
	if(this.type == 'comment')
		this.data.statusID = parseInt(this.form.attr('id').replace('this.form_comment_', ''));

	return this.data;
}

breezePost.prototype.save = function() {

	this.before();

	// Shh!
	if (this.data.content == 'about:Suki')
	{
		alert('Y es que tengo un coraz\xF3n t\xE1n necio \n que no comprende que no entiende \n que le hace da\xF1o amarte tanto \n no comprende que lo haz olvidado \n sigue aferrado a tu recuerdo y a tu amor \n Y es que tengo un coraz\xF3n t\xE1n necio \n que vive preso a las caricias de tus lindas manos \n al dulce beso de tus labios \n y aunque le hace da\xF1o \n te sigue amando igual o mucho m\xE1s que ayer \n mucho m\xE1s que ayer... \n');
		return false;
	}

	// Append some mentions if there are any.

	// The long, long ajax call...
	jQuery.ajax({
		type: 'GET',
		url: this.form.attr('href') + ';js=1',
		data: this.data,
		cache: false,
		dataType: 'json',
		success: function(html)
		{
			// Set the notification
			breeze.tools.showNoti(html);

			// Do some after work...
			if (html.type == 'success')
			{
				jQuery('#statusContent').val('');
				jQuery('#breeze_display_status').prepend(html.data).fadeIn('slow', 'linear', function(){})
			}
		},
		error: function (html)
		{
			// Enable the button again...
			jQuery('.status_button').removeAttr('disabled');
			jQuery('#statusContent').val('');

			jQuery('#breeze_load_image').slideUp('slow', 'linear', function(){
				noty({
					text: html.message,
					timeout: 3500, type: html.type
				});
			});
		}
	});

	this.after();
};
