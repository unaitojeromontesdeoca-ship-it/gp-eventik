<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Puente a la API de GP Ambassadors.
 * Los órganos, miembros y su resolución territorial (congresos/asambleas)
 * ahora los gestiona GP Ambassadors. Aquí solo queda la lógica de invitación
 * de eventos internos, que consume la API pública gpa_api_*.
 */

// ── ¿El usuario está invitado a un evento interno? ───────────────────────────
function gpe_usuario_invitado_interno( $user_id, $evento_id ) {
    $invitados = get_post_meta( $evento_id, '_gpe_organos_invitados', true ) ?: array();
    if ( empty( $invitados ) ) return false;
    if ( ! function_exists( 'gpa_api_get_organos_usuario' ) ) return false;

    $del_usuario = array_map( fn( $o ) => (int) $o['id'], gpa_api_get_organos_usuario( $user_id ) );
    foreach ( $invitados as $org_id ) {
        if ( in_array( (int) $org_id, $del_usuario, true ) ) return true;
    }
    return false;
}

// ── Todos los usuarios invitados a un evento interno ─────────────────────────
function gpe_usuarios_invitados_interno( $evento_id ) {
    $invitados = get_post_meta( $evento_id, '_gpe_organos_invitados', true ) ?: array();
    if ( empty( $invitados ) || ! function_exists( 'gpa_api_get_miembros' ) ) return array();

    $out = array();
    foreach ( $invitados as $org_id ) {
        foreach ( gpa_api_get_miembros( (int) $org_id ) as $m ) {
            $out[ $m['user_id'] ] = (object) array(
                'ID'           => $m['user_id'],
                'display_name' => $m['display_name'],
                'user_email'   => $m['user_email'],
            );
        }
    }
    return array_values( $out );
}