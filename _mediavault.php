<?php
/*
Plugin Name: Media Vault
Text Domain: mgjp_mediavault
Plugin URI: http://wordpress.org/plugins/media-vault/
Description: Protect attachment files from direct access using powerful and flexible restrictions. Offer safe download links for any file in your uploads folder.
Version: 0.7
Author: Max GJ Panas
Author URI: http://maxpanas.com
License: GPLv3 or later

Copyright 2013 Maximilianos G J Panas (email : m@maxpanas.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 3, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Load the plugin textdomain.
 *
 * @since 0.1
 */
load_plugin_textdomain( 'mgjp_mediavault', false, plugin_dir_path( __FILE__ ) . 'languages/' );


/**
 * Return the Media Vault protected upload folder
 *
 * @since 0.1
 *
 * @param $path string path to attach to the end of protected folder dirname
 */
function mgjp_mv_upload_dir( $path = '' ) {

  $dirpath = '/_mediavault';
  $dirpath .= $path;

  return $dirpath;

}


/**
 * The default Media Vault permissions array
 *
 * @since 0.4
 *
 * @global $mgjp_mv_permissions array Array of default Media Vault file access permissions
 */
global $mgjp_mv_permissions;
$mgjp_mv_permissions = array(
  'admin'     =>  array(
                    'description'  => __( 'Admin users only', 'mgjp_mediavault' ),
                    'select'       => __( 'Admin users', 'mgjp_mediavault' ),
                    'logged_in'    => true,
                    'run_in_admin' => true,
                    'cb'           => 'mgjp_mv_check_admin_permission'
                  ),
  'author'    =>  array(
                    'description'  => __( 'The file\'s author', 'mgjp_mediavault' ),
                    'select'       => __( 'The file\'s author', 'mgjp_mediavault' ),
                    'logged_in'    => true,
                    'run_in_admin' => true,
                    'cb'           => 'mgjp_mv_check_author_permission'
                  ),
  'logged-in' =>  array(
                    'description'  => __( 'All logged-in users', 'mgjp_mediavault' ),
                    'select'       => __( 'Logged-in users', 'mgjp_mediavault' ),
                    'logged_in'    => true,
                    'run_in_admin' => false,
                    'cb'           => false
                  ),
  'all'       =>  array(
                    'description'  => __( 'Anyone', 'mgjp_mediavault' ),
                    'select'       => __( 'Anyone', 'mgjp_mediavault' ),
                    'logged_in'    => false,
                    'run_in_admin' => false,
                    'cb'           => false
                  )
);

/**
 * The 'admin' permission checking callback.
 *
 * @since 0.4
 */
function mgjp_mv_check_admin_permission() {
  if ( ! current_user_can( 'manage_options' ) )
    return new WP_Error( 'not_admin', __( 'You do not have sufficient permissions to view this file.', 'mgjp_mediavault' ) );

  return true;
}

/**
 * The 'author' permission checking callback.
 *
 * @since 0.4
 */
function mgjp_mv_check_author_permission( $attachment_id ) {

  if ( current_user_can( 'manage_options' ) )
    return true;

  if ( ! isset( $attachment_id ) || empty( $attachment_id ) )
    return new WP_Error( 'no_id', __( 'There was an error determining this attachment\'s author. Please contact the website administrator.', 'mgjp_mediavault' ) );

  if ( get_current_user_id() != get_post_field( 'post_author', $attachment_id, 'raw' ) )
    return new WP_Error( 'not_author', __( 'You do not have sufficient permissions to view this file.', 'mgjp_mediavault' ) );

  return true;
}


/**
 * Adds a permission to the Media Vault permissions array
 *
 * @since 0.6
 *
 * @uses $mgjp_mv_permissions
 * @param $name string Name-id of the new permission, must be unique
 * @param $args array Array of arguments for permission must include:
 *                    'description' string Human readable short description of permission
 *                    'select' string Human readable very consice description of permission, used in option of select element
 *                    'logged_in' bool Whether the user must be at least logged in
 *                    'run_in_admin' bool Whether to run the permission check in WP Admin
 *                    'cb' string Function name to be called to evaluate file access permissions, false if no callback desired
 *                                Function MUST return TRUE if access permitted to file and FALSE or WP_Error if access denied
 * @return bool false on failure, true on success
 */
