<?php
/*
Plugin Name: create secitons
Plugin URI: http://therisnone.com
Description: create your sections and assign them to the pages of your liking
Version: 1.5
Author: Karl Augustsson
Author URI: 
License: GPL2 

// https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_$post_type_posts_columns
*/
if ( is_admin() ){ // admin actions

 
 add_action( 'admin_init', 'ka_register_section_settings');
 add_action("admin_menu" , 'karla_add_menu_to_admin_menu');
 add_filter("manage_section_posts_columns" , "add_section_columns");
 add_action( 'pre_get_posts', 'add_my_post_types_to_query' );
 add_action( 'manage_section_posts_custom_column', 'set_custom_edit_section_columns', 10, 2 );
 add_action( 'save_post', 'karl_save_postdata', 'save_book_meta', 10, 3 );
 add_action("admin_enqueue_scripts" , "get_them_admin_scripts");

} else {

 add_action( 'wp', 'get_sections_by_page' );

 add_action( 'wp_enqueue_scripts', 'ka_front_scripts_method' ); 

}

add_action("init" , "karla_install");

$plugin_setting_page = plugin_dir_path( __FILE__ ) . "includes/section_options_page.php";

$ka_pages;
$ka_section;
$ka_page_sections;

function karla_install(){

include_once( plugin_dir_path( __FILE__ ) . 'includes/page.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/section.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/page_sections.php' );

if(is_admin() == true ){


global $ka_section;
global $ka_pages;
global $ka_page_sections;

 $ka_section = new Ka_section();
 $ka_pages = new Ka_page();
 $ka_page_sections = new KaPageSections($ka_pages,$ka_section);


 karla_add_custom_post_type();

}
 
}
function ka_register_section_settings(){
global $plugin_setting_page; 

$setting_section_group_name = "section_options";
 
$setting_section_name = "color_options" ; 
 
settings_fields($setting_section_group_name);

$option_name = "pick_color";
 
add_settings_section( "color_options", "color options", 'the_hell', $plugin_setting_page ); 

add_settings_field( "section_link_color", "section link color", "section_link_color_field", $plugin_setting_page , "color_options" ); 

add_settings_field( "section_link_hover", "section link color hover", "section_link_hover_color_field", $plugin_setting_page , "color_options"); 

add_settings_section( "color_options", "color options", 'the_hell', 'section_option_page' );

register_setting( $plugin_setting_page, $option_name );
}
function sanitize_setting_data(){
 
}

function the_hell($args){

}
function sanitize_settings_data(){
 
}
function karla_add_menu_to_admin_menu(){

 add_meta_box("page_select" , "Section pages" , "karla_section_pages" , "section" ,"side", "low");

 add_menu_page("Section options Page" , " Section Settings" , 'administrator' , __FILE__ , 
 "section_option_page");
}

function section_link_color_field(){
$val = ( get_option( 'section_link_color' ) != false ) ? get_option( 'section_link_color' ) : '#03ef00';
 echo '<input type="text" name="pick_color["section_link_color"]" value="'. $val .'" class="color-field">';
}
function section_link_hover_color_field(){
$val = ( get_option( 'section_link_hover_color' ) != false ) ? get_option( 'section_link_hover_color' ) : '#00660f';
 echo '<input type="text" name="pick_color["section_link_hover_color"]" value="'.$val .'" class="color-field">';
}
function set_custom_edit_section_columns($column , $post_id){
global $ka_page_sections;
 switch($column){
 case "pages":
 $pages = $ka_page_sections->getSectionPages($post_id);
 if(!empty($pages)){

 foreach ($pages as $page):?>
<a href="<?php print home_url();?>/wp-admin/edit.php?s&post_type=section&section_page=<?php print $page->post_name;?>">
	<?php print $page->post_title;?>
	</a>
	<?php endforeach;

}else{
 print "<p>No pages associated with this section</p>";
 }

 break;
 }
}
function have_sections(){

global $ka_section;

return $ka_section->section_query->have_posts();

}
function the_section(){
global $ka_section;

return $ka_section->section_query->the_post();
}
function get_sections_by_page(){

 global $ka_section;

 $ka_section = new Ka_section("filtered"); 
 
}
function add_my_post_types_to_query( $query ) {

global $ka_pages;

if ( $query->is_main_query() ) {
 
 if(!empty($_GET['section_page'])){
 $page_id = $ka_pages->find_page_by_post_name($_GET['section_page'])->ID ; 

 $meta_query = array(

 'relation' => 'OR', // Optional, defaults to "AND"
 
 array(
 'key' => '_section_pages',
 'value' => $page_id,
 'compare' => 'LIKE'
 )
 );
 
 $query->set('post_type' , 'section' , 'orderby' ,'meta_value');
 
 $query->set('meta_query',$meta_query); 
 

 $query->set( 'post_type', array( 'post', 'pages', 'section') ); 
 
 }

}


return $query; 
}
function add_section_columns($columns){
 global $ka_query;

 unset($columns['date']);
 unset($columns['author']);
 return array_merge($columns, 
 array('pages' => __('Pages')));
}

