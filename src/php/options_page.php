<div class="wrap">

	<?php screen_icon(); ?>

	<h2>GetGit GitHub Repository Content Embedder Settings</h2>

	<form action="options.php" method="post">
		<?php settings_fields( OptionsPageConstants::$OPTIONS_DATA ); ?>
		<?php do_settings_sections( OptionsPageConstants::$OPTIONS_PAGE_ID ); ?>

		<?php submit_button( ); ?>
	</form>
	<?php ?>
</div>