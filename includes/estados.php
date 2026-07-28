<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Estados adicionales del evento: cancelado y pospuesto
 * Cuando se guarda un evento con uno de estos estados, se envía email a todos los inscritos.
 */

// ── Registrar estados custom ──────────────────────────────────────────────────
add_action( 'init', 'gpe_registrar_estados_evento' );
function gpe_registrar_estados_evento() {
    register_post_status( 'gpe_cancelado', array(
        'label'                     => 'Cancelado',
        'public'                    => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Cancelado <span class="count">(%s)</span>', 'Cancelados <span class="count">(%s)</span>' ),
    ) );
    register_post_status( 'gpe_pospuesto', array(
        'label'                     => 'Pospuesto',
        'public'                    => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Pospuesto <span class="count">(%s)</span>', 'Pospuestos <span class="count">(%s)</span>' ),
    ) );
}

// ── Añadir los estados al dropdown de WordPress ───────────────────────────────
add_action( 'admin_footer-post.php',     'gpe_estados_js_admin' );
add_action( 'admin_footer-post-new.php', 'gpe_estados_js_admin' );
function gpe_estados_js_admin() {
    global $post;
    if ( ! $post || $post->post_type !== 'evento_home' ) return;
    $estado_actual = $post->post_status;
    ?>
    <script>
    jQuery(document).ready(function($){
        var estados = {
            'gpe_cancelado': 'Cancelado',
            'gpe_pospuesto': 'Pospuesto'
        };
        var sel = $('#post_status');
        $.each(estados, function(val, label){
            if (!sel.find('option[value="'+val+'"]').length) {
                sel.append('<option value="'+val+'">'+label+'</option>');
            }
        });
        <?php if ( in_array($estado_actual, array('gpe_cancelado','gpe_pospuesto')) ) : ?>
        sel.val('<?php echo $estado_actual; ?>');
        $('#post-status-display').text(sel.find(':selected').text());
        <?php endif; ?>
    });
    </script>
    <?php
}

// ── Metabox para datos del pospuesto ─────────────────────────────────────────
add_action( 'add_meta_boxes', 'gpe_metabox_pospuesto' );
function gpe_metabox_pospuesto() {
    add_meta_box(
        'gpe_meta_pospuesto',
        'Datos del aplazamiento',
        'gpe_render_metabox_pospuesto',
        'evento_home',
        'side',
        'high'
    );
}

function gpe_render_metabox_pospuesto( $post ) {
    wp_nonce_field( 'gpe_pospuesto_action', 'gpe_pospuesto_nonce' );
    $nueva_fecha  = get_post_meta( $post->ID, '_gpe_nueva_fecha',  true );
    $nueva_hora   = get_post_meta( $post->ID, '_gpe_nueva_hora',   true );
    $nuevo_lugar  = get_post_meta( $post->ID, '_gpe_nuevo_lugar',  true );
    $motivo       = get_post_meta( $post->ID, '_gpe_aplaz_motivo', true );
    $email_enviado = get_post_meta( $post->ID, '_gpe_estado_email_enviado', true );
    ?>
    <p style="font-size:12px; color:#888; margin-top:0;">Solo se usa si el evento está <strong>Pospuesto</strong>. Al guardar con ese estado se envía email automático a todos los inscritos.</p>
    <table class="form-table" style="margin:0;">
        <tr>
            <th style="padding:5px 0;"><label style="font-size:12px;">Nueva fecha</label></th>
            <td><input type="date" name="gpe_nueva_fecha" value="<?php echo esc_attr($nueva_fecha); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th style="padding:5px 0;"><label style="font-size:12px;">Nueva hora</label></th>
            <td><input type="time" name="gpe_nueva_hora" value="<?php echo esc_attr($nueva_hora); ?>" class="widefat"></td>
        </tr>
        <tr>
            <th style="padding:5px 0;"><label style="font-size:12px;">Nuevo lugar</label></th>
            <td><input type="text" name="gpe_nuevo_lugar" value="<?php echo esc_attr($nuevo_lugar); ?>" class="widefat" placeholder="Si cambia la sede"></td>
        </tr>
        <tr>
            <th style="padding:5px 0;"><label style="font-size:12px;">Motivo</label></th>
            <td><textarea name="gpe_aplaz_motivo" rows="3" class="widefat" placeholder="Causas del aplazamiento…"><?php echo esc_textarea($motivo); ?></textarea></td>
        </tr>
    </table>
    <?php if ( $email_enviado ) : ?>
        <p style="background:#d4edda; color:#155724; padding:8px 10px; border-radius:4px; font-size:12px; margin-top:8px;">
            ✅ Email de estado enviado el <?php echo esc_html( date('d/m/Y H:i', strtotime($email_enviado)) ); ?>
        </p>
    <?php endif; ?>
    <?php
}

