<?php
class Ka_section{

 	private $sections ;
 	private $post_type  = "section";
	private $page_section_meta_key = "_page_section";
	private $current_page = 1;
	private $total_pages;
	public $section_query;

	public function __construct($filtered = null){

	$this->sections = $this->getAllSections() ;


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

	public function getSections(){
		return $this->sections;
	}
	 private function getAllSections(){

		$args = array( 'post_type' => $this->post_type 	

	);
	
		return $this->section_query($args);
	}

private function section_query($args){
	$loop = new WP_Query( $args );

	$this->section_query = $loop;

	$sections = $loop->get_posts();


	return $sections;
}

}