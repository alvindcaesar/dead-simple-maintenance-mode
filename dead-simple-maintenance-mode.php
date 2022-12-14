<?php
/**
 * Plugin Name:     Dead Simple Maintenance Mode
 * Plugin URI:      https://wpkartel.com
 * Description:     Simple and bloat free maintenance mode for your website
 * Author:          Alvind
 * Author URI:      https://alvindcaesar.com
 * Text Domain:     dead-simple-maintenance-mode
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 */

defined("WPINC") or die;

if (! class_exists("DSM_Mode")) {
  class DSM_Mode
  {
    private static $instance;

    private static $options;

    public static function instance()
    {
      if (! isset(self::$instance) and !(self::$instance instanceof DSM_Mode)) {
        self::$instance = new DSM_Mode();
        self::$instance->hooks();
        self::$instance->define_constants();
      }

      if (! isset(self::$options)) {
        self::$options = get_option("dsmm_options");
      }
    }

    private function hooks()
    {
      add_action( "get_header", array($this, "maintenance_init" ));
      add_action( "admin_menu", array($this, "setting_menu" ));
      add_action( "admin_init", array($this, "setting_options" ));
    }

    private function define_constants()
    {
        define("DSMM_PLUGIN_PATH", plugin_dir_path(__FILE__));
        define("DSMM_PLUGIN_URL", plugin_dir_url(__FILE__));
        define("DSMM_PLUGIN_FILE", plugin_basename(__FILE__));
        define("DSMM_TEXT_DOMAIN", "dead-simple-maintenance-mode");
        define("DSMM_PLUGIN_VERSION", "1.0");
    }

    public function maintenance_init()
    {
      $is_activated = isset(self::$options['dsmm_activate']) ? self::$options['dsmm_activate'] : "";
      
      if ( ! $is_activated ) return;

      $page_id   = self::$options['dsmm_page'];
      $page_slug = get_post_field("post_name", $page_id );
      if(! current_user_can('manage_options')){
        if (! is_page($page_id)) {
          wp_redirect("/$page_slug/");
          die;
        }
      }

      if (is_page($page_id)) {
        status_header( 503 );
        nocache_headers();
      }
    }

    public function setting_menu()
    {
      add_submenu_page(
        'tools.php',
        __("Dead Simple Maintenance Mode", DSMM_TEXT_DOMAIN ),
        __("Dead Simple Maintenance Mode", DSMM_TEXT_DOMAIN ),
        "manage_options",
        "dsmm-settings",
        array($this, "setting_menu_callback"),
      );

    }

    public function setting_menu_callback()
    {
      ?>
      <div class="wrap">
        <h1>Dead Simple Maintenance Mode Settings</h1>
        <form action="options.php" method="POST">
          <?php
            settings_fields( "dsmm_group" );
            do_settings_sections( "dsmm-settings" );
            submit_button('Save Settings');
          ?>
        </form>
      </div>
      <?php
    }

    public function setting_options()
    {
      register_setting( "dsmm_group", "dsmm_options" );

      add_settings_section(
        "dsmm_section",
        null,
        null,
        "dsmm-settings"
      );

      add_settings_field(
        "dsmm_activate",
        "Activate",
        array($this, "activate_callback"),
        "dsmm-settings",
        "dsmm_section"
      );

      add_settings_field(
        "dsmm_page",
        "Select the maintenance mode page",
        array($this, "page_callback"),
        "dsmm-settings",
        "dsmm_section"
      );
    }

    public function activate_callback()
    {
      ?>
      <input type="checkbox" name="dsmm_options[dsmm_activate]" id="dsmm_activate" value="1" <?php isset(self::$options['dsmm_activate']) ? (checked("1", self::$options['dsmm_activate'], true )) : null ?> >
      <label for="dsmm_activate">Activate Maintenance Mode</label>
      <?php
    }

    public function page_callback()
    {
      $args       = array('post_type' => 'page', 'posts_per_page' => -1);
	    $pages      = get_posts( $args );
      $page_id    = wp_list_pluck( $pages , 'ID' );
	    $page_title = wp_list_pluck( $pages , 'post_title' );
	    $ids_titles = array_combine($page_id, $page_title);
      
      ?>
      
      <select name='dsmm_options[dsmm_page]' id='dsmm_page'>
        <option value="">-- Select a maintenance mode page --</option>
        <?php foreach ($ids_titles as $id => $title) {?>
          <option value=<?php echo esc_attr($id) ?><?php isset(self::$options['dsmm_page']) ? selected( $id, self::$options['dsmm_page'], true ) : "";?>>
            <?php echo esc_html($title); ?>
          </option><?php } ?>
      </select> 

      <?php
    }
  }
}

add_action( "plugins_loaded", array( "DSM_Mode", "instance" ) );

function dsmm_deactivate()
{
  delete_option("dsmm_options");
}

register_deactivation_hook( __FILE__, "dsmm_deactivate");

