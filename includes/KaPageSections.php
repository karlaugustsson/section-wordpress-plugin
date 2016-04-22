<?php 
class KaPageSections{

	private $sections , $pages,
	 $page_section_relationship_data;

	 public static $table_name = "ka_section_pages";

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

	return true;
}
public function getSectionPages($sectionID){

		$sectionPagesIds = $this->get_section_page_ids($sectionID);
		$sectionPages = array();

			foreach ($sectionPagesIds as $page_id ) {
			$sectionPages[] = $this->pages->getPage($page_id);

			}
		
		return $sectionPages;
}

private function get_section_page_ids($sectionID){

	$all_pages = $this->get_section_pages_relationships();
	$result = [];
	foreach($all_pages as $page){

		if($page->section_id == $sectionID){
			$result[] = $page->page_id;
		}
	}
	return $result;
}
public function delete_section_relationships($sectionID){
 $this->delete_relations_associated_with_section($sectionID);
}
public function delete_page_relationships($pageID){
 $this->delete_relations_associated_with_page($pageID);
}
private function delete_relations_associated_with_section($sectionID){
	$sectionID = (INT)$sectionID;

	global $wpdb;

		
		$query = "DELETE FROM self::$table_name WHERE section_id = $sectionID;";
		
		$wpdb->query($query);

			
		$this->page_section_relationship_data = $this->set_section_page_relationships();
		

}
private function delete_relations_associated_with_page($pageID){
	
	$pageID = (INT)$pageID;

	global $wpdb;

		
		$query = "DELETE FROM self::$table_name WHERE page_id = $pageID;";
		
		$wpdb->query($query);

			
		$this->page_section_relationship_data = $this->set_section_page_relationships();
}
public function get_page_sections($pageID){
	

	$section_ids = $this->get_section_ids_by_page_id($pageID);

	$result = array();
	
	foreach($section_ids as $section_id){
		
		$section = $this->sections->getSection($section_id);
		
		if($section != false){
			$result[] = $section;
		}
		
	}
	return $result;
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
		
		$query = "DELETE FROM self::$table_name WHERE page_id = $pageID AND section_id = $sectionID;";
		
	$wpdb->query($query);
			

			
	$this->page_section_relationship_data = $this->set_section_page_relationships();

}

public function get_section_ids_by_page_id($pageID){
	
	$we_belong_together = $this->page_section_relationship_data;
	
 	$result = array();

	foreach ($we_belong_together as $carey) {
		if($carey->page_id == $pageID){
			$result[] = $carey->section_id;

		}
	}

	return $result ;
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
	
		$this->save_section_page_relationship($sectionID , $page_id);

	}
}

foreach($pages_to_be_removed as $page_id){

	$this->destroy_section_page_relationship($page_id , $sectionID );
}

}

private function set_section_page_relationships(){
	global $wpdb;
	return $wpdb->get_results("SELECT * FROM self::$table_name ORDER BY page_section_position ASC");
}

public function get_section_pages_relationships(){

	return $this->page_section_relationship_data;
}
private function save_section_page_relationship($sectionID ,$pageID ){
	global $wpdb;

	try {

		$position = $this->get_next_page_section_pos($pageID , $sectionID);
	
		$query = "INSERT INTO self::$table_name (page_id , section_id , page_section_position) values($pageID,$sectionID,$position);";
		
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
private function count_page_sections($pageID){
	$relation_data = $this->get_section_pages_relationships();
	$i = 0;
	
	foreach ($relation_data as $data) {

		if($data->page_id == $pageID && $data->section_id != null ){
			$i++;
		}
	}
	return $i;
}
function attempt_update_page_section_position($pageID , $section_ids ){

		$position = 1;

		foreach($section_ids as $section_id){
			
			if($this->section_has_page($pageID , $section_id) == true){

				$this->update_section_page_position( (INT)$section_id ,$pageID, $position );
				$position++;
			}
	    }
}
private function update_section_page_position($sectionID ,$pageID ,$position = null){
	
	global $wpdb;
	$sectionID = (INT)$sectionID;
	$pageID = (INT)$pageID;
	try {
		if($position == null){

			$position = $this->get_next_page_section_pos($pageID , $sectionID);
		}

		$query = "UPDATE self::$table_name SET page_section_position = $position  WHERE page_id = $pageID AND section_id = $sectionID;";
		
		if($wpdb->query($query) == false && $wpdb->last_error  != ""){
			
			throw new Exception("something bad happened", 1);

		}else{

			$this->page_section_relationship_data = $this->set_section_page_relationships();
		}
		

	} catch (Exception $e) {
	
		print $e->getMessage();
	
		
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