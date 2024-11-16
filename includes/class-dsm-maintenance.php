<?php
class DSM_Maintenance {
    private $options;

    public function __construct( $options ) {
        $this->options = $options;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action( 'get_header', array( $this, 'maintenance_init' ) );
    }

    /**
     * Initialize maintenance mode
     */
    public function maintenance_init()
    {
        $is_activated = isset(self::$options['dsmm_activate']) ? self::$options['dsmm_activate'] : '';

        if (! $is_activated) {
            return;
        }

        $page_id   = self::$options['dsmm_page'];
        $page_slug = get_post_field('post_name', $page_id);

        if (! current_user_can('manage_options')) {
            if (! is_page($page_id)) {
                wp_redirect('/' . $page_slug . '/');
                exit;
            }
        }

        if (is_page($page_id)) {
            status_header(503);
            nocache_headers();
        }
    }
}
