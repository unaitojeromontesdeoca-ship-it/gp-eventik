<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Inscribirse a un evento ──────────────────────────────────────────────────
add_action( 'wp_ajax_gpe_inscribirse',        'gpe_ajax_inscribirse' );
add_action( 'wp_ajax_nopriv_gpe_inscribirse', 'gpe_ajax_inscribirse' );
function gpe_ajax_inscribirse() {
    if ( ! isset($_POST['gpe_nonce']) || ! wp_verify_nonce( $_POST['gpe_nonce'], 'gpe_inscripcion_nonce' ) ) {
        wp_send_json_error( array('msg' => 'Token de seguridad inválido.') );
    }
    $evento_id = intval( $_POST['evento_id'] ?? 0 );
    if ( ! $evento_id ) wp_send_json_error( array('msg' => 'Evento no válido.') );

    $resultado = gpe_procesar_inscripcion( $evento_id, $_POST );
    if ( $resultado['ok'] ) {
        wp_send_json_success( $resultado );
    } else {
        wp_send_json_error( $resultado );
    }
}

// ─── Crear ponente rápido desde el modal del evento ──────────────────────────
add_action( 'wp_ajax_gpe_crear_ponente_rapido', 'gpe_ajax_crear_ponente_rapido' );
function gpe_ajax_crear_ponente_rapido() {
    check_ajax_referer( 'gpe_ajax_nonce', 'nonce' );
    if ( ! current_user_can( 'edit_gpe_ponentes' ) && ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array('message' => 'Sin permisos.') );
    }

    $nombre   = sanitize_text_field( $_POST['nombre'] ?? '' );
    $apellidos = sanitize_text_field( $_POST['apellidos'] ?? '' );
    $cargo    = sanitize_text_field( $_POST['cargo'] ?? '' );
    $frase    = sanitize_text_field( $_POST['frase'] ?? '' );
    $tema     = sanitize_text_field( $_POST['tema'] ?? '' );

    if ( empty($nombre) ) wp_send_json_error( array('message' => 'El nombre es obligatorio.') );

    $post_id = wp_insert_post( array(
        'post_title'  => trim($nombre . ' ' . $apellidos),
        'post_type'   => 'gpe_contacto',
        'post_status' => 'publish',
    ) );

    if ( is_wp_error($post_id) ) wp_send_json_error( array('message' => 'Error al crear el ponente.') );

    update_post_meta( $post_id, '_gpe_basico_nombre',    $nombre );
    update_post_meta( $post_id, '_gpe_basico_apellidos', $apellidos );
    update_post_meta( $post_id, '_gpe_basico_cargo',     $cargo );
    update_post_meta( $post_id, '_gpe_quote_frase',      $frase );
    update_post_meta( $post_id, '_gpe_quote_debate',     $tema );

    wp_send_json_success( array(
        'id'     => $post_id,
        'nombre' => trim($nombre . ' ' . $apellidos),
        'cargo'  => $cargo,
    ) );
}

// ─── Enviar emails institucionales (lote) ────────────────────────────────────
add_action( 'wp_ajax_gpe_enviar_emails_inst', 'gpe_ajax_enviar_emails_inst' );
function gpe_ajax_enviar_emails_inst() {
    check_ajax_referer( 'gpe_inst_nonce', 'nonce' );
    if ( ! current_user_can('manage_options') ) wp_send_json_error( array('msg'=>'Sin permisos.') );

    $evento_id = intval( $_POST['evento_id'] ?? 0 );
    $emails    = array_map( 'sanitize_email', explode( "\n", $_POST['emails'] ?? '' ) );
    $emails    = array_filter( $emails );

    if ( ! $evento_id || empty($emails) ) wp_send_json_error( array('msg' => 'Datos incompletos.') );

    $enviados = 0;
    $errores  = 0;
    foreach ( $emails as $email ) {
        $ok = gpe_email_invitacion_institucional( $evento_id, $email );
        $ok ? $enviados++ : $errores++;
    }

    wp_send_json_success( array(
        'msg'      => "Enviados: $enviados | Errores: $errores",
        'enviados' => $enviados,
        'errores'  => $errores,
    ) );
}

// ─── Enqueue nonce para admin ─────────────────────────────────────────────────
add_action( 'admin_enqueue_scripts', 'gpe_admin_ajax_vars' );
function gpe_admin_ajax_vars() {
    $screen = get_current_screen();
    if ( ! $screen || strpos($screen->id, 'evento_home') === false && strpos($screen->id, 'gpe_') === false ) return;
    wp_localize_script( 'jquery', 'gpe_ajax', array(
        'url'   => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('gpe_ajax_nonce'),
    ) );
}

// ── AJAX: Buscar usuarios por nombre (para buscador en admin-organos) ────────
add_action('wp_ajax_gpe_buscar_usuarios', 'gpe_ajax_buscar_usuarios');
function gpe_ajax_buscar_usuarios() {
    check_ajax_referer('gpe_buscar_usuarios','nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();
    $q = sanitize_text_field($_POST['q'] ?? '');
    if (strlen($q) < 2) wp_send_json_error();
    $users = get_users(array(
        'search'         => '*' . $q . '*',
        'search_columns' => array('display_name','user_email','user_login'),
        'number'         => 10,
        'fields'         => array('ID','display_name','user_email'),
    ));
    $out = array();
    foreach ($users as $u) {
        $out[] = array('id'=>$u->ID,'name'=>$u->display_name,'email'=>$u->user_email);
    }
    wp_send_json_success($out);
}
