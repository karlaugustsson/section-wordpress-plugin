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

 register_activation_hook( __FILE__, 'ka_create_database_tables' );
 register_uninstall_hook(    __FILE__, 'ka_remove_database_tables' );
  register_uninstall_hook(    __FILE__, 'ka_delete_options' );
  register_uninstall_hook(    __FILE__, 'ka_delete_custom_post_types' );
 add_action( 'admin_init', 'ka_register_section_settings');
 add_action("admin_menu" , 'karla_add_menu_to_admin_menu');
 add_filter("manage_section_posts_columns" , "add_section_columns");
 add_action( 'pre_get_posts', 'add_my_post_types_to_query' );
 add_action( 'manage_section_posts_custom_column', 'set_custom_edit_section_columns', 10, 2 );
 add_action( 'save_post', 'karl_save_postdata');
 add_action('before_delete_post' , "karl_delete_section_page_relation");
 add_action("admin_enqueue_scripts" , "get_them_admin_scripts");
 add_action( 'wp_ajax_find_sections', 'ajax_find_sections' );
 add_action( 'wp_ajax_update_section_order', 'ka_ajax_update_section_order' );
add_action( 'admin_action_delete', 'testeli' );
} else {

 add_action( 'wp', 'get_sections_by_page' );
 add_action("wp_head", "ka_print_style");
 add_action( 'wp_enqueue_scripts', 'ka_front_scripts_method' ); 


// javascript ajax shit and stuff jao

}

add_action("init" , "karla_install");

$plugin_setting_page = plugin_dir_path( __FILE__ ) . "includes/section_options_page.php";

$ka_pages;
$ka_section;
$ka_page_sections;

function testeli(){
    die("aaaa");
}
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
	
$setting_page_slug = "admin_settings_page";
$color_section_name = "color_settings" ; 

add_settings_section( $color_section_name , "Color settings" , "print_color_settings_heading" , $setting_page_slug );

add_settings_field("link_color" , "Link color" , "section_link_hover_color_field" , $setting_page_slug  , $color_section_name );

add_settings_field("link_color_hover" , "Link color hover" , "section_link_color_field" , $setting_page_slug  , $color_section_name );

add_settings_field("link_color_active" , "Link color active" , "section_link_active_color_field" , $setting_page_slug  , $color_section_name );

register_setting( "color_options" , "color" , "sanitize_hex_color" );

}

function sanitize_hex_color( $color ) {

	$pattern = '|^#([A-Fa-f0-9]{3}){1,2}$|';
    if ( '' === $color["link_color_hover"] || $color["link_color"] == "" || $color["link_color_active"] == "")
        return '';
 
    // 3 or 6 hex digits, or the empty string.
    if ( preg_match($pattern, $color["link_color"] ) && preg_match($pattern, $color["link_color_hover"] ) && preg_match($pattern, $color["link_color_active"] ) )
        return $color;
}
function return_option($option_name , $index){
	return get_option( $option_name )[$index];
}
function section_link_color_field($id){

$option = get_option( 'color' )['link_color'] ;
$val = ( $option != false ) ? $option : '#00660f';
 echo '<input  type="text" name="color[link_color]" value="'. $val .'" class="color-field">';
}
function karl_delete_section_page_relation($postID){
  
    global $wpdb;
    global $ka_page_sections;
    global $post_type ; 


        if(current_user_can('delete_post', $postID)){
            switch ($post_type) {
                case 'section':
                $ka_page_sections->delete_section_relationships($postID);
                break;
                case 'page':
                $ka_page_sections->delete_page_relationships($postID);
                break;
                
                default:
                return;
                break;
            }
        }
}
function section_link_hover_color_field(){

$option = get_option( 'color' )['link_color_hover'] ;
$val = ( $option != false ) ? $option : '#00660f';
 echo '<input  type="text" name="color[link_color_hover]" value="'.$val .'" class="color-field">';
}
function section_link_active_color_field(){

$option = get_option( 'color' )['link_color_active'] ;
$val = ( $option != false ) ? $option : '#00660f';
 echo '<input  type="text" name="color[link_color_active]" value="'.$val .'" class="color-field">';
}

function print_color_settings_heading($args){?>

<?php;}

function karla_add_menu_to_admin_menu(){

 add_meta_box("page_select" , "Section pages" , "karla_section_pages" , "section" ,"side", "low");

 add_menu_page("Section options Page" , " Section Settings" , 'administrator' , "admin_settings_page" , 
 "section_option_page");

add_submenu_page( 'edit.php?post_type=section', 'Reorder sections', 'Reorder sections', 'edit_posts', basename(__FILE__), 'print_reorder_sections_page' );
}
function print_reorder_sections_page(){
    include( plugin_dir_path( __FILE__ ) . "/includes/order_sections.php");
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
global $ka_pages;
global $ka_page_sections;

if ( $query->is_main_query() ) {
 
 if(!empty($_GET['section_page'])){
    $page_id = $ka_pages->find_page_by_post_name($_GET['section_page']);
    if($page_id != false){

      $query->set('post__in', $ka_page_sections->get_section_ids_by_page_id((INT)$page_id->ID));  
    } 
    
 
  if ( is_home() )
    $query->set( 'post_type', array( 'post', 'page', 'section' ) );
  return $query;
}

}

return $query; 
}

