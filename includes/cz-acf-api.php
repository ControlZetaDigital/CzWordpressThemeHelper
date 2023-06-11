<?php
/**
 * Clase para configurar las funciones necesarias para gestionar campos de ACF
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz/includes
 */

class CZ_ACF_Api {

    protected $fields;

    protected $groups;

	public function __construct() {
        $this->fields = [];
        $this->groups = [];
    }

    public function set_groups($groups) {
        $this->groups = $groups;
    }

    public function set_fields($fields) {
        $this->fields = $fields;
    }

    public function get_fields() {
        return $this->get_custom_fields();
    }

    public function register_fields() {
        foreach($this->groups as $field_group) {
            $this->set_fields($field_group["fields"]);
            $this->set_custom_group("cz_".$field_group["key"], $field_group["name"], [
                [
                    [
                        'param' => $field_group["type"],
                        'operator' => '==',
                        'value' => "cz_" . $field_group["target"]
                    ]
                ]
            ], [
                'menu_order' => (isset($field_group["priority"])) ? $field_group["priority"] : 0
            ]);
        }
    }

    public function set_custom_group($key, $title, $location, $args = []) {

        acf_add_local_field_group([
            'key' => 'cz_' . $key,
            'title' => $title,
            'fields' => $this->get_custom_fields(),
            'location' => $location,
            'menu_order' => (isset($args['menu_order'])) ? $args['menu_order'] : 0,
            'position' => (isset($args['position'])) ? $args['position'] : 'normal', //acf_after_title - side
            'style' => (isset($args['style'])) ? $args['style'] : 'default', //seamless
            'label_placement' => (isset($args['label_placement'])) ? $args['label_placement'] : 'top',
            'instruction_placement' => (isset($args['instruction_placement'])) ? $args['instruction_placement'] : 'label',
            'hide_on_screen' => (isset($args['hide_on_screen'])) ? $args['hide_on_screen'] : '',
            'active' => (isset($args['active'])) ? $args['active'] : true,
            'description' => (isset($args['description'])) ? $args['description'] : '',
            'show_in_rest' => (isset($args['show_in_rest'])) ? $args['show_in_rest'] : 0
        ]);

    }

    private function get_custom_fields() {
        $fields = [];
        foreach($this->fields as $field_data)
            $fields[] = $this->get_custom_field($field_data);

        return $fields;
    }

    private function get_custom_field( $args ) {
        $label = $args['name'][0];
        $name = $args['name'][1];
        $key = 'cz_acf_' . $name;
        $field = [
            'key' => $key,
            'label' => $label,
            'name' => $key,
            'type' => $args['type'],
            'instructions' => (isset($args['instructions'])) ? $args['instructions'] : '',
            'required' => (isset($args['required'])) ? $args['required'] : 0,
            'conditional_logic' => (isset($args['conditional_logic'])) ? $args['conditional_logic'] : 0,
            'wrapper' => [
                'width' => (isset($args['wp_width'])) ? $args['wp_width'] : '',
                'class' => (isset($args['wp_class'])) ? $args['wp_class'] : '',
                'id' => (isset($args['wp_id'])) ? $args['wp_id'] : '',
            ]
        ];
        
        //Merge sub_fields data if exists
        if (isset($args['sub_fields'])) {
            $field = array_merge(
                $field,
                $this->get_custom_sub_fields($args['sub_fields'])
            );
        }

        //Merge layouts data if exists
        if (isset($args['layouts'])) {
            $field = array_merge(
                $field,
                $this->get_custom_layouts($args['layouts'])
            );
        }

        //Merge default data if exists
        if (isset($args['defaults'])) {
            $field = array_merge(
                $field,
                $this->get_custom_fields_defaults($args['type'], $args['defaults'])
            );
        }

        return $field;
    }

    private function get_custom_sub_fields($subfields) {
        $return = [
            'sub_fields' => []
        ];
        foreach($subfields as $subfield) {
            $args = [];
            foreach($subfield as $key => $value)
                $args[$key] = $value;
            
            $return['sub_fields'][] = $this->get_custom_field($args);
        }

        return $return;
    }

    private function get_custom_layouts($layouts) {
        $return = [
            'layouts' => []
        ];
        foreach($layouts as $layout) {
            $args = [];
            foreach($layout as $key => $value)
                $args[$key] = $value;
            
            $return['layouts'][] = $this->get_custom_layout($args);
        }

        return $return;
    }

    private function get_custom_layout( $args ) {
        $label = $args['name'][0];
        $name = $args['name'][1];
        $key = 'cz_acf_layout_' . $name;
        $layout = [
            'key' => $key,
            'label' => $label,
            'name' => $key,
            'display' => (isset($args['display'])) ? $args['display'] : 'block',
            'min' => (isset($args['min'])) ? $args['min'] : '',
            'max' => (isset($args['max'])) ? $args['max'] : ''
        ];
        
        //Merge sub_fields data if exists
        if (isset($args['sub_fields'])) {
            $layout = array_merge(
                $layout,
                $this->get_custom_sub_fields($args['sub_fields'])
            );
        }

        return $layout;
    }

