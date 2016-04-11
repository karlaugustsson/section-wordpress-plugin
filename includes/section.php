<?php 

class Ka_section{

 	private $sections ;
 	private $post_type  = "section";
	private $page_section_meta_key = "_page_section";
	private $current_page = 1;

	public function __construct(){
	$number_per_page = 2;
	if($_GET['paged'] != null){

		$this->current_page = (INT)$_GET['paged'];
	}
	$this->sections = $this->getAllSections() ;


	}
	public function getSection($id){
		var_dump($this->sections);
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

		$args = array( 'post_type' => $this->post_type , 'posts_per_page' => 3 , 'paged' => $this->current_page,
			'meta_query' => array (
		    array (
		    'post_id' => '2',
			  'key' => '_page_section',
              'compare' => 'IN'
		    )
		  ) 		

	);
		
		$loop = new WP_Query( $args );
		
		$sections = $loop->get_posts();

		$loop->wp_reset_query();
	

		return $sections;
	}
public function the_crazy($page_id){

}


}