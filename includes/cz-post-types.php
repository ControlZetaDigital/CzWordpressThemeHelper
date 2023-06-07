<?php
/**
 * Clase para configurar post types y taxonomías
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz/includes
 */

class CZ_Post_Types {

    protected $post_types;

    private $taxonomies_to_hide;

    public function __construct() {
        $this->post_types = [];
        $this->taxonomies_to_hide = [];
    }

    public function add_data( $post_types ) {
        $this->post_types = $post_types;        
    }

    public function add_hooks() {
        add_action('init', [$this, 'register_all']);
        if ($this->has_to_rewrite_permalinks()) {
            add_filter('post_type_link', [$this, 'rewrite_permalinks'], 10, 2);
        }
        $this->hide_taxonomies();
    }

    public function get_name($key, $type) {
        $match = false;
        foreach($this->post_types as $post_type) {
            if($type === 'post_type') {
                if ($post_type['key'] === $key) {
                    $match = $post_type['labels']['plural'];
                    break;
                }
            } else {
                if (isset($post_type['taxonomies'])) {
                    foreach($post_type['taxonomies'] as $taxonomy) {
                        if ($taxonomy['key'] === $key) {
                            $match = $taxonomy['labels']['plural'];
                            break 2;
                        }
                    }
                }
            }
        }
        return $match;
    }

    public function get_default_taxonomy($taxonomies) {
        $default = false;
        foreach($taxonomies as $taxonomy) {
            if (isset($taxonomy['default']) && $taxonomy['default'] === true) {
                $default = $taxonomy['key'];
                break;
            }
        }
        if (!$default)
            $default = $taxonomies[0]["key"];

        return $default;
    }

    public function has_to_rewrite_permalinks() {
        $to_rewrite = false;
        $default_taxonomy = false;
        foreach($this->post_types as $post_type) {
            if (isset($post_type["taxonomies"])) {
                $to_rewrite = true;
            }
        }

        return $to_rewrite;
    }

    public function hide_taxonomies() {
        $default_taxonomy = false;
        foreach($this->post_types as $post_type) {
            if (isset($post_type["taxonomies"])) {
                foreach($post_type["taxonomies"] as $taxonomy) {
                    if (isset($taxonomy['hide_in_edit']) && $taxonomy['hide_in_edit'] === true) {
                        $this->taxonomies_to_hide[] = (object) [ "post_type" => "cz_" . $post_type["key"], "key" => "cz_" . $taxonomy['key'] ];
                    }
                }
            }
        }

        if (!empty($this->taxonomies_to_hide)) {
            add_action( "do_meta_boxes", function () {
                foreach($this->taxonomies_to_hide as $taxonomy) {
                    remove_meta_box("tagsdiv-{$taxonomy->key}", $taxonomy->post_type, 'side');
                    remove_meta_box('categorydiv', $taxonomy->post_type, 'side');
                }
            });
        }
    }

    public function rewrite_permalinks( $permalink, $post ) { //Filter Hook: 'post_type_link'
        foreach($this->post_types as $post_type) {
            if (isset($post_type["taxonomies"])) {
                $default_taxonomy = $this->get_default_taxonomy($post_type["taxonomies"]);
                if ( false !== strpos( $permalink, "%cz_{$default_taxonomy}%" ) ) {
                    $terms = get_the_terms( $post, "cz_{$default_taxonomy}" );
                    if ( ! empty( $terms ) ) {
                        $term_slug = $terms[0]->slug;
                        $permalink = str_replace( "%cz_{$default_taxonomy}%", $term_slug, $permalink );
                    } else {
                        $permalink = str_replace( "%cz_{$default_taxonomy}%/", '', $permalink );
                    }
                }
            }
        }
        return $permalink;
    }

    public function register_all() { //Action Hook: 'init'
        if (empty($this->post_types))
            return false;

        foreach($this->post_types as $post_type) {
            $this->register_post_type($post_type);
        }
    }

