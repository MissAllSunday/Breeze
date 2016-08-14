/*
 Copyright (c) 2011, 2015, Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
*/
$(function() {

	var previewNode = document.querySelector('#template');
	previewNode.id = '';
	var previewTemplate = previewNode.parentNode.innerHTML;
	previewNode.parentNode.removeChild(previewNode);

	var dOptions = {
		url: smf_scripturl + '?action=breezeajax;sa=cover;rf=profile;u='+ smf_member_id +';area=breezecover;js=1;'+ smf_session_var +'='+ smf_session_id,
		maxFilesize: 1,
		uploadMultiple:false,
		maxFiles:1,
		acceptedFiles: '',
		thumbnailWidth: 150,
		thumbnailHeight: null,
		parallelUploads: 1,
		previewTemplate: previewTemplate,
		autoQueue: false,
		previewsContainer: '#cu-previews',
		clickable: '.fileinput-button',
		paramName: 'files',
		addRemoveLinks: true,
		accept: function(file, done) {

			file.acceptDimensions = done;
			file.rejectDimensions = function(eText) { done(eText); };
		}
	};

	$.extend(true, dOptions, dzOptions);

	var myDropzone = new Dropzone('div#coverUpload', dOptions);

	myDropzone.on('thumbnail', function(file) {

		if (file.width > myDropzone.options.maxFileWidth && file.rejectDimensions) {
			file.rejectDimensions(myDropzone.options.maxWidthMessage);
		}
		else if (file.height > myDropzone.options.maxFileHeight && file.rejectDimensions){
			file.rejectDimensions(myDropzone.options.maxHeightMessage);
		}
		else if (file.acceptDimensions) {
			file.acceptDimensions();
		}
	});

	myDropzone.on('addedfile', function(file) {

		_thisElement = $(file.previewElement);

		// Hookup the start button.
		_thisElement.find('.start').on( 'click', function() {

			if (!confirm(smf_you_sure)){
				return;
			}

			myDropzone.enqueueFile(file);
		});

		// Show the main stuff!
		_thisElement.addClass('descbox');

	});

	myDropzone.on('error', function(file, errorMessage, xhr) {

		_thisElement = $(file.previewElement);

		// Remove the "start" button.
		_thisElement.find('.start').remove();

		// Set a nice css class to make it more obvious theres an error.
		_thisElement.removeClass('descbox').addClass('errorbox');
	});

	myDropzone.on('success', function(file, responseText, e) {

		_thisElement = $(file.previewElement);
		// The request was complete but the server returned an error.
		if (typeof(responseText.error) != "undefined"){

			_thisElement.removeClass('descbox').addClass('errorbox');

			// Show the server error.
			_thisElement.find('p.error').append(responseText.error);

			// Remove the "start" button.
			_thisElement.find('.start').remove();
		}

		// Show a nice sucess message.
		if (responseText.type == 'info'){
			_thisElement.removeClass('descbox').addClass('infobox');

			_thisElement.find('p.message').append(responseText.message);

			// Change the current cover src.
			$('.current_cover').attr('src', myDropzone.options.baseImgsrc);
		}
	});

	myDropzone.on('uploadprogress', function(file, progress, bytesSent) {

		_thisElement = $(file.previewElement);

		// Get the current file box progress bar, set its inner span's width accordingly.
		_thisElement.find('p.progressBar span').width(progress + '%');
	});

	myDropzone.on('complete', function(file) {

		_thisElement = $(file.previewElement);

		// Hide the progress bar.
		_thisElement.find('p.progressBar').fadeOut();

		// Remove the upload and cancel buttons.
		_thisElement.find('p.attach-ui').fadeOut( "slow", function() {
			$(this).remove();
		});

		// Doesn't matter what the result was, remove the ajax indicator.
		ajax_indicator(false);
	});

	myDropzone.on('sending', function(file) {

		_thisElement = $(file.previewElement);

		// Show the progress bar when upload starts.
		_thisElement.find('p.progressBar').fadeIn();

		// Hey! we are actually doing something!
		ajax_indicator(true);

		// And disable the start button
		_thisElement.find('.start').prop('disabled', true);
	});
});