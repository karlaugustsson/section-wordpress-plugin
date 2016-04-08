<?php 

function print_add_new_post_button(){?>
     <?php if(is_user_logged_in()):?>
		<div class="fee-new">
	        	<a href="<?php print home_url();?>/wp-admin/post-new.php?post_type=section">Create a new post</a>
		</div>
    <?php endif;?>
<?php }
function karla_print_index_page(){
	include_once( plugin_dir_path( __FILE__ ) . '/index.php' );
}
function ka_print_section_pages($section){

    global $ka_section;

    try {
       
        $section_pages = $ka_section->getSectionPages($section);
        
        if($section_pages != false){
            
            foreach($section_pages as $page){?>
     
           <a href="<?php print admin_url( 'admin.php?page=my_section_plugin&order_by_section='.$page->post_name )?>"><?php print $page->post_title ?></a>
           <?php }
        }else{
            print "<p>No pages associated with this section</p>";
        }


    } catch (Exception $e) {
      
        echo $e->getMessage();
    }

}

function  ka_print_edit_and_remove_post_link($section){
     
        if(is_user_logged_in()):?>
		
	 	<a href="<?php print home_url();?>/wp-admin/post.php?post=<?php print $section->ID?>&action=edit"><?php print $section->post_title ?></a>

    <?php endif;?>
<?php }

function print_back_link(){?>
    <a href="<?php print admin_url( 'admin.php?page=my_section_plugin' )?>">Back</a>
<?php;}

function ka_print_pages_checkboxes(){

global $ka_pages;

$pages = $ka_pages->getPages();?>


    <label for="my_meta_box_text">This section belongs to:</label>
    <br>
    <br>
    <?php foreach($pages as $page):?>
    <?php $page_sections = get_post_meta($page->ID,'_page_section')?>
   	<input type="checkbox" name="pages-meta-box-sidebar[]" value="<?php print $page->ID?>" <?php print (in_array_r($post->ID, $page_sections ) == true ? 'checked="true"' : "") ?> > <?php print $page->post_title?>
   	<br>
   <?php endforeach;?>
    <?php 
}?>