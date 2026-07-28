<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tareas programadas:
 * 1. Resumen diario de inscripciones al admin a las 20:00
 * 2. Recordatorio automático 48h antes del evento a todos los inscritos
 */

// ── Registrar cron events en la activación ────────────────────────────────────
function gpe_programar_crons() {
    if ( ! wp_next_scheduled('gpe_cron_resumen_diario') ) {
        // Calcular próximas 20:00 en la zona horaria del blog
        $tz     = get_option('timezone_string') ?: 'Europe/Madrid';
        $ahora  = new DateTime('now', new DateTimeZone($tz));
        $target = new DateTime('today 20:00', new DateTimeZone($tz));
        if ( $ahora >= $target ) $target->modify('+1 day');
        wp_schedule_event( $target->getTimestamp(), 'daily', 'gpe_cron_resumen_diario' );
    }
    if ( ! wp_next_scheduled('gpe_cron_recordatorios') ) {
        wp_schedule_event( time(), 'hourly', 'gpe_cron_recordatorios' );
    }
}
add_action( 'init', 'gpe_programar_crons' );

// Limpiar crons al desactivar el plugin
register_deactivation_hook( GPE_PLUGIN_DIR . '../gp-eventiktak.php', 'gpe_limpiar_crons' );
function gpe_limpiar_crons() {
    wp_clear_scheduled_hook('gpe_cron_resumen_diario');
    wp_clear_scheduled_hook('gpe_cron_recordatorios');
}

// ── 1. Resumen diario al admin ────────────────────────────────────────────────
add_action( 'gpe_cron_resumen_diario', 'gpe_ejecutar_resumen_diario' );
function gpe_ejecutar_resumen_diario() {
    global $wpdb;

    $ayer_inicio = date('Y-m-d 00:00:00');
    $ayer_fin    = date('Y-m-d 23:59:59');

    // Inscripciones de hoy
    $inscritos_hoy = $wpdb->get_results( $wpdb->prepare(
        "SELECT i.*, p.post_title as evento_titulo
         FROM {$wpdb->prefix}gpe_inscripciones i
         LEFT JOIN {$wpdb->posts} p ON p.ID = i.evento_id
         WHERE i.fecha_reg BETWEEN %s AND %s
         AND i.estado = 'confirmada'
         ORDER BY i.evento_id ASC, i.fecha_reg ASC",
        $ayer_inicio, $ayer_fin
    ) );

    if ( empty($inscritos_hoy) ) return; // Sin inscripciones hoy, no enviar nada

    // Agrupar por evento
    $por_evento = array();
    foreach ( $inscritos_hoy as $i ) {
        $por_evento[$i->evento_titulo][] = $i;
    }

    $fecha_hoy = date_i18n( get_option('date_format'), current_time('timestamp') );
    $total     = count($inscritos_hoy);

    $tabla_eventos = '';
    foreach ( $por_evento as $titulo => $lista ) {
        $tabla_eventos .= '<tr style="background:#f8f8f8;"><td colspan="4" style="padding:10px 12px;font-weight:700;color:#007a87;border-top:2px solid #e0e0e0;">' . esc_html($titulo) . ' <span style="font-weight:400;color:#999;">(' . count($lista) . ' nuevos)</span></td></tr>';
        foreach ( $lista as $ins ) {
            $tabla_eventos .= '<tr>
                <td style="padding:8px 12px;border-bottom:1px solid #f0f0f0;">' . esc_html($ins->nombre . ' ' . $ins->apellidos) . '</td>
                <td style="padding:8px 12px;border-bottom:1px solid #f0f0f0;">' . esc_html($ins->email) . '</td>
                <td style="padding:8px 12px;border-bottom:1px solid #f0f0f0;">' . esc_html($ins->ccaa ?: '—') . '</td>
                <td style="padding:8px 12px;border-bottom:1px solid #f0f0f0;">' . esc_html(date('H:i', strtotime($ins->fecha_reg))) . '</td>
            </tr>';
        }
    }

    $contenido = '
        <p>Aquí tienes el resumen de inscripciones del día <strong>' . esc_html($fecha_hoy) . '</strong>.</p>
        <div style="background:#007a87;color:#fff;border-radius:10px;padding:16px;text-align:center;margin-bottom:20px;">
            <div style="font-size:2.5rem;font-weight:900;line-height:1;">' . $total . '</div>
            <div style="font-size:13px;opacity:.85;margin-top:4px;">inscripciones nuevas hoy</div>
        </div>
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border:1px solid #eee;border-radius:8px;overflow:hidden;">
            <thead>
                <tr style="background:#f0f0f0;">
                    <th style="padding:10px 12px;text-align:left;font-size:12px;color:#555;">Nombre</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;color:#555;">Email</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;color:#555;">CCAA</th>
                    <th style="padding:10px 12px;text-align:left;font-size:12px;color:#555;">Hora</th>
                </tr>
            </thead>
            <tbody>' . $tabla_eventos . '</tbody>
        </table>
        <p style="text-align:center;margin-top:20px;">
            <a href="' . esc_url(admin_url('edit.php?post_type=evento_home&page=gpe-inscritos')) . '" style="background:linear-gradient(135deg,#007a87,#00b4cc);color:#fff;padding:12px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">Ver panel de inscritos</a>
        </p>
    ';

    $admin_email = get_option('admin_email');
    $asunto = '[GP Eventik] Resumen de inscripciones — ' . $fecha_hoy;
    gpe_enviar_email( $admin_email, $asunto, gpe_email_wrapper($contenido, $asunto) );
}

