<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── Metaboxes de eventos institucionales ─────────────────────────────────────
add_action( 'add_meta_boxes', 'gpe_metaboxes_institucional' );
function gpe_metaboxes_institucional() {
    add_meta_box( 'gpe_inst_datos',  '📋 Datos del Evento Institucional', 'gpe_render_inst_datos',  'gpe_institucional', 'normal', 'high' );
    add_meta_box( 'gpe_inst_email',  '📧 Email de Invitación',            'gpe_render_inst_email',  'gpe_institucional', 'normal', 'default' );
    add_meta_box( 'gpe_inst_enviar', '🚀 Enviar Invitaciones',            'gpe_render_inst_enviar', 'gpe_institucional', 'side',   'high' );
}

function gpe_render_inst_datos( $post ) {
    wp_nonce_field( 'gpe_save_inst_action', 'gpe_inst_nonce' );
    $fecha     = get_post_meta( $post->ID, '_gpe_inst_fecha',  true );
    $lugar     = get_post_meta( $post->ID, '_gpe_inst_lugar',  true );
    $url_rsvp  = get_post_meta( $post->ID, '_gpe_inst_url_rsvp', true );
    $tipo      = get_post_meta( $post->ID, '_gpe_inst_tipo',   true ) ?: 'asamblea';
    $recurrente = get_post_meta( $post->ID, '_gpe_inst_recurrente', true );
    $ccaa_dest = get_post_meta( $post->ID, '_gpe_inst_ccaa_dest', true ) ?: array();
    $territorios = gpe_territorios_espana();
    ?>
    <style>
        .gpe-inst-row { margin-bottom:14px; }
        .gpe-inst-row label { display:block; font-weight:600; font-size:13px; margin-bottom:5px; }
        .gpe-inst-row input, .gpe-inst-row select { width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:13px; }
        .gpe-inst-2col { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    </style>
    <div class="gpe-inst-2col">
        <div class="gpe-inst-row">
            <label>📅 Fecha del evento</label>
            <input type="date" name="gpe_inst_fecha" value="<?php echo esc_attr($fecha); ?>">
        </div>
        <div class="gpe-inst-row">
            <label>🏷️ Tipo de evento</label>
            <select name="gpe_inst_tipo">
                <option value="asamblea"    <?php selected($tipo,'asamblea'); ?>>Asamblea General</option>
                <option value="congreso"    <?php selected($tipo,'congreso'); ?>>Congreso Anual</option>
                <option value="junta"       <?php selected($tipo,'junta'); ?>>Junta Directiva</option>
                <option value="formacion"   <?php selected($tipo,'formacion'); ?>>Formación Interna</option>
                <option value="celebracion" <?php selected($tipo,'celebracion'); ?>>Celebración</option>
                <option value="otro"        <?php selected($tipo,'otro'); ?>>Otro</option>
            </select>
        </div>
    </div>
    <div class="gpe-inst-row">
        <label>📍 Lugar / Sede</label>
        <input type="text" name="gpe_inst_lugar" value="<?php echo esc_attr($lugar); ?>" placeholder="Sede central, Madrid">
    </div>
    <div class="gpe-inst-row">
        <label>🔗 URL de confirmación de asistencia (RSVP)</label>
        <input type="url" name="gpe_inst_url_rsvp" value="<?php echo esc_url($url_rsvp); ?>" placeholder="https://...">
    </div>
    <div class="gpe-inst-row">
        <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="gpe_inst_recurrente" value="1" <?php checked($recurrente,'1'); ?>>
            Evento recurrente anual (Asamblea, Congreso…)
        </label>
    </div>
    <div class="gpe-inst-row">
        <label>🗺️ CCAA destinatarias (vacío = todas)</label>
        <div style="background:#fff; border:1px solid #ccd0d4; border-radius:6px; padding:12px; max-height:180px; overflow-y:auto;">
            <?php foreach ( array_keys($territorios) as $cc ) : ?>
                <label style="display:block; margin-bottom:5px; font-size:13px; cursor:pointer;">
                    <input type="checkbox" name="gpe_inst_ccaa_dest[]" value="<?php echo esc_attr($cc); ?>" <?php checked( in_array($cc, (array)$ccaa_dest) ); ?>>
                    <?php echo esc_html($cc); ?>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function gpe_render_inst_email( $post ) {
    $asunto = get_post_meta( $post->ID, '_gpe_inst_asunto', true );
    $cuerpo = get_post_meta( $post->ID, '_gpe_inst_cuerpo', true );
    ?>
    <div style="margin-bottom:14px;">
        <label style="display:block; font-weight:600; font-size:13px; margin-bottom:5px;">Asunto del email</label>
        <input type="text" name="gpe_inst_asunto" value="<?php echo esc_attr($asunto); ?>" style="width:100%; padding:8px 12px; border:1px solid #ddd; border-radius:6px; font-size:13px;" placeholder="Te invitamos a la Asamblea General 2025">
    </div>
    <div style="background:#f0f9fa; border-radius:8px; padding:12px; margin-bottom:14px; font-size:12px; color:#555; line-height:1.6;">
        <strong>Variables dinámicas disponibles:</strong><br>
        <code>{{nombre}}</code> — Nombre del destinatario &nbsp;|&nbsp;
        <code>{{ccaa}}</code> — Su CCAA &nbsp;|&nbsp;
        <code>{{provincia}}</code> — Su provincia &nbsp;|&nbsp;
        <code>{{fecha}}</code> — Fecha del evento &nbsp;|&nbsp;
        <code>{{lugar}}</code> — Lugar del evento
    </div>
    <div>
        <label style="display:block; font-weight:600; font-size:13px; margin-bottom:5px;">Cuerpo del mensaje</label>
        <?php wp_editor( $cuerpo, 'gpe_inst_cuerpo', array('textarea_name'=>'gpe_inst_cuerpo','media_buttons'=>false,'textarea_rows'=>8) ); ?>
    </div>
    <?php
}

function gpe_render_inst_enviar( $post ) {
    if ( $post->post_status !== 'publish' ) {
        echo '<p style="color:#888; font-size:13px;">Publica el evento primero para poder enviar invitaciones.</p>';
        return;
    }
    global $wpdb;
    $enviados_total = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}gpe_emails_log WHERE evento_id = %d AND estado = 'enviado'",
        $post->ID
    ) );
    ?>
    <p style="font-size:13px; color:#555;">Pega los emails de los destinatarios, uno por línea.</p>
    <textarea id="gpe_emails_dest" rows="8" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px; font-size:12px;" placeholder="maria@ejemplo.com
