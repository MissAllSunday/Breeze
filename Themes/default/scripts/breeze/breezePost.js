/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
*/

var breezePost = function(type, form) {
	this.type = type;
	this.form = $(form);
	this.data = {};
};

breezePost.prototype.before = function() {

	// Disable the submit button.
	$('input[type=submit]', this.form).attr('disabled', 'disabled');

	// Create a nice loading image...
	this.loadImage = $('<div/>', {
		id: 'breeze_load_image'
	}).html('<img src="' + smf_images_url + '/loading_sm.gif">').hide().css({
		'margin': 'auto',
		'text-align' : 'center'});
};

breezePost.prototype.after = function() {
	// Enable the button again...
	$('input[type=submit]', this.form).removeAttr('disabled');

	// Clean the textarea.
	this.form.find('textarea').val('');
};

breezePost.prototype.show = function(html) {

		this.loadImage.fadeOut('slow', 'linear', function(){
			if (html.type == 'info')
				$(div).prepend(DOMPurify.sanitize(html.data)).fadeIn('slow');
		});

	// Do you also have the Ohara youtube installed? Kudos!!!
	if (typeof oh_refresh === 'function') {
		// Lets give it some more time...
		oh_refresh(5000);
	}

	// Show a notification.
	breeze.tools.showNoti(html);
};

breezePost.prototype.validate = function() {
	// Get all the values we need.
	var postData = [];

	this.form.find(':input').each(function(){
		var input = $(this);
		postData[input.attr('name')] = DOMPurify.sanitize(input.val());
	});

	// You need to type something...
	if(postData['message'] == '') {
		breeze.tools.showNoti({message: breeze.text.error_empty, type : 'error'});
		return false;
	}

	// Shh!
	if (postData['message'] == 'about:Suki') {
		alert('Y es que tengo un coraz\xF3n t\xE1n necio \n que no comprende que no entiende \n que le hace da\xF1o amarte tanto \n no comprende que lo haz olvidado \n sigue aferrado a tu recuerdo y a tu amor \n Y es que tengo un coraz\xF3n t\xE1n necio \n que vive preso a las caricias de tus lindas manos \n al dulce beso de tus labios \n y aunque le hace da\xF1o \n te sigue amando igual o mucho m\xE1s que ayer \n mucho m\xE1s que ayer... \n');

		return false;
	}

	// Turn the array into a full object. easier to send to the server.
	this.data = $.extend({}, postData);

	// Fake the status ID for newly created status.
	if(this.type == 'status')
		this.data.statusID = false;

	return true;
};

breezePost.prototype.save = function() {

	this.before();
	var call = this;

	// Append some mentions if there are any.

	// Get the div where the message is going to appear.
	div = '#breeze_display_' + this.type + (this.data.statusID != false ? '_' + this.data.statusID : '');

	// Show a loading image.
	$(div).prepend(this.loadImage);
	this.loadImage.fadeIn('slow');

	// The long, long ajax call...
	$.ajax({
		type: 'GET',
		url: this.form.attr('action') + ';js=1',
		data: this.data,
		cache: false,
		dataType: 'json',
		success: function(html) {
			call.show(html);
		},
		error: function (html) {
			call.error(html);
		}
	});

	this.after();
};
