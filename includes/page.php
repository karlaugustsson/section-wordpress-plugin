<?php 

class Ka_page{

	public $pages , $sections ;
	private $page_section_meta_key = "_page_section";

	public function __construct($args = null){
	
	$args[0] = (!empty($args[0])) ? $args[0] : "ASC";
	$args[1] = (!empty($args[0])) ? $args[1] : 0 ;
	$args[2] = (!empty($args[0])) ? $args[2] : 0 ;
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

public function PageSections($page){
		$section_keys =  get_post_meta($page , $this->page_section_meta_key);
		switch ($section_keys) {
			case false:
				return null;
			break;
			case is_array($section_keys);
			return $section_keys[0];
			break;
			default:
				return $section_keys;
			break;
		}


}

public function page_has_section($sectionID , $pageID){
	
	$page_sections = $this->pageSections($pageID);
	
	if(is_string($page_sections) && $page_sections == $sectionID || is_array($page_sections) && in_array($sectionID, $page_sections) == true){
	
		return true;
	}
	return false;
}

private function get_ids_from_pages($pages){
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
	}
	return get_page_by_path( $post_name );
}
public function get_page_title_by_post_name($post_name){

	$title = $this->find_page_by_post_name($post_name) ;

	if ( $title != false){
		return $title->post_name;
	}
}
private function delete_page_section_relationship($pageID , $sectionID){

	$page_sections = $this->pageSections($pageID);

	if(is_string($page_sections) && $page_sections == $sectionID){

		$this->delete_section($pageID);

	}else if(is_array($page_sections)){
		$key_to_remove = array_search($sectionID, $page_sections);
		
		
		unset($page_sections[$key_to_remove]);

		if(!empty($page_sections)){
			$page_sections = array_values($page_sections);
			$this->update_sections($pageID , $page_sections);
			if(count($page_sections) == 1){
				$page_sections = $page_sections[0] ;
				
				
			}
		}else{
			$this->delete_section($pageID);
		}

	}else{
		die("check this out you cow i dont know what to do :(");
	}

}
private function calculate_difference($all_pages , $posted_pages){

	$values_to_delete = array_intersect( $all_pages , $posted_pages);

	foreach ($values_to_delete as $value) {
		$key = array_search($value, $all_pages);
		if($key !== false){
			unset($all_pages[$key]);
		}
	}
	return $all_pages;
}
private function add_page_section_relationship($pageID , $sectionID){
	$page_sections = $this->pageSections($pageID);

	if($page_sections == null){

		$page_sections = $sectionID;
		

	}else if(is_string($page_sections)){
		
		$page_sections = array((INT)$page_sections);
		array_push($page_sections,$sectionID);
	}else{

		if(is_array($page_sections) == true){

			array_push($page_sections,$sectionID);;
		}else{
			die("check this out");
		}
		
	}
	
	return $page_sections;
	
}
public function  getSectionsByPagePostname( $post_name ){
	try {
		$page = $this->find_page_by_post_name($post_name);
		$section_ids = $this->pageSections($page->ID);

		$section = new Ka_section;

		$result = array();
		foreach($section_ids as $id){
		
			$result[] = $section->getSection($id);
		}
		
		return $result;
	} catch (Exception $e) {
		print $e->getMessage();
	}

}

private function delete_section($pageID){

	try {
	if(is_int($pageID) == false || $pageID == null){

		throw new Exception("unexpected value of the page section untouched", 1);
		
	}
	if(delete_post_meta($pageID , $this->page_section_meta_key ) == true){
		return true;
	}
	throw new Exception("this pages sectionrelationship was not deleted:" . $pageID , 1);
	
	} catch (Exception $e) {
		print $e->getMessage();
		die();
	}


}

public function getPageSections($pageID){
	$sections = new Ka_section();
	$result = $sections->getSection($pageID);
	var_dump($sections);
}
private function update_sections($pageID , $page_sections){

	try {
		if($page_sections == false || null){
			throw new Exception("unexpected data null or false was expection string or array for page:" . $pageID);
		}
		if(update_post_meta($pageID , $this->page_section_meta_key , $page_sections) == false)
			throw new Exception("the data of ".$pageID. " wasnt updated check this out crazy , check out data:<br>" . var_dump($page_sections) );
	} catch (Exception $e) {
		print $e->getMessage();
		die();
	}
}

	public function update_page_section_relationship($posted_pages , $sectionID){

		if(!is_array($posted_pages)){
			$posted_pages = array();
		}
		$pages_to_add_section_relationship = $posted_pages;

		$all_pages_id = $this->get_ids_from_pages($this->pages);

		$pages_remove_section_relation_ship = $this->calculate_difference($all_pages_id , $pages_to_add_section_relationship);
		
		if(!empty($pages_remove_section_relation_ship)){

			foreach($pages_remove_section_relation_ship as $page){

				if($this->page_has_section($sectionID , $page) == true){
				
					$this->delete_page_section_relationship($page,$sectionID);
				}
			}
		}

		if(!empty($pages_to_add_section_relationship)){
	
			foreach ($pages_to_add_section_relationship as $page) {
			

				if($this->page_has_section($sectionID , $page) == false){
				
				$page_sections = $this->add_page_section_relationship( $page, $sectionID);

				$this->update_sections($page , $page_sections);
				
				}
			}		
			
		}

		

	}
}