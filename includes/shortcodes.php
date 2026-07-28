<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── [gp_agenda_eventos] — Tarjetas agenda pública ───────────────────────────
add_shortcode( 'gp_agenda_eventos', 'gpe_shortcode_agenda_eventos' );
function gpe_shortcode_agenda_eventos( $atts ) {
    $atts = shortcode_atts( array(
        'ccaa'     => '',
        'provincia'=> '',
        'limite'   => 12,
        'ordenar'  => 'fecha',
    ), $atts );

    $meta_query = array();
    if ( $atts['ccaa'] )      $meta_query[] = array('key'=>'_gpe_ccaa_evento',      'value'=>$atts['ccaa']);
    if ( $atts['provincia'] ) $meta_query[] = array('key'=>'_gpe_provincia_evento', 'value'=>$atts['provincia']);

    $args = array(
        'post_type'      => 'evento_home',
        'posts_per_page' => intval($atts['limite']),
        'post_status'    => 'publish',
        'orderby'        => 'meta_value',
        'meta_key'       => '_med_fecha_evento',
        'order'          => 'ASC',
    );
    if ( !empty($meta_query) ) $args['meta_query'] = $meta_query;

    $eventos_query = new WP_Query( $args );
    ob_start();
    if ( file_exists( GPE_PLUGIN_DIR . 'templates/public-view.php' ) )
        include GPE_PLUGIN_DIR . 'templates/public-view.php';
    wp_reset_postdata();
    return ob_get_clean();
}

// ─── [gp_agenda_general] — Agenda completa ───────────────────────────────────
add_shortcode( 'gp_agenda_general', 'gpe_shortcode_agenda_general' );
function gpe_shortcode_agenda_general( $atts ) {
    $atts = shortcode_atts( array(
        'vista'           => 'grid',
        'ccaa'            => '',
        'mostrar_pasados' => 'no',
    ), $atts );

    $meta_query = array();
    if ( $atts['mostrar_pasados'] === 'no' ) {
        $meta_query[] = array('key'=>'_med_fecha_evento','value'=>date('Y-m-d'),'compare'=>'>=','type'=>'DATE');
    }
    if ( $atts['ccaa'] ) {
        $meta_query[] = array('key'=>'_gpe_ccaa_evento','value'=>$atts['ccaa']);
        $meta_query['relation'] = 'AND';
    }
    $eventos_query = new WP_Query( array(
        'post_type'      => 'evento_home',
        'posts_per_page' => 50,
        'post_status'    => 'publish',
        'meta_key'       => '_med_fecha_evento',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => $meta_query,
    ) );
    ob_start();
    if ( file_exists( GPE_PLUGIN_DIR . 'templates/agenda-general.php' ) )
        include GPE_PLUGIN_DIR . 'templates/agenda-general.php';
    wp_reset_postdata();
    return ob_get_clean();
}

// ─── Portales de coordinador — uno por idioma ─────────────────────────────────
// La función gpe_render_portal_coordinador() está en portal-coordinador.php
// Aquí creamos los 4 shortcodes forzando el idioma en cada uno.

add_shortcode( 'gpe_portal_es', function() {
    add_filter('gpe_portal_idioma_forzado', function(){ return 'es'; });
    return gpe_render_portal_coordinador();
});
add_shortcode( 'gpe_portal_ca', function() {
    add_filter('gpe_portal_idioma_forzado', function(){ return 'ca'; });
    return gpe_render_portal_coordinador();
});
add_shortcode( 'gpe_portal_gl', function() {
    add_filter('gpe_portal_idioma_forzado', function(){ return 'gl'; });
    return gpe_render_portal_coordinador();
});
add_shortcode( 'gpe_portal_eu', function() {
    add_filter('gpe_portal_idioma_forzado', function(){ return 'eu'; });
    return gpe_render_portal_coordinador();
});

// Compatibilidad con el shortcode genérico antiguo
add_shortcode( 'gpe_portal_coordinador', 'gpe_render_portal_coordinador' );

// ─── [gpe_inscripcion_interna] — Formulario inscripción evento interno ────────
add_shortcode( 'gpe_inscripcion_interna', 'gpe_shortcode_inscripcion_interna' );
function gpe_shortcode_inscripcion_interna( $atts ) {
    $atts = shortcode_atts( array('evento_id' => 0), $atts );
    $id   = intval($atts['evento_id']) ?: get_the_ID();
    if ( !$id ) return '';
    ob_start();
    gpe_render_formulario_interno( $id );
    return ob_get_clean();
}
