/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
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

		var currentName = jQuery(this).attr('class');

		tabs[currentName] = {
			href : jQuery(this).find('a').attr('href'),
			name : jQuery(this).attr('class'),
			active : (currentName == 'wall') ? true : false
		};

		// Hide all tabs by default
		jQuery(tabs[currentName].href).hide();

		// Make the wall page the active tab...
		jQuery(tabs['wall'].href).show();

		jQuery('li.'+ currentName +' a').on('click', false, function(e){

			// Is it active already?
			if (tabs[currentName].active == true){
				return false;
			}

			else {
				tabChange(currentName);
			}

			e.preventDefault();
			return false;
		});
	});

	jQuery(window).hashchange();
 });
