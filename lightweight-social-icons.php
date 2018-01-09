<?php
/*
Plugin Name: Lightweight Social Icons
Plugin URI: http://generatepress.com/lightweight-social-icons
Description: Add simple icon font social media buttons. Choose the order, colors, size and more for 42 different icons!
Version: 1.0.1
Author: Thomas Usborne
Author URI: http://edge22.com
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: lightweight-social-icons
*/

define( 'LSI_VERSION', '1.0.1' );

add_action( 'plugins_loaded', 'lsi_load_textdomain' );
/**
 * Load plugin textdomain.
 *
 * @since 0.1
 */
function lsi_load_textdomain() {
	load_plugin_textdomain( 'lightweight-social-icons' );
}

class lsi_Widget extends WP_Widget {

	/**
	 * Register the widget.
	 *
	 * @since 0.1
	 */
	function __construct() {
		parent::__construct(
			'lsi_Widget',
			esc_html__( 'Lightweight Social Icons', 'lightweight-social-icons' )
		);

		$this->scripts['lsi_scripts'] = false;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_footer-widgets.php', array( $this, 'print_scripts' ), 9999 );
	}

	/**
	 * Front-end display of widget.
	 *
	 * @since 0.1
	 */
	public function widget( $args, $instance ) {
		$this->scripts['lsi_scripts'] = true;

		$title = apply_filters( 'widget_title', $instance['title'] );
		$options = lsi_icons();
		$defaults = lsi_option_defaults();
		$unique_id = esc_attr( $args['widget_id'] );
		$output = '';

		echo $args['before_widget'];

		echo ( ! empty( $title ) ) ? $args['before_title'] . $title . $args['after_title'] : '';

		$new_window = ( isset( $instance['new_window'] ) && '' !== $instance['new_window'] ) ? 'target="_blank"' : $defaults['new_window'];
		$font_size = ( isset( $instance['font_size'] ) && '' !== $instance['font_size'] ) ? $instance['font_size'] : $defaults['font_size'];
		$border_radius = ( isset( $instance['border_radius'] ) && '' !== $instance['border_radius'] ) ? $instance['border_radius'] : $defaults['border_radius'];
		$background = ( isset( $instance['background'] ) && '' !== $instance['background'] ) ? $instance['background'] : $defaults['background'];
		$color = ( isset( $instance['color'] ) && '' !== $instance['color'] ) ? $instance['color'] : $defaults['color'];
		$background_hover = ( isset( $instance['background_hover'] ) && '' !== $instance['background_hover'] ) ? $instance['background_hover'] : $defaults['background_hover'];
		$color_hover = ( isset( $instance['color_hover'] ) && '' !== $instance['color_hover'] ) ? $instance['color_hover'] : $defaults['color_hover'];
		$alignment = ( isset( $instance['alignment'] ) && '' !== $instance['alignment'] ) ? $instance['alignment'] : $defaults['alignment'];
		$tooltip = ( isset( $instance['tooltip'] ) && '' !== $instance['tooltip'] ) ? $instance['tooltip'] : $defaults['tooltip'];

		$count = 0;
		foreach ( $options as $option ) {

			$input = 'input' . $count++;
			$select = 'select' . $count++;

			$id = ( ! empty( $instance[$option['id']] ) ) ? $instance[$option['id']] : '';
			$name = ( ! empty( $instance[$select] ) ) ? $instance[$select] : '';
			$value = ( ! empty( $instance[$input] ) ) ? $instance[$input] : '';

			if ( ! empty( $value ) && ! empty( $name ) ) {
				if ( is_email( $value ) ) {
					$the_value = 'mailto:' . $value;
				} elseif ( 'phone' == $name ) {
					$the_value = 'tel:' . $value;
				} elseif ( 'skype' == $name ) {
					$the_value = 'skype:' . $value;
				} else {
					$the_value = esc_url( $value );
				}

				$show_tooltip = ( ! empty( $tooltip ) ) ? 'tooltip' : '';
				$rel_attribute = apply_filters( 'lsi_icon_rel_attribute','rel="nofollow"' );
				$title_attribute = apply_filters( 'lsi_icon_title_attribute','title="' . $options[$name]['name'] . '"' );
				$accessibility = apply_filters( 'lsi_icon_aria_attribute','aria-label="' . $options[$name]['name'] . '"' );

				$output .= sprintf(
					'<li class="lsi-social-%3$s"><a class="%4$s" %5$s %6$s %7$s href="%1$s" %2$s><i class="lsicon lsicon-%3$s"></i></a></li>',
					$the_value,
					'email' == $name ? '' : $new_window,
					$name,
					$show_tooltip,
					$rel_attribute,
					$title_attribute,
					$accessibility
				);

			}
		}

		if ( $output ) {
			printf(
				'<ul class="lsi-social-icons icon-set-%1$s" style="text-align: %3$s">%2$s</ul>',
				$unique_id,
				apply_filters( 'lsi_icon_output', $output ),
				$alignment
			);
		}

		$css = ".icon-set-{$unique_id} a,
			.icon-set-{$unique_id} a:visited,
			.icon-set-{$unique_id} a:focus {
				border-radius: {$border_radius}px;
				background: {$background} !important;
				color: {$color} !important;
				font-size: {$font_size}px !important;
			}

			.icon-set-{$unique_id} a:hover {
				background: {$background_hover} !important;
				color: {$color_hover} !important;
			}";

		wp_enqueue_style( 'lsi-style', plugin_dir_url( __FILE__ ) . 'css/style-min.css', array(), LSI_VERSION, 'all' );
		wp_add_inline_style( 'lsi-style', $css, 99 );

		if ( ! empty( $tooltip ) ) {
			wp_enqueue_script( 'lsi-tooltipster', plugin_dir_url( __FILE__ ) . 'js/jquery.tooltipster.min.js', array( 'jquery' ), LSI_VERSION, true );
		}

		echo $args['after_widget'];
	}

