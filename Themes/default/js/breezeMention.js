/**
 * breeze.js
 *
 * The purpose of this file is to handle all the client side code, the ajax call for the status, comments and other stuff
 * @package Breeze mod
 * @version 1.0
 * @author Jessica González <suki@missallsunday.com>
 * @copyright Copyright (c) 2011, 2014 Jessica González
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

 // Mentioning
jQuery(document).ready(function(){
	jQuery('textarea[rel*=atwhoMention]').atwho({
		at: "@",
		tpl: "<li data-value='@${name}' data-user='${id}'>${name}</li>",
		callbacks: {
			remote_filter: function(query, callback) {

				if (query.length <= 2)
					return {name: '', id:''};

				jQuery.ajax({
					url: smf_scripturl + '?action=breezeajax;sa=usersmention;js=1' + breeze.session.v + '=' + breeze.session.id,
					type: "GET",
					data: {match: query},
					dataType: "json",
					success: function(data){
						callback(data);
					},
					error: function(data){
					}
				});
			},
			before_insert: function(value, li)
			{
				var userID, name;

				userID = li.data('user');
				name = li.data('value');

				// Set a "global" var to be picked up by the posting functions
				window.breeze.mentions[userID.toString()] = {'id': userID, 'name': name.replace('@', '')};

				return value;
			}
		}
	});
});
