<?php
/**
 * Media Vault Settings Functions.
 *
 * @package WordPress_Plugin
 * @package MediaVault
 *
 * @author Max G J Panas <http://maxpanas.com/>
 * @license GPL-3.0+
 */

/** Register plugin settings **/
register_setting( 'media', 'mgjp_mv_default_permission' );
register_setting( 'media', 'mgjp_mv_options' );

add_settings_section(
  'mgjp_mv_general_settings',
  __( 'Media Vault', 'mgjp_mediavault' ),
  'mgjp_mv_render_general_settings_info_txt',
  'media'
);

add_settings_field(
  'default_permission',
  __( 'Default Protected File Permissions', 'mgjp_mediavault' ),
  'mgjp_mv_render_default_permission_field',
  'media',
  'mgjp_mv_general_settings',
  array( 'label_for' => 'mgjp_mv_default_permission' )
);

add_settings_field(
  'default_upload_protection',
  __( 'Default Upload Protection', 'mgjp_mediavault' ),
  'mgjp_mv_render_default_upload_protection_field',
  'media',
  'mgjp_mv_general_settings',
  array( 'label_for' => 'mgjp_mv_default_upload_protection' )
);

/* To be implemented.. possibly
add_settings_field(
  'can_toggle_protection',
  __( 'User Roles with Capability to change Media File Protection', 'mgjp_mediavault' ),
  'mgjp_mv_render_can_change_protection_field',
  'media',
  'mgjp_mv_general_settings'
);//*/


/**
 * Render the General Settings info txt
 *
 * @since 0.4
 */
function mgjp_mv_render_general_settings_info_txt() {

  echo '<p>';
    esc_html_e( 'Media Vault is a plugin that allows you to protect media files in your uploads folder.', 'mgjp_mediavault' );
    echo ' ';
    esc_html_e( 'Here you can set options for:', 'mgjp_mediavault' );
  echo '</p>';

}


/**
 * Render the default file access permission field
 *
 * @since 0.4
 *
 * @param array Array of arguments passed the specific settings field
 */
function mgjp_mv_render_default_permission_field( $args ) {

  $default_permission = get_option( 'mgjp_mv_default_permission', 'logged-in' );

  ?>

  <select id="mgjp_mv_default_permission" name="mgjp_mv_default_permission">

    <?php foreach( mgjp_mv_get_the_permissions() as $permission => $data ) : ?>

      <option value="<?php echo esc_attr( $permission ); ?>" <?php selected( $default_permission, $permission ); ?>>
        <?php echo esc_html( $data['select'] ); ?>
      </option>

    <?php endforeach; ?>

  </select>
  <span class="description">
    <?php _e( 'Select the default permissions required for accessing protected media uploads.', 'mgjp_mediavault' ); ?>
  </span>

  <?php
}


/**
 * Render the default file access protection toggle field
 *
 * @since 0.4
 *
 * @param array Array of arguments passed the specific settings field
 */
function mgjp_mv_render_default_upload_protection_field( $args ) {

  $options = (array) get_option( 'mgjp_mv_options' );

  ?>

  <label for="mgjp_mv_default_upload_protection">

    <input type="checkbox" id="mgjp_mv_default_upload_protection" name="mgjp_mv_options[default_upload_protection]" <?php if ( isset( $options['default_upload_protection'] ) ) checked( $options['default_upload_protection'], 'on' ); ?>>

    <span class="description">
      <?php _e( 'Set media file upload protection to be enabled by default.', 'mgjp_mediavault' ); ?>
    </span>

  </label>

  <?php
}


/**
 * Render the capability seter field
 * UNIMPLEMENTED in trunk
 *
 * @since 0.4
 *
 * @param array Array of arguments passed the specific settings field
 */
function mgjp_mv_render_can_change_protection_field() { ?>

  <ul>
    <?php foreach ( (array) get_editable_roles() as $role ) :

      if ( isset( $role['capabilities']['manage_options'] ) && $role['capabilities']['manage_options'] )
        continue;

      $can_toggle_protection = isset( $role['capabilities']['edit_posts'] ) ? $role['capabilities']['edit_posts'] : false ; 

      $id = 'mgjp_mv_can_change_protection_' . esc_attr( $role['name'] ); ?>

      <li>
        <label for="<?php echo $id; ?>">
          <input id="<?php echo $id; ?>" type="checkbox" <?php checked( $can_toggle_protection ); ?>>
          <?php echo esc_html( $role['name'] ); ?>
        </label>
      </li>

    <?php endforeach; ?>
  </ul>

  <span class="description">
    <?php esc_html_e( 'Super Admin and Admin users can always change a media file\'s protection.', 'mgjp_mediavault' ); ?>
  </span>

  <?php
}

?>