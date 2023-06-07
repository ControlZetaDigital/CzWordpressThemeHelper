<?php
/**
 * Clase para configurar los menus del admin
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz/includes
 */

class CZ_Admin_Menus {

    protected $admin_menus;

    protected $post_types;

    public function __construct( $post_types ) {
        $this->admin_menus = [];
        $this->post_types = $post_types;
    }

    public function add_data( $admin_menus ) {
        $this->admin_menus = $admin_menus;
    }

    public function render_dashboard() {

    }

    public function is_options_menu($admin_menu) {
        $is_option_menu = true;
        foreach($admin_menu["items"] as $item) {
            if ($item['type'] === 'post_type' || $item['type'] === 'taxonomy') {
                $is_option_menu = false;
                break;
            }
        }
        return $is_option_menu;
    }

    public function register_menus() { //Action Hook: 'admin_menu'
        if (empty($this->admin_menus))
            return false;

        foreach($this->admin_menus as $admin_menu) {
            if (!$this->is_options_menu($admin_menu)) {
                $this->register_menu($admin_menu);
            }            
        }
    }

    public function register_options() { //Action Hook: 'acf/init'
        if (empty($this->admin_menus))
            return false;

        foreach($this->admin_menus as $admin_menu) {
            if ($this->is_options_menu($admin_menu)) {
                $this->register_option_menu($admin_menu);
            }            
        }
    }

    public function taxonomy_menu( $parent_file ) { //Action Hook: 'parent_file'
        if (empty($this->admin_menus))
            return false;
            
        global $current_screen;

        if (!property_exists($current_screen, 'taxonomy'))
            return $parent_file;

        $current_taxonomy = $current_screen->taxonomy;

        foreach($this->admin_menus as $admin_menu) {
            foreach($admin_menu['items'] as $item) {
                if($item['type'] === 'taxonomy') {
                    if ($current_screen->taxonomy === "cz_" . $item["key"]) {
                        $parent_file = "cz_" . $admin_menu['key'];
                        break 2;
                    }
                }
            }
        }
      
        return $parent_file;
    }

    private function register_menu( $admin_menu ) {

        $page_name = $admin_menu['name'];
        $menu_name = $admin_menu['name'];
        $capability = 'read';
        $menu_slug  = "cz_" . $admin_menu['key'];
        $function   = (!isset($admin_menu['dashboard']) || $admin_menu["dashboard"] === false) ? null : (($admin_menu["dashboard"] === true) ? [$this, "render_dashboard"] : "cz_".$admin_menu["dashboard"]);
        $icon_url   = (isset($admin_menu['icon'])) ? $admin_menu['icon'] : CZ_URL . '/assets/img/cz-icon_16x16.png';
        $position   = (isset($admin_menu['priority'])) ? $admin_menu["priority"] : 5;

        add_menu_page( $page_name, $menu_name, $capability, $menu_slug, $function, $icon_url, $position );

        $submenu_pages = [];
        $submenu_options = [];

        foreach($admin_menu["items"] as $item) {
            if ($item["type"] !== 'options') {
                $title = $slug = false;
                if ($item["type"] === 'custom') {
                    $title = $item['name'];
                    $slug = "cz_" . $item['key'];
                } else {
                    if ($this->post_types) {
                        $title = $this->post_types->get_name($item['key'], $item['type']);
                        $slug = ($item['type'] === 'post_type') ? "edit.php?post_type=cz_".$item['key'] : "edit-tags.php?taxonomy=cz_".$item['key']."&post_type=cz_".$item['parent_key'];
                    }                    
                }
                if (isset($item['function'])) {
                    $classname = new $item['function']['classname'];
                    $function = [$classname, $item['function']['method']];
                } else {
                    $function = null;
                }
                if ($title && $slug) {
                    $submenu_pages[] = [
                        'parent_slug' => $menu_slug,
                        'page_title'  => $title,
                        'menu_title'  => $title,
                        'capability'  => 'read',
                        'menu_slug'   => $slug,
                        'function'    => $function
                    ];
                }
            } else {
                $submenu_options[] = [
                    'parent_slug' => $menu_slug,
                    'page_title'  => $item['name'],
                    'menu_title'  => $item['name'],
                    'menu_slug'   => "cz_" . $item['key'],
                ];
            }
        }

        foreach ( $submenu_pages as $submenu ) {
            add_submenu_page(
                $submenu['parent_slug'],
                $submenu['page_title'],
                $submenu['menu_title'],
                $submenu['capability'],
                $submenu['menu_slug'],
                $submenu['function']
            );
        }

        if ($admin_menu["dashboard"] === false)
            remove_submenu_page($menu_slug, $menu_slug);

        if (!empty($submenu_options) && function_exists('acf_add_options_sub_page')) {
            foreach($submenu_options as $submenu) {
                acf_add_options_sub_page([
                    'parent_slug' => $submenu["parent_slug"],
                    'page_title'  => $submenu["page_title"],
                    'menu_title'  => $submenu["menu_title"],
                    'menu_slug'   => $submenu["menu_slug"]
                ]);
            }
        }        
    }

    private function register_option_menu( $admin_menu ) {
        if( !function_exists('acf_add_options_sub_page') )
            return;

        $menu_slug  = "cz_" . $admin_menu['key'];
        $icon_url   = (isset($admin_menu['icon'])) ? $admin_menu['icon'] : CZ_URL . '/assets/img/cz-icon_16x16.png';
        $position   = (isset($admin_menu['priority'])) ? $admin_menu["priority"] : 5;

        $parent = acf_add_options_page([
            'page_title'  => $admin_menu['name'],
            'menu_title'  => $admin_menu['name'],
            'menu_slug' => $menu_slug,
            'position' => $position,
            'icon_url' => $icon_url,
            'redirect'    => true
        ]);

        foreach($admin_menu["items"] as $item) {
            acf_add_options_sub_page([
                'parent_slug' => $parent["menu_slug"],
                'page_title'  => $item["name"],
                'menu_title'  => $item["name"],
                'menu_slug'   => "cz_" . $item["key"]
            ]);
        }
    }
}