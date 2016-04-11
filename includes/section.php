<?php 

class Ka_section{

 	private $sections ;
 	private $post_type  = "section";
	private $page_section_meta_key = "_page_section";
	private $current_page = 1;
	private $total_pages;
	private $number_per_page;

	public function __construct(){

	$user = get_current_user_id();

	$option = get_option('per_page', 'cmi_ka_sections_per_page');
	$per_page = get_user_meta($user, $option, true);
	if ( empty ( $per_page ) || $per_page < 1 ) {
 
    $per_page = get_option( 'per_page', 'default' );
 
	}


	$this->number_per_page = (INT)$per_page;


	$this->current_page = ((INT)$_GET['paged'] != null ) ? $_GET['paged'] : $this->current_page;
	
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
	public function total_pages(){
		//var_dump($this->sections);
		return $this->total_pages;
	}
	public function getSections(){
		return $this->sections;
	}
	 private function getAllSections(){

		$args = array( 'post_type' => $this->post_type , 'posts_per_page' => $this->number_per_page , 'paged' => $this->current_page	

	);
	
		return $this->section_query($args);
	}
public function filter_sections_by_page_id($page_id){

		$args = array( 'post_type' => $this->post_type , 'posts_per_page' => $this->number_per_page , 'paged' => $this->current_page	,
			   'meta_query' => array(
        array(
            'key' => '_section_pages',
            'value' => $page_id,
            'compare' => 'LIKE'
        )
    )

	);

	return $this->section_query($args);
}
private function section_query($args){

	$loop = new WP_Query( $args );
		
	$sections = $loop->get_posts();

	$this->total_pages = $loop->max_num_pages;
		


	return $sections;
}


}