<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Render del formulario de inscripción a evento interno ─────────────────────
function gpe_render_formulario_interno( $evento_id ) {
    if ( ! is_user_logged_in() ) { ?>
        <div style="text-align:center;padding:40px 20px;font-family:'Inter',sans-serif;">
            <p style="font-size:1.1rem;color:#555;margin-bottom:16px;">Necesitas iniciar sesión para acceder a este formulario.</p>
            <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" style="background:#007a87;color:#fff;padding:12px 28px;border-radius:30px;text-decoration:none;font-weight:700;">Iniciar sesión</a>
        </div>
        <?php return;
    }

    $user_id  = get_current_user_id();
    $user     = wp_get_current_user();

    // ¿Está el usuario invitado?
    if ( ! gpe_usuario_invitado_interno( $user_id, $evento_id ) && ! current_user_can('manage_options') ) { ?>
        <div style="text-align:center;padding:40px 20px;font-family:'Inter',sans-serif;color:#c0392b;">
            <p>No estás en la lista de embajadores/as invitados a este evento.</p>
        </div>
        <?php return;
    }

    // ¿Ya inscrito?
    global $wpdb;
    $ya_inscrito = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones_internas WHERE evento_id=%d AND user_id=%d AND estado='confirmada'",
        $evento_id, $user_id
    ));

    // ¿Ya ha delegado su invitación?
    $ya_delegado = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones_internas WHERE evento_id=%d AND delegado_por=%d",
        $evento_id, $user_id
    ));

    // Solo pueden delegar los miembros del Consejo Federal
    $puede_delegar = false;
    $organos_evento = get_post_meta($evento_id, '_gpe_organos_invitados', true) ?: array();
    foreach ($organos_evento as $oid) {
        $org = $wpdb->get_row($wpdb->prepare("SELECT tipo FROM {$wpdb->prefix}gpe_organos WHERE id=%d", intval($oid)));
        if ($org && $org->tipo === 'consejo_federal') {
            // Verificar que el usuario está en el consejo federal
            $en_consejo = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}gpe_organo_miembros WHERE organo_id=%d AND user_id=%d AND activo=1",
                intval($oid), $user_id
            ));
            if ($en_consejo > 0) { $puede_delegar = true; break; }
        }
    }

    // Modalidades disponibles para este evento
    $modalidades_raw = get_post_meta( $evento_id, '_gpe_int_modalidades', true ) ?: array('presencial');

    // DNI/NIE del usuario desde Ultimate Member
    $dni_nie = get_user_meta( $user_id, 'dni-o-nie', true ) ?: '';

    // Procesar delegación
    $msg_delegacion = '';
    if ( isset($_POST['gpe_delegar_nonce']) && wp_verify_nonce($_POST['gpe_delegar_nonce'], 'gpe_delegar_'.$evento_id) ) {
        $email_delegado = sanitize_email($_POST['email_delegado'] ?? '');
        $user_delegado  = get_user_by('email', $email_delegado);
        if ( ! $user_delegado ) {
            $msg_delegacion = '<div style="color:#c0392b;padding:10px;background:#fdf0f0;border-radius:6px;margin-bottom:14px;">No se encontró ningún embajador/a con ese correo.</div>';
        } elseif ( ! gpe_usuario_invitado_interno( $user_delegado->ID, $evento_id ) ) {
            $msg_delegacion = '<div style="color:#c0392b;padding:10px;background:#fdf0f0;border-radius:6px;margin-bottom:14px;">Esa persona no está en la lista de embajadores/as de este evento.</div>';
        } elseif ( $user_delegado->ID === $user_id ) {
            $msg_delegacion = '<div style="color:#c0392b;padding:10px;background:#fdf0f0;border-radius:6px;margin-bottom:14px;">No puedes delegarte a ti mismo/a.</div>';
        } else {
            // Crear inscripción con delegación
            $token = wp_generate_password(32, false);
            $wpdb->insert( $wpdb->prefix.'gpe_inscripciones_internas', array(
                'evento_id'    => $evento_id,
                'user_id'      => $user_delegado->ID,
                'delegado_por' => $user_id,
                'estado'       => 'pendiente',
                'token'        => $token,
                'dni_nie'      => get_user_meta($user_delegado->ID, 'dni-o-nie', true) ?: '',
            ), array('%d','%d','%d','%s','%s','%s'));
            // Email al receptor de la delegación
            gpe_email_delegacion_interna( $evento_id, $user_delegado, $user, $token );
            $msg_delegacion = '<div style="color:#155724;padding:10px;background:#d4edda;border-radius:6px;margin-bottom:14px;">✅ Delegación enviada a ' . esc_html($user_delegado->display_name) . '. Recibirá un correo para confirmar su asistencia.</div>';
            $ya_delegado = true;
        }
    }

    // Procesar inscripción propia
    $msg_inscripcion = '';
    if ( isset($_POST['gpe_interno_nonce']) && wp_verify_nonce($_POST['gpe_interno_nonce'], 'gpe_inscr_interna_'.$evento_id) && !$ya_inscrito ) {
        $modalidad  = sanitize_text_field($_POST['modalidad'] ?? 'presencial');
        $comentario = sanitize_textarea_field($_POST['comentario'] ?? '');
        if ( ! in_array($modalidad, $modalidades_raw) ) $modalidad = $modalidades_raw[0];
        $token = wp_generate_password(32, false);
        $wpdb->insert( $wpdb->prefix.'gpe_inscripciones_internas', array(
            'evento_id'  => $evento_id,
            'user_id'    => $user_id,
            'dni_nie'    => $dni_nie,
            'modalidad'  => $modalidad,
            'estado'     => 'confirmada',
            'token'      => $token,
            'comentario' => $comentario,
        ), array('%d','%d','%s','%s','%s','%s','%s'));
        // Email confirmación
        gpe_email_confirmacion_interna( $evento_id, $user, $token, $modalidad );
        $msg_inscripcion = '<div style="color:#155724;padding:14px;background:#d4edda;border-radius:8px;font-weight:600;">✅ ¡Inscripción confirmada! Recibirás un email con tu acreditación.</div>';
        $ya_inscrito = true;
    }

    $lbl_modalidad = array(
        'presencial'  => 'Presencial',
        'telematica'  => 'Telemática',
        'mixta'       => 'Mixta',
    );
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
    .gpe-int-form{font-family:'Inter',sans-serif;max-width:560px;margin:0 auto;}
    .gpe-int-card{background:#fff;border:1px solid #eee;border-radius:14px;padding:28px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:18px;}
    .gpe-int-label{display:block;font-weight:600;font-size:13px;margin-bottom:6px;color:#333;}
    .gpe-int-input{width:100%;padding:10px 14px;border:1px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box;font-family:inherit;}
    .gpe-int-input:focus{border-color:#007a87;outline:none;box-shadow:0 0 0 3px rgba(0,122,135,.1);}
    .gpe-int-input[disabled]{background:#f5f5f5;color:#888;cursor:not-allowed;}
    .gpe-int-btn{display:block;width:100%;padding:13px;background:linear-gradient(135deg,#007a87,#00b4cc);color:#fff;font-weight:700;font-size:16px;border-radius:30px;border:none;cursor:pointer;text-align:center;margin-top:18px;font-family:inherit;}
    .gpe-int-btn:hover{opacity:.9;}
    .gpe-int-btn-delegar{background:#fff;color:#007a87;border:2px solid #007a87;border-radius:30px;padding:10px 20px;font-weight:700;font-size:14px;cursor:pointer;font-family:inherit;width:100%;}
    .gpe-int-btn-delegar:hover{background:#007a87;color:#fff;}
    .gpe-int-seccion{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#007a87;margin-bottom:14px;}
    @media(max-width:600px){.gpe-int-card{padding:20px 16px;}}
    </style>

    <div class="gpe-int-form">

    <?php echo $msg_delegacion . $msg_inscripcion; ?>

    <?php if ( $ya_inscrito ) : ?>
        <div class="gpe-int-card" style="text-align:center;">
            <div style="font-size:2rem;margin-bottom:10px;">✅</div>
            <p style="font-weight:700;color:#155724;font-size:1.1rem;margin:0;">Ya estás inscrito/a en este evento.</p>
            <?php if ($ya_inscrito && $ya_inscrito->modalidad) : ?>
            <p style="color:#666;margin-top:8px;font-size:14px;">Modalidad: <strong><?php echo esc_html($lbl_modalidad[$ya_inscrito->modalidad] ?? $ya_inscrito->modalidad); ?></strong></p>
            <?php endif; ?>
        </div>
    <?php elseif ( $ya_delegado ) : ?>
        <div class="gpe-int-card" style="text-align:center;">
            <div style="font-size:2rem;margin-bottom:10px;">📨</div>
            <p style="font-weight:700;color:#856404;font-size:1.1rem;margin:0;">Has delegado tu invitación.</p>
        </div>
    <?php else : ?>

        <?php if ($puede_delegar) : ?>
        <!-- Bloque delegación — solo Consejo Federal -->
        <div class="gpe-int-card">
            <div class="gpe-int-seccion">Delegar invitación</div>
            <p style="font-size:14px;color:#666;margin:0 0 14px;">Como miembro del Consejo Federal, puedes ceder tu invitación a otro/a embajador/a.</p>
            <form method="post">
                <?php wp_nonce_field('gpe_delegar_'.$evento_id, 'gpe_delegar_nonce'); ?>
                <input type="hidden" name="evento_id" value="<?php echo intval($evento_id); ?>">
                <label class="gpe-int-label">Email del/la embajador/a</label>
                <input type="email" name="email_delegado" class="gpe-int-input" placeholder="embajador@ejemplo.com" required>
                <button type="submit" class="gpe-int-btn-delegar" style="margin-top:12px;">Delegar invitación →</button>
            </form>
        </div>
        <?php endif; // puede_delegar ?>

        <!-- Formulario inscripción -->
        <div class="gpe-int-card">
            <div class="gpe-int-seccion">Confirmar asistencia</div>
            <form method="post">
                <?php wp_nonce_field('gpe_inscr_interna_'.$evento_id, 'gpe_interno_nonce'); ?>
                <input type="hidden" name="evento_id" value="<?php echo intval($evento_id); ?>">

                <div style="margin-bottom:14px;">
                    <label class="gpe-int-label">Nombre</label>
                    <input type="text" class="gpe-int-input" value="<?php echo esc_attr($user->display_name); ?>" disabled>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="gpe-int-label">Email</label>
                    <input type="email" class="gpe-int-input" value="<?php echo esc_attr($user->user_email); ?>" disabled>
                </div>
                <div style="margin-bottom:14px;">
                    <label class="gpe-int-label">DNI / NIE</label>
                    <input type="text" class="gpe-int-input" value="<?php echo esc_attr($dni_nie ?: '(sin rellenar en tu perfil)'); ?>" disabled>
                    <?php if (!$dni_nie) : ?><p style="font-size:12px;color:#e67e22;margin:4px 0 0;">Completa tu DNI/NIE en tu perfil de usuario para poder inscribirte.</p><?php endif; ?>
                </div>

                <?php if ( count($modalidades_raw) > 1 ) : ?>
                <div style="margin-bottom:14px;">
                    <label class="gpe-int-label">Modalidad de asistencia</label>
                    <select name="modalidad" class="gpe-int-input">
                        <?php foreach ($modalidades_raw as $m) : ?>
                            <option value="<?php echo esc_attr($m); ?>"><?php echo esc_html($lbl_modalidad[$m] ?? $m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else : ?>
                    <input type="hidden" name="modalidad" value="<?php echo esc_attr($modalidades_raw[0]); ?>">
                <?php endif; ?>

                <div style="margin-bottom:14px;">
                    <label class="gpe-int-label">Comentario (opcional)</label>
                    <textarea name="comentario" class="gpe-int-input" rows="3" placeholder="Algún comentario o necesidad especial..."></textarea>
                </div>

                <button type="submit" class="gpe-int-btn" <?php echo !$dni_nie ? 'disabled title="Rellena tu DNI/NIE en el perfil"' : ''; ?>>Confirmar asistencia</button>
            </form>
        </div>

    <?php endif; ?>
    </div>
    <?php
}

// ── Email: notificación de delegación ─────────────────────────────────────────
function gpe_email_delegacion_interna( $evento_id, $receptor, $remitente, $token ) {
    $evento   = get_post($evento_id);
    $fecha    = get_post_meta($evento_id,'_gpe_int_fecha',true);
    $url      = get_permalink($evento_id);
    $asunto   = '[GP Eventik] ' . ($remitente->display_name) . ' te ha delegado su invitación';
    $cuerpo   = '<html><body style="font-family:Arial,sans-serif;color:#333;max-width:560px;margin:0 auto;padding:24px;">'
              . '<h2 style="color:#007a87;">Tienes una invitación delegada</h2>'
              . '<p><strong>' . esc_html($remitente->display_name) . '</strong> te ha cedido su invitación para el evento:</p>'
              . '<div style="background:#f0fafb;border-left:4px solid #007a87;padding:14px;border-radius:6px;margin:16px 0;">'
              . '<strong>' . esc_html($evento ? $evento->post_title : '') . '</strong><br>'
              . ($fecha ? '📅 ' . date_i18n('l, j \d\e F \d\e Y', strtotime($fecha)) : '')
              . '</div>'
              . '<p>Para confirmar tu asistencia inicia sesión y completa el formulario:</p>'
              . '<p style="text-align:center;"><a href="' . esc_url($url) . '" style="background:#007a87;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:700;">Ver evento y confirmar asistencia</a></p>'
              . '</body></html>';
    wp_mail( $receptor->user_email, $asunto, $cuerpo, array('Content-Type: text/html; charset=UTF-8') );
}

// ── Email: confirmación inscripción interna ───────────────────────────────────
function gpe_email_confirmacion_interna( $evento_id, $user, $token, $modalidad ) {
    $evento  = get_post($evento_id);
    $fecha   = get_post_meta($evento_id,'_gpe_int_fecha',true);
    $lugar   = get_post_meta($evento_id,'_gpe_int_lugar',true);
    $asunto  = 'Inscripción confirmada — ' . ($evento ? $evento->post_title : '');
    $lbl_mod = array('presencial'=>'Presencial','telematica'=>'Telemática','mixta'=>'Mixta');
    $url_acred = function_exists('gpe_acreditacion_url') ? gpe_acreditacion_url($token, 'interna') : '';
    $qr_url    = function_exists('gpe_qr_url') ? gpe_qr_url($token, $evento_id) : '';
    $cuerpo  = '<html><body style="font-family:Arial,sans-serif;color:#333;max-width:560px;margin:0 auto;padding:24px;">'
             . '<h2 style="color:#007a87;">Inscripción confirmada</h2>'
             . '<p>Hola <strong>' . esc_html($user->display_name) . '</strong>, tu asistencia ha quedado registrada.</p>'
             . '<div style="background:#f0fafb;border-left:4px solid #007a87;padding:14px;border-radius:6px;margin:16px 0;">'
             . '<strong>' . esc_html($evento ? $evento->post_title : '') . '</strong><br>'
             . ($fecha ? '📅 ' . date_i18n('l, j \d\e F \d\e Y', strtotime($fecha)) . '<br>' : '')
             . ($lugar ? '📍 ' . esc_html($lugar) . '<br>' : '')
             . '🖥 Modalidad: <strong>' . esc_html($lbl_mod[$modalidad] ?? $modalidad) . '</strong>'
             . '</div>'
             . ($url_acred ? '
             <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafb;border-radius:12px;padding:20px;margin:16px 0;border:1px solid #ddd;text-align:center;">
                <tr><td>
                    <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#007a87;margin-bottom:10px;">CREDENCIAL OFICIAL</div>
                    ' . ($qr_url ? '<img src="' . esc_url($qr_url) . '" width="120" height="120" alt="QR" style="display:block;margin:0 auto 12px;border-radius:6px;">' : '') . '
                    <div style="font-weight:800;font-size:1rem;color:#1a1a1a;margin-bottom:12px;">' . esc_html($user->display_name) . '</div>
                    <a href="' . esc_url($url_acred) . '" style="background:#1a1a1a;color:#fff;padding:11px 24px;border-radius:30px;text-decoration:none;font-weight:700;font-size:14px;">🖨 Descargar credencial para imprimir</a>
                    <div style="margin-top:10px;font-size:11px;color:#aaa;">Imprime o guarda como PDF. Preséntala en la acreditación del evento.</div>
                </td></tr>
             </table>' : '')
             . '</body></html>';
    wp_mail( $user->user_email, $asunto, $cuerpo, array('Content-Type: text/html; charset=UTF-8') );
}

// ── AJAX: delegación desde formulario ─────────────────────────────────────────
// (El formulario usa POST normal, no AJAX, para mayor compatibilidad)
