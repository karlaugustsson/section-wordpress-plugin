jQuery(document).ready(function($){

var t;

var t2 = setInterval(update_section_link , 500);

var sections = $(".ka_section");

var lock = false;

var scrolling = false;

$(".ka_section_link").on("click",function(e){

		e.preventDefault();
		
		var link = this;

		lock = true;

		scroll_to_this_section("#" + link.dataset.section , 2000 );
		


		
});

$(window).scroll(function(){

scrolling = true;


if( lock == true ){ 

	return false;
}



clearInterval(t);



t = setTimeout( function(){
		

		scroll_end_function();
		
	},500);	

});




function scroll_to_this_section(sectionID,time,offset = 0){

   $('html, body').animate({

        scrollTop: $(sectionID).offset().top + offset
    },time,function(){
    	
    		lock = false;

    });
   

}

function update_section_link(){

	if ( scrolling == false){

		return false;
	}

	$(sections).each(function(){

		section = get_close_element(this);
		if(section != false){

			if(section_link_is_active(section.id) == false ){

				var link = get_link_matching_id(section.id);
			
				if(link != false){
					make_all_links_default_color();
					color_current_link_active(link);
				}

			}
		}
		
	})
}

   function make_all_links_default_color(){
   	
   	var links = $(".ka_section_link");

   	links.each(function(){

   		$(this).removeClass("active");
   	})
   
   }

function color_current_link_active(link){

$(link).addClass("active");
   
}

function scroll_end_function(){
	setTimeout(function(){
		scrolling = false;
	},1000)
	
	

	
}

function get_close_element(element){
	
	var element_relative_to_top = $(element).offset().top;


	window_position = $(window).scrollTop();
	
	element_position_top =  $(element).offset().top ;
	
	element_position_bottom = element_position_top + element.clientHeight;

	if ( window_position >= element_position_top && window_position <= element_position_bottom){
		
		return element ; 
	}

	return false;
}
function get_link_matching_id(id){
	var winner;
	$(".ka_section_link").each(function(){
		
		

		if ( id == this.dataset.section ){
			
			winner = this;
		}
	});

	if(typeof winner != undefined ){
	
		return winner; 
	}

	return false;
}
function section_link_is_active(id){
	
	var istrue = false;

	var sections = $(".ka_section_link.active");

	if ( sections.length == 0 ){
		
		istrue = false;

	}else{
		
		sections.each(function(){
		
			if ( this.dataset.section == id ){
				
				istrue = true;
			} 
		});
	}

	return istrue;



}

});