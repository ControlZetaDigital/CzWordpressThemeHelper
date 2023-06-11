<?php
/**
 * Clase para declarar funciones y útiles de ayuda
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz/includes
 */

class CZ_Helpers {
    public function __construct() {
    }

    public function display_notice( $notice = "", $type = "warning", $dismissible = true, $get = false ) {
        $dismissible_text = ( $dismissible ) ? "is-dismissible" : "";
        if ($get) {
            return sprintf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                $type,
                $dismissible_text,
                $notice
            );
        } else {
            printf('<div class="notice notice-%1$s %2$s"><p>%3$s</p></div>',
                $type,
                $dismissible_text,
                $notice
            );
        }
    }

    public function log($handle, $text, $dir = false) {
        $dir_path = ($dir) ? "{$dir}/" : "";
        $filename = CZ_PATH . "/logs/{$dir_path}{$handle}.log";
        $line = "[".date('Y-m-d h:i:s')."] >> " . $text . PHP_EOL;
        file_put_contents($filename, $line, FILE_APPEND);
     }
}