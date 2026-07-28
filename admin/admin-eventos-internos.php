<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Metaboxes para evento interno ────────────────────────────────────────────
add_action( 'add_meta_boxes', 'gpe_metaboxes_evento_interno' );
function gpe_metaboxes_evento_interno() {
    add_meta_box('gpe_int_basico',    'Datos del evento',         'gpe_render_int_basico',    'gpe_interno', 'normal', 'high');
    add_meta_box('gpe_int_acceso',    'Acceso y órganos',         'gpe_render_int_acceso',    'gpe_interno', 'side',   'high');
    add_meta_box('gpe_int_modalidad', 'Modalidades de asistencia','gpe_render_int_modalidad', 'gpe_interno', 'side',   'default');
    add_meta_box('gpe_int_inscritos', 'Embajadores/as inscritos', 'gpe_render_int_inscritos', 'gpe_interno', 'normal', 'default');
    add_meta_box('gpe_int_auditoria', '🔐 Creación',             'gpe_render_int_auditoria', 'gpe_interno', 'side',   'low');
}

function gpe_render_int_basico( $post ) {
    wp_nonce_field('gpe_guardar_interno_action', 'gpe_interno_nonce');
    $fecha     = get_post_meta($post->ID,'_gpe_int_fecha',true);
    $hora      = get_post_meta($post->ID,'_gpe_int_hora',true);
    $lugar     = get_post_meta($post->ID,'_gpe_int_lugar',true);
    $direccion = get_post_meta($post->ID,'_gpe_int_direccion',true);
    $claim     = get_post_meta($post->ID,'_gpe_int_claim',true);
    $por_que   = get_post_meta($post->ID,'_gpe_int_por_que',true);
    $timeline  = get_post_meta($post->ID,'_gpe_int_timeline',true) ?: array(array('hora'=>'','titulo'=>'','desc'=>''));
    ?>
    <table class="form-table">
        <tr><th><label>Fecha *</label></th><td><input type="date" name="gpe_int_fecha" value="<?php echo esc_attr($fecha); ?>" class="regular-text"></td></tr>
        <tr><th><label>Hora</label></th><td><input type="time" name="gpe_int_hora" value="<?php echo esc_attr($hora); ?>" class="regular-text"></td></tr>
        <tr><th><label>Lugar / Espacio</label></th><td><input type="text" name="gpe_int_lugar" value="<?php echo esc_attr($lugar); ?>" class="large-text" placeholder="Sede central, videoconferencia..."></td></tr>
        <tr><th><label>Dirección</label></th><td><input type="text" name="gpe_int_direccion" value="<?php echo esc_attr($direccion); ?>" class="large-text"></td></tr>
        <tr><th><label>Claim / Subtítulo</label></th><td><input type="text" name="gpe_int_claim" value="<?php echo esc_attr($claim); ?>" class="large-text"></td></tr>
    </table>
    <h3 style="border-bottom:1px solid #eee;padding-bottom:8px;">Descripción / Orden del día</h3>
    <?php wp_editor($por_que, 'gpe_int_por_que', array('textarea_name'=>'gpe_int_por_que','media_buttons'=>false,'textarea_rows'=>5,'teeny'=>true)); ?>
    <h3 style="border-bottom:1px solid #eee;padding-bottom:8px;margin-top:20px;">Timeline de la sesión</h3>
    <div id="gpe-int-timeline-wrap">
        <?php foreach ($timeline as $i => $b) : ?>
        <div class="gpe-int-tl-bloque" style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;background:#f9f9f9;padding:10px;border-radius:4px;border:1px solid #e0e0e0;">
            <div style="flex-shrink:0;"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;color:#555;">Hora</label>
            <input type="text" name="gpe_int_timeline[<?php echo $i; ?>][hora]" value="<?php echo esc_attr($b['hora']??''); ?>" placeholder="10:00" style="width:72px;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;"></div>
            <div style="flex:1;"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;color:#555;">Punto del orden del día</label>
            <input type="text" name="gpe_int_timeline[<?php echo $i; ?>][titulo]" value="<?php echo esc_attr($b['titulo']??''); ?>" style="width:100%;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;margin-bottom:5px;" placeholder="Apertura">
            <input type="text" name="gpe_int_timeline[<?php echo $i; ?>][desc]" value="<?php echo esc_attr($b['desc']??''); ?>" style="width:100%;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;" placeholder="Descripción opcional"></div>
            <div style="flex-shrink:0;padding-top:20px;"><button type="button" class="button gpe-int-tl-rm">✕</button></div>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="gpe-int-tl-add">+ Añadir punto</button>
    <script>
    jQuery(function($){
        var idx = <?php echo count($timeline); ?>;
        $('#gpe-int-tl-add').click(function(){
            var h='<div class="gpe-int-tl-bloque" style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;background:#f9f9f9;padding:10px;border-radius:4px;border:1px solid #e0e0e0;">'
                +'<div style="flex-shrink:0;"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;color:#555;">Hora</label>'
                +'<input type="text" name="gpe_int_timeline['+idx+'][hora]" placeholder="10:00" style="width:72px;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;"></div>'
                +'<div style="flex:1;"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;color:#555;">Punto del orden del día</label>'
                +'<input type="text" name="gpe_int_timeline['+idx+'][titulo]" style="width:100%;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;margin-bottom:5px;" placeholder="Apertura">'
                +'<input type="text" name="gpe_int_timeline['+idx+'][desc]" style="width:100%;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;" placeholder="Descripción opcional"></div>'
                +'<div style="flex-shrink:0;padding-top:20px;"><button type="button" class="button gpe-int-tl-rm">✕</button></div></div>';
            $('#gpe-int-timeline-wrap').append(h); idx++;
        });
        $(document).on('click','.gpe-int-tl-rm',function(){ $(this).closest('.gpe-int-tl-bloque').remove(); });
    });
    </script>
    <?php
}

