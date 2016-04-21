<?php
class Ka_section{

 	private $sections ;
 	private $post_type  = "section";
	private $current_page = 1;
	private $total_pages;
	public $section_query;

	public function __construct($args = null){
	$args = (isset($args) == true ) ? $args : array();
	$this->sections = $this->getAllSections($args) ;


	}
	public function getSection($id){
	
		if(!empty($this->sections)){
			
			foreach ($this->sections as $section) {
			
				if($section->ID == $id){
					return $section;
				}
			}	

	}
	return false;
}

	public function getSections(){
		return $this->sections;
	}
	 private function getAllSections($special_args){
	 	
	 	if($special_args == null){

	 		$args = array( 'post_type' => $this->post_type  , 'post_status' => array( "publish" , "public" ) );
	 	}else{

	 		$args = $special_args;
	
	 	}
			
	
	
		return $this->section_query($args);
	}

private function section_query($args){

	$loop = new WP_Query( $args );

	$this->section_query = $loop;

	$sections = $loop->get_posts();

	return $sections;
}

}