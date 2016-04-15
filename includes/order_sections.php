<?php global $ka_pages;?>
<?php wp_localize_script( "order_sections", "test", array("site_url" => plugins_url()));?>
<?php $pages = $ka_pages->getPages();?>
<h2>Select page to order sections</h2>
<form  action="#">

	<select class="page_select">
  		<option id="ignore_me" value="---" class="page_select" selected="selected">----</option>
  			<?php foreach($pages as $page):?>
  				<option class="page_select" value="<?php print $page->ID?>"><?php print $page->post_title?></option>
  		<?php endforeach?>
</select>
</form>

<div id="section_listing">
	
</div>
