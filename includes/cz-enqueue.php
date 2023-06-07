<?php
/**
 * Clase para incluir archivos CSS y JS
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz/includes
 */

class CZ_Enqueue {
    protected $config;

    public function __construct( $config ) {
        $this->config = $config;
    }

    public function register_admin() { //Action Hook: 'admin_init'
        if (is_admin()) {

            wp_register_style( CZ . 'global_admin', CZ_URL . '/assets/css/styles.css', '2.0.0');
            wp_register_script( CZ . 'admin_menus', CZ_URL . '/assets/js/admin_menus.js', array( 'jquery' ), '2.0.0', true);            
            
            if ($this->config->admin_menus) {
                $menus = [];
                foreach($this->config->admin_menus as $admin_menu) {
                    $taxonomies = false;
                    foreach($admin_menu["items"] as $item) {
                        if ($item['type'] === 'taxonomy') {
                            if (!$taxonomies)
                                $taxonomies = [];
    
                            $taxonomies[] = [
                                "slug" => "cz_" . $item['key'],
                                "post_type" => "cz_" . $item['parent_key']
                            ];
                        }
                    }
                    if ($taxonomies) {
                        $menus[] = [
                            "slug" => "cz_" . $admin_menu["key"],
                            "taxonomies" => $taxonomies
                        ];
                    }                
                }
                // 'ajaxUrl' => admin_url( 'admin-ajax.php' ) 
                wp_localize_script( CZ . 'admin_menus', 'cz', [
                    "adminMenus" => $menus
                ] );
            }

            wp_enqueue_style( [ CZ . 'global_admin' ] );
            wp_enqueue_script( [ CZ . 'admin_menus' ] );
        }
    }
}