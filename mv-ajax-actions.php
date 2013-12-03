<?php
/**
 * Media Vault Admin Ajax Handling.
 *
 * @package WordPress_Plugin
 * @package MediaVault
 *
 * @author Max G J Panas <m@maxpanas.com>
 * @license GPL-3.0+
 */


// forbid direct calls to this file without wp ajax constants
if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}


/**
 * Get the HTML image element of an attachment via AJAX
 *
 * @since 0.8
 *
 * @return string HTML image element of attachment file,
 *                if there is any, otherwise return 0
 */
function mgjp_mv_get_attachment_image() {

  $id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : '';
  $size = isset( $_GET['size'] ) ? $_GET['size'] : 'thumbnail';
  $icon = isset( $_GET['icon'] ) ? ! ! $_GET['icon'] : false;
  $args = isset( $_GET['args'] ) ? $_GET['args'] : null;

  $html = wp_get_attachment_image( $id, $size, $icon, $args );
  if ( empty( $html ) )
    wp_die( -1 );

  wp_die( $html );
}
add_action( 'wp_ajax_mgjp_mv_get_attachment_image', 'mgjp_mv_get_attachment_image' );


/**
 * Attempt to restore the default placeholder image
 *
 * @since 0.8
 *
 * @return array [0] 
 *               [1] 
 */
function mgjp_mv_restore_default_placeholder_image() {

  if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) )
    wp_die( -1 );

  check_ajax_referer( 'mgjp_mv_ir_restore_default', 'nonce' );

  $size = isset( $_POST['size'] ) ? $_POST['size']: 'thumbnail';
  $args = isset( $_GET['args'] ) ? $_GET['args'] : null;

  $ir_id = mgjp_mv_load_placeholder_image( true );
  if ( ! $ir_id )
    wp_die( -1 );

  wp_die( json_encode( array(
    'id'  => $ir_id,
    'img' => wp_get_attachment_image( $ir_id, $size, false, $args )
  ) ) );
}
add_action( 'wp_ajax_mgjp_mv_restore_default_placeholder_image', 'mgjp_mv_restore_default_placeholder_image' );

?>