<?php
/**
 * Plugin Name:     Dead Simple Maintenance Mode
 * Plugin URI:      https://codepathics.com
 * Description:     Simple and bloat free maintenance mode for your website
 * Author:          Codepathics
 * Author URI:      https://codepathics.com
 * Text Domain:     dead-simple-maintenance-mode
 * Domain Path:     /languages
 * Version:         1.1
 *
 */

defined( 'WPINC' ) || die;

if ( ! class_exists( 'DSM_Mode' ) ) {
    class DSM_Mode {
        private static $instance;
        private static $options;

        /**
         * Initialize the singleton instance.
         *
         * @return DSM_Mode
         */
        public static function instance() {
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof DSM_Mode ) ) {
                self::$instance = new DSM_Mode();
                self::$instance->hooks();
                self::$instance->define_constants();
            }

            if ( ! isset( self::$options ) ) {
                self::$options = get_option( 'dsmm_options' );
            }

            return self::$instance;
        }

        /**
         * Register hooks and filters
         */
        private function hooks() {
            add_action( 'get_header', array( $this, 'maintenance_init' ) );
            add_action( 'admin_menu', array( $this, 'setting_menu' ) );
            add_action( 'admin_init', array( $this, 'setting_options' ) );
        }

        private function define_constants()
        {
            define("DSMM_PLUGIN_PATH", plugin_dir_path(__FILE__));
            define("DSMM_PLUGIN_URL", plugin_dir_url(__FILE__));
            define("DSMM_PLUGIN_FILE", plugin_basename(__FILE__));
            define("DSMM_TEXT_DOMAIN", "dead-simple-maintenance-mode");
            define("DSMM_PLUGIN_VERSION", "1.1");
        }

        /**
         * Initialize maintenance mode
         */
        public function maintenance_init() {
            $is_activated = isset( self::$options['dsmm_activate'] ) ? self::$options['dsmm_activate'] : '';
            
            if ( ! $is_activated ) {
                return;
            }

            $page_id   = self::$options['dsmm_page'];
            $page_slug = get_post_field( 'post_name', $page_id );
            
            if ( ! current_user_can( 'manage_options' ) ) {
                if ( ! is_page( $page_id ) ) {
                    wp_redirect( '/' . $page_slug . '/' );
                    exit;
                }
            }

            if ( is_page( $page_id ) ) {
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
            <script>
            jQuery(document).ready(function($) {
                // Get the field container
                var pageField = $('#dsmm_page').closest('tr');
                
                // Initial state
                if (!$('#dsmm_activate').is(':checked')) {
                    pageField.hide();
                }
                
                // Toggle on checkbox change
                $('#dsmm_activate').on('change', function() {
                    if ($(this).is(':checked')) {
                        pageField.show();
                    } else {
                        pageField.hide();
                    }
                });
            });
            </script>
            <?php
        }

        public function setting_options()
        {
            register_setting( "dsmm_group", "dsmm_options", array( $this, 'sanitize_options' ) );

            add_settings_section(
                "dsmm_section",
                null,
                null,
                "dsmm-settings"
            );

            // Add the activate field first
            add_settings_field(
                "dsmm_activate",
                __("Activate", DSMM_TEXT_DOMAIN),
                array($this, "activate_callback"),
                "dsmm-settings",
                "dsmm_section"
            );

            // Check if maintenance mode is activated
            $is_activated = isset(self::$options['dsmm_activate']) && self::$options['dsmm_activate'] == 1;

            // Add page selector with correct initial state
            add_settings_field(
                "dsmm_page",
                __("Select the maintenance mode page", DSMM_TEXT_DOMAIN),
                array($this, "page_callback"),
                "dsmm-settings",
                "dsmm_section",
                array('class' => 'page-selector-row' . (!$is_activated ? ' hidden-field' : ''))
            );
        }

        public function activate_callback() {
            $is_activated = isset(self::$options['dsmm_activate']) && self::$options['dsmm_activate'] == 1;
            ?>
            <fieldset>
                <label for="dsmm_activate">
                    <input type="checkbox" 
                           name="dsmm_options[dsmm_activate]" 
                           id="dsmm_activate" 
                           value="1" 
                           <?php checked($is_activated); ?>>
                    <?php _e('Activate Maintenance Mode', DSMM_TEXT_DOMAIN); ?>
                </label>
            </fieldset>
            <script>
            jQuery(document).ready(function($) {
                var pageField = $('.page-selector-row');
                
                // Set initial state
                pageField.toggleClass('hidden-field', !$('#dsmm_activate').is(':checked'));
                
                // Handle changes
                $('#dsmm_activate').on('change', function() {
                    pageField.toggleClass('hidden-field', !$(this).is(':checked'));
                });
            });
            </script>
            <?php
        }

        public function page_callback() {
            $args = array(
                'post_type'      => 'page',
                'posts_per_page' => -1,
            );
            $pages      = get_posts( $args );
            $page_id    = wp_list_pluck( $pages, 'ID' );
            $page_title = wp_list_pluck( $pages, 'post_title' );
            $ids_titles = array_combine( $page_id, $page_title );
            ?>
            <select name='dsmm_options[dsmm_page]' id='dsmm_page'>
                <option value=""><?php esc_html_e( '-- Select a maintenance mode page --', 'dead-simple-maintenance-mode' ); ?></option>
                <?php foreach ( $ids_titles as $id => $title ) : ?>
                    <option value="<?php echo esc_attr( $id ); ?>"<?php isset( self::$options['dsmm_page'] ) ? selected( $id, self::$options['dsmm_page'], true ) : ''; ?>>
                        <?php echo esc_html( $title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php
        }

        /**
         * Sanitize the options before saving
         *
         * @param array $input The input array to sanitize.
         * @return array
         */
        public function sanitize_options( $input ) {
            $sanitized = array();
            
            // Sanitize the activate checkbox
            $sanitized['dsmm_activate'] = isset( $input['dsmm_activate'] ) ? 1 : 0;
            
            // Only save the page if maintenance mode is activated
            if ( isset( $input['dsmm_activate'] ) && $input['dsmm_activate'] ) {
                $sanitized['dsmm_page'] = isset( $input['dsmm_page'] ) ? absint( $input['dsmm_page'] ) : '';
            } else {
                $sanitized['dsmm_page'] = '';
            }
            
            return $sanitized;
        }
    }
}

add_action( "plugins_loaded", array( "DSM_Mode", "instance" ) );

function dsmm_deactivate()
{
    delete_option("dsmm_options");
}

register_deactivation_hook( __FILE__, "dsmm_deactivate");

