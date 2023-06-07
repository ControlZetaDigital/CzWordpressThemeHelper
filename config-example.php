<?php
/**
 * Variable de configuración de ejemplo 
 * 
 * Este es un archivo de ejemplo, para que sea reconocido por el sistema debe ser renombrado a config.php
 * 
 * @author      Guillermo Pozo
 * @version     2.0.0
 * 
 * @package     cz
 */


// Elementos del menu de administración de Wordpress
$admin_menus = [
    [
        "key" => "admin-menu", //Identificador del menu
        "name" => __("Admin Menú", "__cz__"), //Nombre que se mostrará
        "priority" => 3, //Orden del menu en la barra lateral del administrador de Wordpress
        "dashboard" => false, //Muestra un escritorio para el menú o no. Por defecto: false
        "icon" => 'dashicons-admin-site-alt', // Dashicons o URL al icono. Ej.: CZ_URL . '/public/img/icono.png'
        "items" => [ // Subelementos del menú
            [
                "type" => "post_type", // Tipo de post personaliado
                "key" => "mi-tipo-post" // Identificador (El tipo de post será definido más abajo en la variable para tipos de post y taxonomías)
            ],
            [
                "type" => "taxonomy", // Taxonomía
                "key" => "mi-taxonomia", // La taxonomía se definirá más abajo en la variable de tipos de posts y sus taxonomías
                "parent_key" => "mi-tipo-post" // Identificador del tipo de post al que estará asociada la taxonomía
            ],
            [
                "type" => "custom", // Página personalizada
                "key" => "mi-pagina",
                "name" => __("Mi página Personalizada", "__cz__"),
                "function" => [
                    'classname' => 'CZ_Mi_Pagina_Functions', // Clase personalizada definida en 'functions' que contenga la lógica y el renderizado de la página
                    'method' => 'render_tools_page' // Nombre del método de la clase indicada arriba que genere la salida de la página
                ]
            ],
            [
                "type" => "options", // Página de opciones (usando la API de ACF Pro)
                "key" => "mis-ajustes",
                "name" => __("Mis Ajustes", "__cz__")
            ]
        ]
    ]
];

// Taxonomías y tipos de post personalizados
$post_types = [
    [
        "key" => "mi-tipo-post", // Identificador del tipo de post
        "labels" => [
            "singular" => __( 'Mi Tipo Post', '__cz__' ), // Nombre singular
            "plural" => __( 'Mis Tipos Posts', '__cz__' ), // Nombre plural
            "gender" => "o" // Género masculino o femenino del nombre
        ],
        "public" => true, // Tendrá acceso público mediante URL o no
        "show_in_rest" => false, // Mostrar en la API REST de Wordpress o no
        "show_in_menu" => false, // No mostrar de forma independiente en el menú (solo ponener true en caso de que no se quiera que aparezca en el menú definido arriba)
        "supports" => ["title"], // Qué elementos del core de Wordpress soporta (title, editor, thumbnail, etc.)
        "rewrite" => [ "slug" => "mi-tipo-post" ], // Reescritura de la base de la URL
        "taxonomies" => [ // Taxonomías del tipo de post
            [
                "key" => "mi-taxonomia", // Identificador
                "labels" => [
                    "singular" => __( 'Mi Taxonomía', '__cz__' ),
                    "plural" => __( 'Mis Taxonomías', '__cz__' ),
                    "gender" => "a"
                ],
                "rewrite" => [ "slug" => "mi-taxonomia" ], // Reescritura de la base de la URL
                "default" => true, // Taxonomía por defecto del tipo de post
                "show_in_menu" => false // No mostrar de forma independiente en el menú (solo ponener true en caso de que no se quiera que aparezca en el menú definido arriba)
            ]
        ]
    ]
];

// Campos personalizados usando la API de Advanced Custom Fields Pro
$custom_fields = [
    [
        "key" => "campos-mis-ajustes", // Identificador del grupo de campos
        "name" => __("Campos de página Mis Ajustes", "__cz__"), // Título del widget que contendrá los campos
        "type" => "options_page", // options_page para definir campos globales de las páginas de opciones definidas en la variable admin_menus como 'options'
        "target" => "mis-ajustes", // Identificador de la página de opciones definida anteriormente como 'options'
        "priority" => 1, // Orden del grupo de campos por si hubiera varios
        "fields" => [ // Campos del grupo
            [
                'name' => [__('Mi opción', '__cz__'), 'mi-opcion'], // Nombre e identificador del campo
                'type' => 'text', // Tipo de campo (consular la documentación de ACF Pro para ver todos los tipos)
                'wp_width' => '50' // Ancho del contenedor en porcentaje, en este caso corresponde a 50%.
            ]
        ]
    ],
    [
        "key" => "campos-mi-tipo-post",
        "name" => __("Campos de Mi Tipo de Post ", "__cz__"),
        "type" => "post_type", // Asignados a un tipo de post personalizado, consultar la documentación de ACF Pro para más información.
        "target" => "mi-tipo-post", // Identificador del tipo de post
        "priority" => 1,
        "fields" => [ // Listado de campos
            [
                'name' => [__('Mi campo 1', '__cz__'), 'mi-campo-1'],
                'type' => 'text',
                'wp_width' => '60',
                'required' => 1, // Si es obligatorio
                'defaults' => [ // Añadir aquí todas las opciones que permita el tipo de campo y que se desee personalizar. Consultar la documentación
                    'placeholder' => __('Placeholder de ejemplo', '__cz__'), // Opción de ACF Pro para el tipo de campo 'text'
                    'maxlength' => '60' // Opción de ACF Pro para el tipo de campo 'text'
                ],
                'admin_column' => true // Poner true para que este campo aparezca como columna en la página del listado de posts del tipo de post personalizado.
            ],            
            [
                'name' => [__('Mi campo 2', '__cz__'), 'mi-campo-2'],
                'type' => 'true_false',
                'wp_width' => '20',
                'defaults' => [
                    'ui' => 1
                ]
            ]
        ]
    ]
];