juan@ejemplo.com
coordinador@murcia.com"></textarea>
    <p style="font-size:12px; color:#888; margin-top:5px;">También puedes incluir nombre separado por coma:<br><code>María García, maria@ejemplo.com</code></p>
    <button type="button" id="gpe_btn_enviar_inst" class="button button-primary" style="width:100%; margin-top:10px; background:#007a87; border-color:#007a87; padding:10px;">
        🚀 Enviar invitaciones
    </button>
    <div id="gpe_inst_respuesta" style="margin-top:10px; display:none;"></div>
    <?php if ( $enviados_total > 0 ) : ?>
        <div style="margin-top:15px; background:#f0f9fa; border-radius:8px; padding:10px; text-align:center;">
            <strong style="color:#007a87;"><?php echo $enviados_total; ?></strong><br>
            <span style="font-size:12px; color:#666;">emails enviados en total</span>
        </div>
        <p style="text-align:center; margin-top:8px;">
            <a href="<?php echo esc_url( admin_url('admin.php?page=gpe-invitaciones&evento_inv=' . $post->ID . '&inv_tab=historial') ); ?>" style="font-size:12px;">Ver historial completo</a>
        </p>
    <?php endif; ?>
    <script>
    document.getElementById('gpe_btn_enviar_inst').addEventListener('click', function(){
        var emails = document.getElementById('gpe_emails_dest').value.trim();
        var resp   = document.getElementById('gpe_inst_respuesta');
        if (!emails) { alert('Escribe al menos un email.'); return; }
        this.disabled = true;
        this.textContent = 'Enviando…';
        resp.style.display = 'none';

        jQuery.ajax({
            url: gpe_ajax.url, type:'POST',
            data: { action:'gpe_enviar_emails_inst', nonce: gpe_ajax.nonce,
                    evento_id: '<?php echo $post->ID; ?>', emails: emails },
            success: function(r){
                resp.style.display = 'block';
                resp.style.background = r.success ? '#d4edda' : '#f8d7da';
                resp.style.color      = r.success ? '#155724' : '#721c24';
                resp.style.padding    = '10px';
                resp.style.borderRadius = '8px';
                resp.textContent = r.data.msg;
                document.getElementById('gpe_btn_enviar_inst').disabled = false;
                document.getElementById('gpe_btn_enviar_inst').textContent = '🚀 Enviar invitaciones';
            }
        });
    });
    </script>
    <?php
}

