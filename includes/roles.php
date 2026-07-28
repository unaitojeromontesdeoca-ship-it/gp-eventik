<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Roles y filtrado territorial de eventos.
 * El territorio del usuario y su condición de coordinador los gestiona y
 * resuelve GP Ambassadors. Eventik solo lee esa información (vía gpa_api_*)
 * para decidir qué eventos puede editar cada usuario. Aquí no se asigna
 * territorio ni se crean pantallas de coordinadores.
 */

// ─── Rol "Coordinador GP": capacidades sobre el CPT de eventos ────────────────
function gpe_registrar_roles_territoriales() {
    if ( get_role( 'gpe_coordinador' ) ) return;

    add_role( 'gpe_coordinador', 'Coordinador GP', array(
        'read'                       => true,
        'upload_files'               => true,
        'edit_gpe_eventos'           => true,
        'edit_published_gpe_eventos' => true,
        'publish_gpe_eventos'        => true,
        'delete_gpe_eventos'         => false,
        'read_private_gpe_eventos'   => false,
        'edit_gpe_ponentes'          => true,
        'publish_gpe_ponentes'       => true,
        'delete_gpe_ponentes'        => false,
        'access_gpe_panel'           => true,
    ) );
}
add_action( 'init', 'gpe_registrar_roles_territoriales' );

// ─── Territorio del usuario (leído de GP Ambassadors) ─────────────────────────
function gpe_territorio_usuario( $user_id = null ) {
    if ( ! $user_id ) $user_id = get_current_user_id();
    if ( function_exists( 'gpa_api_get_territorio_usuario' ) ) {
        return gpa_api_get_territorio_usuario( $user_id );
    }
    return array( 'ccaa' => '', 'provincia' => '' );
}

// ─── Verificar si el usuario puede editar un evento concreto ─────────────────
function gpe_puede_editar_evento( $evento_id, $user_id = null ) {
    if ( ! $user_id ) $user_id = get_current_user_id();

    if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' ) ) return true;

    $territorio  = gpe_territorio_usuario( $user_id );
    $evento_ccaa = get_post_meta( $evento_id, '_gpe_ccaa_evento', true );
    $evento_prov = get_post_meta( $evento_id, '_gpe_provincia_evento', true );

    if ( empty( $territorio['ccaa'] ) ) return false;

    if ( ! empty( $territorio['provincia'] ) ) {
        return ( $territorio['ccaa'] === $evento_ccaa && $territorio['provincia'] === $evento_prov );
    }

    return ( $territorio['ccaa'] === $evento_ccaa );
}

// ─── Filtrar listado de eventos en el admin por territorio ───────────────────
add_action( 'pre_get_posts', 'gpe_filtrar_eventos_por_territorio' );
function gpe_filtrar_eventos_por_territorio( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) return;
    if ( $query->get( 'post_type' ) !== 'evento_home' ) return;
    if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_others_posts' ) ) return;
    if ( ! current_user_can( 'access_gpe_panel' ) ) return;

    $territorio = gpe_territorio_usuario();
    if ( empty( $territorio['ccaa'] ) ) return;

    $meta_query = array( array(
        'key'     => '_gpe_ccaa_evento',
        'value'   => $territorio['ccaa'],
        'compare' => '=',
    ) );

    if ( ! empty( $territorio['provincia'] ) ) {
        $meta_query[] = array(
            'key'     => '_gpe_provincia_evento',
            'value'   => $territorio['provincia'],
            'compare' => '=',
        );
        $meta_query['relation'] = 'AND';
    }

    $query->set( 'meta_query', $meta_query );
}