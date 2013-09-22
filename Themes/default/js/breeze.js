/**
 * breeze.js
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

// The status stuff goes right here...
jQuery(document).ready(function(){

	// Some re-usable vars
	var breeze = {};

	breeze.loadImage = '<img src="' + smf_default_theme_url + '/images/breeze/loading.gif" />';

	// Posting a new status
	jQuery('#form_status').submit(function(event){

		var statusData = {};

		// Get all the values we need
		jQuery('#form_status :input').each(function(){
			var input = $(this);
			statusData[input.attr('name')] = input.val();
		});
		console.log(statusData);

		// Temp
		return false;
	});

});