// ── 2. Recordatorios 48h antes del evento ─────────────────────────────────────
add_action( 'gpe_cron_recordatorios', 'gpe_ejecutar_recordatorios' );
function gpe_ejecutar_recordatorios() {
    global $wpdb;

    // Eventos que ocurren en exactamente 48h (margen de ±1h para no lanzarlo dos veces)
    $desde = date('Y-m-d', strtotime('+47 hours'));
    $hasta = date('Y-m-d', strtotime('+49 hours'));

    $eventos = get_posts( array(
        'post_type'      => 'evento_home',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'meta_query'     => array(
            array( 'key' => '_med_fecha_evento', 'value' => array($desde, $hasta), 'compare' => 'BETWEEN', 'type' => 'DATE' ),
            array( 'key' => '_gpe_recordatorio_enviado', 'compare' => 'NOT EXISTS' ),
        ),
    ) );

    foreach ( $eventos as $evento ) {
        // Marcar antes de enviar para evitar duplicados
        update_post_meta( $evento->ID, '_gpe_recordatorio_enviado', date('Y-m-d H:i:s') );

        $inscritos = $wpdb->get_results( $wpdb->prepare(
            "SELECT nombre, apellidos, email, token FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id = %d AND estado = 'confirmada'",
            $evento->ID
        ) );

        foreach ( $inscritos as $ins ) {
            gpe_email_recordatorio( $evento->ID, $ins->nombre, $ins->email, $ins->token );
        }
    }
}

function gpe_email_recordatorio( $evento_id, $nombre, $email, $token ) {
    $evento    = get_post($evento_id);
    $fecha     = get_post_meta($evento_id, '_med_fecha_evento', true);
    $hora      = get_post_meta($evento_id, '_med_hora_evento',  true);
    $lugar     = get_post_meta($evento_id, '_gpe_lugar_nombre', true) ?: get_post_meta($evento_id, '_med_provincia_sitio', true);
    $direccion = get_post_meta($evento_id, '_gpe_direccion',    true);
    $url_ev    = get_permalink($evento_id);
    $url_cancel = add_query_arg( array('gpe_cancelar'=>1,'token'=>$token), $url_ev );
    $fecha_fmt = $fecha ? date_i18n(get_option('date_format'), strtotime($fecha)) : '—';

    // QR en el recordatorio también
    $qr_html = '';
    if ( function_exists('gpe_qr_html_email') ) {
        $qr_html = gpe_qr_html_email( $token, $evento_id, trim($nombre), $evento->post_title, $fecha_fmt, $lugar );
    }

    $contenido = '
        <p>Hola <strong>' . esc_html($nombre) . '</strong>,</p>
        <p>¡El evento <strong>' . esc_html($evento->post_title) . '</strong> es mañana! Aquí tienes toda la información:</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9fa;border-radius:10px;padding:20px;margin:20px 0;">
            <tr><td style="padding:5px 0;"><strong>📅 Fecha:</strong> ' . esc_html($fecha_fmt) . ($hora ? ' a las ' . esc_html($hora) . 'h' : '') . '</td></tr>
            <tr><td style="padding:5px 0;"><strong>📍 Lugar:</strong> ' . esc_html($lugar ?: '—') . '</td></tr>
            ' . ($direccion ? '<tr><td style="padding:5px 0;font-size:13px;color:#666;">' . esc_html($direccion) . '</td></tr>' : '') . '
        </table>
        ' . $qr_html . '
        <p style="text-align:center;margin:25px 0;">
            <a href="' . esc_url($url_ev) . '" style="background:linear-gradient(135deg,#007a87,#00b4cc);color:#fff;padding:14px 30px;border-radius:30px;text-decoration:none;font-weight:700;font-size:15px;">Ver página del evento</a>
        </p>
        <p style="font-size:13px;color:#888;text-align:center;">¿No puedes asistir? <a href="' . esc_url($url_cancel) . '" style="color:#c0392b;">Cancela aquí</a> para liberar tu plaza.</p>
    ';

    $asunto = '⏰ Mañana: ' . $evento->post_title;
    gpe_enviar_email( $email, $asunto, gpe_email_wrapper($contenido, $asunto) );
}
