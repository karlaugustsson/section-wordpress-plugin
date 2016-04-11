<?php
/*
Plugin Name: create secitons
Plugin URI:  http://therisnone.com
Description: cleans up the dashboard
Version:     1.5
Author:      Karl Augustsson
Author URI:  
License:     GPL2 
*/



add_action("init" , "karla_install");
add_action("admin_menu" , 'karla_add_menu_to_admin_menu');

    $ka_pages;
    $ka_section;
    $ka_page_sections;

function karla_install(){

include_once( plugin_dir_path( __FILE__ ) . 'includes/page.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/section.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/page_sections.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/print_functions.php' );
global $ka_section;
global $ka_pages;
global $ka_page_sections;

    $ka_section = new Ka_section();
    $ka_pages = new Ka_page();
    $ka_page_sections = new KaPageSections($ka_pages,$ka_section);


	karla_add_custom_post_type();
	
flush_rewrite_rules();
    
}
function karla_add_menu_to_admin_menu(){

	add_menu_page('my_section_plugin', 'My Section plugin', 'manage_options', 'my_section_plugin', 'karla_print_index_page');
	 
	add_submenu_page('my_section_plugin', "add/edit sections", "Add sections", "manage_options", "my_section_plugin", "karla_print_index_page");

	add_meta_box("page_select" , "Section pages" , "karla_section_pages" , "section" ,"side", "low");

}

function karla_add_custom_post_type(){
	$labels = array(

		'add_new'            => _x( 'Add New', 'section', 'your-plugin-textdomain' ),
		'add_new_item'       => __( 'Add New Section', 'your-plugin-textdomain' ),
		'new_item'           => __( 'New Section', 'your-plugin-textdomain' ),
		'edit_item'          => __( 'Edit Section', 'your-plugin-textdomain' ),
		'view_item'          => __( 'View Section', 'your-plugin-textdomain' ),
		'all_items'          => __( 'All Sections', 'your-plugin-textdomain' ),
		'search_items'       => __( 'Search Sections', 'your-plugin-textdomain' ),
		'parent_item_colon'  => __( 'Parent Section:', 'your-plugin-textdomain' ),
		'not_found'          => __( 'No Sections found.', 'your-plugin-textdomain' ),
		'not_found_in_trash' => __( 'No Sections found in Trash.', 'your-plugin-textdomain' )
	);

	register_post_type( 'section', array( 'public' => 'true' , 'labels' => $labels ) );
}
function karla_section_pages(){
    global $post;
    global $ka_pages;
	ka_print_pages_checkboxes($post->ID,$ka_pages->getPages());
}
function theme_slug_filter_the_title( $title ) {
     $screen = get_current_screen();

     if  ( 'section' == $screen->post_type ) {
          $title = 'Name that section';

     }

     return $title;

}

add_filter( 'enter_title_here', 'theme_slug_filter_the_title' );

add_action( 'save_post', 'karl_save_postdata' );

function array_values_into_int($array){
    $new_arr = array();
    foreach ($array as $value) {
        $new_arr[] = (INT)$value ; 
    }
    return $new_arr;
}
function karl_save_postdata( $section_id ) {
    global $ka_page_sections;
	$section_id = (INT)$section_id;
  	$posted_pages = $_POST['pages-meta-box-sidebar'];

    if($posted_pages == null){
        $posted_pages = array();
    }



    try {
        $ka_page_sections->update_section_pages($posted_pages , $section_id );
    
        
    } catch (Exception $e) {
        print $e->getMessage();
    }
    

}

function in_array_r($needle, $haystack, $strict = false) {
   if(is_array($haystack) == false){
    var_dump($haystack);
    return;
   }
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

 ?>