    private function get_custom_fields_defaults($type, $values) {
        $defaults = false;        

        switch($type) {
            case 'group':
                $defaults = ['layout'];
                break;
            case 'text':
                $defaults = ['default_value', 'placeholder', 'prepend', 'append', 'maxlength'];
                break;
            case 'email':
                $defaults = ['default_value', 'placeholder', 'prepend', 'append'];
                break;
            case 'password':
                $defaults = ['placeholder', 'prepend', 'append'];
                break;
            case 'url':
                $defaults = ['default_value', 'placeholder'];
                break;
            case 'number':
                $defaults = ['default_value', 'placeholder', 'prepend', 'append', 'min', 'max', 'step'];
                break;
            case 'textarea':
                $defaults = ['default_value', 'placeholder', 'maxlength', 'rows', 'new_lines'];
                break;
            case 'select':
                $defaults = ['choices', 'default_value', 'allow_null', 'multiple', 'ui', 'return_format', 'ajax', 'placeholder'];
                break;
            case 'date_picker':
                $defaults = ['display_format', 'return_format', 'first_day'];
                break;
            case 'color_picker':
                $defaults = ['enable_opacity', 'return_format', 'default_value'];
                break;
            case 'radio':
                $defaults = ['choices', 'default_value', 'allow_null', 'other_choice', 'layout', 'return_format', 'save_other_choice'];
                break;
            case 'true_false':
                $defaults = ['message', 'default_value', 'ui', 'ui_on_text', 'ui_off_text'];
                break;
            case 'message':
                $defaults = ['message', 'new_lines', 'esc_html'];
                break;
            case 'oembed':
                $defaults = ['width', 'height'];
                break;
            case 'gallery':
                $defaults = ['return_format', 'preview_size', 'insert', 'library', 'min', 'max', 'min_width', 
                                  'min_height', 'min_size', 'max_width', 'max_height', 'max_size', 'mime_types'];
                break;
            case 'image':
                $defaults = ['return_format', 'preview_size', 'library', 'min_width', 'min_height', 'min_size', 
                'max_width', 'max_height', 'max_size', 'mime_types'];
                break;
            case 'user':
                $defaults = ['role', 'multiple', 'allow_null', 'return_format'];
                break;
            case 'taxonomy':
                $defaults = ['taxonomy', 'field_type', 'allow_null', 'add_term', 'save_terms', 'load_terms', 'return_format', 'multiple'];
                break;
            case 'post_object':
                $defaults = ['post_type', 'taxonomy', 'allow_null', 'multiple', 'return_format', 'ui'];
                break;
            case 'relationship':
                $defaults = ['post_type', 'taxonomy', 'filters', 'elements', 'min', 'max', 'return_format'];
                break;
            case 'repeater':
                $defaults = ['collapsed', 'min', 'max', 'layout', 'button_label'];
                break;
            case 'flexible_content':
                $defaults = ['min', 'max', 'button_label'];
                break;
            case 'wysiwyg':
                $defaults = ['default_value', 'tabs', 'toolbar', 'media_upload', 'delay'];
                break;
            case 'button_group':
                $defaults = ['choices', 'allow_null', 'default_value', 'layout', 'return_format'];
                break;
            case 'checkbox':
                $defaults = ['choices', 'allow_custom', 'save_custom', 'default_value', 'layout', 'toggle', 'return_format', 'custom_choice_button_text'];
                break;
            case 'file':
                $defaults = ['return_format', 'library', 'min_size', 'max_size', 'mime_types'];
                break;
            case 'google_map':
                $defaults = ['center_lat', 'center_lng', 'zoom', 'height'];
                break;
            default:
                break;
        }

        $return = [];
        foreach ($defaults as $default) {
            $return[$default] = $this->get_default_value($default, ((isset($values[$default])) ? $values[$default] : false));
        }

        return $return;
    }

    private function get_default_value($search, $override = false) {
        $default_values = [
            'numbers' => ['allow_null', 'other_choice', 'save_other_choice', 'add_term', 'save_terms', 'load_terms', 'toggle',
                                'multiple', 'media_upload', 'delay', 'esc_html', 'ajax', 'ui', 'enable_opacity', 'allow_custom', 
                                'save_custom'], // Default value = 0
            'strings' => [ 'default_value', 'placeholder', 'prepend', 'append', 'min', 'max', 'step', 'maxlength', 
                                'width', 'height', 'min_width', 'min_height', 'filters', 'ui_on_text', 'ui_off_text',
                                'elements', 'min_size', 'max_width', 'max_height', 'max_size', 'mime_types', 'taxonomy', 
                                'post_type', 'collapsed', 'button_label', 'message', 'role' ], // Default value = ''
            'others' => [
                'rows' => 8,
                'first_day' => 1,
                'new_lines' => 'br',
                'display_format' => 'd/m/Y',
                'return_format' => 'array',
                'preview_size' => 'shop_thumbnail',
                'insert' => 'append',
                'library' => 'all',
                'layout' => 'block',
                'field_type' => 'select',
                'ui' => 1,
                'tabs' => 'all',
                'toolbar' => 'basic',
                'choices' => [],
                'zoom' => 10,
                'center_lat' => '28.3006188',
                'center_lng' => '-16.5501064',
                'custom_choice_button_text' => __('Añadir nueva opción', '__cz__')
            ] // Mixed default values
        ];

        foreach ($default_values as $key => $list) {
            if ($key != 'others') {
                $value = ($key == 'numbers') ? 0 : '';
                foreach($list as $default) {
                    if ($default == $search)
                        return ($override) ? $override : $value;
                }
            } else {
                foreach($list as $default => $d_value) {
                    if ($default == $search)
                        return ($override) ? $override : $d_value;
                }
            }
        }
    }
}