function gpe_render_int_acceso( $post ) {
    $organos_asignados = get_post_meta($post->ID,'_gpe_organos_invitados',true) ?: array();
    $todos_organos     = function_exists('gpa_api_get_organos')      ? gpa_api_get_organos()      : array();
    $tipos_label       = function_exists('gpa_api_get_tipos_organo') ? gpa_api_get_tipos_organo() : array();
    ?>
    <p style="font-size:12px;color:#666;margin-top:0;">Solo los/las embajadores/as de los órganos marcados podrán inscribirse.</p>
    <div style="max-height:250px;overflow-y:auto;border:1px solid #ddd;padding:10px;border-radius:4px;background:#fff;">
    <?php if (empty($todos_organos)) : ?>
        <p style="color:#aaa;font-size:13px;">Aún no hay órganos creados. <a href="<?php echo esc_url(admin_url('admin.php?page=gp-amb-organos')); ?>">Crear órganos en GP Ambassadors →</a></p>
    <?php else : ?>
        <?php foreach ($todos_organos as $org) : ?>
        <label style="display:block;margin-bottom:6px;font-size:13px;cursor:pointer;">
            <input type="checkbox" name="gpe_organos_invitados[]" value="<?php echo $org['id']; ?>" <?php checked(in_array($org['id'], array_map('intval',$organos_asignados))); ?>>
            <strong><?php echo esc_html($org['nombre']); ?></strong>
            <span style="color:#888;"> — <?php echo esc_html($tipos_label[$org['tipo']] ?? $org['tipo']); ?>
            <?php if ($org['ambito_valor']) echo ' · ' . esc_html($org['ambito_valor']); ?></span>
        </label>
        <?php endforeach; ?>
    <?php endif; ?>
    </div>
    <?php
}

function gpe_render_int_modalidad( $post ) {
    $sel = get_post_meta($post->ID,'_gpe_int_modalidades',true) ?: array('presencial');
    $opts = array('presencial'=>'Presencial','telematica'=>'Telemática','mixta'=>'Mixta');
    ?>
    <p style="font-size:12px;color:#666;margin-top:0;">Selecciona qué modalidades se ofrecerán al inscribirse.</p>
    <?php foreach ($opts as $val => $lbl) : ?>
    <label style="display:block;margin-bottom:8px;font-size:13px;">
        <input type="checkbox" name="gpe_int_modalidades[]" value="<?php echo $val; ?>" <?php checked(in_array($val,$sel)); ?>>
        <?php echo esc_html($lbl); ?>
    </label>
    <?php endforeach; ?>
    <?php
}

