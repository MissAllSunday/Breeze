/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

jQuery(document).ready(function(){

	// Fires up the load more thingy
	if (breeze.currentSettings.load_more){

		var numberOfEvents = 0;

		// Hide the normal pagination.
		jQuery('.pagelinks').hide();

		showMoarButton();
	}

	function showMoarButton(){

	// Don't show anything if there isn't enough items to display...
	if (breeze.pagination.totalItems <= breeze.pagination.maxIndex)
		return false;

		// Add a nice button...
		jQuery('<button/>', {
			id: 'loadMoar',
			class: 'clear',
			text: breeze.text.load_more,
			click: function () {

				numberOfEvents++;

				jQuery('<ul/>', {
					id: 'tempDIV_'+ numberOfEvents,
					class: 'breeze_status',
					style: ''
				}).appendTo('#breezeAppendTo').hide();

				// Append some more data to breeze.pagination.
				breeze.pagination.numberTimes = numberOfEvents;
				breeze.pagination.comingFrom = breeze.tools.comingFrom;

				jQuery.ajax(
				{
					// Send the data and oh boy there's a lot of it!
					url: smf_scripturl + '?action=breezeajax;sa=fetch;js=1;' + smf_session_var + '=' + smf_session_id,
					data : breeze.pagination,
					cache: false,
					dataType: 'json',
					success: function(html)
					{
						// The server response as a JSON object
						if(html.type == 'success'){

							// Append the html to our temp DIV.
							if (html.data != 'end'){

								// Create a unique UL for this very own status, isn't it great?
								jQuery('#tempDIV_'+ numberOfEvents).append(html.data).fadeIn('slow', 'linear', function(){});
							}

							// No more data:(
							else{
								noty({
									text: breeze.text.page_loading_end,
									timeout: 3500,
									theme: 'breezeNoti',
									type: 'success'
								});
								jQuery('#loadMoar').fadeOut('slow');
								return;
							}
						}

						else if(html.type == 'error'){
							breeze.tools.showNoti(html);
						}
					},
					error: function (html){
						breeze.tools.showNoti(html);
					}
				});
			}
		}).appendTo('#tab-wall');
	}

	// Check if we are near the end of the page
	function GetScrollPercent()
	{
		 var bottom = jQuery(window).height() + jQuery(window).scrollTop();
		 var height = jQuery(document).height();

		 return Math.round(100*bottom/height);

	}
});