function add_section_columns($columns){
 global $ka_query;

 unset($columns['date']);
 unset($columns['author']);

 return array_merge($columns, array('pages' => __('Pages')));


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
function karl_save_postdata( $section_id ) {

global $ka_page_sections;
global $post;
// $sections = array( "16" ,"4");

// $pageID = "2";

// $saved = $ka_page_sections->update_section_postition($pageID , $sections);

// print "data was " . $saved;

if($_SERVER['REQUEST_METHOD'] == "POST"){

	 $posted_pages = $_POST['pages-meta-box-sidebar'];
 if($post->post_type != "section"){
    return;
 }

 if($posted_pages == null){
 $posted_pages = array();
 }



 try {
 $ka_page_sections->update_section_pages($posted_pages , $section_id );
 
 
 } catch (Exception $e) {
 print $e->getMessage();
 }
 

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

 function ka_print_section_panels($sections , $page_id){?>

    

<form id="ka_section_order_form" action="#" method="POST">
<input type="hidden" value="<?php print $page_id ?>" name="page_id">
<ul id="section-list" class="ui-sortable">
<?php foreach($sections as $section):?>


<li class="menu-item-handle">
    <input type="hidden" name="section_page_ids[]" value="<?php print $section->ID;?>">
    <?php print $section->post_title;?>
</li>


<?php endforeach?>
</ul>
<?php submit_button();?>
</form>
<div id="section_order_message" style="position:relative; width:200px">
    <div class="spinner"></div>
</div>

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

function ka_print_style(){?>
    <style>
     .ka_section_link{
    	color:<?php print get_option( 'color' )['link_color']?>;
    }
     .ka_section_link:hover{
    	color:<?php print get_option( 'color' )['link_color_hover']?>;
    }
    </style>
<?}
function get_them_admin_scripts(){

 if ( is_admin() ){
 
 wp_enqueue_style( "wp-color-picker" );

 wp_enqueue_script("wp_color_picker" , plugins_url('/js/section_admin_scripts.js' , __FILE__ ) , array("wp-color-picker") , false , true );
 
  wp_enqueue_script("wp_order_sections" , plugins_url('/js/order_sections.js' , __FILE__ ) , array("jquery") , false , true );
  
  wp_enqueue_script('jquery-ui-sortable'); //load sortable

  
 }
}

function ka_front_scripts_method(){

 wp_enqueue_script( 'main_section_script' , plugins_url( "/js/main_section_script.js" , __FILE__ ) , array("jquery"));
}
function ka_print_pages_checkboxes($SectionID , $pages ){

global $ka_page_sections;

include( plugin_dir_path( __FILE__ ) . "/includes/section_pages_meta_box.php");
 
}


function ajax_find_sections(){
 global $ka_page_sections ; 
 // this is how you get access to the database 
 
 $page_id = (INT)$_POST['pageID'];
 
 if ( $page_id === false || $page_id === 0 ){
 
 wp_die(); // this is required to terminate immediately and return a proper response

 }

 $sections = $ka_page_sections->get_page_sections($page_id);
 
 if(empty($sections) == true || $sections == false){
    wp_send_json_success( array("message" => "No sections found to order") );
 
 }else{

    ka_print_section_panels($sections , $page_id);
 }


 wp_die(); // this is required to terminate immediately and return a proper response
}
function ka_ajax_update_section_order($data){
    global $ka_page_sections;
    $pageID = (  !empty( (INT)$_POST['page_id'])) ? $_POST['page_id'] : null ;
    $section_ids = (  !empty( (INT)$_POST['section_ids'])) ? $_POST['section_ids'] : null ;
    
    if($section_ids != null && $pageID != null ){
        

    $ka_page_sections->attempt_update_page_section_position( $pageID , $section_ids  );

    print "<span style=\"color:green\">data saved</span>";  
       
    }

    wp_die();
}

function ka_create_database_tables(){
	global $wpdb;

	$charset_colate = $wpdb->get_charset_collate();
	$tablename = "ka_section_pages";

	$sql = "CREATE TABLE $tablename
	 (id bigint(20) NOT NULL AUTO_INCREMENT,
	  page_id bigint(20) unsigned,
	   section_id bigint(20) unsigned,
	    page_section_position tinyint(3),
	    PRIMARY KEY  (id) , KEY $table_name (id , section_id , page_id) )
	    $charset_colate;
	 ";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

function ka_remove_database_tables(){
	global $wpdb;
	$tablename = "ka_section_pages";

	if( ! current_user_can("activate_plugins")){
		return;
	}

    $wpdb->query("DROP table IF EXISTS $tablename");
}
function ka_delete_options(){
    remove_option("color");
}
function ka_delete_custom_post_types(){
    global $wpdb;
    $post_table = $wpdb->prefix . "posts";

    $query = "DELETE FROM $post_table WHERE post_type = 'section';";
    var_dump($query);
    die();
    $wpdb->query($query);
}
