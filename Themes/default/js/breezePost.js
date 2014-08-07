/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

var BreezePost = function(type) {

	this.type = type;
	this.data = {};
	this.showLoading = function(show)
	{
		// Show a nice loading image so people can think we are actually doing some work...
		if (show){
			jQuery('#breeze_load_image').fadeIn('slow').html('<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" />'); // this should be on the template...
		}

		else{
			jQuery('#breeze_load_image').fadeOut('slow', 'linear');
		}
	};

	this.validate = function()
	{
		// Get all the values we need
		jQuery('#breeze_'+ type + ':input').each(function(){
			var input = jQuery(this);
			this.data[input.attr('name')] = input.val();
		});

		// You need to type something...
		if(this.data.content=='')
		{
			breeze.tools.showNoti({message: breeze.text.error_empty, type : 'error'});
			return false;
		}
	}

	this.save = function() {

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
			url: smf_scripturl + '?action=breezeajax;sa=post;js=1;rf=' + breeze.tools.comingFrom,
			data: this.data,
			cache: false,
			dataType: 'json',
			success: function(html)
			{

				// Enable the button again...
				jQuery('.status_button').removeAttr('disabled');

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
	};
};