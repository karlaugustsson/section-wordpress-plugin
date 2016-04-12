
	jQuery(document).ready(function($) {
		
		alert("hahahhaha");
		$(".page_select").change(function(){
			var pageid = this.value;
		
		var data = {
			'action': 'find_sections',
			'pageID': pageid
		};

		jQuery.post(ajaxurl, data, function(response) {
			alert('Got this from the server: ' + response);
		});

		})


		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php

	});