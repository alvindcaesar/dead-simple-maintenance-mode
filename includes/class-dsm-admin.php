<?php
class DSM_Admin
{
    private $options;

    public function __construct($options)
    {
        $this->options = $options;
        $this->init_hooks();
    }

    private function init_hooks()
    {
        add_action('admin_menu', array($this, 'setting_menu'));
        add_action('admin_init', array($this, 'setting_options'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_indicator'), 100);
    }

    public function setting_menu()
    {
        add_submenu_page(
            'tools.php',
            __('Maintenance Mode', DSMM_TEXT_DOMAIN),
            __('Maintenance Mode', DSMM_TEXT_DOMAIN),
            'manage_options',
            'dsmm-settings',
            array($this, 'setting_menu_callback')
        );
    }

    public function setting_menu_callback()
    {
?>
        <div class="wrap">
            <h1><?php _e('Dead Simple Maintenance Mode Settings', DSMM_TEXT_DOMAIN); ?></h1>
            <?php
            // Show settings saved message
            if ( isset( $_GET['settings-updated'] ) ) {
                add_settings_error(
                    'dsmm_messages',
                    'dsmm_message',
                    __( 'Settings saved.', DSMM_TEXT_DOMAIN ),
                    'updated'
                );
            }
            settings_errors( 'dsmm_messages' );
            ?>
            <form action="options.php" method="POST">
                <?php
                settings_fields('dsmm_group');
                do_settings_sections('dsmm-settings');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }

    public function setting_options()
    {
        register_setting(
            'dsmm_group',
            'dsmm_options',
            array($this, 'sanitize_options')
        );

        add_settings_section(
            'dsmm_section',
            '',
            null,
            'dsmm-settings'
        );

        add_settings_field(
            'dsmm_activate',
            __('Activate', DSMM_TEXT_DOMAIN),
            array($this, 'activate_callback'),
            'dsmm-settings',
            'dsmm_section'
        );

        $is_activated = isset($this->options['dsmm_activate']) && $this->options['dsmm_activate'] == 1;

        add_settings_field(
            'dsmm_page',
            __('Select the maintenance mode page', DSMM_TEXT_DOMAIN),
            array($this, 'page_callback'),
            'dsmm-settings',
            'dsmm_section',
            array('class' => 'page-selector-row' . (! $is_activated ? ' hidden-field' : ''))
        );
    }

    public function activate_callback()
    {
        $is_activated = isset($this->options['dsmm_activate']) && $this->options['dsmm_activate'] == 1;
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
    <?php
    }

    public function page_callback()
    {
        $args = array(
            'post_type'      => 'page',
            'posts_per_page' => -1,
        );
        $pages      = get_posts($args);
        $page_id    = wp_list_pluck($pages, 'ID');
        $page_title = wp_list_pluck($pages, 'post_title');
        $ids_titles = array_combine($page_id, $page_title);
    ?>
        <fieldset>
            <select name='dsmm_options[dsmm_page]' id='dsmm_page'>
                <option value=""><?php esc_html_e('-- Select a maintenance mode page --', DSMM_TEXT_DOMAIN); ?></option>
                <?php foreach ($ids_titles as $id => $title) : ?>
                    <option value="<?php echo esc_attr($id); ?>"
                        <?php selected(isset($this->options['dsmm_page']) ? $this->options['dsmm_page'] : '', $id); ?>>
                        <?php echo esc_html($title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </fieldset>
<?php
    }

    public function sanitize_options($input)
    {
        $sanitized = array();

        // Sanitize the activate checkbox
        $sanitized['dsmm_activate'] = isset($input['dsmm_activate']) ? 1 : 0;

        // Only save the page if maintenance mode is activated
        if (isset($input['dsmm_activate']) && $input['dsmm_activate']) {
            $sanitized['dsmm_page'] = isset($input['dsmm_page']) ? absint($input['dsmm_page']) : '';
        } else {
            $sanitized['dsmm_page'] = '';
        }

        return $sanitized;
    }

    public function enqueue_assets($hook)
    {
        if ('tools_page_dsmm-settings' !== $hook) {
            return;
        }

        wp_enqueue_style('dsmm-admin', DSMM_PLUGIN_URL . 'assets/css/admin.css', array(), DSMM_PLUGIN_VERSION);
        wp_enqueue_script('dsmm-admin', DSMM_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), DSMM_PLUGIN_VERSION, true);
    }

    public function add_admin_bar_indicator( $wp_admin_bar ) {
        if ( ! isset( $this->options['dsmm_activate'] ) || ! $this->options['dsmm_activate'] ) {
            return;
        }

        $wp_admin_bar->add_node(
            array(
                'id'     => 'dsmm-indicator',
                'parent' => 'top-secondary',
                'title'  => sprintf(
                    '<div style="background: #dc3545; height: 100%%; padding: 0 10px; color: #fff; line-height: 28px;">%s</div>',
                    __( 'Maintenance Mode Active', DSMM_TEXT_DOMAIN )
                ),
                'href'   => admin_url( 'tools.php?page=dsmm-settings' ),
                'meta'   => array(
                    'class' => 'dsmm-maintenance-active',
                ),
            )
        );
    }

    public function add_indicator_styles() {
        if ( ! isset( $this->options['dsmm_activate'] ) || ! $this->options['dsmm_activate'] ) {
            return;
        }
        ?>
        <style>
            #wpadminbar .dsmm-maintenance-active .ab-item {
                padding: 0 !important;
            }
            #wpadminbar .dsmm-maintenance-active:hover .ab-item > div {
                background: #c82333 !important;
            }
        </style>
        <?php
    }
}
