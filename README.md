# CZ Wordpress Theme Helper

Este helper tiene la misión de complementar los temas de Wordpress para crear de forma sencilla contenido personalizado como custom post types, campos personalizados a través de la API de Advanced Custom Fields Pro y otros tipos de contenido como menus de administradores y hooks que faciliten la personalización sin necesidad de usar multiples plugins y facilitando el trabajo de codificación.

Para ello se usa un archivo `config.php` con tres variables principales que en un formato de array estructurado similar a JSON se podrán declarar las personalizaciones reduciendo el código empleado en programar la lógica de las personalizaciones dejando limpio el fichero de Wordpress functions.php.

## Características

- Configuración del ecosistema de personalizaciones desde un fichero `config.php` con tres variables principales: `admin_menus`, `post_types` y `custom_fields`.
- Creación de **Custom Post Types** y **taxonomías** asociadas en pocas líneas dentro del fichero de configuración, todos los hooks y código necesarios para crear la estructura de Custom Post Types los hará este helper por ti.
- Permite crear de forma sencilla **admin menus personalizados** con los subelementos que necesites, como los custom post types creados también por ti desde el fichero de configuración, páginas de ajustes usando la API de **Advanced Custom Fields Pro** o páginas personalizadas.
- Directorio `public` disponible para añadir assets varios usados en el frontend como **estilos CSS, scripts JS, librerías JS, views, partials** u otro tipo de contenidos personalizados a discreción del desarrollador.
- Directorio `functions` para añadir ficheros php que contengan funciones o clases que agreguen la lógica necesaria para llevar las personalizaciones un paso más allá.

## Requisitos Previos y Dependencias

No hay requisitos salvo tener instalado y activo el plugin **Advanced Custom Fields Pro** para usar su API de campos personalizados si es que es necesario para el proyecto.

## Instalación

1. Navega al directorio de tu tema instalado en wordpress, por ejemplo: `cd /wp-config/themes/tu-tema`
2. Clona el repositorio: `git clone https://github.com/ControlZetaDigital/CzWordpressThemeHelper.git`
3. Alternativamente puedes descargar los ficheros y guardarlos en la carpeta raíz de tu tema.
4. Editar el archivo `functions.php` y añadir la siguiente línea (**IMPORTANTE:** modificar el nombre carpeta si se ha descargado el helper en otra ruta distinta):
```php
require_once get_stylesheet_directory() . '/CzWordpressThemeHelper/loader.php';
```

## Uso

El helper trabaja con un archivo `config.php` en el que se configurarán todas las personalizaciones que tengan que ver con:
1. **Admin menus** que pueden contener subelementos tales como:
    1. Tipos de post personalizados
    2. Taxonomías de los tipos de posts personalizados (recomendable añadirlas justo después de los tipos de posts)
    3. Páginas de opciones globales usando la API de Advanced Custom Fields Pro
    4. Páginas personalizadas cuyo contenido y lógiva deberá ser definido desde una clase almacenada en el directorio `functions`
2. **Tipos de post personalizados y taxonomías**
3. **Campos personalizados**

Se facilita un archivo `config-example.php` con algunos ejemplos del contenido y cómo definirlo. En futuras actualizaciones se mejorará no solo el propio helper añadiendo nuevas funcionalidades sino que se mejorará y ampliará la documentación a este respecto.

Para defnir las clases que contengan las personalizaciones más avanzadas es recomendable hacerlo dentro de la carpeta `functions` del repositorio. Se recomienda utilizar un formato similar a este para los ficheros incluidos: `cz-{mi-clase}-functions.php`.

Dentro del fichero se deberán declarar funciones o clases PHP para que posteriormente puedan ser instanciadas cuando las necesites. Toda la lógica y hooks que quieras inicializar junto con el helper se recomienda hacerlo con un método público llamado `run()` que será llamado por el core del helper al inicializarse, por ejemplo de la siguiente manera:
```php
class CZ_Mi_Clase_Functions {
    public function __construct() {}

    public function run() {
        // Tus hooks y código van aquí
    }
}
```

## Contribución

Si deseas contribuir a este proyecto, por favor sigue los siguientes pasos:

1. Haz un fork del repositorio
2. Crea una rama para tu contribución: git checkout -b feature/nueva-caracteristica
3. Realiza los cambios y realiza los commits: git commit -m "Agrega nueva característica"
4. Haz un push de la rama: git push origin feature/nueva-caracteristica
5. Abre una Pull Request en GitHub

## Licencia

Este software está sujeto al tipo de licencia GNU GENERAL PUBLIC LICENSE (GPL). Para más información consultar el fichero LICENCIA.

## Contacto

Si tienes alguna pregunta o sugerencia, no dudes en contactarme en code@controlzetadigital.com