function mgjp_mv_add_permission( $name, $args ) {

  $allowed_keys = array( 'description', 'select', 'logged_in', 'run_in_admin', 'cb' );

  $safe_args = array();
  foreach ( $allowed_keys as $key ) {
    if ( isset( $args[$key] ) )
      $safe_args[$key] = $args[$key];
  }

  if ( count( $allowed_keys ) !== count( $safe_args ) )
    return false;

  global $mgjp_mv_permissions;
  if ( isset( $mgjp_mv_permissions[$name] ) )
    return false;

  $mgjp_mv_permissions[$name] = $safe_args;

  return true;
}

/**
 * Returns the array of permission array objects
 *
 * @since 0.4
 *
 * @uses apply_filters() to provide hook to change default permissions, or
 *                       add / remove custom permission objects
 * @uses $mgjp_mv_permissions
 * @return array Array of Media Vault file access permissions
 */
function mgjp_mv_get_the_permissions() {

  global $mgjp_mv_permissions;

  return apply_filters( 'mgjp_mv_edit_permissions', $mgjp_mv_permissions );

}

/**
 * Returns the Media Vault file access permission for an attachment
 * or false if the attachment's files are not marked as protected
 *
 * @since 0.7
 *
 * @return bool false if attachment is not protected
 * @return string the permission name-id set for this attachment if it is protected
 */
function mgjp_mv_get_the_permission( $attachment_id ) {

  if ( ! $meta = get_post_meta( $attachment_id, 'mgjp_mv_meta', true ) )
    return false;

  if ( ! isset( $meta['is_protected'] ) || ! $meta['is_protected'] )
    return false;

  $permission = isset( $meta['permission'] ) && ! empty( $meta['permission'] ) ?
                  $meta['permission'] :
                  get_option( 'mgjp_mv_default_permission', 'logged-in' );

  return $permission;
}

/**
 * Check if the current user is permitted to access an attachment of a
 * specified ID within the WordPress Admin
 *
 * @since 0.7
 *
 * @uses mgjp_mv_get_the_permission()
 * @uses mgjp_mv_get_the_permissions()
 * @param $attachment_id int The id of the attachment to check against
 * @return bool True if current user access permitted
 * @return bool False if current user access denied
 */
function mgjp_mv_admin_check_user_permitted( $attachment_id ) {

  // check if post is attachment & if it has protection and permissions set on it
  if ( ! $permission = mgjp_mv_get_the_permission( $attachment_id ) )
    return true;

  $permissions = mgjp_mv_get_the_permissions();

  // check if permission set on attachment is valid
  if ( ! isset( $permissions[$permission] ) )
    return false; // it is better to fail safely than to reveal something we should not

  // check if permission check is set to need not run in admin
  if ( isset( $permissions[$permission]['run_in_admin'] ) && ! $permissions[$permission]['run_in_admin'] )
    return true;

  // check if permission callback is set to false
  if ( isset( $permissions[$permission]['cb'] ) && false === $permissions[$permission]['cb'] )
    return true;

  // if not false (above), check if permission callback is valid, fail safely if it is not
  if ( ! is_callable( $permissions[$permission]['cb'] ) )
    return false;

  // perform the defined permission check callback on the user for this attachment
  // function MUST return true if the user is allowed access
  $permission_check = call_user_func( $permissions[$permission]['cb'], $attachment_id );

  // if there are no errors permit access
  if ( true === $permission_check )
    return true;

  return false;
}


/**
 * Function for the 'user_has_cap' WP Core filter. Checks the permissions set
 * on an attachment before making it available to a user to edit/delete/read.
 *
 * @since 0.7
 *
 * @uses mgjp_mv_admin_check_user_permitted()
 * @param $allcaps array Array of all user capabilities
 * @param $cap array  [0] string capability required
 * @param $args array [0] string capability requested
 *                    [1] int user ID
 *                    [2] int post ID
 * @return array @param $allcaps unchanged if user permitted to access post
 * @return array @param $allcaps with capability @param $cap[0] set to false
 */
function mgjp_mv_edit_capabilities( $allcaps, $cap, $args ) {

  $disallowed_caps = array(
    'edit_post',
    'delete_post',
    'read_post'
  );

  if ( ! in_array( $args[0], $disallowed_caps ) )
    return $allcaps;

  if ( ! isset( $args[2] ) )
    return $allcaps;

  // check if user is permitted to access the post
  if ( mgjp_mv_admin_check_user_permitted( $args[2] ) )
    return $allcaps;

  $allcaps[$cap[0]] = false;

  return $allcaps;
}
add_filter( 'user_has_cap', 'mgjp_mv_edit_capabilities', 10, 3 );


