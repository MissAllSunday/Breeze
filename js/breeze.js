

/* Css/Jquery tabs courtesy of Soh Tanaka at <http://www.sohtanaka.com/web-design/simple-tabs-w-css-jquery/> */
$(document).ready(function() {

	//When page loads...
	$(".breeze_tabs_content").hide(); // Hide all content
	$("ul.breeze_tabs li:first").addClass("active").show(); // Activate first tab
	$(".breeze_tabs_content:first").show(); //Show first tab content

	//On Click Event
	$("ul.breeze_tabs li").click(function() {

		$("ul.breeze_tabs li").removeClass("active"); // Remove any "active" class
		$(this).addClass("active"); // Add "active" class to selected tab
		$(".breeze_tabs_content").hide(); // Hide all tab content

		var activeTab = $(this).find("a").attr("href"); // Find the href attribute value to identify the active tab + content
		$(activeTab).fadeIn(); // Fade in the active ID content
		return false;
	});

});

/* The ajax stuff goes right here... */
	function breezeAjaxForm(form_id, form_div_to_post, type){

		$.ajax({
			type: 'POST',
			url: smf_scripturl + '?action=wall;sa=post',
			async: false,
			beforeSend: function(x) {
				/* Validation stuff here */
			},
		 dataType: "json",
		 success: function(data){
			//do your stuff with the JSON data
		 }
		});
	}

$(document).ready(function() {  
    new breezeAjaxForm('status');
});