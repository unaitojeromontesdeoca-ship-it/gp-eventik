<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generador de acreditaciones — GP Eventik v3.5
 * Genera HTML de acreditación optimizado para imprimir/exportar a PDF.
 * Se sirve como página web y se adjunta en emails como enlace de descarga.
 * Compatible con cualquier instalación WordPress sin dependencias externas.
 */

// ── URL pública de la acreditación ───────────────────────────────────────────
function gpe_acreditacion_url( $token, $tipo = 'externa' ) {
    return add_query_arg(array(
        'gpe_acred_pdf' => '1',
        'token'         => $token,
        'tipo'          => $tipo,
    ), home_url('/'));
}

// ── Endpoint: servir la acreditación como HTML imprimible ────────────────────
add_action('init', 'gpe_servir_acreditacion_pdf');
function gpe_servir_acreditacion_pdf() {
    if ( ! isset($_GET['gpe_acred_pdf']) || ! isset($_GET['token']) ) return;

    $token = sanitize_text_field($_GET['token']);
    $tipo  = sanitize_text_field($_GET['tipo'] ?? 'externa');

    global $wpdb;

    if ( $tipo === 'interna' ) {
        $insc = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}gpe_inscripciones_internas WHERE token=%s",
            $token
        ));
        if (!$insc || $insc->estado !== 'confirmada') wp_die('Acreditación no encontrada.');
        $user     = get_userdata($insc->user_id);
        $nombre   = $user ? $user->display_name : '—';
        $email    = $user ? $user->user_email : '';
        $evento   = get_post($insc->evento_id);
        $titulo   = $evento ? $evento->post_title : '—';
        $fecha_r  = get_post_meta($insc->evento_id,'_gpe_int_fecha',true);
        $lugar    = get_post_meta($insc->evento_id,'_gpe_int_lugar',true);
        $ccaa     = get_user_meta($insc->user_id,'_gpe_ccaa',true);
        $provincia= get_user_meta($insc->user_id,'_gpe_provincia',true);
        $cargo    = $insc->cargo ?? '';
        if (!$cargo) {
            // Buscar cargo en órganos del evento
            $org_ids = get_post_meta($insc->evento_id,'_gpe_organos_invitados',true) ?: array();
            foreach ($org_ids as $oid) {
                $m = $wpdb->get_row($wpdb->prepare(
                    "SELECT cargo FROM {$wpdb->prefix}gpe_organo_miembros WHERE organo_id=%d AND user_id=%d AND activo=1",
                    intval($oid), $insc->user_id
                ));
                if ($m && $m->cargo) { $cargo = $m->cargo; break; }
            }
        }
        $modalidad_labels = array('presencial'=>'Presencial','telematica'=>'Telemática','mixta'=>'Mixta');
        $modalidad = $modalidad_labels[$insc->modalidad] ?? $insc->modalidad;
        $qr_url   = gpe_qr_url($token, $insc->evento_id);
        gpe_render_acreditacion_interna($nombre,$email,$titulo,$fecha_r,$lugar,$ccaa,$provincia,$cargo,$modalidad,$qr_url,$token);
    } else {
        $insc = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE token=%s",
            $token
        ));
        if (!$insc || $insc->estado !== 'confirmada') wp_die('Acreditación no encontrada.');
        $nombre   = trim($insc->nombre . ' ' . $insc->apellidos);
        $evento   = get_post($insc->evento_id);
        $titulo   = $evento ? $evento->post_title : '—';
        $fecha_r  = get_post_meta($insc->evento_id,'_med_fecha_evento',true);
        $lugar    = get_post_meta($insc->evento_id,'_gpe_lugar_nombre',true);
        $qr_url   = gpe_qr_url($token, $insc->evento_id);
        gpe_render_acreditacion_externa($nombre,$insc->email,$titulo,$fecha_r,$lugar,$insc->ccaa,$insc->provincia,$qr_url,$token);
    }
    exit;
}

