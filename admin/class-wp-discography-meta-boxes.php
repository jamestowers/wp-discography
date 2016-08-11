<?php
class Wp_Discography_Meta_Boxes {

  private $plugin_name;

  private $version;

  private $meta_key;

  private $album_info_fields = array(
    array(
      'name' => 'url',
      'label' => 'iTunes URL',
      'type' => 'url',
      'field-class' => 'large-text',
      'description' => ''
      ),
    array(
      'name' => 'release-date',
      'label' => 'Release date',
      'type' => 'text',
      'field-class' => 'regular-text',
      'description' => 'Date must be in the following format: 2012-07-12 09:00 (time is optional)'
      ),
    array(
      'name' => 'lookup-id',
      'label' => 'iTunes Lookup ID',
      'type' => 'disabled'
      )
  );

  private $track_info_fields = array(
    array(
      'name' => 'preview-url',
      'label' => 'Preview URL',
      'type' => 'url',
      'field-class' => 'large-text',
      'description' => 'The url to the 30 sec. preview audio clip'
      ),
    array(
      'name' => 'url',
      'label' => 'iTunes URL',
      'type' => 'url',
      'field-class' => 'large-text',
      'description' => ''
      ),
    array(
      'name' => 'release-date',
      'label' => 'Release date',
      'type' => 'text',
      'field-class' => 'regular-text',
      'description' => 'Date must be in the following format: 2012-07-12 09:00 (time is optional)'
      ),
    array(
      'name' => 'lookup-id',
      'label' => 'iTunes Lookup ID',
      'type' => 'disabled'
      )
  );

  public function __construct( $plugin_name, $version ) {

    $this->version = $version;
    $this->plugin_name = $plugin_name;
    $this->meta_key = $this->plugin_name . '_';

  }

  public function get_albums()
  {
    $args = array(
      'posts_per_page'   => -1,
      'orderby'          => 'post_date',
      'order'            => 'DESC',
      'post_type'        => 'album',
      'post_status'      => 'publish'
    );

    $albums = get_posts( $args );

    return $albums;
  }

  public function get_album_tracks($album_id)
  {
    $args = array(
      'posts_per_page'   => -1,
      'orderby'          => 'menu_order',
      'order'            => 'ASC',
      'post_type'        => 'track',
      'post_status'      => 'publish',
      'meta_key'     => $this->meta_key . 'album-id',
      'meta_value'   => $album_id,
    );

    $tracks = get_posts( $args );

    return $tracks;
  }



  public function post_meta_boxes_setup()
  {
    /* Add meta boxes on the 'add_meta_boxes' hook. */
    //add_action( 'add_meta_boxes', array( &$this, 'add_search_results_container') );
    add_action( 'add_meta_boxes', array( &$this, 'add_track_meta_boxes') );
    add_action( 'add_meta_boxes', array( &$this, 'add_album_meta_boxes') );
    
    /* Save post meta on the 'save_post' hook. */
    add_action( 'save_post', array( &$this, 'save_post'), 10, 2 );
  }

  public function add_album_meta_boxes($post_type)
  {
    add_meta_box( 
      $this->plugin_name . '_album_info_meta_box', 
      esc_html__( 'Album info', $this->plugin_name ), 
      array( &$this, 'render_album_info_metabox'),
      'album',
      'normal',
      'core'
    );

    add_meta_box( 
      $this->plugin_name . '_album_tracks_meta_box', 
      esc_html__( 'Album tracks', $this->plugin_name ), 
      array( &$this, 'render_album_tracks_metabox'),
      'album',
      'normal',
      'core'
    );
  }

  public function add_track_meta_boxes()
  {
    add_meta_box( 
      $this->plugin_name . '_track_info_meta_box', 
      esc_html__( 'Track info', $this->plugin_name ), 
      array( &$this, 'render_track_info_metabox'),
      'track',
      'normal',
      'core'
    );
    add_meta_box( 
      $this->plugin_name . '_album_select_meta_box', 
      esc_html__( 'Album', $this->plugin_name ), 
      array( &$this, 'render_album_select_metabox'),
      'track',
      'normal',
      'core'
    );
    add_meta_box( 
      $this->plugin_name . '_lyrics_meta_box', 
      esc_html__( 'Lyrics', $this->plugin_name ), 
      array( &$this, 'render_lyrics_metabox'),
      'track',
      'normal',
      'core'
    );
  }