// ─── Guardar metas del evento institucional ───────────────────────────────────
add_action( 'save_post_gpe_institucional', 'gpe_guardar_institucional', 10, 1 );
function gpe_guardar_institucional( $post_id ) {
    if ( ! isset($_POST['gpe_inst_nonce']) || ! wp_verify_nonce($_POST['gpe_inst_nonce'], 'gpe_save_inst_action') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    $campos = array(
        'gpe_inst_fecha' => '_gpe_inst_fecha',
        'gpe_inst_lugar' => '_gpe_inst_lugar',
        'gpe_inst_tipo'  => '_gpe_inst_tipo',
        'gpe_inst_asunto'=> '_gpe_inst_asunto',
    );
    foreach ( $campos as $pk => $mk ) {
        if ( isset($_POST[$pk]) ) update_post_meta( $post_id, $mk, sanitize_text_field($_POST[$pk]) );
    }
    if ( isset($_POST['gpe_inst_url_rsvp']) ) update_post_meta( $post_id, '_gpe_inst_url_rsvp', esc_url_raw($_POST['gpe_inst_url_rsvp']) );
    if ( isset($_POST['gpe_inst_cuerpo']) )   update_post_meta( $post_id, '_gpe_inst_cuerpo',   wp_kses_post($_POST['gpe_inst_cuerpo']) );
    update_post_meta( $post_id, '_gpe_inst_recurrente', isset($_POST['gpe_inst_recurrente']) ? '1' : '0' );
    $ccaa_dest = isset($_POST['gpe_inst_ccaa_dest']) ? array_map('sanitize_text_field', $_POST['gpe_inst_ccaa_dest']) : array();
    update_post_meta( $post_id, '_gpe_inst_ccaa_dest', $ccaa_dest );
}

// Tabla de historial reutilizable (se muestra dentro de la pestaña Historial de Invitaciones).
function gpe_render_inst_log_tabla( $evento_id = 0 ) {
    global $wpdb;
    $evento_id = intval($evento_id);
    $rows = $wpdb->get_results( $evento_id
        ? $wpdb->prepare("SELECT * FROM {$wpdb->prefix}gpe_emails_log WHERE evento_id = %d ORDER BY enviado_at DESC", $evento_id)
        : "SELECT l.*, p.post_title FROM {$wpdb->prefix}gpe_emails_log l LEFT JOIN {$wpdb->posts} p ON p.ID = l.evento_id ORDER BY l.enviado_at DESC LIMIT 200"
    );
    if ( empty($rows) ) {
        echo '<div class="gpe-card"><div class="gpe-table-empty">No hay emails enviados aún para este evento.</div></div>';
        return;
    }
    echo '<div class="gpe-card flush"><table class="gpe-table">';
    echo '<thead><tr><th>Destinatario</th><th>Asunto</th><th>Fecha</th><th>Estado</th></tr></thead><tbody>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td><strong>' . esc_html($r->destinatario) . '</strong></td>';
        echo '<td>' . esc_html($r->asunto) . '</td>';
        echo '<td style="font-size:12px;color:var(--gpe-muted);white-space:nowrap;">' . date('d/m/Y H:i', strtotime($r->enviado_at)) . '</td>';
        echo '<td><span class="gpe-pill ' . ($r->estado==='enviado'?'green':'red') . '">' . ucfirst($r->estado) . '</span></td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
