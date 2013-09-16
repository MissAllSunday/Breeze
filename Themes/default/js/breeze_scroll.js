/**
 * breeze_scroll.js
 *
 * The purpose of this file is to handle all the client side code, the ajax call for the status, comments and other stuff
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica González
 * @license http://www.mozilla.org/MPL/MPL-1.1.html
 */

/*
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://missallsunday.com code.
 *
 * The Initial Developer of the Original Code is
 * Jessica González.
 * Portions created by the Initial Developer are Copyright (c) 2012, 2013
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

// Infinite scrolling
jQuery(document).ready(function(){

	// Hide the pagination
	jQuery('.pagelinks').hide();

	var numberOfScrollEvents = 0;

	// Fires up the infinite scrolling thingy
	TrackEventsForPageScroll();

	function TrackEventsForPageScroll()
	{
		jQuery(window).scroll(function(){

		var scrollPercent = GetScrollPercent();

			if(scrollPercent > 90 || scrollPercent < 95)
			{
				// Increment the number of scroll events
				numberOfScrollEvents++;

				// Have we reached the end?
				if (numberOfScrollEvents * window.breeze_maxIndex >= window.breeze_numberTimes)
				{
					noty({
						text: 'There aren\'t any more messages to fetch',
						timeout: 3500, type: 'success',
					});

					jQuery('#breeze_display_status').append('<li class="windowbg> There aren\'t any more messages to fetch</li>');
					return;
				}

				jQuery.ajax(
				{
					url: smf_scripturl + '?action=breezeajax;sa=fetch;js=1'  + window.breeze_session_var + '=' + window.breeze_session_id,
					data: ({commingFrom : window.breeze_commingFrom, userID : window.breeze_userID, maxIndex : window.breeze_maxIndex, numberTimes : numberOfScrollEvents, totalItems : window.breeze_totalItems}),
					cache: false,
					dataType: 'json',
					success: function(html)
					{
						// The server side found an issue
						if(html.type == 'success'){
							jQuery('#breeze_display_status').append(html.data);
						}

						else if(html.type == 'error'){
							noty({
								text: html.message,
								timeout: 3500, type: html.type,
								type: html.type,
							});
						}
					},
					error: function (html){
						noty({
							text: 'error',
							timeout: 3500,
							type: 'error',
						});
					},
				});
			}
		});
	}

	// Check if we are near the end of the page
	function GetScrollPercent()
	{
		 var bottom = jQuery(window).height() + jQuery(window).scrollTop();
		 var height = jQuery(document).height();

		 return Math.round(100*bottom/height);
	}
});