<?php
/**
 * Cargador inicial
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz
 */

define ('CZ',       '__cz__');
define ('CZ_PATH',  get_stylesheet_directory() . '/cz');
define ('CZ_URL',   get_stylesheet_directory_uri() . '/cz');

require_once CZ_PATH . '/includes/cz-main.php';
require_once CZ_PATH . '/config.php';

global $admin_menus;
global $post_types;
global $custom_fields;

$CZ = new CZ_Main( [
    "admin_menus" => $admin_menus,
    "post_types" => $post_types,
    "custom_fields" => $custom_fields
] );

$CZ->init();