/*
 Copyright (c) 2011, 2014 Jessica González
 @license http://www.mozilla.org/MPL/ MPL 2.0
*/

 // Mentioning
$(document).ready(function(){
	$('textarea[rel*=atwhoMention]').atwho({
		at: "@",
		tpl: "<li data-value='@${name}' data-user='${id}'>${name}</li>",
		callbacks: {
			remote_filter: function(query, callback) {

				if (query.length <= 2)
					return {name: '', id:''};

				$.ajax({
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