/**
 * Include the plugin's general settings
 *
 * @since 0.4
 */
function mgjp_mv_media_vault_options_include() {

  include_once( plugin_dir_path( __FILE__ ) . 'mv-options-media-vault.php' );

}
add_action( 'admin_init', 'mgjp_mv_media_vault_options_include' );


/**
 * Include the options for protected media uploads
 * on the 'media-new.php' admin page
 *
 * @since 0.2
 */
function mgjp_mv_media_new_options_include() {

  include_once( plugin_dir_path( __FILE__ ) . 'mv-options-media-new.php' );

}
add_action( 'load-media-new.php', 'mgjp_mv_media_new_options_include' );


/**
 * Include the options for protected media uploads
 * on the 'upload.php' (Media Library) admin page
 *
 * @since 0.3
 */
function mgjp_mv_media_library_options_include() {

  include_once( plugin_dir_path( __FILE__ ) . 'mv-options-media-library.php' );

}
add_action( 'load-upload.php', 'mgjp_mv_media_library_options_include' );


/**
 * Change upload directory for media uploads to a protected
 * folder if the 'protected' post/get parameter has been set
 * during the upload process.
 *
 * @since 0.1
 *
 * @uses mgjp_mv_upload_dir()
 * @param $param array Array of path info for WP Upload Directory
 * @return array Array of path info for Media Vault protected directory
 */
function mgjp_mv_change_upload_directory( $param ) {

  if ( isset( $_POST['mgjp_mv_protected'] ) && 'on' == $_POST['mgjp_mv_protected'] ) {
    $param['subdir'] = mgjp_mv_upload_dir( $param['subdir'] );
    $param['path']   = $param['basedir'] . $param['subdir'];
    $param['url']    = $param['baseurl'] . $param['subdir'];
  }

  return $param;
}
add_filter( 'upload_dir', 'mgjp_mv_change_upload_directory', 999 );


/**
 * Add plugin related metadata to media uploads
 * if the 'protected' post/get parameter has been set
 * during the upload process.
 *
 * @since 0.1
 *
 * @param $attachment_id int ID of attachment being created
 */
function mgjp_mv_add_custom_media_meta( $attachment_id ) {

  if ( ! is_wp_error( $attachment_id ) && isset( $_POST['mgjp_mv_protected'] ) && 'on' == $_POST['mgjp_mv_protected'] )
    add_post_meta( $attachment_id, 'mgjp_mv_meta', array( 'is_protected' => true ) );

}
add_action( 'add_attachment', 'mgjp_mv_add_custom_media_meta' );


/**
 * Trigger protected media uploads file handling function
 * if 'file' GET parameter is set in URL on wp init
 *
 * @since 0.1
 *
 * @uses mgjp_mv_get_file()
 */
function mgjp_mv_handle_media_access_and_download() {
  if ( isset( $_GET['mgjp_mv_file'] ) && ! empty( $_GET['mgjp_mv_file'] ) ) {

    include( plugin_dir_path( __FILE__ ) . 'mv-file-handler.php' );

    // Check if force download flag is set
    $force_download = isset( $_REQUEST['mgjp_mv_download'] ) ?
                        $_REQUEST['mgjp_mv_download'] :
                        '';

    if ( function_exists( 'mgjp_mv_get_file' ) ) {
      mgjp_mv_get_file( $_GET['mgjp_mv_file'], $force_download );
      exit; // This exit is important as all we want to do when a
            // media download is requested is to serve it and exit
            // If it is missing WP will continue serving the page
            // after the media file, thus breaking it
    }
  }
}
add_action( 'init', 'mgjp_mv_handle_media_access_and_download', 0 );


/**
 * Return attachment file download url
 *
 * @since 0.5
 *
 * @param $attachment_id int ID of attachment whose file download url we want
 * @param $size string optional name-id of size of file if attachment is of type 'image'
 * @return string full filepath to attachment file of specified size with Media Vault force download
 *                query parameter set
 */
