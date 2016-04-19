
jQuery(document).ready(function($) {

	
		$(".page_select").change(function(){

			var pageid = parseInt(this.value);
			
			if( isNaN(pageid) == true || pageid == "undefined" || pageid === false ){
				return;
			}
		
		var data = {
			'action': 'find_sections',
			'pageID': pageid
		};

		$.post(ajaxurl, data, function(response) {
			
			var html = $("#section_listing");
			
			html.html(response);
			create_drag_event_for_sections();
			create_section_submit_pages_event();

		});

		});

	function get_section_page_ids(){
		var result  = [];

		$('input[name="section_page_ids[]"]').each(function(){
				result.push(this.value);
		});

		return result ; 
	}
	function create_drag_event_for_sections(){
	
		$('#section-list').sortable(); 
	}
	function create_section_submit_pages_event(){
		$("#ka_section_order_form").submit(function(e){
		e.preventDefault();
		ids = get_section_page_ids()
		var pageID = $('input[name="page_id"]')[0].value;
		var message = $("#section_order_message");
		var data = {
		'action': 'update_section_order',
		'section_ids':ids , 
		'page_id': pageID
		};

			$.post(ajaxurl, data, function(response) {
			
			
			message.html(response);
			
			});

			});
	}		

	});