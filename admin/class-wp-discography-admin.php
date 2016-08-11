<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://beantowers.com/
 * @since      1.0.0
 *
 * @package    Wp_Discography
 * @subpackage Wp_Discography/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Discography
 * @subpackage Wp_Discography/admin
 * @author     James Towers <james@songdrop.com>
 */
class Wp_Discography_Admin {


	private $plugin_name;

	private $version;

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}


	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-discography-admin.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-discography-admin.min.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}

	public function create_post_types()
	{
		register_post_type( 'album',
		  array(
		    'labels' => array(
		      'name' => __( 'Albums' ),
		      'singular_name' => __( 'Album' ),
		      'add_new' => __( 'Add new album' ),
		      'add_new_item' => __( 'Add New Album' ),
		      'edit_item' => 'Edit Album',
		      'featured_image' => __( 'Album artwork' ),
		      'use_featured_image' => __( 'Use as album artwork' ),
		      'archives' => __( 'Album archives' )
		    ),
		    'public' => true,
		    'menu_icon' => 'dashicons-album',
		    'supports' => array(
		    	'title',
		    	'editor' => false,
		    	'thumbnail',
		    	'page-attributes',
		    	),
		    'rewrite' => array( 
		    	'slug' => 'albums', 
		    	'with_front' => true 
		    ),
		    'has_archive' => 'albums',
		  )
		);

		register_post_type( 'track',
		  array(
		    'labels' => array(
		      'name' => __( 'Tracks' ),
		      'singular_name' => __( 'Track' ),
		      'add_new' => __( 'Add new track' ),
		      'add_new_item' => __( 'Add New Track' ),
		      'edit_item' => 'Edit Track',
		      'featured_image' => __( 'Artwork' ),
		      'use_featured_image' => __( 'Use as artwork' ),
		      'archives' => __( 'Track archives' )
		    ),
		    'public' => true,
		    'menu_icon' => 'dashicons-playlist-audio',
		    'supports' => array(
		    	'title',
		    	'editor' => false,
		    	'thumbnail',
		    	'page-attributes',
		    	),
		    'rewrite' => array( 
		    	'slug' => 'tracks', 
		    	'with_front' => false 
		    ),
		    'has_archive' => 'tracks',
		  )
		);
	}

	public function insert_track($data)
	{
		$data = $_POST;
		$existing = $this->track_exists($data['lookupId']);
		$existing_id = $existing[0];

		if($existing_id){
			$added_to_album = get_post_meta($existing_id, $this->plugin_name . '_album-id', true);

			if($added_to_album == $data['albumId']){
				$response = array(
					'error' => 'Track ' . $existing_id . ' exists'
				);
			}else{
				add_post_meta($existing_id, $this->plugin_name . '_album-id', $data['albumId']);
				$response = array(
					'message' => 'Track ' . $existing_id . ' added to album ' . $data['albumId']
				);
			}
		}
		else{
			$args = array(
				'post_title' => $data['postTitle'],
				'post_type' => 'track',
				'post_status' => 'publish',
				'menu_order' => $data['menuOrder']
				);
			$new_track = wp_insert_post($args);
			update_post_meta($new_track, $this->plugin_name . '_preview-url', $data['previewUrl']);
			update_post_meta($new_track, $this->plugin_name . '_album-id', $data['albumId']);
			update_post_meta($new_track, $this->plugin_name . '_lookup-id', $data['lookupId']);
			update_post_meta($new_track, $this->plugin_name . '_url', $data['itunesUrl']);
			update_post_meta($new_track, $this->plugin_name . '_release-date', $data['releaseDate']);

			$response = array(
				'trackId' => $new_track
			);
			
		}

		echo json_encode($response);
		die();
	}

	private function track_exists( $lookup_id ) {
    global $wpdb;

    $track_id = $wpdb->get_row( "SELECT ID FROM {$wpdb->prefix}posts LEFT JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.id WHERE post_status = 'publish' && post_type = 'track' && meta_key = 'wp-discography_lookup-id' && meta_value = {$lookup_id}", 'ARRAY_N' );

    if( empty( $track_id ) ) {
        return false;
    } else {
        return $track_id;
    }
}

	public function search_itunes()
	{
		$response = $this->fetch_url($_POST);
		echo $this->render_search_results(json_decode($response['body']));
		wp_die(); 
	}

	public function fetch_album_tracks()
	{
		$response = $this->fetch_url($_POST, 'https://itunes.apple.com/lookup');
		echo $this->render_album_tracks(json_decode($response['body']), $_POST['album_id']);
		wp_die(); 
	}

	public function fetch_url($data, $url = 'https://itunes.apple.com/search')
	{
		$queryUrl = $url . '?' . $this->concatenate_query_string($data);
		return wp_remote_get($queryUrl);
	}

	private function concatenate_query_string($data)
	{
		$querystr = '';
		foreach ($data as $key => $value) {
			$querystr .= $key . '=' . $value . '&';
		}
		return str_replace(" ", "+", $querystr);
	}

	private function render_search_results($data)
	{ 
		$html = '<h3 class="hndle">Select the correct result from the list below:</h3>';
		$html .= '<table class="widefat"><tbody>';
		foreach($data->results as $result){

			$name = isset($result->trackName) ? $result->trackName : $result->collectionName;
			$lookupId = isset($result->trackId) ? $result->trackId : $result->collectionId;
			$url = isset($result->trackViewUrl) ? $result->trackViewUrl : $result->collectionViewUrl;
			$previewUrl = isset($result->previewUrl) ? $result->previewUrl : '';
			
			$html .= '<tr>
				<td width="60"><img src="' . $result->artworkUrl60 . '" /></td>
				<td>' . $result->artistName . ' - <strong>' . $name . '</strong></td>
				<td width="60"><a class="button-secondary" href="' . $url . '" data-lookup-id="' . $lookupId . '" data-release-date="' . $result->releaseDate . '" data-url="' . $result->collectionViewUrl . '" data-preview-url="' . $previewUrl . '">Select</a></td>
			</tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}

	private function render_album_tracks($data, $album_id)
	{
		$html = '<table class="widefat">
		  <thead>
		    <th class="row-title">Track name</th>
		    <th>Preview</th>
		    <th><a id="insert-all-tracks" class="button-secondary" href=""><span class="dashicons dashicons-plus-alt"></span> Add all</a></th>
		  </thead><tbody>';
		foreach($data->results as $result){
		  
		  if($result->wrapperType != 'track')
		  	continue;

		  $lookupId = isset($result->trackId) ? $result->trackId : $result->collectionId;
		  $d = new DateTime($result->releaseDate);
		  $date = $d->format('Y-m-d H:i');

	    $html .= '<tr>
	      <td>' . $result->trackName . '</td>
	      <td>
	        <audio controls>
	          <source src="' . $result->previewUrl . '" type="audio/mp3">
	        </audio>
	      </td>
	      <td><a class="insert-track button-secondary" href="#" 
	      	data-post-title="' . $result->trackName . '" 
	      	data-preview-url="' . $result->previewUrl . '" 
	      	data-itunes-url="' . $result->trackViewUrl . '" 
	      	data-album-id="' . $album_id . '" 
	      	data-lookup-id="' . $lookupId . '"
	      	data-menu-order="' . $result->trackNumber . '"
	      	data-release-date="' . $date . '"
	      	><span class="dashicons dashicons-plus-alt"></span> Add</a> <div class="spinner" style="float:none;width:auto;height:auto;padding:10px 0 10px 20px;"></div></td>
	    </tr>';
		}
		$html .= '</tbody></table>';
		return $html;
	}


	function set_post_columns($columns) {
	    return array(
	        'cb' => '<input type="checkbox" />',
	        'title' => __('Title'),
	        'album' =>__( 'Album'),
	        //'tags' =>__( 'Tags'),
	        //'date' => __('Date')
	    );
	}

	public function populate_custom_columns( $column, $post_id ) {
	  switch ( $column ) {
	    case 'album':
	    	$album_id = get_post_meta($post_id, $this->plugin_name . '_album-id', true);
	    	if($album_id){
	      	echo '<a href="' . get_edit_post_link($album_id) . '">'. get_post($album_id)->post_title . '</a>'; 
	      }
	      break;
	  }
	}
}
