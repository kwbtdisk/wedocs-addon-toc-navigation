<?php
/* wedocs_toc_navgation Settings Page */
class WeDocs_Toc_Navgation_Settings_Page {
  public function __construct() {
    add_action( 'admin_menu', array( $this, 'wph_create_settings' ) );
//    add_action( 'admin_init', array( $this, 'wph_setup_sections' ) );
//    add_action( 'admin_init', array( $this, 'wph_setup_fields' ) );
  }
  public function wph_create_settings() {
//    $page_title = 'Navigation Settings';
//    $menu_title = 'Navigation Settings';
    $capability = 'publish_posts';
//    $slug = 'wedocs_toc_navgation_settings';
//    $callback = array($this, 'wph_settings_content');
    add_submenu_page( 'wedocs', 'Docs List', 'Docs List', $capability, 'edit.php?post_type=docs' );
//    add_submenu_page( 'wedocs', $page_title, $menu_title, $capability, $slug, $callback );
  }
  public function wph_settings_content() { ?>
    <div class="wrap">
      <h1>Docs Navigation Settings</h1>
      <?php settings_errors(); ?>
      <form method="POST" action="options.php">
        <?php
        settings_fields( 'wedocstocnavgation' );
        do_settings_sections( 'wedocstocnavgation' );
        submit_button();
        ?>
      </form>
    </div> <?php
  }
  public function wph_setup_sections() {
    add_settings_section( 'wedocstocnavgation_section', '', array(), 'wedocstocnavgation' );
  }
  public function wph_setup_fields() {
    $fields = array(
      array(
        'label' => 'Submenu Toggle',
        'id' => 'wedocs_toc_navgation_enable_submenu_toggle',
        'type' => 'select',
        'section' => 'wedocstocnavgation_section',
        'options' => array(
          false => 'Show All Children Menu (Default)',
          true => 'Enable Toggle Menu',
        ),
        'desc' => '',
        'default_value' => array( false )
      ),
      array(
        'label' => 'Heading Levels',
        'id' => 'wedocs_toc_navgation_heading_levels',
        'type' => 'checkbox',
        'section' => 'wedocstocnavgation_section',
        'options' => array(
          '1' => 'H1',
          '2' => 'H2',
          '3' => 'H3',
          '4' => 'H4',
          '5' => 'H5',
          '6' => 'H6',
        ),
        'default_value' => array( 1, 2, 3 ),
        'desc' => 'Include the following heading levels. Deselecting a heading will exclude it.',
      ),
    );
    foreach( $fields as $field ){
      if(get_option($field['id'], 'undefined') === 'undefined') {
        update_option($field['id'], $field['default_value']);
      }
      add_settings_field( $field['id'], $field['label'], array( $this, 'wph_field_callback' ), 'wedocstocnavgation', $field['section'], $field );
      register_setting( 'wedocstocnavgation', $field['id'] );
    }
  }
  public function wph_field_callback( $field ) {
    $value = get_option( $field['id'] );
    switch ( $field['type'] ) {
      case 'radio':
      case 'checkbox':
        if( ! empty ( $field['options'] ) && is_array( $field['options'] ) ) {
          $options_markup = '';
          $iterator = 0;
          foreach( $field['options'] as $key => $label ) {
            $iterator++;
            $options_markup.= sprintf('<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>',
              $field['id'],
              $field['type'],
              $key,
              checked($value[array_search($key, $value)], $key, false),
              $label,
              $iterator
            );
          }
          printf( '<fieldset>%s</fieldset>',
            $options_markup
          );
        }
        break;
      case 'select':
      case 'multiselect':
        if( ! empty ( $field['options'] ) && is_array( $field['options'] ) ) {
          $attr = '';
          $options = '';
          foreach( $field['options'] as $key => $label ) {
            $options.= sprintf('<option value="%s" %s>%s</option>',
              $key,
              selected($value[array_search($key, $value, true)], $key, false),
              $label
            );
          }
          if( $field['type'] === 'multiselect' ){
            $attr = ' multiple="multiple" ';
          }
          printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>',
            $field['id'],
            $attr,
            $options
          );
        }
        break;
      default:
        printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
          $field['id'],
          $field['type'],
          $field['placeholder'],
          $value
        );
    }
    if( $desc = $field['desc'] ) {
      printf( '<p class="description">%s </p>', $desc );
    }
  }
}
new WeDocs_Toc_Navgation_Settings_Page();