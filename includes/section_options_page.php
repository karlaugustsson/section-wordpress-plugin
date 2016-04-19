<form method="post" action="options.php"><?php
settings_fields("color_options");      
do_settings_sections("admin_settings_page");
submit_button();?></form>

<form method="post" action="options.php">
	<input type="hidden" name="test[]"value="test">
	<input type="hidden" name="test[]"value="test2">
<?php submit_button();?>
</form>