// ── Acreditación EXTERNA (evento público) — estilo entrada ───────────────────
function gpe_render_acreditacion_externa($nombre,$email,$titulo,$fecha_r,$lugar,$ccaa,$provincia,$qr_url,$token) {
    $org   = get_bloginfo('name');
    $fecha = $fecha_r ? date_i18n('l, j \d\e F \d\e Y', strtotime($fecha_r)) : '—';
    $color = '#007a87';
    header('Content-Type: text/html; charset=UTF-8');
    ?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acreditación — <?php echo esc_html($titulo); ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f0f0f0; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
.ticket { width: 680px; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,0,0,0.15); }
.ticket-top { background: linear-gradient(135deg, <?php echo $color; ?>, #00b4cc); padding: 32px 36px; color: #fff; }
.ticket-org { font-size: 11px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; opacity: .8; margin-bottom: 8px; }
.ticket-evento { font-size: 1.6rem; font-weight: 900; line-height: 1.2; margin-bottom: 6px; }
.ticket-sub { font-size: 13px; opacity: .85; }
.ticket-body { display: flex; gap: 0; }
.ticket-info { flex: 1; padding: 28px 32px; border-right: 2px dashed #e0e0e0; }
.ticket-qr-col { width: 200px; padding: 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; background: #fafafa; }
.ticket-asistente-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #999; margin-bottom: 4px; }
.ticket-nombre { font-size: 1.25rem; font-weight: 900; color: #1a1a1a; margin-bottom: 2px; }
.ticket-email { font-size: 12px; color: #888; margin-bottom: 16px; }
.ticket-dato { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px; font-size: 13px; color: #444; }
.ticket-dato-icon { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
.ticket-dato-text strong { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #999; margin-bottom: 1px; }
.ticket-qr img { width: 140px; height: 140px; border-radius: 8px; }
.ticket-qr-label { font-size: 10px; color: #aaa; text-align: center; font-weight: 600; letter-spacing: .5px; }
.ticket-token { font-size: 9px; color: #ccc; font-family: monospace; margin-top: 4px; }
.ticket-footer { background: <?php echo $color; ?>; color: rgba(255,255,255,.7); text-align: center; padding: 10px; font-size: 11px; letter-spacing: .5px; }
.no-print { text-align: center; margin-bottom: 20px; }
.btn-print { background: <?php echo $color; ?>; color: #fff; border: none; padding: 12px 28px; border-radius: 30px; font-size: 14px; font-weight: 700; cursor: pointer; }
@media print { body { background: #fff; padding: 0; } .no-print { display: none; } .ticket { box-shadow: none; width: 100%; } }
</style>
</head>
<body>
<div>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar como PDF</button>
</div>
<div class="ticket">
    <div class="ticket-top">
        <div class="ticket-org"><?php echo esc_html($org); ?></div>
        <div class="ticket-evento"><?php echo esc_html($titulo); ?></div>
        <div class="ticket-sub">Acreditación oficial de asistencia</div>
    </div>
    <div class="ticket-body">
        <div class="ticket-info">
            <div class="ticket-asistente-label">Asistente</div>
            <div class="ticket-nombre"><?php echo esc_html($nombre); ?></div>
            <div class="ticket-email"><?php echo esc_html($email); ?></div>

            <div class="ticket-dato">
                <span class="ticket-dato-icon">📅</span>
                <div class="ticket-dato-text"><strong>Fecha</strong><?php echo esc_html($fecha); ?></div>
            </div>
            <?php if ($lugar) : ?>
            <div class="ticket-dato">
                <span class="ticket-dato-icon">📍</span>
                <div class="ticket-dato-text"><strong>Lugar</strong><?php echo esc_html($lugar); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($ccaa || $provincia) : ?>
            <div class="ticket-dato">
                <span class="ticket-dato-icon">🗺️</span>
                <div class="ticket-dato-text"><strong>Procedencia</strong><?php echo esc_html(implode(' · ', array_filter([$ccaa, $provincia]))); ?></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="ticket-qr-col">
            <img src="<?php echo esc_url($qr_url); ?>" alt="QR Acreditación">
            <div class="ticket-qr-label">ESCANEAR EN ENTRADA</div>
            <div class="ticket-token"><?php echo esc_html(strtoupper(substr($token,0,8))); ?></div>
        </div>
    </div>
    <div class="ticket-footer"><?php echo esc_html($org); ?> · Muestra esta acreditación en la entrada</div>
</div>
</div>
</body>
</html>
<?php
}

// ── Acreditación INTERNA — formato para imprimir, estilo credencial ──────────
function gpe_render_acreditacion_interna($nombre,$email,$titulo,$fecha_r,$lugar,$ccaa,$provincia,$cargo,$modalidad,$qr_url,$token) {
    $org   = get_bloginfo('name');
    $fecha = $fecha_r ? date_i18n('l, j \d\e F \d\e Y', strtotime($fecha_r)) : '—';
    $color = '#1a1a1a';
    header('Content-Type: text/html; charset=UTF-8');
    ?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Credencial — <?php echo esc_html($titulo); ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #f0f0f0; display: flex; flex-direction: column; align-items: center; padding: 20px; min-height: 100vh; }
.credencial { width: 340px; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 30px rgba(0,0,0,0.15); border-top: 6px solid #007a87; }
.cred-header { background: #1a1a1a; color: #fff; padding: 18px 20px; text-align: center; }
.cred-org { font-size: 10px; letter-spacing: 2px; text-transform: uppercase; opacity: .6; margin-bottom: 4px; }
.cred-evento { font-size: 13px; font-weight: 800; line-height: 1.3; }
.cred-interno-badge { background: #007a87; color: #fff; font-size: 9px; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; padding: 3px 10px; border-radius: 20px; display: inline-block; margin-top: 6px; }
.cred-foto { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg,#007a87,#00b4cc); color: #fff; font-size: 2rem; font-weight: 900; display: flex; align-items: center; justify-content: center; margin: 20px auto 12px; }
.cred-nombre { text-align: center; font-size: 1.1rem; font-weight: 900; color: #1a1a1a; padding: 0 16px; }
.cred-cargo { text-align: center; background: #007a87; color: #fff; font-size: 11px; font-weight: 800; letter-spacing: .5px; padding: 5px 20px; border-radius: 20px; display: inline-block; margin: 8px auto 16px; }
.cred-datos { padding: 0 20px 16px; border-top: 1px solid #f0f0f0; margin-top: 4px; }
.cred-dato { display: flex; gap: 8px; padding: 8px 0; border-bottom: 1px solid #f9f9f9; font-size: 12px; color: #555; }
.cred-dato-icon { font-size: 14px; flex-shrink: 0; }
.cred-dato strong { display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; color: #aaa; margin-bottom: 1px; }
.cred-qr { text-align: center; padding: 16px 20px 20px; background: #fafafa; border-top: 1px solid #f0f0f0; }
.cred-qr img { width: 110px; height: 110px; border-radius: 6px; }
.cred-qr-label { font-size: 9px; color: #bbb; margin-top: 6px; letter-spacing: 1px; text-transform: uppercase; font-weight: 700; }
.cred-footer { background: #007a87; color: rgba(255,255,255,.8); text-align: center; padding: 8px; font-size: 10px; letter-spacing: .5px; }
.no-print { text-align: center; margin-bottom: 20px; }
.btn-print { background: #1a1a1a; color: #fff; border: none; padding: 12px 28px; border-radius: 30px; font-size: 14px; font-weight: 700; cursor: pointer; }
@media print { body { background: #fff; padding: 0; } .no-print { display: none; } .credencial { box-shadow: none; } }
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar como PDF</button>
    <p style="margin-top:10px;font-size:12px;color:#888;">Imprime en A4 o guarda como PDF. Tamaño tarjeta.</p>
</div>
<div class="credencial">
    <div class="cred-header">
        <div class="cred-org"><?php echo esc_html($org); ?></div>
        <div class="cred-evento"><?php echo esc_html($titulo); ?></div>
        <div><span class="cred-interno-badge">Evento Interno</span></div>
    </div>

    <?php
    $iniciales = '';
    $parts = explode(' ', $nombre);
    foreach (array_slice($parts,0,2) as $p) $iniciales .= strtoupper(substr($p,0,1));
    ?>
    <div class="cred-foto"><?php echo esc_html($iniciales ?: '?'); ?></div>
    <div class="cred-nombre"><?php echo esc_html($nombre); ?></div>
    <?php if ($cargo) : ?>
    <div style="text-align:center;"><span class="cred-cargo"><?php echo esc_html($cargo); ?></span></div>
    <?php endif; ?>

    <div class="cred-datos">
        <div class="cred-dato">
            <span class="cred-dato-icon">📅</span>
            <div><strong>Fecha</strong><?php echo esc_html($fecha); ?></div>
        </div>
        <?php if ($lugar) : ?>
        <div class="cred-dato">
            <span class="cred-dato-icon">📍</span>
            <div><strong>Lugar</strong><?php echo esc_html($lugar); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($ccaa || $provincia) : ?>
        <div class="cred-dato">
            <span class="cred-dato-icon">🗺️</span>
            <div><strong>Procedencia</strong><?php echo esc_html(implode(' · ', array_filter([$ccaa,$provincia]))); ?></div>
        </div>
        <?php endif; ?>
        <?php if ($modalidad) : ?>
        <div class="cred-dato">
            <span class="cred-dato-icon">🖥</span>
            <div><strong>Modalidad</strong><?php echo esc_html($modalidad); ?></div>
        </div>
        <?php endif; ?>
        <div class="cred-dato">
            <span class="cred-dato-icon">✉️</span>
            <div><strong>Email</strong><?php echo esc_html($email); ?></div>
        </div>
    </div>

    <div class="cred-qr">
        <img src="<?php echo esc_url($qr_url); ?>" alt="QR Acreditación">
        <div class="cred-qr-label">Acreditación oficial</div>
    </div>
    <div class="cred-footer"><?php echo esc_html($org); ?> · Evento Interno</div>
</div>
</body>
</html>
<?php
}