    private function register_post_type($post_type) {
        if (empty($post_type["labels"]["singular"]) || empty($post_type["labels"]["plural"]))
            return false;

        $labels_default = (object) [
            "singular" => (object) [
                "cap" => $post_type["labels"]["singular"],
                "low" => mb_strtolower($post_type["labels"]["singular"], 'UTF-8')
            ],
            "plural" => (object) [
                "cap" => $post_type["labels"]["plural"],
                "low" => mb_strtolower($post_type["labels"]["plural"], 'UTF-8')
            ],
            "gender" => (isset($post_type["labels"]["gender"])) ? $post_type["labels"]["gender"] : "o"
        ];

        $labels = [
            'name' => $labels_default->plural->cap,
            'singular_name' => $labels_default->singular->cap,
            'menu_name' => $labels_default->plural->cap,
            'name_admin_bar' => $labels_default->plural->cap,
            'add_new' => __( "Añadir nuev{$labels_default->gender}", '__cz__' ),
            'add_new_item' => sprintf(__( "Añadir Nuev{$labels_default->gender} %s", '__cz__' ), $labels_default->singular->cap),
            'edit_item' => sprintf(__( "Editar %s", '__cz__' ), $labels_default->singular->cap),
            'new_item' => sprintf(__( "Nuev{$labels_default->gender} %s", '__cz__' ), $labels_default->singular->cap),
            'view_item' => sprintf(__( "Ver %s", '__cz__' ), $labels_default->singular->cap),
            'search_item' => sprintf(__( "Buscar %s", '__cz__' ), $labels_default->plural->cap),
            'not_found' => sprintf(__( 'No se encontraron %s', '__cz__' ), $labels_default->plural->low),
            'not_found_in_trash' => sprintf(__( 'No se encontraron %s en la papelera', '__cz__' ), $labels_default->plural->low),
            'all_items' => sprintf(__( "Tod{$labels_default->gender}s l{$labels_default->gender}s %s", '__cz__' ), $labels_default->plural->low)
        ];

        $default_taxonomy = false;
        if (isset($post_type["taxonomies"]))
            $default_taxonomy = $this->get_default_taxonomy($post_type["taxonomies"]);

        register_post_type( "cz_" . $post_type["key"], [
            'labels' => $labels,
            'public' => (isset($post_type["public"])) ? $post_type["public"] : true,
            'has_archive' => (isset($post_type["has_archive"])) ? $post_type["has_archive"] : false,
            'show_in_rest' => (isset($post_type["show_in_rest"])) ? $post_type["show_in_rest"] : true,
            'show_ui' => (isset($post_type["show_ui"])) ? $post_type["show_ui"] : true,
            'show_in_menu' => (isset($post_type["show_in_menu"])) ? $post_type["show_in_menu"] : true,
            'supports' => (isset($post_type["supports"])) ? $post_type["supports"] : [ 'title', 'editor', 'thumbnail' ],
            'capability_type' => (isset($post_type["capability_type"])) ? $post_type["capability_type"] : 'post',
            'rewrite' => [
                'slug' => (isset($post_type["rewrite"]["slug"])) ? $post_type["rewrite"]["slug"] : (($default_taxonomy) ? $post_type["key"] . "/%cz_{$default_taxonomy}%" : $post_type["key"]),
                'with_front' => (isset($post_type["rewrite"]["with_front"])) ? $post_type["rewrite"]["with_front"] : false
            ]
        ] );

        if (isset($post_type["taxonomies"])) {
            foreach($post_type["taxonomies"] as $taxonomy) {
                $this->register_taxonomy($taxonomy, $post_type["key"]);
            }
        }
    }

    private function register_taxonomy($taxonomy, $post_type_key) {
        if (empty($taxonomy["labels"]["singular"]) || empty($taxonomy["labels"]["plural"]))
            return false;

        $labels_default = (object) [
            "singular" => (object) [
                "cap" => $taxonomy["labels"]["singular"],
                "low" => mb_strtolower($taxonomy["labels"]["singular"], 'UTF-8')
            ],
            "plural" => (object) [
                "cap" => $taxonomy["labels"]["plural"],
                "low" => mb_strtolower($taxonomy["labels"]["plural"], 'UTF-8')
            ],
            "gender" => (isset($taxonomy["labels"]["gender"])) ? $taxonomy["labels"]["gender"] : "o"
        ];

        $labels = [
            'name' => $labels_default->plural->cap,
            'singular_name' => $labels_default->singular->cap,
            'menu_name' => $labels_default->plural->cap,
            'name_admin_bar' => $labels_default->plural->cap,
            'add_new' => __( "Añadir nuev{$labels_default->gender}", '__cz__' ),
            'add_new_item' => sprintf(__( "Añadir Nuev{$labels_default->gender} %s", '__cz__' ), $labels_default->singular->cap),
            'edit_item' => sprintf(__( "Editar %s", '__cz__' ), $labels_default->singular->cap),
            'new_item' => sprintf(__( "Nuev{$labels_default->gender} %s", '__cz__' ), $labels_default->singular->cap),
            'view_item' => sprintf(__( "Ver %s", '__cz__' ), $labels_default->singular->cap),
            'search_item' => sprintf(__( "Buscar %s", '__cz__' ), $labels_default->plural->cap),
            'not_found' => sprintf(__( 'No se encontraron %s', '__cz__' ), $labels_default->plural->low),
            'all_items' => sprintf(__( "Tod{$labels_default->gender}s l{$labels_default->gender}s %s", '__cz__' ), $labels_default->plural->low)
        ];

        register_taxonomy( "cz_" . $taxonomy['key'], "cz_{$post_type_key}", [
            'labels' => $labels,
            'hierarchical' => (isset($taxonomy["hierarchical"])) ? $taxonomy["hierarchical"] : false,
            'show_ui' => (isset($taxonomy["show_ui"])) ? $taxonomy["show_ui"] : true,
            'show_in_rest' => (isset($taxonomy["show_in_rest"])) ? $taxonomy["show_in_rest"] : true,
            'show_in_menu' => (isset($taxonomy["show_in_menu"])) ? $taxonomy["show_in_menu"] : true,
            'rewrite' => [ 
                'slug' => (isset($taxonomy["rewrite"]["slug"])) ? $taxonomy["rewrite"]["slug"] : $taxonomy["key"], 
                'with_front' => (isset($taxonomy["rewrite"]["with_front"])) ? $taxonomy["rewrite"]["with_front"] : false 
            ]
        ] );
    }
}

