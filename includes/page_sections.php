<?php 
class KaPageSections{

	private $sections , $pages, $page_section_meta_key = "_page_section";

public function __construct(Ka_page $pages ,Ka_section $sections){
		
		$this->sections = $sections ; 
		$this->pages = $pages;
}
private function return_page_sections_format($section_ids){
	switch ($section_ids) {
		case false:
			return null;
		break;
		case is_array($section_ids);
			if(is_array($section_ids[0]) == true){
				return $section_ids[0];
			}
			return $section_ids;
		break;
		default:
			return $this->sections->getSection($sectionID);
		break;
	}
}
public function getSectionPages($sectionID){

		$pages = $this->pages->getPages();
		$sectionPages = [];

		foreach ($pages as $page) {
		$page_section = $this->get_page_section_ids($page->ID);
		
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
public function GetPageSections($pageID){
		

	return $this->get_page_section_relationship($pageID);
}
private function get_page_section_ids($pageID){

		$section_ids = get_post_meta($pageID , $this->page_section_meta_key);
		
		return $this->return_page_sections_format($section_ids);
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
public function page_has_section($pageID , $sectionID){
	$page_sections = $this->get_page_section_ids($pageID);

	if(is_string($page_sections) && $page_sections == $sectionID || is_array($page_sections) && in_array_r($sectionID, $page_sections) == true){
		
		return true;
	}

	return false;
}
private function delete_page_section_relationship($pageID , $sectionID){

	$page_sections = $this->get_page_section_ids($pageID);

	if(is_string($page_sections) && $page_sections == $sectionID){

		$this->destroy_page_section_relationship($pageID);

	}else if(is_array($page_sections)){
		$key_to_remove = array_search($sectionID, $page_sections);
		
		
		unset($page_sections[$key_to_remove]);

		if(!empty($page_sections)){
			$page_sections = array_values($page_sections);
			$this->update_page_sections_relationship($pageID , $page_sections);
			if(count($page_sections) == 1){
				array_shift($page_sections);	
			}
		}else{
			$this->delete_page_section_relationship($pageID);
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
private function prepare_page_section_relationship($pageID , $sectionID){
	$page_sections = $this->get_page_section_ids($pageID);

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
private function destroy_page_section_relationship($pageID){

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


	try {
		if($page_sections == false || $page_sections == null || is_string($page_sections) == true){
			throw new Exception("unexpected data null or false was expection string or array for page:" . $pageID);
		}
		if(update_post_meta($pageID , $this->page_section_meta_key , $page_sections) == false)
			throw new Exception("the data of ".$pageID. " wasnt updated check this out crazy , check out data:<br>" . var_dump($page_sections) );
	} catch (Exception $e) {
		print $e->getMessage();
		die();
	}
}

public function update_page_sections($posted_pages , $sectionID){

		if(!is_array($posted_pages)){
			$posted_pages = array();
		}
		$pages_to_add_section_relationship = $posted_pages;

		$all_pages_id = $this->pages->get_ids_from_pages($this->pages->getPages());

		$pages_remove_section_relation_ship = $this->calculate_difference($all_pages_id , $pages_to_add_section_relationship);

		if(!empty($pages_remove_section_relation_ship)){

			foreach($pages_remove_section_relation_ship as $pageID){

				if($this->page_has_section($pageID , $sectionID ) == true){
		
					$this->delete_page_section_relationship($pageID,$sectionID);
				}
			}
		}

		if(!empty($pages_to_add_section_relationship)){
	
			foreach ($pages_to_add_section_relationship as $pageID) {
			
				if($this->page_has_section( $pageID , $sectionID) == false){
					
				$page_sections = $this->prepare_page_section_relationship( $pageID, $sectionID);
		
				$this->update_page_sections_relationship($pageID , $page_sections);
				
				}
			}		
		}
		
}
private function update_page_sections_relationship($pageID ,$page_sections ){
	try {
		if(update_post_meta($pageID , $this->page_section_meta_key,$page_sections) == false ){
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
		
		var_dump($section_ids);
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