function gpe_render_int_inscritos( $post ) {
    if ( $post->post_status !== 'publish' ) { echo '<p style="color:#aaa;">Publica el evento para ver inscritos.</p>'; return; }
    global $wpdb;
    $inscritos = $wpdb->get_results($wpdb->prepare(
        "SELECT i.*, u.display_name, u.user_email, pu.display_name as delegado_nombre
         FROM {$wpdb->prefix}gpe_inscripciones_internas i
         LEFT JOIN {$wpdb->users} u  ON u.ID  = i.user_id
         LEFT JOIN {$wpdb->users} pu ON pu.ID = i.delegado_por
         WHERE i.evento_id = %d AND i.estado = 'confirmada'
         ORDER BY i.fecha_reg ASC",
        $post->ID
    ));
    echo '<p><strong>' . count($inscritos) . '</strong> embajadores/as confirmados/as</p>';
    if (empty($inscritos)) { echo '<p style="color:#aaa;">Aún sin inscripciones.</p>'; return; }
    echo '<table class="widefat striped" style="border-radius:8px;overflow:hidden;font-size:13px;">';
    echo '<thead><tr><th>Embajador/a</th><th>Email</th><th>DNI/NIE</th><th>Modalidad</th><th>Delegado por</th><th>Fecha</th></tr></thead><tbody>';
    $lbl = array('presencial'=>'Presencial','telematica'=>'Telemática','mixta'=>'Mixta');
    foreach ($inscritos as $i) {
        echo '<tr>';
        echo '<td><strong>' . esc_html($i->display_name) . '</strong></td>';
        echo '<td>' . esc_html($i->user_email) . '</td>';
        echo '<td>' . esc_html($i->dni_nie ?: '—') . '</td>';
        echo '<td>' . esc_html($lbl[$i->modalidad] ?? $i->modalidad) . '</td>';
        echo '<td>' . esc_html($i->delegado_nombre ?: '—') . '</td>';
        echo '<td style="font-size:11px;color:#888;">' . date('d/m/Y H:i',strtotime($i->fecha_reg)) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    // CSV
    $url_csv = add_query_arg(array('gpe_int_csv'=>1,'evento_id'=>$post->ID), admin_url('edit.php?post_type=gpe_interno'));
    echo '<p style="margin-top:12px;"><a href="' . esc_url($url_csv) . '" class="button">⬇ Exportar CSV</a></p>';
}

function gpe_render_int_auditoria( $post ) {
    $autor = get_userdata($post->post_author);
    $nombre = $autor ? $autor->display_name : '—';
    $email  = $autor ? $autor->user_email   : '—';
    $fecha  = date_i18n('d/m/Y H:i', strtotime($post->post_date));
    echo '<table style="width:100%;font-size:13px;">';
    echo '<tr><td style="color:#555;font-weight:600;width:55px;">Usuario</td><td><strong style="color:#007a87;">' . esc_html($nombre) . '</strong></td></tr>';
    echo '<tr><td style="color:#555;font-weight:600;">Email</td><td style="font-size:12px;color:#666;">' . esc_html($email) . '</td></tr>';
    echo '<tr><td style="color:#555;font-weight:600;">Fecha</td><td style="font-size:12px;color:#666;">' . esc_html($fecha) . '</td></tr>';
    echo '</table><p style="font-size:11px;color:#aaa;margin-top:8px;">Solo lectura.</p>';
}

// ── Guardar metadatos evento interno ─────────────────────────────────────────
add_action('save_post_gpe_interno', 'gpe_guardar_evento_interno');
function gpe_guardar_evento_interno($post_id) {
    if (!isset($_POST['gpe_interno_nonce']) || !wp_verify_nonce($_POST['gpe_interno_nonce'],'gpe_guardar_interno_action')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post',$post_id)) return;

    $campos = array('gpe_int_fecha','gpe_int_hora','gpe_int_lugar','gpe_int_direccion','gpe_int_claim');
    foreach ($campos as $c) {
        if (isset($_POST[$c])) update_post_meta($post_id,'_'.$c, sanitize_text_field($_POST[$c]));
    }
    if (isset($_POST['gpe_int_por_que'])) update_post_meta($post_id,'_gpe_int_por_que', wp_kses_post($_POST['gpe_int_por_que']));

    $organos = isset($_POST['gpe_organos_invitados']) ? array_map('intval',$_POST['gpe_organos_invitados']) : array();
    update_post_meta($post_id,'_gpe_organos_invitados', $organos);

    $modalidades = isset($_POST['gpe_int_modalidades']) ? array_map('sanitize_text_field',$_POST['gpe_int_modalidades']) : array('presencial');
    update_post_meta($post_id,'_gpe_int_modalidades', $modalidades);

    $tl_raw = isset($_POST['gpe_int_timeline']) ? (array)$_POST['gpe_int_timeline'] : array();
    $tl = array();
    foreach ($tl_raw as $b) {
        $tit = sanitize_text_field($b['titulo']??'');
        if ($tit) $tl[] = array('hora'=>sanitize_text_field($b['hora']??''),'titulo'=>$tit,'desc'=>sanitize_text_field($b['desc']??''));
    }
    update_post_meta($post_id,'_gpe_int_timeline',$tl);
}

// ── CSV inscritos internos ────────────────────────────────────────────────────
add_action('admin_init','gpe_exportar_csv_internos');
function gpe_exportar_csv_internos() {
    if (!isset($_GET['gpe_int_csv']) || !isset($_GET['evento_id'])) return;
    if (!current_user_can('edit_posts')) wp_die('Sin permisos.');
    global $wpdb;
    $eid = intval($_GET['evento_id']);
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT i.*, u.display_name, u.user_email FROM {$wpdb->prefix}gpe_inscripciones_internas i
         LEFT JOIN {$wpdb->users} u ON u.ID=i.user_id WHERE i.evento_id=%d ORDER BY i.fecha_reg ASC",
        $eid
    ));
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="inscritos-internos-'.$eid.'.csv"');
    $fp = fopen('php://output','w');
    fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
    fputcsv($fp,array('Nombre','Email','DNI/NIE','Modalidad','Delegado por','Fecha'));
    foreach ($rows as $r) {
        $del = $r->delegado_por ? get_userdata($r->delegado_por)->display_name : '';
        fputcsv($fp,array($r->display_name,$r->user_email,$r->dni_nie,$r->modalidad,$del,date('d/m/Y H:i',strtotime($r->fecha_reg))));
    }
    fclose($fp); exit;
}
