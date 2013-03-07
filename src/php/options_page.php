<div class="wrap">

	<div id="icon-options-general" class="icon32">
		<br>
	</div>

	<h2>GetGit GitHub Repository Content Embedder Settings 30</h2>

	<form action="options.php" method="post">
		<?php settings_fields( OptionsPageConstants::$SECTION_SHORTCODE_SETTINGS ); ?>
		<?php do_settings_sections( OptionsPageConstants::$SECTION_SHORTCODE ); ?>

		<?php submit_button( ); ?>
	</form>
	<?php ?>
</div>