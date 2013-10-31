<?php
/**
 * Functions to completely uninstall all settings,
 * options and meta the plugin has populated the
 * database with
 *
 * @package WordPress_Plugins
 * @package MediaVault
 *
 * @author Max G J Panas <http://maxpanas.com/>
 * @license GPL-3.0+
 */

/** Make sure this file is not being called directly **/
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) || ! current_user_can( 'delete_plugins' ) ) {
  header( 'Status: 403 Forbidden' );
  header( 'HTTP/1.1 403 Forbidden' );
  exit();
}


// Unload Media Vault translations
unload_textdomain( 'mgjp_mediavault' );


// Delete the default Media Vault placeholder image
$ir['default'] = get_option( 'mgjp_mv_ir' );
if ( $ir['default'] && wp_attachment_is_image( $ir['default'] ) )
  wp_delete_attachment( $ir['default'], true );


// Delete all Media Vault options from the options table
delete_site_option( 'mgjp_mv_default_permission' );
delete_site_option( 'mgjp_mv_options' );
delete_site_option( 'mgjp_mv_version' );
delete_site_option( 'mgjp_mv_ir' );


// Delete all Media Vault attachment metadata from the postmeta table
delete_post_meta_by_key( '_mgjp_mv_permission' );


// Flush rewrite rules to remove all Media Vault rewrite rules from
// the site's .htaccess file
remove_filter( 'mod_rewrite_rules', 'mgjp_mv_add_plugin_rewrite_rules' );
flush_rewrite_rules();

?>