  /* Display the post meta box. */
  public function render_album_info_metabox( $post, $box ) { 
      // Save meta key name for later use
      //$meta_key = $this->plugin_name . '_album_info';
      // Add nonce field - use meta key name with '_nonce' appended
      wp_nonce_field( basename( __FILE__ ), $this->meta_key . 'nonce' );

      $this->search_box($post);
      
      $fields = $this->album_info_fields;
      $this->render_fields($post, $fields);
  }

  public function render_album_tracks_metabox( $post, $box) 
  {
    $lookup_id = get_post_meta($post->ID, $this->meta_key . 'lookup-id', true);
    
    //$lookup_id = isset($lookup_id) ? $lookup_id : '';
    
    if(!$lookup_id){

      echo '<p>You need to add the iTunes Lookup ID and save this album in order to auto-fetch tracks from iTunes. Alternatively, <a href="' . get_settings('siteurl') . '/wp-admin/post-new.php?post_type=track">add tracks manually</a></p>';
      echo '<a class="button-primary" disabled >Fetch album tracks from iTunes</a></p>';

    }else{

      echo '<a id="fetchTracks" class="button-primary" href="https://itunes.apple.com/lookup?id=' . $lookup_id . '" data-lookup-id="' . $lookup_id . '" data-album-id="' . $post->ID . '" title="click to fetch album tracks from iTunes">Fetch album tracks from iTunes</a> <div class="spinner" style="float:none;width:auto;height:auto;padding:10px 0 10px 20px;"></div><p>&nbsp;</p>';

      $tracks = $this->get_album_tracks($post->ID);
      echo '<div id="album-tracks" class="track-list"><table class="widefat">
          <thead>
            <th class="row-title">Track name</th>
            <th>Preview</th>
            <th>View on iTunes</th>
          </thead><tbody>';
      foreach($tracks as $track){ ?>
        
        <tr valign="top">
          <td scope="row"><?php echo $track->post_title;?> - <a href="<?php echo get_edit_post_link($track->ID);?>"><span class="dashicons dashicons-edit"></span> Edit</a></td>
          <td>
            <audio controls>
              <source src="<?php echo get_post_meta($track->ID, $this->meta_key . 'preview-url', true);?>" type="audio/mp3">
            </audio>
          </td>
          <td>
            <a href="<?php echo get_post_meta($track->ID, $this->meta_key . 'url', true);?>" target="_blank"><span class="dashicons dashicons-admin-links"></span></a>
          </td>
        </tr>
      <?php } 
      echo '</tbody></table></div>';

    }
  }

  public function render_track_info_metabox( $post, $box ) { 
      // Save meta key name for later use
      //$meta_key = $this->plugin_name . '_track_info';
      // Add nonce field - use meta key name with '_nonce' appended
      wp_nonce_field( basename( __FILE__ ), $this->meta_key . 'nonce' );

      $this->search_box($post);

      $fields = $this->track_info_fields;

      $this->render_fields($post, $fields);
  }

  private function search_box($post)
  { 
    $btn_text = "Search for this " .  $post->post_type . " on iTunes"; ?>

    <p class="description">Autofill fields below by fetching data from iTunes (requires <?php echo $post->post_type;?> title first)</p>

    <a id="itunes-search-btn" class="button-primary" href="#" title="click to fetch listings from iTunes" data-entity="<?php echo $post->post_type;?>"><?php echo $btn_text;?></a> 

    <div class="spinner" style="float:none;width:auto;height:auto;padding:10px 0 10px 20px;"></div>

    <div id="search-results" class="track-list hide">
      <p class="description">Choose from the list below to autocomplete the fields for this entry</p>
      <ul></ul>
    </div>
  <?php }

