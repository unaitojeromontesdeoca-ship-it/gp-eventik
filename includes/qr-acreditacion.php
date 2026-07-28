<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * QR de acreditación
 * Genera un QR único por inscripción usando la API de qr-server.com (gratuita, sin clave).
 * El QR se incluye en el email de confirmación de inscripción.
 */

// ── Generar URL del QR a partir del token de inscripción ─────────────────────
function gpe_qr_url( $token, $evento_id ) {
    // El QR contiene una URL de verificación en la web + datos codificados
    $datos  = base64_encode( json_encode( array(
        'token'    => $token,
        'evento'   => $evento_id,
        'ver'      => '1',
    ) ) );
    $url_verificacion = add_query_arg( array(
        'gpe_acred' => '1',
        'data'      => $datos,
    ), home_url('/') );

    // API pública de QR Server — sin límite de uso razonable
    return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode( $url_verificacion );
}

// ── HTML del QR para incluir en emails ───────────────────────────────────────
function gpe_qr_html_email( $token, $evento_id, $nombre, $evento_titulo, $fecha_fmt, $lugar ) {
    $qr_url = gpe_qr_url( $token, $evento_id );
    return '
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafb;border-radius:12px;padding:24px;margin:20px 0;border:1px solid rgba(0,122,135,0.1);">
        <tr>
            <td align="center">
                <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#007a87;margin-bottom:12px;">ACREDITACIÓN OFICIAL</div>
                <img src="' . esc_url($qr_url) . '" width="160" height="160" alt="QR Acreditación" style="display:block;margin:0 auto 16px;border-radius:8px;">
                <div style="font-size:1.1rem;font-weight:800;color:#1a1a1a;margin-bottom:4px;">' . esc_html($nombre) . '</div>
                <div style="font-size:13px;color:#007a87;font-weight:700;margin-bottom:12px;">' . esc_html($evento_titulo) . '</div>
                <div style="font-size:12px;color:#666;line-height:1.8;">
                    ' . ($fecha_fmt ? '📅 ' . esc_html($fecha_fmt) . '<br>' : '') . '
                    ' . ($lugar     ? '📍 ' . esc_html($lugar)     . '<br>' : '') . '
                </div>
                <div style="margin-top:12px;font-size:11px;color:#aaa;">Muestra este código en la entrada</div>
            </td>
        </tr>
    </table>';
}

// ── Verificación QR — multiescaneo, contador, caducidad 5 días ────────────────
add_action( 'init', 'gpe_verificar_acreditacion' );
function gpe_verificar_acreditacion() {
    if ( ! isset($_GET['gpe_acred']) || ! isset($_GET['data']) ) return;

    $datos_raw = base64_decode( sanitize_text_field($_GET['data']) );
    if ( ! $datos_raw ) wp_die('QR inválido.');

    $datos = json_decode( $datos_raw, true );
    if ( ! $datos || empty($datos['token']) ) wp_die('QR inválido.');

    if ( ! current_user_can('manage_options') && ! current_user_can('access_gpe_panel') ) {
        wp_die('No tienes permisos para verificar acreditaciones.');
    }

    global $wpdb;
    $insc = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE token = %s",
        $datos['token']
    ) );

    if ( ! $insc ) wp_die('Acreditación no encontrada.');

    $evento       = get_post( $insc->evento_id );
    $fecha_evento = get_post_meta( $insc->evento_id, '_med_fecha_evento', true );

    // Caducidad: 5 días después del evento
    $caducado = false;
    if ( $fecha_evento && time() > strtotime($fecha_evento) + (5 * 86400) ) {
        $caducado = true;
    }

    // Contador multiescaneo
    $meta_key   = '_gpe_qr_scans_' . $datos['token'];
    $scan_count = (int) get_option( $meta_key, 0 );
    if ( ! $caducado && $insc->estado === 'confirmada' ) {
        $scan_count++;
        update_option( $meta_key, $scan_count, false );
    }

    $valido        = $insc->estado === 'confirmada' && ! $caducado;
    $estado_color  = $valido ? '#27ae60' : '#c0392b';
    $estado_label  = $valido ? '✅ VÁLIDA' : ( $caducado ? '⏰ QR CADUCADO' : '❌ ' . strtoupper($insc->estado) );
    $ya_escaneado  = $valido && $scan_count > 1;

    ?><!DOCTYPE html>
    <html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verificación</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
    *{box-sizing:border-box;}
    body{font-family:'Inter',sans-serif;background:#f5f5f5;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;}
    .box{background:#fff;border-radius:20px;padding:36px 28px;max-width:420px;width:100%;text-align:center;box-shadow:0 10px 40px rgba(0,0,0,0.1);}
    .estado{font-size:1.7rem;font-weight:900;padding:13px 26px;border-radius:50px;color:#fff;margin-bottom:16px;display:inline-block;}
    .aviso-naranja{background:#e67e22;color:#fff;border-radius:8px;padding:9px 14px;font-size:14px;font-weight:700;margin-bottom:16px;display:inline-block;}
    .campo{margin-bottom:8px;font-size:15px;color:#555;}
    .campo strong{color:#1a1a1a;}
    </style>
    </head>
    <body>
    <div class="box">
        <div class="estado" style="background:<?php echo $estado_color; ?>"><?php echo $estado_label; ?></div>
        <?php if ($ya_escaneado) : ?>
        <div class="aviso-naranja">⚠️ Ya escaneado <?php echo $scan_count; ?> veces</div>
        <?php endif; ?>
        <h2 style="margin:0 0 16px;font-size:1.2rem;"><?php echo esc_html($evento ? $evento->post_title : '—'); ?></h2>
        <div class="campo"><strong><?php echo esc_html($insc->nombre . ' ' . $insc->apellidos); ?></strong></div>
        <div class="campo"><?php echo esc_html($insc->email); ?></div>
        <?php if ($insc->ccaa) : ?><div class="campo"><?php echo esc_html($insc->ccaa); ?><?php if ($insc->provincia) echo ' · ' . esc_html($insc->provincia); ?></div><?php endif; ?>
        <div class="campo" style="margin-top:14px;font-size:13px;color:#aaa;">Inscrito el <?php echo date('d/m/Y H:i', strtotime($insc->fecha_reg)); ?></div>
        <?php if ($caducado) : ?>
        <div style="margin-top:12px;font-size:13px;color:#c0392b;font-weight:600;">QR caducado (5 días tras el evento).</div>
        <?php endif; ?>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// ── Añadir QR al email de confirmación de inscripción ────────────────────────
// Reemplaza la función en emails.php añadiendo el QR
add_filter( 'gpe_email_confirmacion_extra', 'gpe_añadir_qr_email', 10, 4 );
function gpe_añadir_qr_email( $extra_html, $evento_id, $datos, $token ) {
    $evento    = get_post($evento_id);
    $fecha     = get_post_meta($evento_id, '_med_fecha_evento', true);
    $lugar     = get_post_meta($evento_id, '_gpe_lugar_nombre', true) ?: get_post_meta($evento_id, '_med_provincia_sitio', true);
    $fecha_fmt = $fecha ? date_i18n(get_option('date_format'), strtotime($fecha)) : '';
    $nombre    = trim( ($datos['nombre'] ?? '') . ' ' . ($datos['apellidos'] ?? '') );

    return $extra_html . gpe_qr_html_email( $token, $evento_id, $nombre, $evento->post_title, $fecha_fmt, $lugar );
}
