<?php
class Ka_page{

	public $pages , $sections ;
	private $page_section_meta_key = "_page_section";

	public function __construct($args = null){
	
	$args[0] = (!empty($args[0])) ? $args[0] : "ASC";
	$args[1] = (!empty($args[1])) ? $args[1] : 0 ;
	$args[2] = (!empty($args[2])) ? $args[2] : 0 ;
	global $post;
 	$args = array(
		'sort_order' => $args[0],
		'sort_column' => 'post_title',
		'hierarchical' => 1,
		'exclude' => '',
		'include' => '',
		'meta_key' => '',
		'meta_value' => '',
		'authors' => '',
		'child_of' => 0,
		'parent' => -1,
		'exclude_tree' => '',
		'number' => $args[1],
		'offset' => $args[2],
		'post_type' => 'page',
		'post_status' => 'publish'
); 
try {
	
	$this->pages = get_pages($args);
	
	if($this->pages == false){

		throw new Exception("check your parameters for the page no page found", 1);
		
	}

} catch (Exception $e) {

	echo $e->getMessage();
}


}
public function getPages(){

		return $this->pages;

}
public function get_ids_from_pages($pages){
	$id_array = array();
	foreach ($pages as $page) {
		$id_array [] = $page->ID;
	}
	return $id_array;
}
public function find_page_by_post_name($post_name){
	
	if( !empty($this->pages)){
			foreach ($this->pages as $page) {

				if($page->post_name == $post_name){

					return $page;
				}

			}
			return false;
	}
	
}
public function page_exist($id){
	$result = false;
	foreach($this->pages as $page){
		
		if($page->ID == $id){
			$result = true;
		}
	}
	return $result;
}
public function get_page_title_by_post_name($post_name){

	$title = $this->find_page_by_post_name($post_name) ;

	if ( $title != false){

		return $title->post_name;
	}
	return false;
}

public function GETpage($page_id){

	foreach ($this->pages as $page) {

		if($page_id == $page->ID){
			return $page;
		}
	
	}
	return false;
}
}