function karla_add_custom_post_type(){
$labels = array(
 'label' => "sections",
 'name' => "sections",
 'name_admin_bar' => _x( 'Section', 'Add New on Toolbar', 'textdomain' ),
'add_new' => _x( 'Add New section', 'section', 'your-plugin-textdomain' ),
'add_new_item' => __( 'Add New Section', 'your-plugin-textdomain' ),
'new_item' => __( 'New Section', 'your-plugin-textdomain' ),
'edit_item' => __( 'Edit Section', 'your-plugin-textdomain' ),
'view_item' => __( 'View Section', 'your-plugin-textdomain' ),
'all_items' => __( 'All Sections', 'your-plugin-textdomain' ),
'search_items' => __( 'Search Sections', 'your-plugin-textdomain' ),
'parent_item_colon' => __( 'Parent Section:', 'your-plugin-textdomain' ),
'not_found' => __( 'No Sections found.', 'your-plugin-textdomain' ),
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

 if ( 'section' == $screen->post_type ) {
 $title = 'Name that section';

 }

 return $title;

}
function ka_start_section($classnames = null){
 global $post;
 if ($classnames != null && is_array($classnames) == true ){
 $class_string = "";

 foreach ($classnames as $classname) {
 
 $class_string .= $classname . " ";
 }
 $class_string = chop($class_string); 
 
 }?>
<div id="<?php print $post->post_name ?>" class="<?php print $class_string ?>">
<?php }

function ka_end_section(){
 global $post;?>

 </div>
<?php }
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


function find_page_sections(){
 
 global $ka_page_sections ; 
 // this is how you get access to the database 
 
 $page_id = (INT)$_POST['pageID'];
 
 if ( $page_id === false || $page_id === 0 ){
 
 wp_die(); // this is required to terminate immediately and return a proper response
 die("hahH");
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
<input type="hidden" name="update_section_page_ids[]" value="<?php print $section->ID;?>">
<span><?php print $section->post_title;?></span>
</div>
<?php endforeach?>
<?php submit_button();?></form>
<?php }
function ka_get_section_links(){
 
 global $ka_page_sections;
 global $ka_section ;

 $sections = $ka_section->getSections();

 foreach ($sections as $section ) {?>
<a href="" class="ka_section_link" data-section="<?php print $section->post_name;?>">

<?php print $section->post_title ;?></a>
<?php }
}


function section_option_page(){
 global $plugin_setting_page ;

 include( $plugin_setting_page );

}

function get_them_admin_scripts(){

 if ( is_admin() ){
 
 wp_enqueue_style( "wp-color-picker" );

 wp_enqueue_script("wp_color_picker" , plugins_url('/js/section_admin_scripts.js' , __FILE__ ) , array("wp-color-picker") , false , true );
 }
}

function ka_front_scripts_method(){
 
 wp_enqueue_script( 'main_section_script' , plugins_url( "/js/main_section_script.js" , __FILE__ ) , array("jquery"));
}
function ka_print_pages_checkboxes($SectionID , $pages ){

global $ka_page_sections;

include( plugin_dir_path( __FILE__ ) . "/includes/section_pages_meta_box.php");
 
}