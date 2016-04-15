
jQuery(document).ready(function($) {

	
		$(".page_select").change(function(){

			var pageid = parseInt(this.value);
			
			if( isNaN(pageid) == true || pageid == "undefined" || pageid === false ){
				return;
			}
		
		var data = {
			'action': 'find_page_sections',
			'pageID': pageid
		};

		$.post(ajaxurl, data, function(response) {
	
			var html = $("#section_listing");
			
			if(typeof response.message != 'undefined' ){
				html.html(response.message);
				return;
			}
			console.log(response);
			html.html(response);

		});

		});

	});