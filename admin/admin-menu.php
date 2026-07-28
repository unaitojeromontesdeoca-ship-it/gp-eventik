<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Menú de administración centralizado de GP Eventik.
 * Todos los submenús del plugin se registran aquí, colgando de un menú
 * propio de nivel superior (estilo GP Ambassadors). Los CPT (evento_home,
 * gpe_interno, gpe_contacto) se enganchan a este menú vía show_in_menu.
 */

add_action( 'admin_menu', 'gpe_admin_menu', 9 );
function gpe_admin_menu() {
    add_menu_page(
        'GP Eventik',
        'GP Eventik',
        'edit_posts',
        'gpe-dashboard',
        'gpe_render_dashboard',
        'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path fill="#a7aaad" d="M128 96C110.3 96 96 110.3 96 128L96 160L544 160L544 128C544 110.3 529.7 96 512 96L128 96zM96 224L96 512C96 529.7 110.3 544 128 544L512 544C529.7 544 544 529.7 544 512L544 224L96 224zM240 320C248.8 320 256 327.2 256 336L256 432C256 440.8 248.8 448 240 448L176 448C167.2 448 160 440.8 160 432L160 336C160 327.2 167.2 320 176 320L240 320z"/></svg>'),
        30
    );

    // Inicio (mismo slug que el menú padre)
    add_submenu_page( 'gpe-dashboard', 'Inicio — GP Eventik', 'Inicio', 'edit_posts', 'gpe-dashboard', 'gpe_render_dashboard' );

    // Listados de los CPT (nombres limpios; los nativos se ocultan más abajo)
    add_submenu_page( 'gpe-dashboard', 'Eventos',          'Eventos',          'edit_posts', 'edit.php?post_type=evento_home' );
    add_submenu_page( 'gpe-dashboard', 'Eventos Internos', 'Eventos Internos', 'edit_posts', 'edit.php?post_type=gpe_interno' );
    add_submenu_page( 'gpe-dashboard', 'Ponentes',         'Ponentes',         'edit_posts', 'edit.php?post_type=gpe_contacto' );

    // Páginas propias
    add_submenu_page( 'gpe-dashboard', 'Inscritos por Evento', 'Inscritos',    'edit_posts',     'gpe-inscritos',    'gpe_render_inscritos_page' );
    add_submenu_page( 'gpe-dashboard', 'Estadísticas',         'Estadísticas', 'edit_posts',     'gpe-estadisticas', 'gpe_render_estadisticas' );
    add_submenu_page( 'gpe-dashboard', 'Invitaciones',         'Invitaciones', 'edit_posts',     'gpe-invitaciones', 'gpe_render_invitaciones' );
    add_submenu_page( 'gpe-dashboard', 'Referencia de Shortcodes', 'Shortcodes','manage_options', 'gpe-shortcodes',   'gpe_render_shortcodes_ref' );
}

// ── Ocultar los submenús nativos duplicados de los CPT ───────────────────────
add_action( 'admin_menu', 'gpe_ocultar_nativos_cpt', 9999 );
function gpe_ocultar_nativos_cpt() {
    global $submenu;
    if ( empty( $submenu['gpe-dashboard'] ) ) return;

    // Slugs nativos que WordPress añade solo ("Todos los…" y "Añadir nuevo")
    $quitar = array(
        'edit.php?post_type=evento_home',   // duplicado nativo de Eventos
        'post-new.php?post_type=evento_home',
        'edit.php?post_type=gpe_interno',   // duplicado nativo de Eventos Internos
        'post-new.php?post_type=gpe_interno',
        'edit.php?post_type=gpe_contacto',  // duplicado nativo de Ponentes
        'post-new.php?post_type=gpe_contacto',
    );

    // Conservar solo la PRIMERA aparición de cada slug (la mía, con nombre limpio)
    $vistos = array();
    foreach ( $submenu['gpe-dashboard'] as $i => $item ) {
        $slug = $item[2] ?? '';
        if ( in_array( $slug, $quitar, true ) ) {
            if ( isset( $vistos[ $slug ] ) ) {
                unset( $submenu['gpe-dashboard'][ $i ] ); // segunda vez → fuera
            } else {
                $vistos[ $slug ] = true; // primera vez → conservar
            }
        }
    }
    $submenu['gpe-dashboard'] = array_values( $submenu['gpe-dashboard'] );
}