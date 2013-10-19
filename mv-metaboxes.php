<?php
/**
 * Media Vault custom attachment metabox functions.
 *
 * @package WordPress_Plugin
 * @package MediaVault
 *
 * @author Max G J Panas <http://maxpanas.com/>
 * @license GPL-3.0+
 */


/** Register custom metabox **/
add_meta_box(
  'mgjp_mv_protection_metabox',
  __( 'Media Vault Protection Settings', 'mgjp_mediavault' ),
  'mgjp_mv_render_attachment_protection_metabox',
  'attachment',
  'side'
);


/**
 * Rendering function for the Media Vault attachment
 * metabox
 *
 * @since 0.7.1
 *
 * @uses mgjp_mv_get_the_permission()
 * @uses mgjp_mv_get_the_permissions()
 * @param $post object WP_Post object of current attachment
 */
function mgjp_mv_render_attachment_protection_metabox( $post ) {

  // enqueue metabox styles
  wp_enqueue_style( 'mgjp-mv-attachment-edit-styles', plugin_dir_url( __FILE__ ) . 'css/mv-attachment-edit.css', 'screen', null );

  wp_nonce_field( 'mgjp_mv_protection_metabox', 'mgjp_mv_protection_metabox_nonce' );

  $permission = mgjp_mv_get_the_permission( $post->ID );

  // if permission is not === false here attachment files are protected
  $protected = ! ! $permission;

  $permissions = mgjp_mv_get_the_permissions();

  if ( ! isset( $permissions[$permission] ) )
    $permission = get_option( 'mgjp_mv_default_permission', 'logged-in' ); ?>

  <input type="hidden" name="mgjp_mv_protection_toggle" value="off">
  <input type="checkbox" id="mgjp_mv_protection_toggle" name="mgjp_mv_protection_toggle" <?php checked( $protected ); ?>>

  <label class="mgjp-mv-protection-toggle" for="mgjp_mv_protection_toggle">

    <span aria-role="hidden" class="mgjp-on button button-primary" data-mgjp-content="<?php esc_attr_e( 'Add to Protected', 'mgjp_mediavault' ); ?>"></span>
    <span aria-role="hidden" class="mgjp-off" data-mgjp-content="<?php esc_attr_e( 'Remove from Protected', 'mgjp_mediavault' ); ?>"></span>

    <span class="visuallyhidden"><?php esc_html_e( 'Protect this attachment\'s files with Media Vault.', 'mgjp_mediavault' ); ?></span>

  </label>

  <p class="mgjp-mv-permission-select">

    <label for="mgjp_mv_permission_select">

      <span class="description"><?php esc_html_e( 'File access permission', 'mgjp_mediavault' ); ?></span>

    </label>

    <select id="mgjp_mv_per/mission_select" name="mgjp_mv_permission_select">

      <?php foreach ( $permissions as $key => $data ) : ?>

        <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $permission, $key ); ?>>
          <?php echo esc_html( $data['select'] ); ?>
        </option>

      <?php endforeach; ?>

    </select>

  </p>

  <?php
}


/**
 * Save Media Vault attachment metabox data on
 * edit attachments
 *
 * @since 0.7.1
 *
 * @global $post WP_Post object of current post
 *
 * @uses mgjp_move_attachment_files()
 * @return void if any of the validations fail
 */
function mgjp_mv_save_attachment_metabox_data() {

  global $post;
  $post_id = $post->ID;

  if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

  if( ! isset( $_POST['mgjp_mv_protection_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['mgjp_mv_protection_metabox_nonce'], 'mgjp_mv_protection_metabox' ) ) 
    return;

  if( ! current_user_can( 'edit_post', $post_id ) )
    return;


  if ( ! isset( $_POST['mgjp_mv_protection_toggle'] ) )
    return;

  if ( $_POST['mgjp_mv_protection_toggle'] == 'on' ) {

    $meta = get_post_meta( $post_id, 'mgjp_mv_meta', true );

    if ( ! isset( $meta['is_protected'] ) || ! $meta['is_protected'] ) {

      $file = get_post_meta( $post_id, '_wp_attached_file', true );

      $new_reldir = path_join(
        ltrim( mgjp_mv_upload_dir(), '/' ),
        dirname( $file )
      );

      include( plugin_dir_path( __FILE__ ) . 'includes/mgjp-functions.php' );

      remove_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      $move = mgjp_move_attachment_files( $post_id, $new_reldir );

      add_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      if ( is_wp_error( $move ) )
        return;

    }

    $meta['is_protected'] = true;

    $meta['permission'] = isset( $_POST['mgjp_mv_permission_select'] ) && ! empty( $_POST['mgjp_mv_permission_select'] ) ?
                            $_POST['mgjp_mv_permission_select'] :
                            get_option( 'mgjp_mv_default_permission', 'logged-in' );

  } else if ( $_POST['mgjp_mv_protection_toggle'] == 'off' ) {

    $meta = get_post_meta( $post_id, 'mgjp_mv_meta', true );

    if ( ! isset( $meta['is_protected'] ) || ! $meta['is_protected'] )
      return;

    if ( isset( $meta['is_protected'] ) && $meta['is_protected'] ) {

      $file = get_post_meta( $post_id, '_wp_attached_file', true );

      $new_reldir = ltrim(
        dirname( $file ),
        ltrim( mgjp_mv_upload_dir( '/' ), '/' )
      );

      include( plugin_dir_path( __FILE__ ) . 'includes/mgjp-functions.php' );

      remove_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      $move = mgjp_move_attachment_files( $post_id, $new_reldir );

      add_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      if ( is_wp_error( $move ) )
        return;
    }

    $meta['is_protected'] = false;

    if ( isset( $meta['permission'] ) )
      unset( $meta['permission'] );

  }

  if ( ! isset( $meta ) )
    return;

  update_post_meta( $post_id, 'mgjp_mv_meta', $meta );
}
add_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

?>