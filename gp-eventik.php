<?php
/**
 * Plugin Name: GP Eventik - Gestión de Eventos Juveniles
 * Plugin URI:  https://generacionpresente.org
 * Description: Eventos públicos e internos, inscripciones, ponentes, portal coordinadores, órganos de gobierno y acreditación QR para Generación Presente.
 * Version:     0.0.0-dev
 * Author:      Generación Presente
 * License:     GPL2
 */
if ( ! defined( 'ABSPATH' ) ) exit;

define('GPE_DIR', plugin_dir_path(__FILE__));
   define('GPE_URL', plugin_dir_url(__FILE__));
   define('GPE_PLUGIN_DIR', plugin_dir_path(__FILE__));
   define('GPE_PLUGIN_URL', plugin_dir_url(__FILE__));
   define('GPE_FILE', __FILE__);
   define('GPE_SLUG', 'gp-eventik');
   define('GPE_VERSION', '0.0.0-dev');

function gpe_load( $ruta ) {
    $full = GPE_PLUGIN_DIR . $ruta;
    if ( file_exists( $full ) ) {
        require_once $full;
    } else {
        if ( is_admin() ) {
            add_action( 'admin_notices', function() use ($ruta) {
                echo '<div class="notice notice-warning"><p><strong>GP Eventik:</strong> Archivo no encontrado: <code>' . esc_html($ruta) . '</code></p></div>';
            });
        }
    }
}

// ── Núcleo ───────────────────────────────────────────────────────────────────
gpe_load( 'includes/install.php' );
gpe_load( 'includes/cpts.php' );
gpe_load( 'includes/territorios.php' );
gpe_load( 'includes/roles.php' );
gpe_load( 'includes/inscripciones.php' );
gpe_load( 'includes/espera.php' );
gpe_load( 'includes/emails.php' );
gpe_load( 'includes/estados.php' );
gpe_load( 'includes/qr-acreditacion.php' );
gpe_load( 'includes/cron-emails.php' );
gpe_load( 'includes/polylang.php' );
gpe_load( 'includes/shortcodes.php' );
gpe_load( 'includes/ajax.php' );
gpe_load( 'includes/portal-coordinador.php' );
gpe_load( 'includes/organos-gobierno.php' );       // Bridge a la API de GP Ambassadors
gpe_load( 'includes/eventos-internos.php' );
gpe_load( 'includes/acreditacion-pdf.php' );       // Acreditaciones imprimibles       // NUEVO

// ── Actualizaciones (OTA) ────────────────────────────────────────────────────
require_once GPE_DIR . 'includes/update-manager.php';
new \GP_Eventik\Update_Manager\Update_Manager();

// ── Administración ───────────────────────────────────────────────────────────
if ( is_admin() ) {
    gpe_load( 'admin/admin-menu.php' );
    gpe_load( 'admin/admin-dashboard.php' );
    gpe_load( 'admin/admin-eventos.php' );
    gpe_load( 'admin/admin-eventos-internos.php' ); // NUEVO
    gpe_load( 'admin/admin-ponentes.php' );
    gpe_load( 'admin/admin-inscripciones.php' );    // lista espera integrada
    gpe_load( 'admin/admin-estadisticas.php' );
    gpe_load( 'admin/admin-shortcodes.php' );       // NUEVO
}

// ── Plantillas ───────────────────────────────────────────────────────────────
add_filter( 'template_include', 'gpe_plantilla_single_evento' );
function gpe_plantilla_single_evento( $template ) {
    if ( is_singular( 'evento_home' ) ) {
        $t = GPE_PLUGIN_DIR . 'templates/single-evento_home.php';
        if ( file_exists($t) ) return $t;
    }
    if ( is_singular( 'gpe_interno' ) ) {
        $t = GPE_PLUGIN_DIR . 'templates/single-evento_interno.php';
        if ( file_exists($t) ) return $t;
    }
    return $template;
}

// ── Footer de marca en pantallas del plugin ──────────────────────────────────
add_filter( 'admin_footer_text', 'gpe_admin_footer_text' );
function gpe_admin_footer_text( $text ) {
    $screen = get_current_screen();
    if ( ! $screen ) return $text;
    $es_gpe = ( strpos( $screen->id, 'gpe-' ) !== false )
        || in_array( $screen->post_type, array('evento_home','gpe_interno','gpe_contacto'), true );
    if ( ! $es_gpe ) return $text;
    return '<strong>GP Eventik</strong> · Exclusivo de Generación Presente';
}

add_filter( 'update_footer', 'gpe_admin_footer_version', 11 );
function gpe_admin_footer_version( $text ) {
    $screen = get_current_screen();
    if ( ! $screen ) return $text;
    $es_gpe = ( strpos( $screen->id, 'gpe-' ) !== false )
        || in_array( $screen->post_type, array('evento_home','gpe_interno','gpe_contacto'), true );
    if ( ! $es_gpe ) return $text;
    return 'v' . GPE_VERSION;
}

// ── Assets admin ─────────────────────────────────────────────────────────────
add_action( 'admin_enqueue_scripts', 'gpe_enqueue_admin_assets' );
function gpe_enqueue_admin_assets( $hook ) {
    global $post_type;
    $es_pagina_gpe = ( strpos( $hook, 'gpe-' ) !== false )
        || ( isset($post_type) && in_array( $post_type, array('evento_home','gpe_interno','gpe_contacto'), true ) )
        || ( isset($_GET['post_type']) && in_array( $_GET['post_type'], array('evento_home','gpe_interno','gpe_contacto'), true ) );
    if ( ! $es_pagina_gpe ) return;
    wp_enqueue_style( 'gpe-admin', GPE_URL . 'assets/css/admin.css', array(), GPE_VERSION );
}

// ── Assets front ─────────────────────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'gpe_enqueue_assets' );
function gpe_enqueue_assets() {
    if ( file_exists( GPE_PLUGIN_DIR . 'assets/style.css' ) ) {
        wp_enqueue_style( 'gpe-style', GPE_PLUGIN_URL . 'assets/style.css', array(), GPE_VERSION );
    }
    // Font Awesome para iconos de temáticas en tarjetas y landing
    if ( ! wp_style_is('font-awesome','enqueued') && ! wp_style_is('fontawesome','enqueued') ) {
        wp_enqueue_style('gpe-fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
            array(), '6.5.0');
    }
}

// ── Activación ───────────────────────────────────────────────────────────────
register_activation_hook( __FILE__, 'gpe_activar_plugin' );
function gpe_activar_plugin() {
    if ( function_exists('gpe_crear_tablas_db') )               gpe_crear_tablas_db();
    if ( function_exists('gpe_registrar_roles_territoriales') ) gpe_registrar_roles_territoriales();
    if ( function_exists('gpe_crear_pagina_portal') )           gpe_crear_pagina_portal();
    if ( function_exists('gpe_crear_menu_navegacion') )         gpe_crear_menu_navegacion();
    if ( function_exists('gpe_programar_crons') )               gpe_programar_crons();

    // Limpiar caché de actualizaciones al activar
    delete_transient('gpe_update_check');
    delete_site_transient('update_plugins');

    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'gpe_desactivar_plugin' );
function gpe_desactivar_plugin() {
    wp_clear_scheduled_hook('gpe_cron_resumen_diario');
    wp_clear_scheduled_hook('gpe_cron_recordatorios');
    flush_rewrite_rules();
}
