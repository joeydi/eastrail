<form class="lgl-keys" method="post" action="options.php">
	<?php
	settings_fields('arcada_labs_lgl_sync_settings');
	do_settings_sections($this->plugin_name . '-settings');
	submit_button();
	?>
</form>