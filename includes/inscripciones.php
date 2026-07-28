<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Helpers de aforo ─────────────────────────────────────────────────────────

function gpe_aforo_maximo( $evento_id ) {
    $aforo = get_post_meta( $evento_id, '_gpe_aforo', true );
    return $aforo ? intval( $aforo ) : 0; // 0 = sin límite
}

function gpe_inscritos_count( $evento_id ) {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id = %d AND estado = 'confirmada'",
        $evento_id
    ) );
}

function gpe_plazas_libres( $evento_id ) {
    $max = gpe_aforo_maximo( $evento_id );
    if ( $max === 0 ) return PHP_INT_MAX; // sin límite
    return max( 0, $max - gpe_inscritos_count( $evento_id ) );
}

function gpe_evento_sold_out( $evento_id ) {
    return gpe_plazas_libres( $evento_id ) === 0 && gpe_aforo_maximo( $evento_id ) > 0;
}

// ─── Procesar inscripción (llamado desde AJAX) ────────────────────────────────

function gpe_procesar_inscripcion( $evento_id, $datos ) {
    global $wpdb;

    // Comprobar aforo
    if ( gpe_evento_sold_out( $evento_id ) ) {
        return array( 'ok' => false, 'msg' => 'Lo sentimos, el aforo está completo.' );
    }

    // Evitar doble inscripción
    $existe = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id = %d AND email = %s AND estado = 'confirmada'",
        $evento_id, sanitize_email( $datos['email'] )
    ) );
    if ( $existe ) {
        return array( 'ok' => false, 'msg' => 'Ya existe una inscripción con ese email para este evento.' );
    }

    $token = wp_generate_password( 32, false );

    $insert = $wpdb->insert(
        $wpdb->prefix . 'gpe_inscripciones',
        array(
            'evento_id'    => $evento_id,
            'nombre'       => sanitize_text_field( $datos['nombre'] ),
            'apellidos'    => sanitize_text_field( $datos['apellidos'] ),
            'email'        => sanitize_email( $datos['email'] ),
            'telefono'     => sanitize_text_field( $datos['telefono'] ?? '' ),
            'ccaa'         => sanitize_text_field( $datos['ccaa'] ?? '' ),
            'provincia'    => sanitize_text_field( $datos['provincia'] ?? '' ),
            'edad'         => intval( $datos['edad'] ?? 0 ),
            'como_conocio' => sanitize_text_field( $datos['como_conocio'] ?? '' ),
            'comentario'   => sanitize_textarea_field( $datos['comentario'] ?? '' ),
            'estado'       => 'confirmada',
            'token'        => $token,
        ),
        array( '%d','%s','%s','%s','%s','%s','%s','%d','%s','%s','%s','%s' )
    );

    if ( ! $insert ) {
        return array( 'ok' => false, 'msg' => 'Error al guardar la inscripción. Inténtalo de nuevo.' );
    }

    // Enviar email de confirmación
    gpe_email_confirmacion_inscripcion( $evento_id, $datos, $token );

    return array( 'ok' => true, 'msg' => '¡Inscripción confirmada! Te hemos enviado un email de confirmación.' );
}

// ─── Cancelar inscripción por token (enlace en el email) ──────────────────────

add_action( 'init', 'gpe_manejar_cancelacion_token' );
function gpe_manejar_cancelacion_token() {
    if ( isset( $_GET['gpe_cancelar'] ) && isset( $_GET['token'] ) ) {
        global $wpdb;
        $token = sanitize_text_field( $_GET['token'] );
        $row   = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE token = %s AND estado = 'confirmada'",
            $token
        ) );
        if ( $row ) {
            $wpdb->update(
                $wpdb->prefix . 'gpe_inscripciones',
                array( 'estado' => 'cancelada' ),
                array( 'id' => $row->id ),
                array( '%s' ), array( '%d' )
            );
            // Avisar lista de espera
            if ( function_exists('gpe_notificar_lista_espera') ) {
                gpe_notificar_lista_espera( $row->evento_id );
            }
            wp_safe_redirect( add_query_arg( 'gpe_msg', 'cancelada', get_permalink( $row->evento_id ) ) );
            exit;
        }
    }
}

