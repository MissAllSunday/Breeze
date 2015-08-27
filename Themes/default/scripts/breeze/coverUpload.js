/*
 Copyright (c) 2011, 2015, Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
*/

$(function () {
	var uploadButton = $('<a/>')
		.addClass('button_submit uploadButton')
		.prop('disabled', true)
		.text('upload')
		.one('click', function (e) {
			e.preventDefault();
			$(this).data().submit();
		}),
	cancelButton = $('<a/>')
		.addClass('button_submit cancelButton')
		.prop('disabled', false)
		.text('cancel')
		.one('click', function (e) {
			e.preventDefault();
			var inData = $(this).data();
			$('.b_cover_preview').fadeOut('slow', function() {
				$('#files').empty();
				delete inData.files;
				$('#fileupload').prop('disabled', false);
			});
		});

		$('#fileupload').fileupload({
			dataType: 'json',
			url : _coverUpload.url,
			autoUpload: false,
			getNumberOfFiles: 1,
			acceptFileTypes: /(\.|\/)(gif|jpg|png)$/i,
			disableImageResize: /Android(?!.*Chrome)|Opera/
				.test(window.navigator.userAgent),
			previewMaxWidth: 100,
			previewMaxHeight: 100,
			previewCrop: true,
			maxNumberOfFiles: 1,
			add: function (e, data) {
				data.context = $('#files');
				$('#fileupload').prop('disabled', true);
				$.each(data.files, function (index, file) {

					var node = $('<p/>').addClass('b_cover_preview').append('<img src="' + URL.createObjectURL(file) + '"/ style="max-width: 300px;">');
					node.appendTo(data.context);
				});
				data.context.append(uploadButton.clone(true).data(data));
				data.context.append(cancelButton.clone(true).data(data));
			}
		}).on('fileuploaddone', function (e, data) {

			$('#fileupload').prop('disabled', false);
			ajax_indicator(false);
			if (data.result.error) {
				data.abort();
				$('.b_cover_preview').replaceWith('<div class="errorbox">' + data.result.error + '</div>');
			}

			else {
				$('.b_cover_preview').replaceWith('<div class="'+ data.result.type +'box">' + data.result.message + '</div>');

				// Replace the old cover preview with the new one.
				if (data.result.type == 'info') {
					var image = JSON.parse(data.result.data);
					// Gotta make sure it exists...
					var imgsrc = _coverUpload.imgsrc;
					var imgcheck = imgsrc.width;

					if (imgcheck != 0)
						$('.current_cover').attr('src', imgsrc);
				}
			}

			data.abort();
		}).on('fileuploadfail', function (e, data) {
				ajax_indicator(false);
				$('.b_cover_preview').replaceWith('<div class="errorbox">'+ _coverUpload.error +'</div>');
				data.abort();
		}).on('always', function (e, data) {

		});
	});