class CZ_Admin_Columns {

    protected $post_types;

    protected $custom_fields;

    protected $custom_columns;

    private $current_columns;

    public function __construct() {
        $this->post_types = [];
        $this->custom_fields = [];
        $this->custom_columns = [];
    }

    public function add_data( $post_types, $custom_fields = false ) {
        $this->post_types = $post_types;
        $this->custom_fields = $custom_fields;
        $this->get_column_fields();
    }

    public function add_hooks() {
        
        foreach($this->custom_columns as $group) {
            extract($group);
            $this->current_columns = $fields;
            add_filter( "manage_{$post_type}_posts_columns", function ( $columns ) {
                if (isset($columns["date"])) {
                    $date_column = $columns["date"];
                    unset($columns["date"]);
                }
                foreach($this->current_columns as $column) {
                    extract($column);
                    $columns["cz_acf_{$key}"] = $name;
                }
                if ($date_column) {
                    $columns["date"] = $date_column;
                }

                return $columns;
            } );

            add_action( "manage_{$post_type}_posts_custom_column", function ( $column_name, $post_id ) {
	
                $this->render_column($column_name, $post_id);
                
            }, 10, 2 );
        }
    }

    public function render_column( $column_name, $post_id ) {
	
        $prefix = 'cz_acf_';
        $match = false;
        foreach($this->custom_columns as $group) {
            extract($group);
            foreach($fields as $field) {
                if ($column_name === $prefix . $field['key']) {
                    $match = $field;
                    break 2;
                }
            }
        }

        if ($match)
            echo $this->get_formatted_data($match, $post_id);
    }

    private function get_formatted_data($field, $post_id) {
        $prefix = 'cz_acf_';
        $output = '';
        if ($field['type'] !== 'thumbnail') {
            if ($field['parent'] === false) {                
                $data = get_field($prefix . $field['key'], $post_id);
            } else {
                $group = get_field($prefix . $field['parent'], $post_id);
                $data = $group[$prefix . $field['key']];
            }
        }
        switch($field['type']) {
            case 'text':
                $output = $data;
                break;
            case 'thumbnail':
                $thumbnail_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
                $output = ($thumbnail_url) ? "<img src=\"$thumbnail_url\" width=\"48\" height=\"48\" />" : "";
                break;
            case 'taxonomy':
                $terms = get_terms( [
                    'taxonomy'   => $field['defaults']['taxonomy'],
                    'hide_empty' => false,
                    'include' => [$data]
                ] );
                $output = $terms[0]->name;
                break;
            case 'gallery':
                $thumbnail_url = $data[0];
                $output = ($thumbnail_url) ? "<img src=\"$thumbnail_url\" width=\"64\" height=\"64\" />" : "";
                break;
            case 'true_false':
                $options = [ 
                    "on" => __('Sí', '__cz__'),
                    "off" => __('No', '__cz__')
                ];
                if (isset($field['defaults'])) {
                    $options["on"] = (isset($field['defaults']['ui_on_text'])) ? $field['defaults']['ui_on_text'] : $options["on"];
                    $options["off"] = (isset($field['defaults']['ui_off_text'])) ? $field['defaults']['ui_off_text'] : $options["off"];
                }
                $output = ($data == 1) ? $options["on"] : $options["off"];
                break;
            default:
                $output = $data;
                break;
        }

        return $output;
    }

    private function get_column_fields() {
        if (!$this->custom_fields)
            return;

        foreach($this->custom_fields as $group) {
            $fields = [];
            if ($group['type'] === 'post_type') {
                $match = false;
                foreach($this->post_types as $post_type) {
                    if ($group['target'] === $post_type['key']) {
                        $match = $post_type;
                        break;
                    }
                }
                if ($match && isset($match['thumbnail_column']) && $match['thumbnail_column'] === true) {
                    $fields[] = [
                        "key" => "thumbnail",
                        "name" => __("Imagen", "__cz__"),
                        "type" => "thumbnail"
                    ];
                }
                $fields = array_merge($fields, $this->get_fields_to_add($group['fields']));
                $this->custom_columns[] = [
                    'post_type' => "cz_" . $group['target'],
                    'fields' => $fields
                ];
            }
        }
    }

    private function get_fields_to_add( $fields, $parent = false ) {
        $fields_to_add = [];
        foreach($fields as $field) {
            if ($field['type'] === 'group') {
                $parent_key = $field['name'][1];
                $fields_to_add = array_merge($fields_to_add, $this->get_fields_to_add($field['sub_fields'], $parent_key));
            } else {
                if (isset($field['admin_column']) && $field['admin_column'] === true) {
                    $data = [
                        'key' => $field['name'][1],
                        'name' => $field['name'][0],
                        'type' => $field['type'],
                        'parent' => $parent
                    ];

                    $fields_to_add[] = (isset($field['defaults'])) ? array_merge($data, [ "defaults" => $field['defaults'] ]) : $data;
                }
            }
        }
        return $fields_to_add;
    }
}