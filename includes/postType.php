<?php 
abstract class postType {
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
}