	/**
	 * Build the actual widget in the Dashboard
	 * @since 0.1
	 */
	public function form( $instance ) {

		$options = lsi_icons();

		$defaults = lsi_option_defaults();

		$title = ( isset( $instance[ 'title' ] ) && '' !== $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
		$border_radius = ( isset( $instance[ 'border_radius' ] ) && '' !== $instance[ 'border_radius' ] ) ? $instance[ 'border_radius' ] : $defaults['border_radius'];
		$font_size = ( isset( $instance[ 'font_size' ] ) && '' !== $instance[ 'font_size' ] ) ? $instance[ 'font_size' ] : $defaults['font_size'];
		$background = ( isset( $instance[ 'background' ] ) && '' !== $instance[ 'background' ] ) ? $instance[ 'background' ] : $defaults['background'];
		$color = ( isset( $instance[ 'color' ] ) && '' !== $instance[ 'color' ] ) ? $instance[ 'color' ] : $defaults['color'];
		$background_hover = ( isset( $instance[ 'background_hover' ] ) && '' !== $instance[ 'background_hover' ] ) ? $instance[ 'background_hover' ] : $defaults['background_hover'];
		$color_hover = ( isset( $instance[ 'color_hover' ] ) && '' !== $instance[ 'color_hover' ] ) ? $instance[ 'color_hover' ] : $defaults['color_hover'];
		$alignment = ( isset( $instance[ 'alignment' ] ) && '' !== $instance[ 'alignment' ] ) ? $instance[ 'alignment' ] : $defaults['alignment'];
		$tooltip = ( isset( $instance[ 'tooltip' ] ) && '' !== $instance[ 'tooltip' ] ) ? $instance[ 'tooltip' ] : $defaults['tooltip'];
		$new_window = ( isset( $instance[ 'new_window' ] ) && '' !== $instance[ 'new_window' ] ) ? $instance[ 'new_window' ] : $defaults['new_window'];

		$c = 0;
		foreach ( $options as $option ) {
			$defaults['input' . $c++] = '';
			$defaults['select' . $c++] = '';
		}

		$instance = wp_parse_args( (array) $instance, $defaults );

		$id = $this->id;

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:','lightweight-social-icons' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<label>
				<input class="widefat" style="max-width:65px;" id="<?php echo $this->get_field_id( 'font_size' ); ?>" name="<?php echo $this->get_field_name( 'font_size' ); ?>" type="text" value="<?php echo intval( $font_size ); ?>">
				<span class="pixels" style="display: inline-block;background:#efefef;position:relative;margin-left: -33px;padding: 3px 7px;">px</span>
				<?php esc_html_e( 'Icon Size', 'lightweight-social-icons' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input class="widefat" style="max-width:65px;" id="<?php echo $this->get_field_id( 'border_radius' ); ?>" name="<?php echo $this->get_field_name( 'border_radius' ); ?>" type="text" value="<?php echo intval( $border_radius ); ?>">
				<span class="pixels" style="display: inline-block;background:#efefef;position:relative;margin-left: -33px;padding: 3px 7px;">px</span>
				<?php esc_html_e( 'Border Radius', 'lightweight-social-icons' ); ?>
			</label>
		</p>

		<hr />

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo $this->get_field_id( 'background' ); ?>" name="<?php echo $this->get_field_name( 'background' ); ?>" type="text" value="<?php echo $background; ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Background Color', 'lightweight-social-icons' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo $this->get_field_id( 'color' ); ?>" name="<?php echo $this->get_field_name( 'color' ); ?>" type="text" value="<?php echo $color; ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Text Color', 'lightweight-social-icons' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo $this->get_field_id( 'background_hover' ); ?>" name="<?php echo $this->get_field_name( 'background_hover' ); ?>" type="text" value="<?php echo $background_hover; ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Background Hover Color', 'lightweight-social-icons' ); ?></label>
		</p>

		<p>
			<input class="widefat color-picker" style="max-width:75px;" id="<?php echo $this->get_field_id( 'color_hover' ); ?>" name="<?php echo $this->get_field_name( 'color_hover' ); ?>" type="text" value="<?php echo $color_hover; ?>">
			<label style="font-size: 90%;opacity: 0.8"><?php esc_html_e( 'Text Hover Color', 'lightweight-social-icons' ); ?></label>
		</p>

		<hr />

		<p>
			<label>
				<input id="<?php echo $this->get_field_id( 'new_window' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'new_window' ); ?>" value="1" <?php checked( 1, $new_window ); ?>/>
				<?php esc_html_e( 'Open links in new window?', 'lightweight-social-icons' ); ?>
			</label>
		</p>

		<p>
			<label>
				<input id="<?php echo $this->get_field_id( 'tooltip' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'tooltip' ); ?>" value="1" <?php checked( 1, $tooltip ); ?>/>
				<?php esc_html_e( 'Enable tooltips?', 'lightweight-social-icons' ); ?>
			</label>
		</p>

		<p>
			<select name="<?php echo $this->get_field_name( 'alignment' );?>" id="<?php echo $this->get_field_id( 'alignment' );?>">
				<option value="left" <?php selected( $instance['alignment'], 'left' ); ?>><?php _e( 'Left', 'lightweight-social-icons' );?></option>
				<option value="center" <?php selected( $instance['alignment'], 'center' ); ?>><?php _e( 'Center', 'lightweight-social-icons' );?></option>
				<option value="right" <?php selected( $instance['alignment'], 'right' ); ?>><?php _e( 'Right', 'lightweight-social-icons' );?></option>
			</select>
			<?php esc_html_e( 'Alignment', 'lightweight-social-icons' ); ?>
		</p>

		<hr />

		<ul class="social-icon-fields" style="margin-left: 0;">
			<?php
			$count = 0;
			foreach ( $options as $option ) {

				$input = 'input' . $count++;
				$select = 'select' . $count++;
				?>
				<li class="lsi-container" style="display: flex;">
					<select class="left choose-icon" name="<?php echo $this->get_field_name( $select );?>" id="<?php echo $this->get_field_id( $select );?>">
						<option value=""></option>
						<?php foreach ( $options as $option ) {  ?>
							<option value="<?php echo $option['id']; ?>" <?php selected( $instance[$select], $option['id'] ); ?>><?php echo $option['name']; ?></option>
						<?php } ?>
					</select>
					<input class="widefat right social-item" id="<?php echo $this->get_field_id( $input ); ?>" name="<?php echo $this->get_field_name( $input ); ?>" type="text" value="<?php echo esc_attr( $instance[$input] ); ?>">

				</li>
			<?php } ?>

			<span style="float:right;font-size: 90%;padding-top:3px;">
				Developed by: <a href="https://generatepress.com" target="_blank">GeneratePress</a>
			</span>
			<button onclick="event.preventDefault();lsiAddIcon(this)" class="button add-lsi-row <?php echo $id;?>" data-id="<?php echo $id;?>" style="margin-bottom:10px;"><?php _e( 'Add Icon', 'lsi' ); ?></button>
		</ul>

		<script>
			jQuery(document).ready(function ($) {
					$( '.social-item' ).each( function( index ) {
						if( ! $(this).val() ) {
							$( this ).parent().hide();
						}
					});

					$('.lsi-container .choose-icon').each(function(){
						$(this).change(function() {
							var select = $(this);

							if ( $(this).attr('value') == 'phone' ) {
								$(this).next('input').attr('placeholder', '<?php _e( '1 (123)-456-7890','lightweight-social-icons'); ?>');
							} else if ( $(this).attr('value') == 'email' ) {
								$(this).next().attr('placeholder', '<?php _e( 'you@yourdomain.com or http://', 'lightweight-social-icons' ); ?>');
							} else if ( $(this).attr('value') == 'skype' ) {
								$(this).next().attr('placeholder', '<?php _e( 'Username', 'lightweight-social-icons' ); ?>');
							}else if ( $(this).attr('value') == '' ) {
								$(this).next().attr('placeholder','');
							} else {
								$(this).next().attr('placeholder','http://');
							}
						});
					});
				});

				function lsiAddIcon(elem) {
				   jQuery( elem ).siblings('li:hidden:first').css( 'display', 'flex' );
			   }
		</script>
		<?php
	}

	/**
	 * Save and sanitize values
	 * @since 0.1
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$options = lsi_icons();

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['border_radius'] = intval( $new_instance['border_radius'] );
		$instance['font_size'] = intval( $new_instance['font_size'] );
		$instance['background'] = lsi_sanitize_hex_color( $new_instance['background'] );
		$instance['color'] = lsi_sanitize_hex_color( $new_instance['color'] );
		$instance['background_hover'] = lsi_sanitize_hex_color( $new_instance['background_hover'] );
		$instance['color_hover'] = lsi_sanitize_hex_color( $new_instance['color_hover'] );
		$instance['new_window'] = ( isset( $instance['new_window'] ) ) ? strip_tags( $new_instance['new_window'] ) : '';
		$instance['alignment'] = strip_tags( $new_instance['alignment'] );
		$instance['tooltip'] = ( isset( $new_instance['tooltip'] ) ) ? strip_tags( $new_instance['tooltip'] ) : '';
		$count = 0;

		foreach ( $options as $option ) {

			$input = 'input' . $count++;
			$select = 'select' . $count++;

			$instance[$select] = strip_tags( $new_instance[$select] );

			if ( 'skype' == $new_instance[$select] ) {
				$instance[$input] = strip_tags( $new_instance[$input] );
			} elseif ( 'phone' == $new_instance[$select] ) {
				$instance[$input] = strip_tags( $new_instance[$input] );
			} elseif ( 'email' == $new_instance[$select] ) {
				if ( is_email( $new_instance[$input] ) ) {
					$instance[$input] = sanitize_email( $new_instance[$input] );
				} else {
					$instance[$input] = esc_url( $new_instance[$input] );
				}
			} else {
				$instance[$input] = esc_url( $new_instance[$input] );
			}

		}

		return $instance;
	}

	/**
	 * Enqueue the admin scripts
	 * @since 0.1
	 */
	function enqueue_admin_scripts() {
		$screen = get_current_screen();
		if ( 'widgets' !== $screen->base ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'underscore' );
	}

	public function print_scripts() {
		?>
		<script>
			( function( $ ){
				function initColorPicker( widget ) {
					widget.find( '.color-picker' ).wpColorPicker( {
						change: _.throttle( function() { // For Customizer
							$(this).trigger( 'change' );
						}, 3000 )
					});
				}

				function onFormUpdate( event, widget ) {
					initColorPicker( widget );
				}

				$( document ).on( 'widget-added widget-updated', onFormUpdate );

				$( document ).ready( function() {
					$( '#widgets-right .widget:has(.color-picker)' ).each( function () {
						initColorPicker( $( this ) );
					} );
				} );
			}( jQuery ) );
		</script>
		<?php
	}

}

add_action( 'widgets_init', 'lsi_register_widget' );
/**
 * Register the widget
 * @since 0.1
 */
function lsi_register_widget() {
    register_widget( 'lsi_Widget' );
}

/**
 * Set the widget option defaults
 * @since 0.1
 */
function lsi_option_defaults() {
	$defaults = array(
		'title' => '',
		'new_window' => '',
		'border_radius' => 2,
		'font_size' => 20,
		'background' => '#1E72BD',
		'color' => '#FFFFFF',
		'background_hover' => '#777777',
		'color_hover' => '#FFFFFF',
		'alignment' => 'left',
		'tooltip' => ''
	);

	return apply_filters( 'lsi_option_defaults', $defaults );
}

/**
 * Set the default widget icons
 * @since 0.1
 */
function lsi_icons( $options = '' ) {
	$options = array (
		'fivehundredpx' => array(
			'id' => 'fivehundredpx',
			'name' => __( '500px', 'lightweight-social-icons' )
		),
		'angellist' => array(
			'id' => 'angellist',
			'name' => __( 'AngelList', 'lightweight-social-icons' )
		),
		'bandcamp' => array(
			'id' => 'bandcamp',
			'name' => __( 'Bandcamp', 'lightweight-social-icons' )
		),
		'behance' => array(
			'id' => 'behance',
			'name' => __( 'Behance', 'lightweight-social-icons' )
		),
		'bitbucket' => array(
			'id' => 'bitbucket',
			'name' => __( 'BitBucket', 'lightweight-social-icons' )
		),
		'bloglovin' => array(
			'id' => 'bloglovin',
			'name' => __( "Blog Lovin'", 'lightweight-social-icons' )
		),
		'codepen' => array(
			'id' => 'codepen',
			'name' => __( 'Codepen', 'lightweight-social-icons' )
		),
		'email' => array(
			'id' => 'email',
			'name' => __( 'Contact', 'lightweight-social-icons' )
		),
		'delicious' => array(
			'id' => 'delicious',
			'name' => __( 'Delicious', 'lightweight-social-icons' )
		),
		'deviantart' => array(
			'id' => 'deviantart',
			'name' => __( 'DeviantArt', 'lightweight-social-icons' )
		),
		'digg' => array(
			'id' => 'digg',
			'name' => __( 'Digg', 'lightweight-social-icons' )
		),
		'dribbble' => array(
			'id' => 'dribbble',
			'name' => __( 'Dribbble', 'lightweight-social-icons' )
		),
		'dropbox' => array(
			'id' => 'dropbox',
			'name' => __( 'Dropbox', 'lightweight-social-icons' )
		),
		'facebook' => array(
			'id' => 'facebook',
			'name' => __( 'Facebook', 'lightweight-social-icons' )
		),
		'flickr' => array(
			'id' => 'flickr',
			'name' => __( 'Flickr', 'lightweight-social-icons' )
		),
		'foursquare' => array(
			'id' => 'foursquare',
			'name' => __( 'Foursquare', 'lightweight-social-icons' )
		),
		'github' => array(
			'id' => 'github',
			'name' => __( 'Github', 'lightweight-social-icons' )
		),
		'gplus' => array(
			'id' => 'gplus',
			'name' => __( 'Google+', 'lightweight-social-icons' )
		),
		'houzz' => array(
			'id' => 'houzz',
			'name' => __( 'Houzz', 'lightweight-social-icons' )
		),
		'instagram' => array(
			'id' => 'instagram',
			'name' => __( 'Instagram', 'lightweight-social-icons' )
		),
		'itunes' => array(
			'id' => 'itunes',
			'name' => __( 'iTunes', 'lightweight-social-icons' )
		),
		'jsfiddle' => array(
			'id' => 'jsfiddle',
			'name' => __( 'JSFiddle', 'lightweight-social-icons' )
		),
		'lastfm' => array(
			'id' => 'lastfm',
			'name' => __( 'Last.fm', 'lightweight-social-icons' )
		),
		'linkedin' => array(
			'id' => 'linkedin',
			'name' => __( 'LinkedIn', 'lightweight-social-icons' )
		),
		'mixcloud' => array(
			'id' => 'mixcloud',
			'name' => __( 'Mixcloud', 'lightweight-social-icons' )
		),
		'paper-plane' => array(
			'id' => 'paper-plane',
			'name' => __( "Newsletter", 'lightweight-social-icons' )
		),
		'phone' => array(
			'id' => 'phone',
			'name' => __( 'Phone', 'lightweight-social-icons' )
		),
		'pinterest' => array(
			'id' => 'pinterest',
			'name' => __( 'Pinterest', 'lightweight-social-icons' )
		),
		'reddit' => array(
			'id' => 'reddit',
			'name' => __( 'Reddit', 'lightweight-social-icons' )
		),
		'rss' => array(
			'id' => 'rss',
			'name' => __( 'RSS', 'lightweight-social-icons' )
		),
		'skype' => array(
			'id' => 'skype',
			'name' => __( 'Skype', 'lightweight-social-icons' )
		),
		'snapchat' => array(
			'id' => 'snapchat',
			'name' => __( 'Snapchat', 'lightweight-social-icons' )
		),
		'soundcloud' => array(
			'id' => 'soundcloud',
			'name' => __( 'Soundcloud', 'lightweight-social-icons' )
		),
		'spotify' => array(
			'id' => 'spotify',
			'name' => __( 'Spotify', 'lightweight-social-icons' )
		),
		'stackoverflow' => array(
			'id' => 'stackoverflow',
			'name' => __( 'Stack Overflow', 'lightweight-social-icons' )
		),
		'steam' => array(
			'id' => 'steam',
			'name' => __( 'Steam', 'lightweight-social-icons' )
		),
		'stumbleupon' => array(
			'id' => 'stumbleupon',
			'name' => __( 'Stumbleupon', 'lightweight-social-icons' )
		),
		'tripadvisor' => array(
			'id' => 'tripadvisor',
			'name' => __( 'Trip Advisor', 'lightweight-social-icons' )
		),
		'tumblr' => array(
			'id' => 'tumblr',
			'name' => __( 'Tumblr', 'lightweight-social-icons' )
		),
		'twitch' => array(
			'id' => 'twitch',
			'name' => __( 'Twitch', 'lightweight-social-icons' )
		),
		'twitter' => array(
			'id' => 'twitter',
			'name' => __( 'Twitter', 'lightweight-social-icons' )
		),
		'vimeo' => array(
			'id' => 'vimeo',
			'name' => __( 'Vimeo', 'lightweight-social-icons' )
		),
		'vine' => array(
			'id' => 'vine',
			'name' => __( 'Vine', 'lightweight-social-icons' )
		),
		'vkontakte' => array(
			'id' => 'vkontakte',
			'name' => __( "VK", 'lightweight-social-icons' )
		),
		'wordpress' => array(
			'id' => 'wordpress',
			'name' => __( 'WordPress', 'lightweight-social-icons' )
		),
		'xing' => array(
			'id' => 'xing',
			'name' => __( 'Xing', 'lightweight-social-icons' )
		),
		'yelp' => array(
			'id' => 'yelp',
			'name' => __( 'Yelp', 'lightweight-social-icons' )
		),
		'youtube' => array(
			'id' => 'youtube',
			'name' => __( 'YouTube', 'lightweight-social-icons' )
		),
		'yahoo' => array(
			'id' => 'yahoo',
			'name' => __( 'Yahoo', 'lightweight-social-icons' )
		)
	);


	return apply_filters( 'lsi_icons_defaults', $options );
}

/**
 * Function to sanitize the hex values
 * @since 0.1
 */
function lsi_sanitize_hex_color( $color ) {
	if ( '' === $color ) {
		return '';
	}

	// 3 or 6 hex digits, or the empty string.
	if ( preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
		return $color;
	}

	return null;
}
