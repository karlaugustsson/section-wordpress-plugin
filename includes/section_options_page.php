<h1>Section option page</h1>
<form method="post" action="options.php"><?php
settings_fields(__FILE__);      
do_settings_sections(__FILE__);
submit_button();?></form>