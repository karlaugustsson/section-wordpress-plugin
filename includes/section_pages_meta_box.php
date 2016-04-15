
    <label for="my_meta_box_text">This section belongs to:</label>
    <br>
    <br>
<?php foreach($pages as $page):?>
 
    <input type="checkbox" name="pages-meta-box-sidebar[]" value="<?php print $page->ID?>" <?php print $ka_page_sections->section_has_page($page->ID , $SectionID) == true ? 'checked="true"' : "" ?> > <?php print $page->post_title?>
    <br>

<?php endforeach;?>