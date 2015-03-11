/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

var breezeLoadMore = function(oOptions) {
	this.options = oOptions;

	this.numberOfEvents = 0;

	this.showMoarButton();
};

breezeLoadMore.prototype.showMoarButton = function()
{
	// Need to keep a reference...
	var oObject = this;

	// Don't show anything if there isn't enough items to display...
	if (this.options.pagination.totalItems <= this.options.pagination.maxIndex)
		return false;

	// Replacing any pagination div?
	if (this.options.hidePagination)
		$('.pagesection').hide();

	// The rest is pretty straight forward...
	$('<button/>', {
		id: this.options.button.id,
		class : 'button_submit loadMoar',
		text: this.options.button.text,
	}).appendTo(this.options.button.appendTo).on('click', function(){console.log(oObject);
		oObject.clickButton();
	});
}

breezeLoadMore.prototype.clickButton = function()
{
	// Add one to the number of iterations, pretty important!
	this.numberOfEvents++;

	// Need to keep a reference...
	var oObject = this;

	$('<ul/>', {
		id: 'tempDIV_'+ this.numberOfEvents,
		class: this.options.target.css,
		style: ''
	}).appendTo(this.options.target.appendTo).hide();

	// Let us pass some data to the server.
	passingData = {
		numberTimes : this.numberOfEvents,
		comingFrom : breeze.tools.comingFrom,
};
	// And fill the rest with options.pagination object.
	$.extend(passingData, this.options.pagination);

	$.ajax({
		// Send the data and oh boy there's a lot of it!
		url: smf_scripturl + '?action=breezeajax;sa='+ oObject.options.urlSa +';js=1;' + smf_session_var + '=' + smf_session_id,
		data : passingData,
		cache: false,
		dataType: 'json',
		success: function(html)
		{
			oObject.onSuccess(html);
		},
		error: function (html){
			breeze.tools.showNoti(html);
		}
	});
}

breezeLoadMore.prototype.onSuccess = function(html)
{
	// The server response as a JSON object
	if(html.type == 'info'){

		// Append the html to our temp DIV.
		if (html.data != 'end'){

			// Create a unique UL for this very own status, isn't it great?
			$('#tempDIV_'+ this.numberOfEvents).append(html.data).fadeIn('slow', 'linear', function(){});
		}

		// No more data:(
		else{
			noty({
				text: this.options.endText,
				timeout: 3500,
				theme: 'relax',
				type: 'success'
			});
			$('#' + this.options.button.id).fadeOut('slow');
		}
	}

	else if(html.type == 'error'){
		breeze.tools.showNoti(html);
	}
}







