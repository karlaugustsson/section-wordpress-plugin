<?php 
abstract class PostType {

	private $postType ;
	private $posts;

	public function __construct($post_type, $args = null){
	 	
	 if($args == null){

	 		$args = array( 'post_type' => $this->postType , 'post_status' => array( "publish" , "public" ) );
	 }

	 $loop = new WP_Query( $args );


	$posts = $loop->get_posts();

	}
	public function getPost($post_id){
		$all_posts = $posts;

		foreach( $posts as $post){
			if($post_id == $post->ID) {
				return $post;
			}
		}

		return false;
	}

	public function getPosts(){
		if($this->posts != null){
			return $this->posts;
		}
		return false;
		
	}

	public function get_ids_from_post_type($post_type){
	$id_array = array();
	foreach ($post_type as $post) {
		$id_array [] = $post->ID;
	}
	return $id_array;
	}

public function post_exist($id){
	$result = false;
	foreach($this->posts as $post){
		
		if($post->ID == $id){
			$result = true;
		}
	}
	return $result;
}
public function get_post_title_by_post_name($post_name){

	if( !empty($this->posts)){
			foreach ($this->posts as $post) {

				if($post->post_name == $post_name){

					$title = $post;;
				}
			}

	}
	if ( $title != null){

		return $title->post_name;
	}
	return false;
}

}