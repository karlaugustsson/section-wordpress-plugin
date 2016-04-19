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
private function return_section_pages_format($pages_ids){
	
	switch ($pages_ids) {
		case false:
			return null;
		break;
		case is_array($pages_ids);
			if(is_array($pages_ids[0]) == true){
				return $pages_ids[0];
			}
			return $pages_ids;
		break;
		default:
			return $this->sections->getSection($pages_ids);
		break;
	}
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

private function destroy_section_pape_relationship($sectionID){
	print "fix this";
	die();
	// try {

	// if(is_int($sectionID) == false || $sectionID == null){

	// 	throw new Exception("unexpected value of the page section untouched", 1);
		
	// }
	// if(delete_post_meta($sectionID , $this->page_section_meta_key ) == true){
	// 	return true;
	// }
	// throw new Exception("this pages sectionrelationship was not deleted:" . $sectionID , 1);
	
	// } catch (Exception $e) {
	// 	print $e->getMessage();
	// 	die();
	// }


	// try {
	// 	if($section_pages == false || $section_pages == null || is_string($section_pages) == true){
	// 		throw new Exception("unexpected data null or false was expection string or array for page:" . $pageID);
	// 	}
	// 	if(update_post_meta($sectionID , $this->page_section_meta_key , $section_pages) == false)
	// 		throw new Exception("the data of ".$sectionID. " wasnt updated check this out crazy , check out data:<br>" . var_dump($page_sections) );
	// } catch (Exception $e) {
	// 	print $e->getMessage();
	// 	die();
	// }
}

public function update_section_pages($posted_pages , $sectionID){

		$page_ids_to_add = $posted_pages;

		$all_pages_id = $this->pages->get_ids_from_pages($this->pages->getPages());

		$remove_pages = $this->calculate_difference($all_pages_id , $page_ids_to_add);


		if(!empty($remove_pages)){

			foreach($remove_pages as $pageID){

				if($this->section_has_page($pageID , $sectionID ) == true){
		
					$this->delete_section_page_relationship($pageID,$sectionID);
				}
			}
		}

		if(!empty($page_ids_to_add)){
	
			foreach ($page_ids_to_add as $pageID) {
			
				if($this->section_has_page( $pageID , $sectionID) == false){
					
				$page_sections = $this->prepare_section_page_relationship( $pageID, $sectionID);

				$this->update_section_pages_relationship($sectionID , $page_sections);
			
				}
			}		
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

		$postion = get_next_page_section_pos($pageID , $sectionID);
		var_dump($position);
		die();
		if($wpdb->query("insert into $this->table_name() values($page_id,$sectionID,$positon);") == false ){
			throw new Exception("data not saved", 1);	
		}
	} catch (Exception $e) {
		
		print $e->getMessage();
		die();
		
	}
}
private function get_next_page_section_pos(){
	return 2;
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

}