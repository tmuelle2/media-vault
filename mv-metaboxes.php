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
  __( 'Media Vault Protection Settings', 'media-vault' ),
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
  wp_enqueue_style( 'mgjp-mv-attachment-edit-styles', plugins_url( 'css/mv-attachment-edit.css', __FILE__ ), 'all', null );


  wp_nonce_field( 'mgjp_mv_protection_metabox', 'mgjp_mv_protection_metabox_nonce' );


  $permission = get_post_meta( $post->ID, '_mgjp_mv_permission', true );

  $permissions = mgjp_mv_get_the_permissions();

  if ( empty( $permission ) || ! isset( $permissions[$permission] ) )
    $permission = 'default'; ?>

  <input type="hidden" name="mgjp_mv_protection_toggle" value="off">
  <input type="checkbox" id="mgjp_mv_protection_toggle" name="mgjp_mv_protection_toggle" <?php checked( mgjp_mv_is_protected( $post->ID ) ); ?>>

  <label class="mgjp-mv-protection-toggle" for="mgjp_mv_protection_toggle">

    <span aria-role="hidden" class="mgjp-on button button-primary" data-mgjp-content="<?php esc_attr_e( 'Add to Protected', 'media-vault' ); ?>"></span>
    <span aria-role="hidden" class="mgjp-off" data-mgjp-content="<?php esc_attr_e( 'Remove from Protected', 'media-vault' ); ?>"></span>

    <span class="visuallyhidden"><?php esc_html_e( 'Protect this attachment\'s files with Media Vault.', 'media-vault' ); ?></span>

  </label>

  <p class="mgjp-mv-permission-select">

    <label for="mgjp_mv_permission_select">

      <span class="description"><?php esc_html_e( 'File access permission', 'media-vault' ); ?></span>

    </label>

    <select id="mgjp_mv_permission_select" name="mgjp_mv_permission_select">

      <option value="default" <?php selected( $permission, 'default' ); ?>>
        <?php esc_html_e( 'Use Default Setting', 'media-vault' ); ?>
      </option>

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
  $attachment_id = $post->ID;

  if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    return;

  if( ! isset( $_POST['mgjp_mv_protection_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['mgjp_mv_protection_metabox_nonce'], 'mgjp_mv_protection_metabox' ) ) 
    return;

  if( ! current_user_can( 'edit_post', $attachment_id ) )
    return;


  if ( ! isset( $_POST['mgjp_mv_protection_toggle'] ) )
    return;

  switch ( $_POST['mgjp_mv_protection_toggle'] ) {

    case 'off' :
      remove_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      $move = mgjp_mv_move_attachment_from_protected( $attachment_id );

      add_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      if ( is_wp_error( $move ) )
        return;

      delete_post_meta( $attachment_id, '_mgjp_mv_permission' );

      return;

    case 'on':
      remove_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      $move = mgjp_mv_move_attachment_to_protected( $attachment_id );

      add_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

      if ( is_wp_error( $move ) )
        return;

      if ( ! isset( $_POST['mgjp_mv_permission_select'] ) || empty( $_POST['mgjp_mv_permission_select'] ) )
        return;
      
      $permissions = mgjp_mv_get_the_permissions();  

      if ( 'default' == $_POST['mgjp_mv_permission_select'] || ! isset( $permissions[$_POST['mgjp_mv_permission_select']] ) )
        delete_post_meta( $attachment_id, '_mgjp_mv_permission' );
      else
        update_post_meta( $attachment_id, '_mgjp_mv_permission', $_POST['mgjp_mv_permission_select'] );

      return;

    default: return;
  }
}
add_action( 'edit_attachment', 'mgjp_mv_save_attachment_metabox_data' );

?>