<?php
class DSM_Mode {
    private static $instance;
    private static $options;

    public static function instance()
    {
        if (! isset(self::$instance) && ! (self::$instance instanceof DSM_Mode)) {
            self::$instance = new DSM_Mode();
            self::$instance->includes();

            // Load options
            self::$options = get_option('dsmm_options', array());

            self::$instance->init();
        }
        return self::$instance;
    }

    private function includes() {
        require_once DSMM_PLUGIN_PATH . 'includes/class-dsm-admin.php';
        require_once DSMM_PLUGIN_PATH . 'includes/class-dsm-maintenance.php';
    }

    private function init() {
        // Initialize admin
        if ( is_admin() ) {
            new DSM_Admin( self::$options );
        }

        // Initialize maintenance
        new DSM_Maintenance( self::$options );
    }
}