  private function render_fields($post, $fields)
  {

    foreach($fields as $field){ 
      $current_value = get_post_meta($post->ID, $this->meta_key . $field['name'], true);
      echo '<p>';
      if($field['label']){
        echo '<label for="' . $field['name'] . '">' . $field['label'] . '</label><br />';
      }
      
      switch($field['type']){
        
        case 'hidden' :
          echo '<input type="hidden" name="' . $this->meta_key . $field['name'] . '" value="' . $current_value . '" />';
          break;
        
        case 'url' :
          echo '<input type="url" name="' . $this->meta_key . $field['name'] . '" value="' . $current_value . '" placeholder="Enter ' . $field['label'] . '" class="' . $field['field-class'] . '" />';
          break;
        
        case 'date' : 
          echo '<input type="date" name="' . $this->meta_key.$field['name'] . '" value="' . $current_value . '" placeholder="Enter ' . $field['label'] . '" class="' . $field['field-class'] . '" />';
          break;

        case 'disabled':
         echo '<input type="text" name="' . $this->meta_key . $field['name'] . '" value="' . $current_value . '" placeholder="' . $field['label'] . '" class="" />';
          break;

        default :
          echo '<input type="text" name="' . $this->meta_key . $field['name'] . '" value="' . $current_value . '" placeholder="Enter ' . $field['label'] . '" class="' . $field['field-class'] . '" />';
          break;
      }
      if($field['description']){
        echo '<p class="description">' . $field['description'] . '</p>';
      }
      echo '</p>';
    }
  }



  public function save_post( $post_id, $post )
  {
    
    switch($post->post_type){

      case 'track' :
        $this->save_meta($post_id, $post, $this->meta_key . 'album-id'); 
        $this->save_meta($post_id, $post, $this->meta_key . 'lyrics'); 
        $fields = $this->track_info_fields;
        foreach($fields as $field){
          $this->save_meta($post_id, $post, $this->meta_key . $field['name']); 
        }
        break;

      case 'album' :
        //log_it($post);
        $fields = $this->album_info_fields;
        foreach($fields as $field){
          $this->save_meta($post_id, $post, $this->meta_key . $field['name']); 
        }
        // Set post date to release date
        if(isset($_POST[$this->meta_key . 'release-date'])){
          $updated_post = array();
          $updated_post['ID'] = $post_id;
          $updated_post['post_date'] = $_POST[$this->meta_key . 'release-date'];
          remove_action('save_post', array( $this, 'save_post' ));
          wp_update_post( $updated_post );
          add_action('save_post', array( $this, 'save_post' ));
        }
        break;
    }
  }

  public function render_album_select_metabox( $post, $box )
  {
    $albums = $this->get_albums();
    $current = get_post_meta($post->ID, $this->meta_key . 'album-id', true); ?>
    
    <div id="album-select" class="">
      <select name="<?php echo $this->meta_key . 'album-id' ;?>">
        <option value="">Select album</option>
        <?php foreach($albums as $album){ 
          $selected = $current == $album->ID ? 'selected' : ''; 
          echo '<option value="' . $album->ID . '" ' . $selected . '>' . $album->post_title . '</option>';
        }?>
      </select>
      <p><a href="<?php echo get_settings('siteurl');?>/wp-admin/post-new.php?post_type=album">Add new album</a></p>
    </div>
    <?php
  }

  public function render_lyrics_metabox( $post, $box)
  {
    $content = get_post_meta($post->ID, $this->meta_key . 'lyrics', true);
    return wp_editor( $content, $this->meta_key . 'lyrics', $settings = array() );
  }




  public function save_meta($post_id, $post, $meta_key)
  {
    $this->verify_nonce($meta_key . '_nonce', $post_id);

    /* Get the post type object. */
    $post_type = get_post_type_object( $post->post_type );
    /* Check if the current user has permission to edit the post. */
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
      return $post_id;

    /* Get the posted data and sanitize it for use as an HTML class. */
    $new_meta_value = ( isset( $_POST[$meta_key] ) ? $_POST[$meta_key] : '' );

    $this->save_or_edit_meta($post_id, $meta_key, $new_meta_value);
  }



  public function verify_nonce($nonce_key, $post_id)
  {
    if ( !isset( $_POST[$nonce_key] ) || !wp_verify_nonce( $_POST[$nonce_key], basename( __FILE__ ) ) )
      return $post_id;
  }



  public function save_or_edit_meta($post_id, $meta_key, $new_meta_value)
  {
    /* Get the meta value of the custom field key. */
    $meta_value = get_post_meta( $post_id, $meta_key, true );

    /* If a new meta value was added and there was no previous value, add it. */
    if ( $new_meta_value && '' == $meta_value )
      add_post_meta( $post_id, $meta_key, $new_meta_value, true );

    /* If the new meta value does not match the old value, update it. */
    elseif ( $new_meta_value && $new_meta_value != $meta_value )
      update_post_meta( $post_id, $meta_key, $new_meta_value );

    /* If there is no new meta value but an old value exists, delete it. */
    elseif ( '' == $new_meta_value && $meta_value )
      delete_post_meta( $post_id, $meta_key, $meta_value );
  }

}