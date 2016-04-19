<?php 
class KaPageSections{

	private $sections , $pages,
	 $page_section_relationship_data,
	 $table_name = "ka_section_pages";

public function __construct(Ka_page $pages ,Ka_section $sections){
		
		$this->sections = $sections ; 
		$this->pages = $pages;
		$this->page_section_relationship_data = $this->set_section_page_relationships();
}

public function update_section_postition($pageID , $section_ids){
	
	$pageID = (INT)$pageID;
	$section_ids = (INT)$section_ids;

	foreach( $section_ids as $ID){
		if($this->section_has_page($pageID , $ID) == false ){
			return false;
		}
	}
	//$this->$this->update_section_pages_position($sectionID , $section_pages)
	return true;



}
public function section_belongs_to_current_page($sectionID , $current_page){
	return $this->section_has_page($current_page , $sectionID);
}

public function getSectionPages($sectionID){

		$sectionPagesIds = $this->get_section_page_ids($sectionID);
		$sectionPages = array();
		if(is_string($sectionPagesIds) == true){
			$sectionPages[] = $this->getPage($sectionPagesIds);
		}
		else if (is_array($sectionPagesIds) == true ){

			foreach ($sectionPagesIds as $page_id ) {
			$sectionPages[] = $this->pages->getPage($page_id);

			}

		}else{
			return false;
		}

		return $sectionPages;
}

private function get_section_page_ids($sectionID){

		$page_ids = get_post_meta($sectionID , $this->page_section_meta_key);

		return $this->return_section_pages_format($page_ids);
}
public function get_page_sections($pageID){

	print "not working get_page_sections";
die();

}
public function section_has_page($pageID , $sectionID){

	$result = false;

	foreach ($this->get_section_pages_relationships() as $relationdata){
		
		if($relationdata->page_id == $pageID && $relationdata->section_id == $sectionID){
			$result = true ; 
		}
	}
	
	return $result ; 
}

private function destroy_section_page_relationship($pageID , $sectionID){
	
	global $wpdb;

	try {

		
		$query = "DELETE FROM $this->table_name WHERE page_id = $pageID AND section_id = $sectionID;";
		
		if($wpdb->query($query) == false ){
			
			throw new Exception("data WAS NOT REMOVED WERE SORRRRRRY ABOUT THIS REALLY SORRY", 1);	
		}else{
			
			$this->page_section_relationship_data = $this->set_section_page_relationships();
		}
		

	} catch (Exception $e) {
		
		print $e->getMessage();
		die();
		
	}

}

public function update_section_pages( $posted_pages , $sectionID ){



//remove pages that does not exist from post_data
$i = 0;
foreach( $posted_pages as $pageID ):


	if ( !$this->pages->page_exist( $pageID ) ){
		unset($posted_pages[$i]);
	}else{
		$posted_pages[$i] = (INT)$pageID; 
	}
	$i++;
endforeach;

$pages_to_be_removed = $this->get_pages_to_remove($sectionID,$posted_pages);

foreach($posted_pages as $page_id){

	if ( !$this->section_has_page( $page_id , $sectionID ) ){
	
		$this->update_section_page_relationship($sectionID , $page_id);

	}
}

foreach($pages_to_be_removed as $page_id){

	$this->destroy_section_page_relationship($page_id , $sectionID );
}

}

private function set_section_page_relationships(){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM $this->table_name");
}

public function get_section_pages_relationships(){
	return $this->page_section_relationship_data;
}
private function update_section_page_relationship($sectionID ,$pageID ){
	global $wpdb;

	try {

		$position = $this->get_next_page_section_pos($pageID , $sectionID);
		
		$query = "INSERT INTO $this->table_name (page_id , section_id , page_section_position) values($pageID,$sectionID,$position);";
		
		if($wpdb->query($query) == false ){
			
			throw new Exception("data not saved for real", 1);	
		}else{

			$this->page_section_relationship_data = $this->set_section_page_relationships();
		}
		

	} catch (Exception $e) {
		
		print $e->getMessage();
		die();
		
	}
}
private function get_next_page_section_pos($pageid){
 

 $highest_position = 0;
 
 foreach($this->page_section_relationship_data as $data){

 	if($data->page_id == $pageid){

 	
 		if($data->page_section_position > $highest_position){
 			$highest_position = $data->page_section_position;
 			
 		}
 	}
 }

 return $highest_position + 1;

}

public function getSectionsByPagePostname($post_name){
	
	try {
		$page = $this->pages->find_page_by_post_name($post_name);

		$sections = $this->sections->filter_sections_by_page_id($page->ID);
	
		return $sections;
	} catch (Exception $e) {
		print $e->getMessage();
	}

}

private function get_pages_to_remove($sectionID , $pages_to_be_added){
	$pages_to_remove = array();
	
	foreach($this->get_section_pages_relationships() as $data){
	
		if( !in_array( $data->page_id , $pages_to_be_added ) && $this->section_has_page( $data->page_id , $sectionID) == true){
			$pages_to_remove[] = $data->page_id;
		}
	}
	return $pages_to_remove;
}

}