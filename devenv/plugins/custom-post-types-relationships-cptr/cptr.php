<?php
/*
Plugin Name: Custom Post Types Relationships (CPTR)
Plugin URI: http://www.cssigniter.com/ignite/custom-post-types-relationships/
Description: An easy way to create relationships between posts, pages, and custom post types in WordPress
Version: 2.5.1
Author: The CSSIgniter Team
Author URI: http://www.cssigniter.com/


Copyright 2010-2017  The CSSigniter Team (email : info@cssigniter.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


//
// NOTE: EVERYTHING with a CPR has been renamed to CPTR. Files/functions/options using CPR have been deprecated and will be removed by v3.0
//

if ( ! defined( 'CPTR_VERSION' ) ) {
	define( 'CPTR_VERSION', '2.5.1' );
}

if ( ! defined( 'CI_CPTR_PLUGIN_OPTIONS' ) ) {
	define( 'CI_CPTR_PLUGIN_OPTIONS', 'ci_cptr_plugin' );
}

if ( ! defined( 'CI_CPTR_PLUGIN_INSTALLED' ) ) {
	define( 'CI_CPTR_PLUGIN_INSTALLED', 'ci_cptr_plugin_version' );
}

if ( ! defined( 'CI_CPTR_POST_RELATED' ) ) {
	define( 'CI_CPTR_POST_RELATED', 'cptr_related' );
}

if ( ! defined( 'CI_CPTR_BASENAME' ) ) {
	define( 'CI_CPTR_BASENAME', plugin_basename( __FILE__ ) );
}


// Set defaults
define( 'CPTR_DEFAULT_LIMIT', 0 );
define( 'CPTR_DEFAULT_EXCERPT', 0 ); // 0 is false, 1 is true
define( 'CPTR_DEFAULT_EXCERPT_LENGTH', 55 );
define( 'CPTR_DEFAULT_THUMB', 0 ); // 0 is false, 1 is true
define( 'CPTR_DEFAULT_THUMB_WIDTH', 100 );
define( 'CPTR_DEFAULT_THUMB_HEIGHT', 100 );

load_plugin_textdomain( 'cptr', false, basename( dirname( __FILE__ ) ) . '/languages' );

// Include deprecated file for compatibility.
require_once( 'cpr.php' );
require_once( 'generic.php' );

// Not all files should be loaded every time, right?
global $pagenow;
if ( is_admin() and $pagenow == 'options-general.php' && ! empty( $_GET['page'] ) && $_GET['page'] == 'ci_cptr_plugin' ) {
	require_once( 'panel.php' );
}

if ( is_admin() ) {
	add_action( 'admin_menu', 'ci_cptr_menu' );
	add_action( 'admin_init', 'register_ci_cptr_settings' );
	register_activation_hook( __FILE__, 'ci_cptr_activate' );
	register_deactivation_hook( __FILE__, 'ci_cptr_deactivate' );
}

function ci_cptr_menu() {
	add_options_page( __( 'CSSIgniter Custom Post Types Relationships Options', 'cptr' ), __( 'Custom Post Types Relationships', 'cptr' ), 'manage_options', CI_CPTR_PLUGIN_OPTIONS, 'ci_cptr_plugin_options' );
}

function register_ci_cptr_settings() {
	register_setting( 'ci_cptr_plugin_settings', CI_CPTR_PLUGIN_OPTIONS, 'ci_cptr_plugin_settings_validate' );
}

function ci_cptr_plugin_settings_validate( $settings ) {
	$settings['limit']              = intval( $settings['limit'] ) > 0 ? intval( $settings['limit'] ) : CPTR_DEFAULT_LIMIT;
	$settings['excerpt']            = ( isset( $settings['excerpt'] ) and ( $settings['excerpt'] == 1 ) ) ? 1 : 0;
	$settings['words']              = intval( $settings['words'] ) > 0 ? intval( $settings['words'] ) : CPTR_DEFAULT_EXCERPT_LENGTH;
	$settings['thumb']              = ( isset( $settings['thumb'] ) and ( $settings['thumb'] == 1 ) ) ? 1 : 0;
	$settings['width']              = intval( $settings['width'] ) > 0 ? intval( $settings['width'] ) : CPTR_DEFAULT_THUMB_WIDTH;
	$settings['height']             = intval( $settings['height'] ) > 0 ? intval( $settings['height'] ) : CPTR_DEFAULT_THUMB_HEIGHT;
	$settings['allowed_roles']      = ( ! empty( $settings['allowed_roles'] ) and is_array( $settings['allowed_roles'] ) ) ? (array) $settings['allowed_roles'] : array();
	$settings['allowed_post_types'] = ( ! empty( $settings['allowed_post_types'] ) and is_array( $settings['allowed_post_types'] ) ) ? (array) $settings['allowed_post_types'] : array();
	$settings['metabox_name']       = ! empty( $settings['metabox_name'] ) ? $settings['metabox_name'] : __( 'Custom Post Types Relationships (CPTR)', 'cptr' );

	return $settings;
}

function ci_cptr_activate() {
	$options = get_option( CI_CPTR_PLUGIN_OPTIONS );
	if ( ! isset( $options['limit'] ) ) {
		$options['limit'] = CPTR_DEFAULT_LIMIT;
	}
	if ( ! isset( $options['excerpt'] ) ) {
		$options['excerpt'] = CPTR_DEFAULT_EXCERPT;
	}
	if ( ! isset( $options['words'] ) ) {
		$options['words'] = CPTR_DEFAULT_EXCERPT_LENGTH;
	}
	if ( ! isset( $options['thumb'] ) ) {
		$options['thumb'] = CPTR_DEFAULT_THUMB;
	}
	if ( ! isset( $options['width'] ) ) {
		$options['width'] = CPTR_DEFAULT_THUMB_WIDTH;
	}
	if ( ! isset( $options['height'] ) ) {
		$options['height'] = CPTR_DEFAULT_THUMB_HEIGHT;
	}
	if ( ! isset( $options['allowed_roles'] ) ) {
		$options['allowed_roles'] = array( 'administrator' );
	}
	if ( ! isset( $options['allowed_post_types'] ) ) {
		$options['allowed_post_types'] = _cptr_get_default_post_types();
	}
	if ( ! isset( $options['metabox_name'] ) ) {
		$options['metabox_name'] = sanitize_text_field( __( 'Custom Post Types Relationships (CPTR)', 'cptr' ) );
	}

	update_option( CI_CPTR_PLUGIN_OPTIONS, $options );
}

function ci_cptr_deactivate() {
	unregister_setting( 'ci_cptr_plugin_settings', CI_CPTR_PLUGIN_OPTIONS );
}


add_action( 'admin_menu', 'cptr_scripts_admin_styles' );
function cptr_scripts_admin_styles() {
	global $pagenow;


	if ( is_admin() and $pagenow == 'options-general.php' and $_GET['page'] == 'ci_cptr_plugin' ) {
		wp_enqueue_style( 'cptr-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin.css', true, CPTR_VERSION, 'all' );
	}

	if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ), true ) ) {
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'cptr-post-edit', plugin_dir_url( __FILE__ ) . 'css/post-edit.css', true, CPTR_VERSION, 'all' );
		wp_enqueue_script( 'cptr-post-edit-scripts', plugin_dir_url( __FILE__ ) . 'js/post-edit-scripts.js', array( 'jquery' ) );

		$settings = array(
			'ajaxurl'             => admin_url( 'admin-ajax.php' ),
			'btn_Add_text'        => _x( 'Add', '"Add" button text', 'cptr' ),
			'btn_Added_text'      => _x( 'Added', '"Added" button text', 'cptr' ),
			'btn_Remove_text'     => _x( 'Remove', '"Remove" button text', 'cptr' ),
			'duplicate_post_text' => __( 'This post is already in your list', 'cptr' ),
		);
		wp_localize_script( 'cptr-post-edit-scripts', 'cptrSettings', $settings );
	}

}


add_action( 'init', 'cptr_scripts_styles' );
function cptr_scripts_styles() {
	wp_enqueue_style( 'cptr-css', plugin_dir_url( __FILE__ ) . 'css/cptr.css', true, CPTR_VERSION, 'all' );
}

function cptr_box() {

	$options = get_option( CI_CPTR_PLUGIN_OPTIONS );

	if ( ! cptr_user_should_see_metabox( $options ) ) {
		return;
	}

	foreach ( $options['allowed_post_types'] as $type ) {
		add_meta_box( 'cptr-meta-box', esc_html( $options['metabox_name'] ), 'cptr_category_selector', $type, 'normal', 'default' );
	}
}

function cptr_category_selector( $object, $box ) {

	$cptr_post_types = _cptr_get_selected_post_types();

	?>
	<div id="cat-selector">
		<select id="howmany" name="howmany">
			<?php
				$howmany = apply_filters( 'cptr_results_num_per_category', array( 10, 50, 100 ) );
				foreach ( $howmany as $val ) {
					?><option value="<?php echo esc_attr( $val ); ?>"><?php echo intval( $val ); ?></option><?php
				}
			?>
			<option value="-1"><?php echo esc_html_x( 'All', 'e.g. All items from Posts ordered by Date in Descending order', 'cptr' ); ?></option>
		</select> <?php echo esc_html_x( 'items from', 'e.g. All items from Posts ordered by Date in Descending order', 'cptr' ); ?>

		<select id="posttype" name="cptr_post_type">
			<?php foreach ( $cptr_post_types as $key => $type ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>">
					<?php echo wp_kses( $type->labels->name, 'strip' ); ?>
				</option>
			<?php endforeach; ?>
		</select>

		<?php echo esc_html_x( 'ordered by', 'e.g. All items from Posts ordered by Date in Descending order', 'cptr' ); ?>
		
		<select id="orderby" name="orderby">
			<option value="title"><?php echo wp_kses( _x( 'Title', 'e.g. All items from Posts ordered by Title in Descending order', 'cptr' ), 'strip' ); ?></option>
			<option value="date"><?php echo wp_kses( _x( 'Date', 'e.g. All items from Posts ordered by Date in Descending order', 'cptr' ), 'strip' ); ?></option>
		</select>

		<?php echo esc_html_x( 'in', 'e.g. All items from Posts ordered by Date in Descending order', 'cptr' ); ?>

		<select id="orderin" name="orderin">
			<option value="ASC"><?php echo wp_kses( _x( 'Ascending', 'e.g. All items from Posts ordered by Date in Ascending order', 'cptr' ), 'strip' ); ?></option>
			<option value="DESC"><?php echo wp_kses( _x( 'Descending', 'e.g. All items from Posts ordered by Date in Descending order', 'cptr' ), 'strip' ); ?></option>
		</select> <?php echo esc_html_x( 'order', 'e.g. All items from Posts ordered by Date in Descending order', 'cptr' ); ?>&nbsp;
		&nbsp; <?php esc_html_e( 'Filter:', 'cptr' ); ?> <input type="text" id="filtered" name="filtered"/>
		<input type="hidden" id="h_pid" name="h_pid" value="<?php echo esc_attr( $object->ID ); ?>" />
		<input type="button" class="cptr_button button" value="<?php esc_attr_e( 'Search', 'cptr' ); ?>"/>
	</div>
	
	<div class="postbox">
		<h3><?php esc_html_e( 'Available Posts', 'cptr' ); ?></h3>
		<div id="available-posts">
			<p class="temp-message"><?php esc_html_e( 'Search results will appear here', 'cptr' ); ?></p>
		</div>

		<h3 id="related-posts-title"><?php esc_html_e( 'Related Posts (Drag to reorder)', 'cptr' ); ?></h3>
		<div id="related-posts">
			<?php
				$relations = get_post_meta( $object->ID, CI_CPTR_POST_RELATED, true );

				if ( ! empty( $relations ) ) :
					foreach ( $relations as $relation ) {
						$post = get_post( $relation );
						?>
						<div title="<?php echo esc_attr( $post->post_title ); ?>" class="thepost" id="post-<?php echo esc_attr( $post->ID ); ?>">
							<a href="#" class="removeme"><?php esc_html_e( 'Remove', 'cptr' ); ?></a>
							<p><strong><?php echo esc_html( $post->post_title ); ?></strong></p>
							<input type="hidden" name="reladded[]" value="<?php echo esc_attr( $post->ID ); ?>"/>
						</div>
						<?php
					}
				endif;
			?>
			<input type="hidden" name="cptr_noncename" id="cptr_noncename" value="<?php echo esc_attr( wp_create_nonce( plugin_basename( __FILE__ ) ) ); ?>"/>
		</div>
	</div>

	<?php
}

// Where's Dukey? Wa zaaaaaaaaaaaa (the call)
function cptr_cats() {
	$post_type = isset( $_POST['cptr_post_type'] ) ? sanitize_key( $_POST['cptr_post_type'] ) : 'post' ;
	$postID    = isset( $_POST['postID'] ) ? intval( $_POST['postID'] ) : false;
	$howMany   = isset( $_POST['howMany'] ) ? intval( $_POST['howMany'] ) : 10;
	$orderBy   = isset( $_POST['orderBy'] ) ? sanitize_key( $_POST['orderBy'] ) : 'title' ;
	$orderIn   = isset( $_POST['orderIn'] ) ? sanitize_key( $_POST['orderIn'] ) : 'ASC' ;
	$filter    = isset( $_POST['filtered'] ) ? wp_kses_post( $_POST['filtered'] ) : '';

	$args = array(
		'post_type'   => $post_type,
		'numberposts' => $howMany,
		'post_status' => 'publish',
		'orderby'     => $orderBy,
		'order'       => $orderIn,
	);

	if ( ! empty( $postID ) ) {
		$args['post__not_in'] = array( $postID );
	}

	if ( ! empty( $filter ) ) {
		$args['s'] = $filter;
	}

	$posts = get_posts( $args );

	if ( ! empty( $posts ) ) {
		foreach ( $posts as $post ) {
			setup_postdata($post);
			?>
			<div title="<?php echo esc_attr( $post->post_title ); ?>" class="thepost" id="post-<?php echo esc_attr( $post->ID ); ?>">
				<a href="#" class="addme"><?php esc_html_e( 'Add', 'cptr' ); ?></a>
				<p><strong><?php echo esc_html( $post->post_title ); ?></strong></p>
				<input type="hidden" name="related[]" value="<?php echo esc_attr( $post->ID ); ?>"/>
			</div>
			<?php
		}
	} else {
		?><div class="thepost"><?php esc_html_e( 'This category is empty', 'cptr' ); ?></div><?php
	}
	
	exit;
}

function cptr_save() {	
	global $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! isset( $_POST['cptr_noncename'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['cptr_noncename'], plugin_basename( __FILE__ ) ) ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$related = isset( $_POST['reladded'] ) ? $_POST['reladded'] : array();
	update_post_meta( $post_id, CI_CPTR_POST_RELATED, $related );
}

function cptr_populate($id) {
	$related_meta  = get_post_meta( $id, CI_CPTR_POST_RELATED, true );
	$related_posts = array();
	if ( ! empty( $related_meta ) ) {
		foreach ( $related_meta as $related ) {
			$post            = get_post( $related );
			$related_posts[] = $post;
		}
	}

	return $related_posts;
}

function cptr( $echo = null, $limit = null, $excerpt = null, $words = null, $thumb = null, $width = null, $height = null ) {

	if ( ! is_array( $echo ) ) {

		_cptr_deprecated_parameters( __FUNCTION__, '2.5' );

		$params = array();

		if ( $echo !== null and is_bool( $echo ) ) {
			$params['echo'] = $echo;
		} else {
			$params['echo'] = true;
		}

		if ( $limit !== null ) {
			$params['limit'] = $limit;
		}

		if ( $excerpt !== null ) {
			$params['excerpt'] = $excerpt;
		}

		if ( $words !== null ) {
			$params['words'] = $words;
		}

		if ( $thumb !== null ) {
			$params['thumb'] = $thumb;
		}

		if ( $width !== null ) {
			$params['width'] = $width;
		}

		if ( $height !== null ) {
			$params['height'] = $height;
		}

		return ci_cptr_short( $params );

	} elseif ( is_array( $echo ) ) {

		return ci_cptr_short( $echo );

	}
}

function cptr_show(
		$echo = true,
		$limit = CPTR_DEFAULT_LIMIT,
		$excerpt = CPTR_DEFAULT_EXCERPT,
		$words = CPTR_DEFAULT_EXCERPT_LENGTH,
		$thumb = CPTR_DEFAULT_THUMB,
		$width = CPTR_DEFAULT_THUMB_WIDTH,
		$height = CPTR_DEFAULT_THUMB_HEIGHT
	) {

	// Is it an "old" call?
	if ( ! is_array( $echo ) ) {
		_cptr_deprecated_parameters( __FUNCTION__, '2.5' );

		return _old_cptr_show( $echo, $limit, $excerpt, $words, $thumb, $width, $height );
	}
	
	// Nope, it's a new, with the array parameter.

	// Let's rename the array to avoid confusion.
	$att = $echo;
	unset( $echo );

	global $post, $wpdb;

	$old_post = $post;

	if ( isset( $att['post_id'] ) and intval( $att['post_id'] ) > 0 ) {
		$post_id = $att['post_id'];
	} else {
		$post_id = $post->ID;
	}

	$text = '';

	$related_meta  = get_post_meta( $post->ID, CI_CPTR_POST_RELATED, true );
	$related_posts = array();

	if ( ! empty( $related_meta ) ) {
		// Get a list of post objects
		foreach ( $related_meta as $related ) {
			$post            = get_post( $related );
			$related_posts[] = $post;
		}

		if ( count( $related_posts ) > 0 ) {
			$text .= '<ul id="cptr_related_posts">';

			$count = 0;
			foreach ( $related_posts as $post ) {
				setup_postdata( $post );
				if ( $att['limit'] != CPTR_DEFAULT_LIMIT && $count >= $att['limit'] ) {
					break;
				}

				$text .= '<li class="' . esc_attr( $count % 2 == 0 ? 'odd' : 'even' ) . '">';
				$text .= '<h4><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . get_the_title() . '</a></h4>';

				if ( current_theme_supports('post-thumbnails') && $att['thumb'] == true && has_post_thumbnail( $post->ID ) ) {
					$thumbnail = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . get_the_post_thumbnail( $post->ID, array( $att['width'], $att['height'] ) ) . '</a>';
					$text .= $thumbnail;
				}

				if ( $att['excerpt'] == true ) {
					$the_excerpt = _cptr_create_excerpt( $post->post_content, $att['words'] );
					$text        .= '<p>' . $the_excerpt . '</p>';
				}
				$text .= '</li>';
				$count++;
			}
			$text .= '</ul>';
		}
		
	}
	
	$post = $old_post;
	setup_postdata( $post );

	if ( $att['echo'] ) {
		echo $text;
	} else {
		return $text;
	}
}


// This function should be removed by version 3.0
// Don't use this function!
function _old_cptr_show(
		$echo = true,
		$limit = CPTR_DEFAULT_LIMIT,
		$excerpt = CPTR_DEFAULT_EXCERPT,
		$words = CPTR_DEFAULT_EXCERPT_LENGTH,
		$thumb = CPTR_DEFAULT_THUMB,
		$width = CPTR_DEFAULT_THUMB_WIDTH,
		$height = CPTR_DEFAULT_THUMB_HEIGHT,
		$output_order = null
	) {

	global $post, $wpdb;

	$old_post = $post;

	$text = '';

	$related_meta  = get_post_meta( $post->ID, CI_CPTR_POST_RELATED, true );
	$related_posts = array();

	if ( ! empty( $related_meta ) ) {
		// Get a list of post objects
		foreach ( $related_meta as $related ) {
			$post            = get_post( $related );
			$related_posts[] = $post;
		}

		if ( count( $related_posts ) > 0 ) {
			$text .= '<ul id="cptr_related_posts">';

			$count = 0;
			foreach ( $related_posts as $post ) {
				setup_postdata( $post );
				if ( $limit != CPTR_DEFAULT_LIMIT and $count >= $limit ) {
					break;
				}

				$text .= '<li class="' . esc_attr( $count % 2 == 0 ? 'odd' : 'even' ) . '">';
				$text .= '<h4><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . get_the_title() . '</a></h4>';

				if ( current_theme_supports( 'post-thumbnails' ) && $thumb == true && has_post_thumbnail( $post->ID ) ) {
					$thumbnail = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . get_the_post_thumbnail( $post->ID, array( $width, $height ) ) . '</a>';
					$text      .= $thumbnail;
				}

				if ( $excerpt == true ) {
					$the_excerpt = _cptr_create_excerpt( $post->post_content, $words );
					$text        .= '<p>' . $the_excerpt . '</p>';
				}
				$text .= '</li>';
				$count ++;
			}
			$text .= '</ul>';
		}
		
	}

	$post = $old_post;
	setup_postdata( $post );

	if ( $echo ) {
		echo $text;
	} else {
		return $text;
	}
}


// [cptr limit=0 excerpt=0 etc... ]
add_shortcode( 'cptr', 'ci_cptr_short' );
function ci_cptr_short( $atts ) {

	if ( isset( $atts['echo'] ) ) {
		$echo = $atts['echo'];
	} else {
		$echo = false;
	}

	$options = get_option( CI_CPTR_PLUGIN_OPTIONS );

	// $params will hold the default values ($options) overwritten by the values passed ($atts)
	$params = wp_parse_args( $atts, $options );

	$params['echo'] = $echo;

	// Now check the whole thing against the defaults and remove undefined attributes.
	$p = shortcode_atts( array(
		'echo'    => true,
		'limit'   => CPTR_DEFAULT_LIMIT,
		'excerpt' => CPTR_DEFAULT_EXCERPT,
		'words'   => CPTR_DEFAULT_EXCERPT_LENGTH,
		'thumb'   => CPTR_DEFAULT_THUMB,
		'width'   => CPTR_DEFAULT_THUMB_WIDTH,
		'height'  => CPTR_DEFAULT_THUMB_HEIGHT,
	), $params );

	return cptr_show( $p );
}


add_filter( 'plugin_action_links_' . CI_CPTR_BASENAME, 'ci_cptr_plugin_action_links' );
if ( ! function_exists( 'ci_cptr_plugin_action_links' ) ):
function ci_cptr_plugin_action_links( $links ) {
	$url = admin_url( 'options-general.php?page=ci_cptr_plugin' );
	array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'cisiw' ) . '</a>' );

	return $links;
}
endif;

add_action( 'in_plugin_update_message-' . CI_CPTR_BASENAME, 'ci_cptr_plugin_update_message', 10, 2 );
if ( ! function_exists( 'ci_cptr_plugin_update_message' ) ):
function ci_cptr_plugin_update_message( $plugin_data, $r ) {
	if ( ! empty( $r->upgrade_notice ) ) {
		printf( '<p style="margin: 3px 0 0 0; border-top: 1px solid #ddd; padding-top: 3px">%s</p>', $r->upgrade_notice );
	}
}
endif;


// oi! wait! where are you going? are you sure? 100%? a second thought? come on let's talk about it. oh well.
function cptr_uninstall() {
	global $wpdb;
	$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->postmeta WHERE meta_key = %s", CI_CPTR_POST_RELATED ) );
	delete_option( CI_CPTR_PLUGIN_OPTIONS );
}


//
// Determine if we need to run the upgrade procedure.
//
$cptr_installed_version = get_option( CI_CPTR_PLUGIN_INSTALLED );
if ( $cptr_installed_version === false or $cptr_installed_version != CPTR_VERSION ) {
	_cptr_do_upgrade( $cptr_installed_version );
}

function _cptr_do_upgrade( $version ) {
	$version = _cptr_upgrade_to_2_2( $version );
	$version = _cptr_upgrade_to_2_5( $version );
	update_option( CI_CPTR_PLUGIN_INSTALLED, CPTR_VERSION );
}

//
// Upgrade Functions
//
function _cptr_upgrade_to_2_5( $version ) {
	if ( $version == '2.2' or $version == '2.3' or $version == '2.4' ) {
		// No DB changes in this update
		return '2.5';
	} else {
		return $version;
	}
}

function _cptr_upgrade_to_2_2( $version ) {
	if ( $version !== false ) {
		return $version;
	}

	// Update the plugin options
	$opt_name = defined( CI_CPR_PLUGIN_OPTIONS ) ? CI_CPR_PLUGIN_OPTIONS : 'ci-cpr-plugin';
	$options  = get_option( $opt_name );
	if ( $options !== false ) {
		delete_option( $opt_name );
		update_option( CI_CPTR_PLUGIN_OPTIONS, $options );
	}

	// Update the posts
	$meta_name = defined( CI_CPR_POST_RELATED ) ? CI_CPR_POST_RELATED : 'cpr_related';
	global $wpdb;
	$query = "UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s";
	$wpdb->query( $wpdb->prepare( $query,
		CI_CPTR_POST_RELATED,
		$meta_name
	) );

	return '2.2';
}

add_action( 'admin_menu', 'cptr_box' );
add_action( 'wp_ajax_cptr-cats', 'cptr_cats' );
add_action( 'save_post', 'cptr_save' );
register_uninstall_hook( __FILE__, 'cptr_uninstall' );
