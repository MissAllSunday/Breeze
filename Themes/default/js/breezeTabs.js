/**
 * breezeTabs.js
 *
 * @package Breeze mod
 * @version 1.0 Beta 3
 * @author Jessica Gonz�lez <suki@missallsunday.com>
 * @copyright Copyright (c) 2013 Jessica Gonz�lez
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
 * Jessica Gonz�lez.
 * Portions created by the Initial Developer are Copyright (c) 2012
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 */

jQuery(function() {
	jQuery('#breezeTabs > div').hide();
	jQuery('#breezeTabs div:first').fadeIn('slow');
	jQuery('#breezeTabs ul li:first').addClass('active');
	jQuery('#breezeTabs ul li a').click(function(){
		jQuery('#breezeTabs ul li.active').removeClass('active');
		jQuery(this).parent().addClass('active');
		var selectedTab=jQuery(this).attr('href');
		jQuery('#breezeTabs > div').fadeOut('slow', function() {
			jQuery(selectedTab).delay(100).fadeIn('slow');
		});        
		return false;
	});
});