// ─── Shortcode del formulario de inscripción ──────────────────────────────────
// Uso: [gpe_formulario_inscripcion evento_id="123"]
// En la landing del evento se incluye automáticamente.

add_shortcode( 'gpe_formulario_inscripcion', 'gpe_render_formulario_inscripcion' );
function gpe_render_formulario_inscripcion( $atts ) {
    $atts = shortcode_atts( array( 'evento_id' => get_the_ID() ), $atts );
    $eid  = intval( $atts['evento_id'] );
    if ( ! $eid ) return '';

    ob_start();

    $sold_out = gpe_evento_sold_out( $eid );
    $libres   = gpe_plazas_libres( $eid );
    $max      = gpe_aforo_maximo( $eid );
    $territorios = gpe_territorios_espana();

    if ( isset( $_GET['gpe_msg'] ) ) :
        $msg = sanitize_text_field( $_GET['gpe_msg'] );
        if ( $msg === 'ok' ) echo '<div class="gpe-alerta gpe-ok">✅ ¡Inscripción confirmada! Revisa tu email.</div>';
        if ( $msg === 'cancelada' ) echo '<div class="gpe-alerta gpe-warn">Tu inscripción ha sido cancelada.</div>';
    endif;
    ?>

    <div class="gpe-inscripcion-wrapper" id="gpe-inscripcion-<?php echo $eid; ?>">

        <?php if ( $sold_out ) : ?>
            <div class="gpe-sold-out-badge">
                <span>🔴 SOLD OUT — Aforo Completo</span>
            </div>
            <?php
            $lista_espera = get_post_meta( $eid, '_gpe_lista_espera', true );
            if ( $lista_espera ) echo '<p style="text-align:center; color:#666; margin-top:10px;">Puedes apuntarte a la lista de espera:</p>';
            ?>

        <?php else : ?>

            <?php if ( $max > 0 ) : ?>
                <div class="gpe-aforo-barra">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:600; color:#555;">
                        <span>Plazas disponibles</span>
                        <span><?php echo $libres; ?> / <?php echo $max; ?></span>
                    </div>
                    <div style="background:#f0f0f0; border-radius:20px; height:8px; overflow:hidden;">
                        <?php $pct = round( ( gpe_inscritos_count($eid) / $max ) * 100 ); ?>
                        <div style="width:<?php echo $pct; ?>%; height:100%; background:linear-gradient(90deg,#007a87,#00b4cc); border-radius:20px; transition:width 0.5s;"></div>
                    </div>
                </div>
            <?php endif; ?>

            <form id="gpe-form-inscripcion" data-evento="<?php echo $eid; ?>">
                <?php wp_nonce_field( 'gpe_inscripcion_nonce', 'gpe_nonce' ); ?>

                <div class="gpe-form-grid">
                    <div class="gpe-form-field">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required placeholder="María" class="gpe-input">
                    </div>
                    <div class="gpe-form-field">
                        <label>Apellidos *</label>
                        <input type="text" name="apellidos" required placeholder="García López" class="gpe-input">
                    </div>
                    <div class="gpe-form-field">
                        <label>Email *</label>
                        <input type="email" name="email" required placeholder="maria@ejemplo.com" class="gpe-input">
                    </div>
                    <div class="gpe-form-field">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" placeholder="600 000 000" class="gpe-input">
                    </div>
                    <div class="gpe-form-field">
                        <label>CCAA</label>
                        <select name="ccaa" class="gpe-input" id="gpe-ccaa-form-<?php echo $eid; ?>" onchange="gpe_form_provincias(this, <?php echo $eid; ?>)">
                            <option value="">-- Selecciona --</option>
                            <?php foreach ( array_keys($territorios) as $cc ) : ?>
                                <option value="<?php echo esc_attr($cc); ?>"><?php echo esc_html($cc); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="gpe-form-field">
                        <label>Provincia</label>
                        <select name="provincia" class="gpe-input" id="gpe-prov-form-<?php echo $eid; ?>">
                            <option value="">-- Primero selecciona CCAA --</option>
                        </select>
                    </div>
                    <div class="gpe-form-field">
                        <label>Edad</label>
                        <input type="number" name="edad" min="14" max="35" placeholder="22" class="gpe-input">
                    </div>
                    <div class="gpe-form-field">
                        <label>¿Cómo conociste el evento?</label>
                        <select name="como_conocio" class="gpe-input">
                            <option value="">-- Selecciona --</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Twitter/X">Twitter / X</option>
                            <option value="Amigo/a">Me lo dijo un amigo/a</option>
                            <option value="Web GP">Web de Generación Presente</option>
                            <option value="Email">Email de la asociación</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>

                <div class="gpe-form-field" style="margin-top:15px;">
                    <label>Comentario o necesidades especiales</label>
                    <textarea name="comentario" rows="3" class="gpe-input" placeholder="Cuéntanos si necesitas algo especial para el evento..."></textarea>
                </div>

                <div id="gpe-form-respuesta" style="margin: 15px 0; display:none;"></div>

                <button type="submit" class="gpe-btn-inscribir" id="gpe-btn-submit-<?php echo $eid; ?>">
                    Reservar mi plaza
                </button>
            </form>

        <?php endif; ?>
    </div>

    <style>
        .gpe-inscripcion-wrapper { font-family: 'Inter', sans-serif; }
        .gpe-sold-out-badge { background: linear-gradient(135deg, #c0392b, #e74c3c); color: #fff; text-align: center; padding: 20px; border-radius: 12px; font-size: 1.3rem; font-weight: 800; letter-spacing: 1px; }
        .gpe-aforo-barra { margin-bottom: 20px; }
        .gpe-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .gpe-form-field { display: flex; flex-direction: column; gap: 5px; }
        .gpe-form-field label { font-weight: 600; font-size: 13px; color: #1a1a1a; }
        .gpe-input { padding: 10px 14px; border: 2px solid rgba(0,122,135,0.15); border-radius: 8px; font-size: 14px; width: 100%; box-sizing: border-box; transition: border-color 0.2s; }
        .gpe-input:focus { border-color: #00b4cc; outline: none; box-shadow: 0 0 0 3px rgba(0,180,204,0.12); }
        .gpe-btn-inscribir { width: 100%; margin-top: 20px; padding: 14px; background: linear-gradient(135deg, #007a87, #00b4cc); color: #fff; font-weight: 700; font-size: 16px; border: none; border-radius: 30px; cursor: pointer; transition: all 0.3s; box-shadow: 0 8px 25px rgba(0,122,135,0.3); }
        .gpe-btn-inscribir:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,180,204,0.4); }
        .gpe-btn-inscribir:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .gpe-alerta { padding: 15px 20px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; }
        .gpe-ok   { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .gpe-warn { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .gpe-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 600px) { .gpe-form-grid { grid-template-columns: 1fr; } }
    </style>

    <script>
    var gpe_territorios_form = <?php echo json_encode( $territorios ); ?>;
    function gpe_form_provincias(sel, eid) {
        var ps = document.getElementById('gpe-prov-form-' + eid);
        ps.innerHTML = '<option value="">-- Selecciona provincia --</option>';
        var ccaa = sel.value;
        if (ccaa && gpe_territorios_form[ccaa]) {
            gpe_territorios_form[ccaa].forEach(function(p) {
                ps.innerHTML += '<option value="' + p + '">' + p + '</option>';
            });
        }
    }

    document.getElementById('gpe-form-inscripcion').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('gpe-btn-submit-<?php echo $eid; ?>');
        var resp = document.getElementById('gpe-form-respuesta');
        btn.disabled = true;
        btn.textContent = 'Enviando...';
        resp.style.display = 'none';

        var fd = new FormData(this);
        fd.append('action', 'gpe_inscribirse');
        fd.append('evento_id', '<?php echo $eid; ?>');

        fetch(<?php echo json_encode( admin_url('admin-ajax.php') ); ?>, {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            resp.style.display = 'block';
            if (data.success) {
                resp.className = 'gpe-alerta gpe-ok';
                resp.textContent = data.data.msg;
                document.getElementById('gpe-form-inscripcion').reset();
                <?php if ( $max > 0 ) : ?>
                // Recargar para actualizar barra de aforo
                setTimeout(() => location.reload(), 2500);
                <?php endif; ?>
            } else {
                resp.className = 'gpe-alerta gpe-error';
                resp.textContent = data.data.msg;
                btn.disabled = false;
                btn.textContent = 'Reservar mi plaza';
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
