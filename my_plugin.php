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
class Ka_section_plugin{

public function __construct(){

if ( is_admin() ){ // admin actions

 register_activation_hook( __FILE__, array( 'Ka_section_plugin' , 'ka_create_database_tables' ) );
 
 register_uninstall_hook(    __FILE__, array( 'Ka_section_plugin' , 'ka_remove_database_tables' ) );
 
 register_uninstall_hook(    __FILE__, array( 'Ka_section_plugin' ,'ka_delete_options') );
 
 register_uninstall_hook(    __FILE__, array( 'Ka_section_plugin' ,'ka_delete_custom_post_types' ) );

 // add_action( 'admin_init', 'ka_register_section_settings');
 // add_action("admin_menu" , 'karla_add_menu_pages');
 // add_action( "add_meta_boxes" , "ka_meta_box_func");
 // add_filter("manage_section_posts_columns" , "add_section_columns");
 // add_action( 'pre_get_posts', 'add_my_post_types_to_query' );
 // add_action( 'manage_section_posts_custom_column', 'set_custom_edit_section_columns', 10, 2 );
 // add_action( 'save_post', 'karl_save_postdata');
 // add_action('before_delete_post' , "karl_delete_section_page_relation");
 // add_action("admin_enqueue_scripts" , "get_them_admin_scripts");
 // add_action( 'wp_ajax_find_sections', 'ajax_find_sections' );
 // add_action( 'wp_ajax_update_section_order', 'ka_ajax_update_section_order' );

} else {
 // add_action( 'wp', 'ka_setup_page_sections' );

 // add_action("wp_head", "ka_print_style");

 // add_action( 'wp_enqueue_scripts', 'ka_front_scripts_method' ); 

}

add_action("init" , array( &$this , "karla_install" ));

}


public static function deactivate(){

}

public static function activate(){

}
public function karla_install(){

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

public function ka_create_database_tables(){
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
public static function uninstall(){

}
public function ka_remove_database_tables(){
    if( 1 == 1){
        die();
    } 
    global $wpdb;
    $tablename = "ka_section_pages";


    $wpdb->query("DROP table IF EXISTS $tablename");
}

}

// Installation and uninstallation hooks
register_activation_hook(__FILE__, array('Ka_section_plugin', 'activate'));

register_uninstall_hook(__FILE__, array('Ka_section_plugin', 'uninstall'));

register_deactivation_hook(__FILE__, array('Ka_section_plugin', 'deactivate'));

// instantiate the plugin class
$wp_plugin_template = new Ka_section_plugin();
