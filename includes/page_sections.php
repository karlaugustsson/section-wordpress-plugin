<?php 
class KaPageSections{

	private $sections , $pages, $page_section_meta_key = "_section_pages";

public function __construct(Ka_page $pages ,Ka_section $sections){
		
		$this->sections = $sections ; 
		$this->pages = $pages;
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
	
		$all_sections = $this->sections->getSections();
		$result = array();

		foreach ($all_sections as $section ) {
			
			if ( $this->section_has_page($pageID ,$section->ID) === true ){
				array_push($result, $section);
			}
		}
		if(empty($result) == true){
			return false;
		}
		return $result;

}
public function section_has_page($pageID , $sectionID){
	
	$section_pages = $this->get_section_page_ids($sectionID);

	if(is_string($section_pages) && $section_pages == $pageID || is_array($section_pages) == true && in_array_r($pageID, $section_pages) == true){
		
		return true;
	}

	return false;
}
private function delete_section_page_relationship($pageID , $sectionID){

	$section_pages = $this->get_section_page_ids($sectionID);

	if(is_string($section_pages) && $section_pages == $pageID){

		$this->destroy_section_page_relationship($sectionID);

	}else if(is_array($section_pages) == true){

		$key_to_remove = array_search($pageID, $section_pages);
		
		
		unset($section_pages[$key_to_remove]);

		if(!empty($section_pages)){

			$section_pages = array_values($section_pages);
			$this->update_section_pages_relationship($sectionID , $section_pages);
			if(count($section_pages) == 1 ){
				array_shift($section_pages);	
			}
		}else{
			$this->destroy_section_pape_relationship($sectionID);
		}

	}else{
			
		
		die("check this out you cow i dont know what to do :(");
	}

}
private function calculate_difference($all_pages , $posted_pages){

	$values_to_delete = array_intersect( $all_pages , $posted_pages);

	foreach ($values_to_delete as $value) {
		$key = array_search($value, $all_pages);
		if($key !== false ){
			unset($all_pages[$key]);
		}
	}
	return $all_pages;
}
private function prepare_section_page_relationship($pageID , $sectionID){
	
	$section_pages = $this->get_section_page_ids($sectionID);

	if($section_pages == null ){

		$section_pages = (INT)$pageID;
		

	}else{

		if( count($section_pages) == 1){

			$section_pages[0] = (INT)$section_pages[0];

		}

		array_push($section_pages,(INT)$pageID);	
	}

	return $section_pages;
	
}
private function destroy_section_pape_relationship($sectionID){

	try {

	if(is_int($sectionID) == false || $sectionID == null){

		throw new Exception("unexpected value of the page section untouched", 1);
		
	}
	if(delete_post_meta($sectionID , $this->page_section_meta_key ) == true){
		return true;
	}
	throw new Exception("this pages sectionrelationship was not deleted:" . $sectionID , 1);
	
	} catch (Exception $e) {
		print $e->getMessage();
		die();
	}


	try {
		if($section_pages == false || $section_pages == null || is_string($section_pages) == true){
			throw new Exception("unexpected data null or false was expection string or array for page:" . $pageID);
		}
		if(update_post_meta($sectionID , $this->page_section_meta_key , $section_pages) == false)
			throw new Exception("the data of ".$sectionID. " wasnt updated check this out crazy , check out data:<br>" . var_dump($page_sections) );
	} catch (Exception $e) {
		print $e->getMessage();
		die();
	}
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
private function update_section_pages_relationship($sectionID ,$section_pages ){

	try {
	
		if(update_post_meta($sectionID , $this->page_section_meta_key,$section_pages) == false ){
			throw new Exception("data not saved", 1);
			
		}
	} catch (Exception $e) {
		print $e->getMessage();
		die();
		
	}
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