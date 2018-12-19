<?php
// Build the admin panel

function ci_cptr_plugin_options() {

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<div class="wrap" id="cptr-options">
		<h2><?php esc_html_e( 'CSSIgniter Custom Post Types Relationships', 'cptr' ); ?></h2>
		<p><?php esc_html_e( "In this page you can define general options for the Custom Post Types Relationships plugin. All options here can be overridden manually by passing the appropriate parameters to the shortcode or the theme function. If you find yourself making changes here that don't have any effect, it's because your WordPress theme has hardcoded options for you, so check with the theme's developer.", 'cptr' ); ?></p>
		<p><?php echo wp_kses( sprintf( __( 'For complete usage instructions, please visit the <a href="%s">plugin\'s homepage</a>.', 'cptr' ), 'https://www.cssigniter.com/custom-post-types-relationships/' ), array( 'a' => array( 'href' => true ) ) ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'ci_cptr_plugin_settings' ); ?>
	
			<?php
				$options = get_option( CI_CPTR_PLUGIN_OPTIONS );
				$options = ci_cptr_plugin_settings_validate( $options );
			?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Max number of displayed related posts:', 'cptr' ); ?></th>
					<td>
						<input name="<?php echo esc_attr( CI_CPTR_PLUGIN_OPTIONS ); ?>[limit]" type="text" value="<?php echo esc_attr( $options['limit'] ); ?>"/>
						<p><?php esc_html_e( 'Set to 0 for no limit.', 'cptr' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Show the excerpt for each post?', 'cptr' ); ?></th>
					<td>
						<input name="<?php echo esc_attr( CI_CPTR_PLUGIN_OPTIONS ); ?>[excerpt]" type="checkbox" value="1" <?php checked( $options['excerpt'], 1 ); ?> />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'How many words the excerpt should be?', 'cptr' ); ?></th>
					<td>
						<input name="<?php echo esc_attr( CI_CPTR_PLUGIN_OPTIONS ); ?>[words]" type="text" value="<?php echo esc_attr( $options['words'] ); ?>"/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Display the thumbnail?', 'cptr' ); ?></th>
					<td>
						<input name="<?php echo esc_attr( CI_CPTR_PLUGIN_OPTIONS ); ?>[thumb]" type="checkbox" value="1" <?php checked( $options['thumb'], 1 ); ?> />
						<?php esc_html_e( 'The thumbnail will be displayed after the title and before the excerpt.', 'cptr' ); ?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Thumbnail size', 'cptr' ); ?></th>
					<td>
						<label><?php esc_html_e( 'Width', 'cptr' ); ?></label>
						<input name="<?php echo esc_attr( CI_CPTR_PLUGIN_OPTIONS ); ?>[width]" type="text" value="<?php echo esc_attr( $options['width'] ); ?>"/>
						<label><?php esc_html_e( 'Height', 'cptr' ); ?></label>
						<input name="<?php echo esc_attr( CI_CPTR_PLUGIN_OPTIONS ); ?>[height]" type="text" value="<?php echo esc_attr( $options['height'] ); ?>"/>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><h3><?php esc_html_e( 'Display Options', 'cptr' ); ?></h3></th>
					<td>
						&nbsp;
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Metabox title', 'cptr' ); ?></th>
					<td>
						<input name="<?php echo esc_attr( CI_CPTR_PLUGIN_OPTIONS ); ?>[metabox_name]" type="text" value="<?php echo esc_attr( $options['metabox_name'] ); ?>" class="regular-text"/>
						<p><?php esc_html_e( 'This is the title of the metabox that the users will see while in the post edit screens.', 'cptr' ); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Allowed roles', 'cptr' ); ?></th>
					<td>
						<p><?php esc_html_e( 'Select the roles that will have access to the plugin (i.e. can create/delete relationships).', 'cptr' ); ?></p>
						<fieldset class="allowed-roles">
							<?php cptr_checkbox_roles( CI_CPTR_PLUGIN_OPTIONS . '[allowed_roles][]', $options['allowed_roles'] ); ?>
						</fieldset>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Allowed post types', 'cptr' ); ?></th>
					<td>
						<p><?php esc_html_e( 'Select the post types that the plugin will be available to.', 'cptr' ); ?></p>
						<fieldset class="allowed-post-types">
							<?php cptr_checkbox_post_types( CI_CPTR_PLUGIN_OPTIONS . '[allowed_post_types][]', $options['allowed_post_types'] ); ?>
						</fieldset>
					</td>
				</tr>

			</table>
	
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>"/>
			</p>
		</form>
	</div>
	<?php
}
