/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/MPL-1.1.html
*/

var breezeTabs = function(list, defaultTab){
	this.list = $(list);
	this.defaultTab = defaultTab;

	// Make it happen!
	this.init();
};

breezeTabs.prototype.init = function(){

	var tabs = [],
		listObject = this;

	this.list.children('li').each(function(){

		var currentName = $(this).attr('class');

		tabs[currentName] = {
			href : $(this).find('a').attr('href'),
			name : currentName,
			active : (currentName == listObject.defaultTab)
		};

		// Hide all tabs by default
		if (tabs[currentName].active != true){
			$(tabs[currentName].href).hide();
		}

		$(this).find('a').on('click', false, function(e){

			var thisAnchor = $(this);

			// Is it active already?
			if (tabs[currentName].active != true){
				currentActive = listObject.getCurrentActive(tabs);

				// Tell whatever tab is active at the moment to get lost...
				tabs[currentActive].active = false;
				$(tabs[currentActive].href).fadeOut('slow', function() {

					// Remove the active class and add it to the newtab
					$('.'+ currentActive).find('a').removeClass('active');
					thisAnchor.addClass('active');

					$(tabs[currentName].href).fadeIn('slow');
				});

				// And set this as the active one...
				tabs[currentName].active = true;
			}

			e.preventDefault();
			return false;
		});
	});
};

breezeTabs.prototype.getCurrentActive = function (tabs){

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