// ── Detectar cambio de estado y enviar emails ─────────────────────────────────
add_action( 'transition_post_status', 'gpe_detectar_cambio_estado_evento', 10, 3 );
function gpe_detectar_cambio_estado_evento( $new_status, $old_status, $post ) {
    if ( $post->post_type !== 'evento_home' ) return;
    if ( $new_status === $old_status ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    // Guardar metadatos de pospuesto si vienen en el POST
    if ( isset($_POST['gpe_pospuesto_nonce']) && wp_verify_nonce($_POST['gpe_pospuesto_nonce'], 'gpe_pospuesto_action') ) {
        update_post_meta( $post->ID, '_gpe_nueva_fecha',  sanitize_text_field($_POST['gpe_nueva_fecha']  ?? '') );
        update_post_meta( $post->ID, '_gpe_nueva_hora',   sanitize_text_field($_POST['gpe_nueva_hora']   ?? '') );
        update_post_meta( $post->ID, '_gpe_nuevo_lugar',  sanitize_text_field($_POST['gpe_nuevo_lugar']  ?? '') );
        update_post_meta( $post->ID, '_gpe_aplaz_motivo', sanitize_textarea_field($_POST['gpe_aplaz_motivo'] ?? '') );
    }

    // Solo actuamos en cambios a cancelado o pospuesto
    if ( ! in_array( $new_status, array('gpe_cancelado','gpe_pospuesto') ) ) return;

    // Evitar doble envío
    $ya_enviado = get_post_meta( $post->ID, '_gpe_estado_email_enviado', true );
    $clave_envio = $new_status . '_' . date('Y-m-d');
    if ( $ya_enviado === $clave_envio ) return;

    // Obtener todos los inscritos confirmados
    global $wpdb;
    $inscritos = $wpdb->get_results( $wpdb->prepare(
        "SELECT nombre, apellidos, email FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id = %d AND estado = 'confirmada'",
        $post->ID
    ) );

    if ( empty($inscritos) ) return;

    $enviados = 0;
    foreach ( $inscritos as $inscrito ) {
        if ( $new_status === 'gpe_cancelado' ) {
            gpe_email_evento_cancelado( $post->ID, $inscrito->nombre, $inscrito->email );
        } else {
            gpe_email_evento_pospuesto( $post->ID, $inscrito->nombre, $inscrito->email );
        }
        $enviados++;
    }

    update_post_meta( $post->ID, '_gpe_estado_email_enviado', $clave_envio );

    // Aviso en el admin
    add_action( 'admin_notices', function() use ($enviados, $new_status) {
        $label = $new_status === 'gpe_cancelado' ? 'cancelación' : 'aplazamiento';
        echo '<div class="notice notice-success"><p>✅ Email de ' . esc_html($label) . ' enviado a <strong>' . $enviados . '</strong> inscritos.</p></div>';
    });
}

// ── Emails de estado ──────────────────────────────────────────────────────────
function gpe_email_evento_cancelado( $evento_id, $nombre, $email ) {
    $evento  = get_post($evento_id);
    $asunto  = 'Evento cancelado: ' . $evento->post_title;
    $contenido = '
        <p>Hola <strong>' . esc_html($nombre) . '</strong>,</p>
        <p>Lamentamos informarte de que el evento <strong>' . esc_html($evento->post_title) . '</strong> ha sido <strong style="color:#c0392b;">cancelado</strong>.</p>
        <p>Si tienes cualquier duda, no dudes en ponerte en contacto con nosotros respondiendo a este email.</p>
        <p>Pedimos disculpas por las molestias.</p>
        <p style="margin-top:20px;">Un saludo,<br><strong>' . esc_html(get_bloginfo('name')) . '</strong></p>
    ';
    gpe_enviar_email( $email, $asunto, gpe_email_wrapper($contenido, $asunto) );
}

function gpe_email_evento_pospuesto( $evento_id, $nombre, $email ) {
    $evento      = get_post($evento_id);
    $nueva_fecha = get_post_meta($evento_id, '_gpe_nueva_fecha',  true);
    $nueva_hora  = get_post_meta($evento_id, '_gpe_nueva_hora',   true);
    $nuevo_lugar = get_post_meta($evento_id, '_gpe_nuevo_lugar',  true);
    $motivo      = get_post_meta($evento_id, '_gpe_aplaz_motivo', true);
    $url_ev      = get_permalink($evento_id);

    $fecha_fmt = $nueva_fecha ? date_i18n( get_option('date_format'), strtotime($nueva_fecha) ) : 'Por confirmar';

    $asunto = 'Evento aplazado: ' . $evento->post_title;
    $contenido = '
        <p>Hola <strong>' . esc_html($nombre) . '</strong>,</p>
        <p>Te informamos de que el evento <strong>' . esc_html($evento->post_title) . '</strong> ha sido <strong style="color:#e67e22;">aplazado</strong>.</p>
        ' . ($motivo ? '<p><strong>Motivo:</strong> ' . esc_html($motivo) . '</p>' : '') . '
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1;border-radius:10px;padding:20px;margin:20px 0;border:1px solid #ffe082;">
            <tr><td style="padding:5px 0;"><strong>📅 Nueva fecha:</strong> ' . esc_html($fecha_fmt) . '</td></tr>
            ' . ($nueva_hora  ? '<tr><td style="padding:5px 0;"><strong>🕐 Nueva hora:</strong> ' . esc_html($nueva_hora) . 'h</td></tr>' : '') . '
            ' . ($nuevo_lugar ? '<tr><td style="padding:5px 0;"><strong>📍 Nuevo lugar:</strong> ' . esc_html($nuevo_lugar) . '</td></tr>' : '') . '
        </table>
        <p>Tu inscripción sigue siendo válida para la nueva fecha.</p>
        <p style="text-align:center; margin:25px 0;">
            <a href="' . esc_url($url_ev) . '" style="background:linear-gradient(135deg,#e67e22,#f39c12);color:#fff;padding:14px 30px;border-radius:30px;text-decoration:none;font-weight:700;font-size:15px;">Ver información actualizada</a>
        </p>
        <p>Un saludo,<br><strong>' . esc_html(get_bloginfo('name')) . '</strong></p>
    ';
    gpe_enviar_email( $email, $asunto, gpe_email_wrapper($contenido, $asunto) );
}

// ── Mostrar estado en la landing si está cancelado/pospuesto ─────────────────
add_action( 'wp_head', 'gpe_aviso_estado_evento_head' );
function gpe_aviso_estado_evento_head() {
    if ( ! is_singular('evento_home') ) return;
    $estado = get_post_status();
    if ( ! in_array($estado, array('gpe_cancelado','gpe_pospuesto')) ) return;
    ?>
    <style>
    .gpe-aviso-estado { position: fixed; top: 0; left: 0; right: 0; z-index: 9999; text-align: center; padding: 12px 20px; font-family: 'Inter', sans-serif; font-weight: 700; font-size: 15px; }
    .gpe-aviso-cancelado  { background: #c0392b; color: #fff; }
    .gpe-aviso-pospuesto  { background: #e67e22; color: #fff; }
    body { padding-top: 48px !important; }
    </style>
    <?php if ( $estado === 'gpe_cancelado' ) : ?>
        <div class="gpe-aviso-estado gpe-aviso-cancelado">🚫 Este evento ha sido CANCELADO</div>
    <?php else :
        $nueva_fecha = get_post_meta(get_the_ID(), '_gpe_nueva_fecha', true);
        $nueva_hora  = get_post_meta(get_the_ID(), '_gpe_nueva_hora',  true);
        $fecha_fmt   = $nueva_fecha ? date_i18n(get_option('date_format'), strtotime($nueva_fecha)) : '';
    ?>
        <div class="gpe-aviso-estado gpe-aviso-pospuesto">
            ⚠️ Evento APLAZADO<?php if ($fecha_fmt) echo ' — Nueva fecha: ' . esc_html($fecha_fmt) . ($nueva_hora ? ' a las ' . esc_html($nueva_hora) . 'h' : ''); ?>
        </div>
    <?php endif; ?>
    <?php
}
