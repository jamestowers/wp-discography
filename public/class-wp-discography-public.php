<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://beantowers.com/
 * @since      1.0.0
 *
 * @package    Wp_Discography
 * @subpackage Wp_Discography/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Discography
 * @subpackage Wp_Discography/public
 * @author     James Towers <james@songdrop.com>
 */
class Wp_Discography_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private static $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		self::$plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( self::$plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-discography-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( self::$plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-discography-public.min.js', array( 'jquery' ), $this->version, false );

	}

	public static function get_album_tracks($album_id)
  {

    $args = array(
      'posts_per_page'   => -1,
      'orderby'          => 'menu_order',
      'order'            => 'ASC',
      'post_type'        => 'track',
      'post_status'      => 'publish',
      'meta_key'     => self::$plugin_name . '_album-id',
      'meta_value'   => $album_id,
    );

    $tracks = get_posts( $args );

    return $tracks;
  }

  /**
   * Add album column to tracks write panel
   * @param see: https://codex.wordpress.org/Plugin_API/Action_Reference/manage_posts_custom_column
   */
  function set_post_columns($columns) {
      return array(
          'cb' => '<input type="checkbox" />',
          'title' => __('Title'),
          'album' => __('Album'),
          'tags' =>__( 'Tags'),
          'date' => __('Date')
      );
  }

}
