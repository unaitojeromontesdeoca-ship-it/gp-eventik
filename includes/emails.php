<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Configuración general de emails ─────────────────────────────────────────

function gpe_email_from_name( $name )  { return get_bloginfo('name') . ' · Generación Presente'; }
function gpe_email_from_email( $email ){ return get_option('admin_email'); }
// Solo aplicamos los filtros durante nuestros envíos, no globalmente
function gpe_enviar_email( $to, $asunto, $html_body ) {
    add_filter( 'wp_mail_from',      'gpe_email_from_email' );
    add_filter( 'wp_mail_from_name', 'gpe_email_from_name' );
    add_filter( 'wp_mail_content_type', function(){ return 'text/html'; } );

    $headers = array( 'Content-Type: text/html; charset=UTF-8' );
    $result  = wp_mail( $to, $asunto, $html_body, $headers );

    remove_filter( 'wp_mail_from',      'gpe_email_from_email' );
    remove_filter( 'wp_mail_from_name', 'gpe_email_from_name' );
    return $result;
}

// ─── Plantilla base HTML de email ─────────────────────────────────────────────

function gpe_email_wrapper( $contenido, $titulo = '' ) {
    $color  = '#007a87';
    $nombre_org = get_bloginfo('name');
    $url_org    = home_url();
    return '<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width"><title>' . esc_html($titulo) . '</title></head>
<body style="margin:0; padding:0; background:#f4f6f8; font-family: Arial, sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8; padding: 30px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.08);">
        <tr>
          <td style="background:linear-gradient(135deg,' . $color . ',#00b4cc); padding: 30px 40px; text-align:center;">
            <h1 style="color:#ffffff; margin:0; font-size:22px; font-weight:800;">' . esc_html($nombre_org) . '</h1>
          </td>
        </tr>
        <tr>
          <td style="padding: 35px 40px; color:#1a1a1a; font-size:15px; line-height:1.7;">
            ' . $contenido . '
          </td>
        </tr>
        <tr>
          <td style="padding: 20px 40px; background:#f8fafb; border-top:1px solid #eee; text-align:center; color:#999; font-size:12px;">
            <a href="' . esc_url($url_org) . '" style="color:' . $color . '; text-decoration:none;">' . esc_html($nombre_org) . '</a> · Asociación juvenil
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body></html>';
}

// ─── Email: confirmación de inscripción ───────────────────────────────────────

function gpe_email_confirmacion_inscripcion( $evento_id, $datos, $token ) {
    $evento    = get_post( $evento_id );
    $nombre    = sanitize_text_field( $datos['nombre'] );
    $apellidos = sanitize_text_field( $datos['apellidos'] ?? '' );
    $email     = sanitize_email( $datos['email'] );
    $fecha     = get_post_meta( $evento_id, '_med_fecha_evento', true );
    $lugar     = get_post_meta( $evento_id, '_gpe_lugar_nombre', true ) ?: get_post_meta( $evento_id, '_med_provincia_sitio', true );
    $direccion = get_post_meta( $evento_id, '_gpe_direccion', true );
    $url_ev    = get_permalink( $evento_id );
    $url_cancel = add_query_arg( array( 'gpe_cancelar' => 1, 'token' => $token ), $url_ev );

    $fecha_fmt = $fecha ? date_i18n( get_option('date_format'), strtotime($fecha) ) : '—';
    $hora      = get_post_meta( $evento_id, '_med_hora_evento', true );

    // Enlace a la acreditación imprimible (sustituye al QR inline)
    $nombre_completo = trim($nombre . ' ' . $apellidos);
    $url_acred = function_exists('gpe_acreditacion_url') ? gpe_acreditacion_url($token, 'externa') : '';
    $qr_url    = function_exists('gpe_qr_url') ? gpe_qr_url($token, $evento_id) : '';
    $qr_html   = '';
    if ($url_acred) {
        $qr_html = '
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafb;border-radius:12px;padding:24px;margin:20px 0;border:1px solid rgba(0,122,135,0.1);">
        <tr><td align="center">
            <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#007a87;margin-bottom:12px;">ACREDITACIÓN OFICIAL</div>
            ' . ($qr_url ? '<img src="' . esc_url($qr_url) . '" width="140" height="140" alt="QR" style="display:block;margin:0 auto 14px;border-radius:8px;">' : '') . '
            <div style="font-size:1rem;font-weight:800;color:#1a1a1a;margin-bottom:4px;">' . esc_html($nombre_completo) . '</div>
            <div style="font-size:13px;color:#007a87;font-weight:700;margin-bottom:14px;">' . esc_html($evento->post_title) . '</div>
            <a href="' . esc_url($url_acred) . '" style="background:linear-gradient(135deg,#007a87,#00b4cc);color:#fff;padding:11px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">🖨 Descargar acreditación</a>
            <div style="margin-top:12px;font-size:11px;color:#aaa;">Imprime esta acreditación o guárdala como PDF y preséntala en la entrada</div>
        </td></tr>
    </table>';
    }

    $contenido = '
        <p>Hola <strong>' . esc_html($nombre) . '</strong>,</p>
        <p>Tu inscripción al evento <strong>' . esc_html($evento->post_title) . '</strong> ha sido confirmada. 🎉</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9fa; border-radius:10px; padding:20px; margin:20px 0;">
            <tr><td style="padding:5px 0;"><strong>📅 Fecha:</strong> ' . esc_html($fecha_fmt) . ($hora ? ' a las ' . esc_html($hora) . 'h' : '') . '</td></tr>
            <tr><td style="padding:5px 0;"><strong>📍 Lugar:</strong> ' . esc_html($lugar ?: '—') . '</td></tr>
            ' . ($direccion ? '<tr><td style="padding:5px 0; font-size:13px; color:#666;">' . esc_html($direccion) . '</td></tr>' : '') . '
        </table>
        ' . $qr_html . '
        <p style="text-align:center; margin:25px 0;">
            <a href="' . esc_url($url_ev) . '" style="background:linear-gradient(135deg,#007a87,#00b4cc); color:#fff; padding:14px 30px; border-radius:30px; text-decoration:none; font-weight:700; font-size:15px;">Ver página del evento</a>
        </p>
        <p style="font-size:13px; color:#888; text-align:center;">¿No puedes asistir? <a href="' . esc_url($url_cancel) . '" style="color:#c0392b;">Cancela tu inscripción aquí</a>.</p>
    ';

    $asunto = '✅ Inscripción confirmada: ' . $evento->post_title;
    gpe_enviar_email( $email, $asunto, gpe_email_wrapper( $contenido, $asunto ) );

    // Notificación inmediata al coordinador
    $coord_email = get_post_meta( $evento_id, '_gpe_email_coordinador', true );
    if ( $coord_email ) {
        $notif = '<p>Nueva inscripción en <strong>' . esc_html($evento->post_title) . '</strong>:</p>
                  <p><strong>' . esc_html($datos['nombre'] . ' ' . ($datos['apellidos']??'')) . '</strong><br>
                  Email: ' . esc_html($email) . '<br>
                  CCAA: ' . esc_html($datos['ccaa'] ?? '—') . '</p>
                  <p><a href="' . esc_url( admin_url('edit.php?post_type=evento_home&page=gpe-inscritos&evento_id=' . $evento_id) ) . '">Ver todos los inscritos</a></p>';
        gpe_enviar_email( $coord_email, 'Nueva inscripción: ' . $evento->post_title, gpe_email_wrapper($notif, 'Nueva inscripción') );
    }
}

