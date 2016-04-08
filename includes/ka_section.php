<?php 

class Ka_section{

 	private $sections ;
 	private $post_type  = "section";
	private $page_section_meta_key = "_page_section";

	public function __construct($single_section = null){
	
	$this->sections = ($single_section != null ) ? get_post($single_section) : $this->getAllSections() ;


	}
	public function getSection($id){

		if(!empty($this->sections)){
			
			foreach ($this->sections as $section) {
			
				if($section->ID == $id){
					return $section;
				}
			}

			throw new Exception("no section was found", 1);

		}else{

				$thepost = get_post($id);

				if($thepost == false){

					throw new Exception("no section was found", 1);
				}
				return $thepost;
		}

	}

	public function getSectionPages($section){
		$args = array(
        'post_type'     => 'page',
        'post_status'   => 'publish',
        'meta_query' => array(
            array(
                'key' => $this->page_section_meta_key,
                'value' => $section->ID,
                'compare' => "LIKE"
            )
        )
        );
        $page_query = new WP_Query($args);

        if($page_query->have_posts()){
        	
        	return $page_query->get_posts();
        }else{
        	return false;
        }

	}
	public function getSections(){
		return $this->sections;
	}
	 private function getAllSections(){

		$args = array( 'post_type' => $this->post_type);

		$loop = new WP_Query( $args );
		
		$sections = $loop->get_posts();
		
		

		$loop->wp_reset_query();

		return $sections;
	}



}