/* Fix para marcar como 'current' las taxonomías dentro de menus */

if (window.location.href.indexOf('edit-tags.php')) {
    cz.adminMenus.forEach(function(menu) {
        menu.taxonomies.forEach(function(taxonomy) {
            if (window.location.href.indexOf('taxonomy=' + taxonomy.slug) > -1) {
                var elements = document.querySelectorAll('#toplevel_page_'+menu.slug+' .wp-submenu li a[href*="edit-tags.php?taxonomy='+taxonomy.slug+'&post_type='+taxonomy.post_type+'"]');

                for (var i = 0; i < elements.length; i++) {
                    var element = elements[i];
                    var parentElement = element.parentNode;
                    parentElement.classList.add('current');
                }
            }
        });
    });
}