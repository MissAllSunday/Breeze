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

	// Fires up the load more thingy
	if (window.breeze_loadMore){

		var numberOfEvents = 0;

		// Hide the normal pagination.
		jQuery('.pagelinks').hide();

		showMoarButton();
	}

	function showMoarButton()
	{
		// Add a nice button...
		jQuery('<button/>', {
			id: 'loadMoar',
			class: 'clear',
			text: breeze_loadMore,
			click: function () {

				numberOfEvents++;

				jQuery('<div/>', {
					id: 'tempDIV_'+ numberOfEvents,
					class: 'clear',
					style: '',
				}).appendTo('#breezeAppendTo').hide();

				jQuery.ajax(
				{
					// Yes, this is a VERY large url...
					url: smf_scripturl + '?action=breezeajax;sa=fetch;js=1;commingFrom='+ window.breeze_commingFrom +';userID='+ window.breeze_userID +';maxIndex='+ window.breeze_maxIndex +';numberTimes='+ numberOfEvents +';totalItems='+ window.breeze_totalItems +';' + window.breeze_session_var + '=' + window.breeze_session_id,
					cache: false,
					dataType: 'json',
					success: function(html)
					{
						// The server response as a JSON object
						if(html.type == 'success'){

							// There are no more data to load!
							if (html.data != 'end'){
								jQuery('#tempDIV_'+ numberOfEvents).append(html.data).fadeIn('slow', 'linear', function(){});
							}

							else{
								noty({
									text: breeze_loadMore_no,
									timeout: 3500,
									type: 'success',
								});
								jQuery('#loadMoar').fadeOut('slow');
								return;
							}
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
							text: html,
							timeout: 3500,
							type: 'error',
						});
					},
				});
			},
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