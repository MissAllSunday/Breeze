/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

var breezePost = function(type, form) {

	this.type = type;
	this.form = jQuery(form);
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

breezePost.prototype.show = function(html){

	if (html.type == 'sucess') {

		// Time to actually show the message.
		div = '#breeze_display_' + this.type + (html.statusID != false ? '_' + statusID : '');

		jQuery(div).prepend(html.data).fadeIn('slow', 'linear', function(){});
	}

	// Show a notification.
	// breeze.tools.showNoti(html);

	// Clean your mess.
	this.clean();
};

breezePost.prototype.clean = function(){

};

breezePost.prototype.validate = function()
{
	// Get all the values we need.
	var postData = [];

	this.form.find(':input').each(function(){
		var input = jQuery(this);
		postData[input.attr('name')] = input.val();
	});

	// You need to type something...
	if(postData['postContent'] == '')
	{
		breeze.tools.showNoti({message: breeze.text.error_empty, type : 'error'});
		return false;
	}

	// Shh!
	if (postData['postContent'] == 'about:Suki')
	{
		alert('Y es que tengo un coraz\xF3n t\xE1n necio \n que no comprende que no entiende \n que le hace da\xF1o amarte tanto \n no comprende que lo haz olvidado \n sigue aferrado a tu recuerdo y a tu amor \n Y es que tengo un coraz\xF3n t\xE1n necio \n que vive preso a las caricias de tus lindas manos \n al dulce beso de tus labios \n y aunque le hace da\xF1o \n te sigue amando igual o mucho m\xE1s que ayer \n mucho m\xE1s que ayer... \n');

		return false;
	}

	// Are we posting a comment? if so, get the status ID.
	if(this.type == 'comment')
		postData['statusID'] = parseInt(this.form.attr('id').replace('form_comment_', ''));

	// Turn the array into a full object. easier to send to the server.
	this.data = jQuery.extend({}, postData);

	return true;
};

breezePost.prototype.save = function() {

	this.before();
	var call = this;

	// Append some mentions if there are any.

	// The long, long ajax call...
	jQuery.ajax({
		type: 'GET',
		url: this.form.attr('action') + ';js=1',
		data: this.data,
		cache: false,
		dataType: 'json',
		success: function(html) {
			call.show(html);
		},
		error: function (html) {
			call.show(html);
		}
	});

	this.after();
};