function mgjp_mv_get_attachment_download_url( $attachment_id, $size = null ) {

  if ( 'attachment' !== get_post_type( $attachment_id ) )
    return new WP_Error( 'not_attachment', sprintf( __( 'The post type of the post with ID %d, is not %s.', 'mgjp_mediavault' ), $attachment_id, '\'attachment\'' ) );

  $query_arg = array( 'mgjp_mv_download' => 'safeforce' );

  if ( ! wp_attachment_is_image( $attachment_id ) || ! isset( $size ) )
    return add_query_arg( $query_arg, wp_get_attachment_url( $attachment_id ) );

  $image = wp_get_attachment_image_src( $attachment_id, $size );

  return add_query_arg( $query_arg, $image[0] );
}


/**
 * Register Media Vault Shortcodes
 *
 * @since 0.5
 */
function mgjp_mv_register_shortcodes() {

  include_once( plugin_dir_path( __FILE__ ) . 'mv-shortcodes.php' );

  add_shortcode( 'mv_dl_links', 'mgjp_mv_download_links_list_shortcode_handler' );

}
add_action( 'init', 'mgjp_mv_register_shortcodes' );


/**
 * Generate new rewrite rules to reroute requests for
 * media uploads within protected folders and requests 
 * for media uploads with the `safeforce` download flag
 * set, to the file-handling script
 *
 * @since 0.1
 *
 * @uses mgjp_mv_upload_dir()
 * @param $rules string String containing all rewrite rules to be written in htaccess
 * @return string String containing all rewrite rules to be written in htaccess
 *                including Media Vault custom rewrite rules
 */
function mgjp_mv_add_plugin_rewrite_rules( $rules ) {

  $home_root = parse_url( home_url() );
  if ( isset( $home_root['path'] ) )
    $home_root = trailingslashit( $home_root['path'] );
  else
    $home_root = '/';

  $upload             = wp_upload_dir();
  $uploads_path       = str_replace( site_url( '/' ), '', $upload['baseurl'] );
  $old_path_protected = $uploads_path . '(' . mgjp_mv_upload_dir( '/.*\.\w+)$' );
  $old_path_downloads = $uploads_path . '(.*\.\w+)$';
  $new_path           = $home_root . '?mgjp_mv_file=$1';

  $plugin_rules = array(
    '# Media Vault Rewrite Rules #',
    'RewriteRule ^' . $old_path_protected . ' ' . $new_path . ' [QSA,L]',
    'RewriteCond %{QUERY_STRING} ^(?:.*&)?mgjp_mv_download=safeforce(?:&.*)?$',
    'RewriteRule ^' . $old_path_downloads . ' ' . $new_path . ' [QSA,L]',
    '# Media Vault Rewrite Rules End #'
  );

  $pattern = "RewriteRule ^index\.php$ - [L]\n";

  return str_replace( $pattern, $pattern . implode( "\n", $plugin_rules ) . "\n", $rules );
}
add_filter( 'mod_rewrite_rules', 'mgjp_mv_add_plugin_rewrite_rules' );


/**
 * Return plugin default options
 *
 * @since 0.4
 *
 * @uses apply_filters() provides hook to modify default plugin options
 * @return array Array of Media Vault options
 */
function mgjp_mv_default_options() {

  $options = array(
    'default_upload_protection' => 'off' // possible values 'on' && 'off'
  );

  return apply_filters( 'mgjp_mv_default_options', $options );

}


/**
 * On plugin activation
 *
 * @since 0.1
 *
 * @uses mgjp_mv_default_options()
 */
function mgjp_mv_activate() {

  add_option( 'mgjp_mv_default_permission', 'logged-in', '', 'no' );
  add_option( 'mgjp_mv_options', mgjp_mv_default_options(), '', 'no' );

  // Flush rewrite rules for private upload dir protection on plugin activation
  add_filter( 'mod_rewrite_rules', 'mgjp_mv_add_plugin_rewrite_rules' );
  flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mgjp_mv_activate' );


/**
 * On plugin deactivation
 *
 * @since 0.1
 */
function mgjp_mv_deactivate() {

  delete_option( 'mgjp_mv_default_permission' );
  delete_option( 'mgjp_mv_options' );

  delete_post_meta_by_key( 'mgjp_mv_meta' );

  // Flush rewrite rules on plugin deactivation
  remove_filter( 'mod_rewrite_rules', 'mgjp_mv_add_plugin_rewrite_rules' );
  flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mgjp_mv_deactivate' );

?>