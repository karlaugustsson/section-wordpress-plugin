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
public function getSectionPages($pageID){

		$sections = $this->section->getSections();
		$sectionPages = [];

		foreach ($sections as $section) {
		$section_pages = $this->get_section_page_ids($section->ID);
		
		if($page_section != null){
			
			foreach( $page_section as $section_id){
	
				if($section_id == $sectionID){

					$sectionPages [] = $page;
				}
			}		
		}

		}
		return $sectionPages;
}
private function get_section_page_ids($sectionID){

		$page_ids = get_post_meta($sectionID , $this->page_section_meta_key);
		
		return $this->return_section_pages_format($page_ids);
}
private function get_page_section($pageID){

		$section_ids = $this->get_page_section_ids($pageID);

		$section_ids = $this->return_page_sections_format($section_ids);
	
		if(is_array($section_ids)){

			$result = array();
			foreach ($section_ids as $sectionID) {
				$result[] = $this->sections->getSection($sectionID);
				}
			return $result;
		}

}
public function section_has_page($pageID , $sectionID){
	$section_pages = $this->get_section_page_ids($pageID);

	if(is_string($section_pages) && $section_pages == $pageID || is_array($section_pages) && in_array_r($pageID, $section_pages) == true){
		
		return true;
	}

	return false;
}
private function delete_section_page_relationship($pageID , $sectionID){

	$section_pages = $this->get_page_section_ids($pageID);

	if(is_string($section_pags) && $section_pages == $pageID){

		$this->destroy_section_page_relationship($sectionID);

	}else if(is_array($section_pages)){

		$key_to_remove = array_search($pageID, $section_pages);
		
		
		unset($section_pages[$key_to_remove]);

		if(!empty($section_pages)){
			$section_pages = array_values($section_pages);
			$this->update_section_pages_relationship($sectionID , $section_pages);
			if(count($section_pages) == 1 ){
				array_shift($section_pages);	
			}
		}else{
			$this->delete_section_page_relationship($sectionID);
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

		$section_pages = $pageID;
		

	}else{
		
		$section_pages = array((INT)$section_pages);
		array_push($section_pages,$pageID);		
	}

	return $page_sections;
	
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
		var_dump($sectionID);
		var_dump($section_pages);
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
		$section_ids = $this->get_page_section_ids($page->ID);
		

		$result = array();
		foreach($section_ids as $id){
		
			$result[] = $this->sections->getSection($id);
		}
		
		return $result;
	} catch (Exception $e) {
		print $e->getMessage();
	}

}

}