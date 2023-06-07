<?php
/**
 * Clase principal
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz/includes
 */

class CZ_Main {

    protected $config;

    protected $enqueue;

    protected $admin_menus;

    protected $post_types;

    protected $admin_columns;

    protected $custom_fields;

    protected $acf_api;

    public function __construct( $config ) {        
        $this->config = (object) [
            "admin_menus" => (isset($config["admin_menus"]) && !empty($config["admin_menus"])) ? $config["admin_menus"] : false,
            "post_types" => (isset($config["post_types"]) && !empty($config["post_types"])) ? $config["post_types"] : false,
            "custom_fields" => (isset($config["custom_fields"]) && !empty($config["custom_fields"])) ? $config["custom_fields"] : false
        ];
    }

    public function init() {
        $this->declare_dependencies();        

        spl_autoload_register([$this, 'autoloader']);

        $this->add_hooks();
    }

    public function get_config() {        
        return $this->config;
    }

    private function declare_dependencies() {
        require_once CZ_PATH . '/includes/cz-helpers.php';
        require_once CZ_PATH . '/includes/cz-enqueue.php';
        require_once CZ_PATH . '/includes/cz-admin-menus.php';
        require_once CZ_PATH . '/includes/cz-post-types.php';
        require_once CZ_PATH . '/includes/cz-acf-api.php';

        // Enqueue
        $this->enqueue = new CZ_Enqueue($this->config);

        // Post Types
        if ($this->config->post_types) {
            $this->post_types = new CZ_Post_Types();
            $this->post_types->add_data($this->config->post_types);
            if (class_exists('ACF') && $this->config->custom_fields) {
                $this->admin_columns = new CZ_Admin_Columns();
                $this->admin_columns->add_data($this->config->post_types, $this->config->custom_fields );
            }  
        }

        // Admin Menus
        if ($this->config->admin_menus) {
            $post_types = ($this->config->post_types) ? $this->post_types : false;
            $this->admin_menus = new CZ_Admin_Menus( $post_types );
            $this->admin_menus->add_data($this->config->admin_menus);
        }

        // ACF Fields
        if( class_exists('ACF') && $this->config->custom_fields ) {
            $this->acf_api = new CZ_ACF_Api();
        }
    }

    public function autoloader($class) {

        $files = glob( CZ_PATH . "/functions/*.php" );
        sort( $files );

        $prev_classes = get_declared_classes();

        foreach ($files as $file) {
            require_once $file;
        }

        $new_classes = array_diff(get_declared_classes(), $prev_classes);

        foreach ($new_classes as $new_class) {
            $instance = new $new_class();
            if (method_exists($instance, 'run'))
                $instance->run();
        }
    }

    private function add_hooks() {
        // Enqueue
        add_action('admin_init', [$this->enqueue, 'register_admin']);

        // Admin Menus
        add_action('admin_menu', [$this->admin_menus, 'register_menus']);
        if( class_exists('ACF') ) {
            add_action('acf/init', [$this->admin_menus, 'register_options']);
        }            
        add_action('parent_file', [$this->admin_menus, 'taxonomy_menu']);

        // Post Types        
        $this->post_types->add_hooks();        

        // Admin Columns
        if ($this->admin_columns)
            $this->admin_columns->add_hooks();

        //Custom Fields
        if( class_exists('ACF') && $this->config->custom_fields ) {
            $this->acf_api->set_groups($this->config->custom_fields);
            add_action('acf/init', [$this->acf_api, 'register_fields']);
        }
    }
}