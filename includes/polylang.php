<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Compatibilidad con Polylang
 * - Registra las cadenas del plugin para traducción en Polylang > Cadenas
 * - Filtra los shortcodes para mostrar solo eventos del idioma activo
 * - Permite que las landings de eventos se muestren en el idioma correcto
 */

// Solo actuamos si Polylang está activo
add_action( 'init', 'gpe_polylang_init', 20 );
function gpe_polylang_init() {
    if ( ! function_exists('pll_register_string') ) return;

    // Registrar cadenas estáticas del plugin para traducción
    $cadenas = array(
        'gpe_inscribete'          => 'Inscríbete',
        'gpe_aforo_completado'    => 'Aforo completado',
        'gpe_lista_espera_btn'    => 'Apuntarse a la lista de espera',
        'gpe_reservar_plaza'      => 'Reservar mi plaza',
        'gpe_plazas_disponibles'  => 'plazas disponibles',
        'gpe_proximos_eventos'    => 'No hay eventos próximos programados.',
        'gpe_inscripcion_ok'      => '¡Inscripción confirmada! Te hemos enviado un email.',
        'gpe_espera_ok'           => 'Apuntado a la lista de espera. Te avisaremos si se libera una plaza.',
        'gpe_por_que_asistir'     => '¿Por qué asistir?',
        'gpe_ponentes_titulo'     => 'Ponentes',
        'gpe_programa_titulo'     => 'Programa completo',
        'gpe_agenda_titulo'       => 'Agenda',
        'gpe_preguntas_titulo'    => '¿Qué ideas se debatirán?',
        'gpe_faqs_titulo'         => 'Preguntas frecuentes',
        'gpe_localizacion_titulo' => 'Cómo llegar',
        'gpe_cuenta_atras_dias'   => 'días',
        'gpe_cuenta_atras_horas'  => 'horas',
        'gpe_cuenta_atras_min'    => 'min',
        'gpe_cuenta_atras_seg'    => 'seg',
        'gpe_cuenta_atras_label'  => 'Faltan',
        'gpe_evento_comenzado'    => '¡El evento ha comenzado!',
        'gpe_participacion'       => 'Participación juvenil',
        'gpe_acreditacion'        => 'Reserva de plaza y acreditación',
        'gpe_proximamente'        => 'Inscripciones próximamente',
        'gpe_cancelar_link'       => 'Cancela tu inscripción aquí',
        'gpe_email_confirmacion'  => 'Tu inscripción ha sido confirmada.',
        'gpe_email_fecha'         => 'Fecha',
        'gpe_email_lugar'         => 'Lugar',
    );

    foreach ( $cadenas as $key => $valor ) {
        pll_register_string( $key, $valor, 'GP Eventik' );
    }
}

/**
 * Obtener cadena traducida si Polylang está activo, o el valor por defecto
 */
function gpe__( $key, $default ) {
    if ( function_exists('pll__') ) {
        $traducida = pll__( $default );
        return $traducida ?: $default;
    }
    return $default;
}

/**
 * Filtrar query de eventos por idioma activo de Polylang
 * Se aplica a WP_Query cuando post_type = evento_home
 */
add_action( 'pre_get_posts', 'gpe_polylang_filtrar_idioma' );
function gpe_polylang_filtrar_idioma( $query ) {
    if ( ! function_exists('pll_current_language') ) return;
    if ( is_admin() ) return;
    if ( $query->get('post_type') !== 'evento_home' ) return;

    // Polylang ya filtra automáticamente por idioma en el front
    // Este hook existe por si hay casos edge con multisite
    $lang = pll_current_language();
    if ( $lang ) {
        // Polylang maneja esto solo, pero forzamos por si acaso
        $query->set( 'lang', $lang );
    }
}

/**
 * Asegurar que el CPT evento_home es traducible en Polylang
 * (se llama desde cpts.php después de registrar el CPT)
 */
add_action( 'init', 'gpe_polylang_registrar_cpts', 30 );
function gpe_polylang_registrar_cpts() {
    if ( ! function_exists('pll_is_translated_post_type') ) return;

    // Registrar CPTs como traducibles si Polylang está activo
    // Esto complementa la configuración manual en Polylang > Ajustes > Tipos de contenido
    $option = get_option('polylang');
    if ( ! $option ) return;

    foreach ( array('evento_home','gpe_contacto','gpe_institucional') as $cpt ) {
        if ( ! in_array($cpt, $option['post_types'] ?? array()) ) {
            $option['post_types'][] = $cpt;
        }
    }
    update_option( 'polylang', $option );
}
