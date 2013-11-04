/**
 * breezeTabs.js
 *
 * The purpose of this file is handling tabs on user's profile
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

 jQuery(document).ready(function(){

	var tabs = {};

	var tabChange = function (newTab){

		currentActive = getCurrentActive();

		// Tell whatever tab is active at the moment to get lost...
		tabs[currentActive].active = false;
		jQuery(tabs[currentActive].href).fadeOut('slow', function() {

			// Remove the active class and add it to the newtab
			jQuery('li.'+ currentActive +' a').removeClass('active');
			jQuery('li.'+ newTab +' a').addClass('active');

			jQuery(tabs[newTab].href).fadeIn('slow', function() {});
		});

		// And set this as the active one...
		tabs[newTab].active = true;
	}

	var getCurrentActive = function (){

		var output = null,
			key;

		for (key in tabs) {
			if (tabs.hasOwnProperty(key)) {
				if (tabs[key].active == true){
					output = tabs[key].name;
				}
			}
		}

		return output;
	};

	// Get all available <li> tags
	jQuery('ul.breezeTabs li').each(function(){

		tabs[jQuery(this).attr('class')] = {
			href : jQuery(this).find('a').attr('href'),
			name : jQuery(this).attr('class'),
			active : (jQuery(this).attr('class') == 'wall') ? true : false
		};

		// Hide all tabs by default
		jQuery(tabs[jQuery(this).attr('class')].href).hide();

		// Make the wall page the active tab...
		jQuery(tabs['wall'].href).show();
	});

	// The Wall tab
	jQuery('li.wall a').click(function (e) {

		// Is it active already?
		if (tabs['wall'].active == true){
			return false;
		}

		else{
			tabChange('wall');
		}

		e.preventDefault();
		return false;
	});

	// Posts tab
	jQuery('li.posts a').click(function (e) {

		// Is it active already?
		if (tabs['posts'].active == true){
			return false;
		}

		else{
			tabChange('posts');
		}

		e.preventDefault();
		return false;
	});

	// Activity tab
	jQuery('li.activity a').click(function (e) {

		// Is it active already?
		if (tabs['activity'].active == true){
			return false;
		}

		else {
			tabChange('activity');
		}

		e.preventDefault();
		return false;
	});

	jQuery(window).hashchange();
 });
