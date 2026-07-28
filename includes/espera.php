<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Apuntarse a la lista de espera ────────────────────────────────────────────
add_action( 'wp_ajax_gpe_lista_espera',        'gpe_ajax_lista_espera' );
add_action( 'wp_ajax_nopriv_gpe_lista_espera', 'gpe_ajax_lista_espera' );
function gpe_ajax_lista_espera() {
    if ( ! isset($_POST['gpe_nonce']) || ! wp_verify_nonce($_POST['gpe_nonce'], 'gpe_inscripcion_nonce') ) {
        wp_send_json_error( array('msg' => 'Token de seguridad inválido.') );
    }

    global $wpdb;
    $evento_id = intval( $_POST['evento_id'] ?? 0 );
    $nombre    = sanitize_text_field( $_POST['nombre'] ?? '' );
    $email     = sanitize_email( $_POST['email'] ?? '' );

    if ( ! $evento_id || ! $email || ! $nombre ) {
        wp_send_json_error( array('msg' => 'Nombre y email son obligatorios.') );
    }

    // Comprobar si ya está en la lista
    $existe = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}gpe_lista_espera WHERE evento_id = %d AND email = %s",
        $evento_id, $email
    ) );
    if ( $existe ) {
        wp_send_json_error( array('msg' => 'Ya estás en la lista de espera para este evento.') );
    }

    $wpdb->insert(
        $wpdb->prefix . 'gpe_lista_espera',
        array( 'evento_id' => $evento_id, 'nombre' => $nombre, 'email' => $email ),
        array( '%d', '%s', '%s' )
    );

    // Email de confirmación de espera
    gpe_email_confirmacion_espera( $evento_id, $nombre, $email );

    wp_send_json_success( array('msg' => gpe__('gpe_espera_ok', 'Apuntado a la lista de espera. Te avisaremos si se libera una plaza.') ) );
}

// ── Notificar lista de espera cuando se cancela una inscripción ───────────────
// Se llama desde admin-inscripciones.php y desde el enlace de cancelación por token
function gpe_notificar_lista_espera( $evento_id ) {
    global $wpdb;

    // Solo si ahora hay plazas libres
    if ( ! gpe_evento_sold_out($evento_id) || gpe_plazas_libres($evento_id) < 1 ) {
        // Puede que acabar de liberarse una plaza
        if ( gpe_plazas_libres($evento_id) < 1 ) return;
    }

    // Coger el primero de la lista que aún no ha sido notificado
    $persona = $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_lista_espera WHERE evento_id = %d AND notificado = 0 ORDER BY fecha_reg ASC LIMIT 1",
        $evento_id
    ) );
    if ( ! $persona ) return;

    // Marcar como notificado antes de enviar (evita dobles envíos)
    $wpdb->update(
        $wpdb->prefix . 'gpe_lista_espera',
        array( 'notificado' => 1 ),
        array( 'id' => $persona->id ),
        array( '%d' ), array( '%d' )
    );

    gpe_email_plaza_disponible( $evento_id, $persona->nombre, $persona->email );
}

// ── Emails ────────────────────────────────────────────────────────────────────
function gpe_email_confirmacion_espera( $evento_id, $nombre, $email ) {
    $evento   = get_post($evento_id);
    $fecha    = get_post_meta($evento_id, '_med_fecha_evento', true);
    $provincia = get_post_meta($evento_id, '_med_provincia_sitio', true);
    $fecha_fmt = $fecha ? date_i18n( get_option('date_format'), strtotime($fecha) ) : '—';

    $contenido = '
        <p>Hola <strong>' . esc_html($nombre) . '</strong>,</p>
        <p>Te hemos añadido a la lista de espera del evento <strong>' . esc_html($evento->post_title) . '</strong>.</p>
        <p>Si se libera alguna plaza, serás el/la primero/a en recibir un aviso.</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9fa;border-radius:10px;padding:20px;margin:20px 0;">
            <tr><td style="padding:5px 0;"><strong>Fecha:</strong> ' . esc_html($fecha_fmt) . '</td></tr>
            <tr><td style="padding:5px 0;"><strong>Lugar:</strong> ' . esc_html($provincia ?: '—') . '</td></tr>
        </table>
    ';
    $asunto = 'Lista de espera: ' . $evento->post_title;
    gpe_enviar_email( $email, $asunto, gpe_email_wrapper($contenido, $asunto) );
}

function gpe_email_plaza_disponible( $evento_id, $nombre, $email ) {
    $evento   = get_post($evento_id);
    $url_ev   = get_permalink($evento_id);

    $contenido = '
        <p>Hola <strong>' . esc_html($nombre) . '</strong>,</p>
        <p>¡Buenas noticias! Se ha liberado una plaza en <strong>' . esc_html($evento->post_title) . '</strong>.</p>
        <p>Date prisa, las plazas son limitadas:</p>
        <p style="text-align:center; margin:25px 0;">
            <a href="' . esc_url($url_ev) . '" style="background:linear-gradient(135deg,#007a87,#00b4cc);color:#fff;padding:14px 30px;border-radius:30px;text-decoration:none;font-weight:700;font-size:15px;">Ir al evento e inscribirme</a>
        </p>
    ';
    $asunto = '¡Plaza disponible! ' . $evento->post_title;
    gpe_enviar_email( $email, $asunto, gpe_email_wrapper($contenido, $asunto) );
}

// ── Shortcode del formulario de lista de espera ───────────────────────────────
function gpe_render_formulario_espera( $evento_id ) {
    ob_start();
    ?>
    <div id="gpe-espera-<?php echo $evento_id; ?>" style="margin-top:16px;">
        <p style="text-align:center; font-size:13px; color:#666; margin-bottom:12px;">
            ¿Quieres que te avisemos si se libera una plaza?
        </p>
        <form id="gpe-form-espera-<?php echo $evento_id; ?>">
            <?php wp_nonce_field('gpe_inscripcion_nonce','gpe_nonce'); ?>
            <input type="hidden" name="evento_id" value="<?php echo $evento_id; ?>">
            <input type="text"  name="nombre" required placeholder="Tu nombre" class="gpe-input" style="width:100%;margin-bottom:8px;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-sizing:border-box;">
            <input type="email" name="email"  required placeholder="Tu email"  class="gpe-input" style="width:100%;margin-bottom:8px;padding:10px 14px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-sizing:border-box;">
            <div id="gpe-espera-resp-<?php echo $evento_id; ?>" style="display:none; padding:10px; border-radius:6px; margin-bottom:8px; font-size:14px;"></div>
            <button type="submit" style="width:100%;padding:10px;background:#1a1a1a;color:#fff;border:none;border-radius:25px;font-weight:600;font-size:14px;cursor:pointer;">
                Apuntarme a la lista de espera
            </button>
        </form>
    </div>
    <script>
    document.getElementById('gpe-form-espera-<?php echo $evento_id; ?>').addEventListener('submit', function(e){
        e.preventDefault();
        var resp = document.getElementById('gpe-espera-resp-<?php echo $evento_id; ?>');
        var fd = new FormData(this);
        fd.append('action','gpe_lista_espera');
        fetch(<?php echo json_encode(admin_url('admin-ajax.php')); ?>, {method:'POST',body:fd})
        .then(r=>r.json()).then(data=>{
            resp.style.display = 'block';
            resp.style.background = data.success ? '#d4edda' : '#f8d7da';
            resp.style.color = data.success ? '#155724' : '#721c24';
            resp.textContent = data.data.msg;
            if (data.success) this.reset();
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
