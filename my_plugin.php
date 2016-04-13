<?php
/*
Plugin Name: create secitons
Plugin URI:  http://therisnone.com
Description: cleans up the dashboard
Version:     1.5
Author:      Karl Augustsson
Author URI:  
License:     GPL2 

// https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$post_type_posts_columns
*/



add_action("init" , "karla_install");
add_action("admin_menu" , 'karla_add_menu_to_admin_menu');
add_filter("manage_section_posts_columns" , "add_section_columns");
add_action( 'pre_get_posts', 'add_my_post_types_to_query' );
add_action( 'manage_section_posts_custom_column', 'set_custom_edit_section_columns', 10, 2  );
add_action( 'admin_enqueue_scripts', 'my_scripts_method' );
add_action( 'save_post', 'karl_save_postdata', 'save_book_meta', 10, 3 );
add_action( 'wp_ajax_find_page_sections', 'find_page_sections' );
add_action( 'update_post_section', 'update_section_page_order', 10, 3 );
function set_custom_edit_section_columns($column , $post_id){
global $ka_page_sections;
    switch($column){
        case "pages":
        $pages = $ka_page_sections->getSectionPages($post_id);
        if(!empty($pages)){
        foreach ($pages as $page) {?>
        
           
            <a href="<?php print home_url();?>/wp-admin/edit.php?s&post_type=section&section_page=<?php print $page->post_name?>
            ">
            <?php if($_GET['section_page']):?>
                <?php if ( $_GET['section_page'] == $page->post_name ):?>

                    <?php print $page->post_title?>
                <?php endif?>
            <?php else:?>
            <?php print $page->post_title;?></a>
            <?php endif?>  
            
        <?php };         
        }else{
            print "<p>No pages associated with this section</p>";
        }

        break;
    }
}

function add_my_post_types_to_query( $query ) {

global $ka_pages;
if ( $query->is_main_query() ) {
    
    if(!empty($_GET['section_page'])){
        $page_id = $ka_pages->find_page_by_post_name($_GET['section_page'])->ID  ;  

    $meta_query = array(
    'relation' => 'OR', // Optional, defaults to "AND"
    array(
        'key'     => '_section_pages',
        'value'   => $page_id,
        'compare' => 'LIKE'
    )
    );
   
        $query->set('post_type' , 'section' , 'orderby' ,'meta_value');
        
        $query->set('meta_query',$meta_query); 
    
        if($query->is_home()){

            $query->set( 'post_type', array( 'post', 'pages', 'section') );             

        }
        
    }

}


return $query;    
}
function add_section_columns($columns){

    unset($columns['date']);
    unset($columns['author']);
    return array_merge($columns, 
              array('pages' => __('Pages')));
}
    $ka_pages;
    $ka_section;
    $ka_page_sections;

function update_new_section_order(){
    die();
}
function karla_install(){

include_once( plugin_dir_path( __FILE__ ) . 'includes/page.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/section.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/page_sections.php' );

global $ka_section;
global $ka_pages;
global $ka_page_sections;

    $ka_section = new Ka_section();
    $ka_pages = new Ka_page();
    $ka_page_sections = new KaPageSections($ka_pages,$ka_section);


	karla_add_custom_post_type();

    
}
function karla_add_menu_to_admin_menu(){

    global $hook;
    $pg_title = 'Sections';
    $menu_title = 'Sections';
    $cap = 'read';
    $slug = 'my_sections';
    $function = 'karla_print_order_sections_page';
    add_menu_page("Order sections" , "sectionPlugin" , $cap , "order_sections"  , $function );
	add_meta_box("page_select" , "Section pages" , "karla_section_pages" , "section" ,"side", "low");


}
function karla_add_custom_post_type(){
	$labels = array(
        'label'                   =>  "sections",
        'name' =>   "sections",
        'name_admin_bar'        => _x( 'Section', 'Add New on Toolbar', 'textdomain' ),
		'add_new'            => _x( 'Add New section', 'section', 'your-plugin-textdomain' ),
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

	register_post_type( 'section',
     array( 'public' => 'true' , 'labels' => $labels  
        
    ,
    'public' => true,
    'menu_position' => 15,
     'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ))

     );
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



function array_values_into_int($array){
    $new_arr = array();
    foreach ($array as $value) {
        $new_arr[] = (INT)$value ; 
    }
    return $new_arr;
}
function karl_save_postdata( $section_id, $post, $update ) {

    if($post->post_type != "section"){
        return;
    }
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

    return;
   }
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}
function my_scripts_method($hook){

    if( 'toplevel_page_order_sections' != $hook ) {
   
    return;
    }
    
    wp_enqueue_script( 'order_sections' , plugins_url( "/js/order_sections.js" , __FILE__ ) , array("jquery"));
  
   // wp_localize_script( 'ajax-script', 'ajax_object',
           // array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
}
function karla_print_order_sections_page(){

    include_once( plugin_dir_path( __FILE__ ) . "/includes/order_sections.php");

}

function find_page_sections(){
    
    global $ka_page_sections ; 
    global $wpdb; // this is how you get access to the database  
   
    $page_id = (INT)$_POST['pageID'];
    
    if ( $page_id === false || $page_id === 0 ){
        
        wp_die(); // this is required to terminate immediately and return a proper response
        die();
    }

     $sections = $ka_page_sections->get_page_sections($page_id);
    

    
    if(empty($sections) == true || $sections == false){
        wp_send_json_success( array("message" => "No sections found to order") );
    
    }else{
        ka_print_section_panels($sections , $page_id);
    }


    wp_die(); // this is required to terminate immediately and return a proper response
}
    function ka_print_section_panels($sections , $page_id){?>

    <form action="post.php" method="options.php">
        <?php foreach($sections as $section):?>

        <input type="hidden" value="<?php print $page_id ?>" name="page_id">
            <div class="dragable panel">
                <input type="hidden" name="update_section_page_ids[]" value="<?php print $section->ID ?>">
                <span><?php print $section->post_title ?></span>
            </div>
        <?php endforeach?>
        <?php submit_button();?>
    
    </form>
    <?php }
function ka_print_pages_checkboxes($SectionID , $pages ){

global $ka_page_sections;?>


    <label for="my_meta_box_text">This section belongs to:</label>
    <br>
    <br>

    <?php foreach($pages as $page):?>
 
    <input type="checkbox" name="pages-meta-box-sidebar[]" value="<?php print $page->ID?>" <?php print $ka_page_sections->section_has_page($page->ID , $SectionID) == true ? 'checked="true"' : "" ?> > <?php print $page->post_title?>
    <br>

   <?php endforeach;?>
    <?php 
}?>