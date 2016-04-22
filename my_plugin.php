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

spl_autoload_register( 'simplarity_autoloader' );

function simplarity_autoloader( $class_name ) {

    $classes_dir = realpath( plugin_dir_path( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
    $class_file =  $class_name  . '.php';
    if(file_exists($classes_dir . $class_file)){
        require_once $classes_dir . $class_file;
    }
    
  
}
class Ka_section_plugin{

private static $instance;

private $section_post_type_name = "section";

private $plugin_setting_page , $ka_pages, $ka_section, $ka_page_sections;
public static $ka_query;
public function __construct(){
$this->plugin_setting_page = plugin_dir_path(__FILE__) . "includes/section_options_page.php";
if ( is_admin() ){ // admin actions

 add_action( 'admin_init', array( &$this ,'ka_register_section_settings') );
 add_action("admin_menu" , array( &$this , 'karla_add_menu_pages') );
 add_action( "add_meta_boxes" , array( &$this , "ka_meta_box_func") );
 add_filter("manage_section_posts_columns" , array( &$this , "add_section_columns"));
 add_action( 'pre_get_posts', array( &$this , 'add_my_post_types_to_query' ) );
 add_action( 'manage_section_posts_custom_column', array( &$this , 'set_custom_edit_section_columns' ),10, 2 );
 add_action( 'save_post', array( &$this , 'karl_save_postdata' ));
 add_action('before_delete_post' , array( &$this , "karl_delete_section_page_relation" ) );
 add_action("admin_enqueue_scripts" , array( &$this , "get_them_admin_scripts") );
 add_action( 'wp_ajax_find_sections', array( &$this ,'ajax_find_sections' ) );
 add_action( 'wp_ajax_update_section_order', array( &$this , 'ka_ajax_update_section_order' ) );

} else {
 add_action( 'wp', array( &$this , 'ka_setup_page_sections') );

 add_action("wp_head", array( &$this ,"ka_print_style" ) );

 add_action( 'wp_enqueue_scripts', array( &$this , 'ka_front_scripts_method' ) ); 

}


add_action("init" , array( &$this , "karla_install" ));


}

public function ka_meta_box_func(){

 add_meta_box("page_select" , "Section pages" , array($this,"karla_section_pages") , "section" ,"side", "low");
}

public function ka_register_section_settings(){
    
$setting_page_slug = "admin_settings_page";

$color_section_name = "color_settings" ; 

if(get_option( 'color' ) == false){
    $values =
        array(
        
        "link_color_hover" => "#00af46" ,
        "link_color" => "#00af46" ,
        "link_color_active" => "#00af46" 
    );

    update_option("color" , $values);

}

add_settings_section( $color_section_name , "Color settings" , array($this,"print_color_settings_heading") , $setting_page_slug );



add_settings_field("link_color" , "Link color" , array($this , "section_link_hover_color_field") , $setting_page_slug  , $color_section_name );

add_settings_field("link_color_hover" , "Link color hover" , array($this,"section_link_color_field") , $setting_page_slug  , $color_section_name );

add_settings_field("link_color_active" , "Link color active" , array($this , "section_link_active_color_field") , $setting_page_slug , $color_section_name  );

add_settings_field("link_menu_offset" , "link menu offset number" , array($this , "link_menu_offset_field") , $setting_page_slug , $color_section_name);

register_setting( "color_options" , "color" , array($this , "sanitize_hex_color") );



}
public function sanitize_int($data){
    return $data;
}
public function sanitize_hex_color( $color ) {

    $pattern = '|^#([A-Fa-f0-9]{3}){1,2}$|';
    if ( '' === $color["link_color_hover"] || $color["link_color"] == "" || $color["link_color_active"] == "")
        return '';
 
    // 3 or 6 hex digits, or the empty string.
    if ( preg_match($pattern, $color["link_color"] ) && preg_match($pattern, $color["link_color_hover"] ) && preg_match($pattern, $color["link_color_active"] ) )
        return $color;
}
public function section_link_color_field($id){

$option = get_option( 'color' )['link_color'] ;
$val = ( $option != false )? $option : '#00660f';
 echo '<input  type="text" name="color[link_color]" value="'. $val .'" class="color-field">';
}
public function karl_delete_section_page_relation($postID){
  
    global $wpdb;
    global $post_type ; 


        if(current_user_can('delete_post', $postID)){
            switch ($post_type) {
                case 'section':
                $this->ka_page_sections->delete_section_relationships($postID);
                break;
                case 'page':
                $this->ka_page_sections->delete_page_relationships($postID);
                break;
                
                default:
                return;
                break;
            }
        }
}
public function section_link_hover_color_field(){
$option = get_option( 'color' ) ;

$option = get_option( 'color' )['link_color_hover'] ;
$val = ( $option != false ) ? $option : '#00660f';
 echo '<input  type="text" name="color[link_color_hover]" value="'.$val .'" class="color-field">';
}
public function section_link_active_color_field(){

$option = get_option( 'color' )['link_color_active'] ;
$val = ( $option != false ) ? $option : '#00660f';
 echo '<input  type="text" name="color[link_color_active]" value="'.$val .'" class="color-field">';
}
public function link_menu_offset_field(){
    $option = get_option("color")['link_menu_offset'];
    $val = ($option  == null) ? 0 : $option;
   echo '<input type="text" name="color[link_menu_offset]" value="' . $val . '">';

}
public function print_color_settings_heading($args){?>
    
<?php;}

public function karla_add_menu_pages(){


 add_menu_page("Section options Page" , " Section Settings" , 'administrator' , "admin_settings_page" , 
 array($this,"section_option_page"));

add_submenu_page( 'edit.php?post_type=section', 'Reorder sections', 'Reorder sections', 'edit_posts', basename(__FILE__), array($this , 'print_reorder_sections_page') );
}

public function print_reorder_sections_page(){
    include( plugin_dir_path( __FILE__ ) . "/includes/order_sections.php");
}

public function set_custom_edit_section_columns($column , $post_id){


 switch($column){
 case "pages":
 $pages = $this->ka_page_sections->getSectionPages($post_id);
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


public function ka_setup_page_sections(){

global $post;
global $wpdb;
$sections = array(0);
 
if($post->ID != null){



    $query = "SELECT section_id FROM ka_section_pages WHERE page_id = $post->ID;";
    $result = $wpdb->get_results($query);

  
    if( !empty($result) ){

     $sections = array();
      foreach ($result as $data) {
            $sections[] = (INT)$data->section_id;
        }  
    }
}
 self::$ka_query = new WP_Query( array( 'post_type' => $this->section_post_type_name  , 'post_status' => array( "publish" , "public" ) , "post__in" => $sections) );
 
}
public function add_my_post_types_to_query( $query ) {


if ( $query->is_main_query() ) {
 
 if(!empty($_GET['section_page'])){
    $page_id = $this->ka_pages->find_page_by_post_name($_GET['section_page']);
    if($page_id != false){

      $query->set('post__in', $this->ka_page_sections->get_section_ids_by_page_id((INT)$page_id->ID));  
    } 
    
 
  if ( is_home() )
    $query->set( 'post_type', array( 'post', 'page', 'section' ) );
  return $query;
}

}

return $query; 
}

public function add_section_columns($columns){


 unset($columns['date']);
 unset($columns['author']);

 return array_merge($columns, array('pages' => __('Pages')));


}

public function karla_section_pages(){
    global $post;
$this->ka_print_pages_checkboxes($post->ID,$this->ka_pages->getPages());
}
function theme_slug_filter_the_title( $title ) {

 $screen = get_current_screen();

 if ( 'section' == $screen->post_type ) {
 $title = 'Name that section';

 }

 return $title;

}

public function karl_save_postdata( $section_id ) {

global $post;

if($_SERVER['REQUEST_METHOD'] == "POST"){

     $posted_pages = $_POST['pages-meta-box-sidebar'];
 if($post->post_type != "section"){
    return;
 }

 if($posted_pages == null){

 $posted_pages = array();
 }



 try {

 $this->ka_page_sections->update_section_pages($posted_pages , $section_id );
 
 
 } catch (Exception $e) {
 print $e->getMessage();
 }
 

}

}
public static function in_array_r($needle, $haystack, $strict = false) {
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

public function ka_print_section_panels($sections , $page_id){?>

    
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

public function section_option_page(){

 include( $this->plugin_setting_page );

}

public function ka_print_style(){?>
    <style>
     .ka_section_link{
        color:<?php print get_option( 'color' )['link_color']?>;
    }
     .ka_section_link:hover{
        color:<?php print get_option( 'color' )['link_color_hover']?>;
    }
         .ka_section_link:active{
        color:<?php print get_option( 'color' )['link_color_active']?>;
    }
    .ka_section_link.active{
        color:<?php print get_option( 'color' )['link_color_active']?>;
    }
    </style>
<?}
public function get_them_admin_scripts(){

 if ( is_admin() ){
 
 wp_enqueue_style( "wp-color-picker" );

 wp_enqueue_script("wp_color_picker" , plugins_url('/js/section_admin_scripts.js' , __FILE__ ) , array("wp-color-picker") , false , true );
 
  wp_enqueue_script("wp_order_sections" , plugins_url('/js/order_sections.js' , __FILE__ ) , array("jquery") , false , true );
  
  wp_enqueue_script('jquery-ui-sortable'); //load sortable


 }
}

public function ka_front_scripts_method(){
$option = array(
     "offset" => get_option("color")['link_menu_offset']
    );
 wp_enqueue_script( 'main_section_script' , plugins_url( "/js/main_section_script.js" , __FILE__ ) , array("jquery"));
  wp_localize_script( 'main_section_script', 'sectionOBJ', $option );
}

public function ka_print_pages_checkboxes($SectionID , $pages ){


include( plugin_dir_path( __FILE__ ) . "/includes/section_pages_meta_box.php");
 
}


public function ajax_find_sections(){

 
 $page_id = (INT)$_POST['pageID'];
 
 if ( $page_id === false || $page_id === 0 ){
 
 wp_die(); // this is required to terminate immediately and return a proper response

 }

 $sections = $this->ka_page_sections->get_page_sections($page_id);
 
 if(empty($sections) == true || $sections == false){
    
    wp_send_json_success( array("message" => "No sections found to order") );
 
 }else{

    $this->ka_print_section_panels($sections , $page_id);
 }


 wp_die(); // this is required to terminate immediately and return a proper response
}
public function ka_ajax_update_section_order($data){
    
    $pageID = (  !empty( (INT)$_POST['page_id'])) ? $_POST['page_id'] : null ;
    $section_ids = (  !empty( (INT)$_POST['section_ids'])) ? $_POST['section_ids'] : null ;
    
    if($section_ids != null && $pageID != null ){
        

    $this->ka_page_sections->attempt_update_page_section_position( $pageID , $section_ids  );

    print "<span style=\"color:green\">data saved</span>";  
       
    }

    wp_die();
}








// install and unistall shit

public static function get_instance(){
    if(self::$instance == null){
    self::$instance = new self;
    }
    return self::$instance;
}

public static function return_sections(){
    return self::$ka_query->get_posts();

}

public static function uninstall(){

    global $wpdb;
    $name = KaPageSections::$table_name;
    $wpdb->query("DROP table IF EXISTS $name");

    self::$instance->ka_delete_options();

    self::$instance->ka_delete_custom_post_types();

}
public function karla_install(){

if(is_admin() == true ){


 $this->ka_section = new Ka_section($this->section_post_type_name);
 $this->ka_pages = new Ka_page();
 $this->ka_page_sections = new KaPageSections($this->ka_pages,$this->ka_section);

 $this->karla_add_custom_post_type();

}
 
}

public function karla_add_custom_post_type(){
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

public static function ka_create_database_tables(){
    global $wpdb;

    $charset_colate = $wpdb->get_charset_collate();
    $tablename = "ka_section_pages";

    $sql = "CREATE TABLE $tablename
     (id bigint(20) NOT NULL AUTO_INCREMENT,
      page_id bigint(20) unsigned,
       section_id bigint(20) unsigned,
        page_section_position tinyint(3),
        PRIMARY KEY  (id) , KEY $ka_page_sections->table_name (id , section_id , page_id) )
        $charset_colate;
     ";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
public static function get_query(){
    return self::$ka_query;
}
function ka_delete_options(){
    delete_option("color");
}
function ka_delete_custom_post_types(){
    global $wpdb;
    $post_table = $wpdb->prefix . "posts";

    $query = "DELETE FROM $post_table WHERE post_type = 'section';";
    $wpdb->query($query);
}

}
// Installation and uninstallation hooks
register_activation_hook(__FILE__, array('Ka_section_plugin', 'ka_create_database_tables'));

register_uninstall_hook(__FILE__, array('Ka_section_plugin','uninstall'));


// instantiate the plugin class
Ka_section_plugin::get_instance();

function ka_get_section_links(){
 
    $class_instance = KA_section_plugin::get_instance();
    $sections = $class_instance->return_sections();
  
if(!empty($sections)){
     foreach ($sections as $section ):?>

    <a href="" class="ka_section_link" data-section="<?php print $section->post_name;?>">

    <?php print $section->post_title ;?></a>
    <?php endforeach;
}



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
<div id="<?php print $post->post_name ?>" class="ka_section<?php print $class_string ?>">
<?php }

function ka_end_section(){?>
 </div>
<?php }

function ka_have_sections(){
  $instance = Ka_section_plugin::get_instance() ;
  $test_query = $instance::get_query(); 

return $test_query->have_posts();
}

function ka_section(){
    $instance = Ka_section_plugin::get_instance() ;
     $test_query = $instance::get_query(); 

    return $test_query->the_post();
}

