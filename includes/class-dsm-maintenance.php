<?php
class DSM_Maintenance {
    private $options;

    public function __construct( $options ) {
        $this->options = $options;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action( 'template_redirect', array( $this, 'maintenance_init' ) );
    }

    public function maintenance_init() {
        // Check if maintenance mode is activated
        $is_activated = isset( $this->options['dsmm_activate'] ) && $this->options['dsmm_activate'] == 1;
        
        if ( ! $is_activated ) {
            return;
        }

        // Get maintenance page ID
        $page_id = isset( $this->options['dsmm_page'] ) ? $this->options['dsmm_page'] : '';
        
        if ( empty( $page_id ) ) {
            return;
        }

        // Allow admin users to view the site
        if ( current_user_can( 'manage_options' ) ) {
            return;
        }

        // Get current page ID
        $current_page_id = get_queried_object_id();

        // If we're not on the maintenance page, redirect to it
        if ( $current_page_id != $page_id ) {
            // Set maintenance mode headers
            status_header( 503 );
            header( 'Retry-After: 600' );
            
            // Redirect to maintenance page
            wp_redirect( get_permalink( $page_id ) );
            exit;
        }
    }
}