// ─── Email: invitación institucional personalizada ────────────────────────────

function gpe_email_invitacion_institucional( $evento_inst_id, $destinatario_email, $datos_persona = array() ) {
    global $wpdb;

    $evento   = get_post( $evento_inst_id );
    $asunto   = get_post_meta( $evento_inst_id, '_gpe_inst_asunto', true ) ?: 'Invitación: ' . $evento->post_title;
    $cuerpo   = get_post_meta( $evento_inst_id, '_gpe_inst_cuerpo', true );
    $fecha    = get_post_meta( $evento_inst_id, '_gpe_inst_fecha', true );
    $lugar    = get_post_meta( $evento_inst_id, '_gpe_inst_lugar', true );
    $url_rsvp = get_post_meta( $evento_inst_id, '_gpe_inst_url_rsvp', true );

    // Reemplazar variables dinámicas {{nombre}}, {{ccaa}}, etc.
    $nombre = $datos_persona['nombre'] ?? '';
    $cuerpo = str_replace(
        array('{{nombre}}', '{{ccaa}}', '{{provincia}}', '{{fecha}}', '{{lugar}}'),
        array(
            esc_html($nombre),
            esc_html($datos_persona['ccaa'] ?? ''),
            esc_html($datos_persona['provincia'] ?? ''),
            $fecha ? date_i18n( get_option('date_format'), strtotime($fecha) ) : '—',
            esc_html($lugar ?: '—'),
        ),
        $cuerpo
    );

    $contenido = '
        <p>' . nl2br( wp_kses_post($cuerpo) ) . '</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9fa; border-radius:10px; padding:20px; margin:20px 0;">
            <tr><td style="padding:5px 0;"><strong>📅 Fecha:</strong> ' . ( $fecha ? esc_html( date_i18n( get_option('date_format'), strtotime($fecha) ) ) : '—' ) . '</td></tr>
            <tr><td style="padding:5px 0;"><strong>📍 Lugar:</strong> ' . esc_html($lugar ?: '—') . '</td></tr>
        </table>
        ' . ($url_rsvp ? '<p style="text-align:center; margin:25px 0;"><a href="' . esc_url($url_rsvp) . '" style="background:linear-gradient(135deg,#007a87,#00b4cc); color:#fff; padding:14px 30px; border-radius:30px; text-decoration:none; font-weight:700; font-size:15px;">Confirmar asistencia</a></p>' : '') . '
    ';

    $ok = gpe_enviar_email( $destinatario_email, $asunto, gpe_email_wrapper($contenido, $asunto) );

    // Log del envío
    $wpdb->insert( $wpdb->prefix . 'gpe_emails_log', array(
        'evento_id'    => $evento_inst_id,
        'destinatario' => $destinatario_email,
        'asunto'       => $asunto,
        'estado'       => $ok ? 'enviado' : 'error',
    ), array('%d','%s','%s','%s') );

    return $ok;
}
