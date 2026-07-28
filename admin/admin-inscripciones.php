<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gpe_render_inscritos_page() {
    global $wpdb;

    // Procesar cancelación manual
    if ( isset($_GET['cancelar_id']) && check_admin_referer('gpe_cancelar_inscripcion') ) {
        $wpdb->update( $wpdb->prefix . 'gpe_inscripciones', array('estado'=>'cancelada'), array('id'=>intval($_GET['cancelar_id'])), array('%s'), array('%d') );
        // Notificar lista de espera si se libera una plaza
        $ev_id_cancel = intval( $_GET['evento_id'] ?? 0 );
        if ( $ev_id_cancel && function_exists('gpe_notificar_lista_espera') ) {
            gpe_notificar_lista_espera( $ev_id_cancel );
        }
        echo '<div class="notice notice-success"><p>Inscripción cancelada.</p></div>';
    }

    // Evento seleccionado
    $evento_id = intval( $_GET['evento_id'] ?? 0 );
    $eventos = get_posts(array('post_type'=>'evento_home','posts_per_page'=>-1,'orderby'=>'meta_value','meta_key'=>'_med_fecha_evento','order'=>'DESC'));

    // Filtrar eventos por territorio si es coordinador
    if ( ! current_user_can('manage_options') ) {
        $territorio = gpe_territorio_usuario();
        $eventos = array_filter($eventos, function($ev) use ($territorio) {
            return get_post_meta($ev->ID, '_gpe_ccaa_evento', true) === $territorio['ccaa'];
        });
    }

    // Exportar CSV
    if ( isset($_GET['exportar_csv']) && $evento_id ) {
        gpe_exportar_csv_inscritos($evento_id);
        exit;
    }

    echo '<div class="wrap gpe-wrap">';

    // Selector de evento
    echo '<form method="get" class="gpe-filters"><input type="hidden" name="page" value="gpe-inscritos">';
    echo '<div class="gpe-filter-row"><label style="font-weight:600;font-size:13px;">Evento:</label>';
    echo '<select name="evento_id" class="gpe-select" onchange="this.form.submit()">';
    echo '<option value="">— Selecciona un evento —</option>';
    foreach ( $eventos as $ev ) {
        $fecha = get_post_meta($ev->ID,'_med_fecha_evento',true);
        printf('<option value="%d" %s>%s%s</option>', $ev->ID, selected($evento_id,$ev->ID,false), esc_html($ev->post_title), $fecha ? ' (' . date('d/m/Y',strtotime($fecha)) . ')' : '');
    }
    echo '</select></div></form>';

    if ( ! $evento_id ) {
        echo '<div class="gpe-card"><div class="gpe-table-empty">Selecciona un evento para ver sus inscritos.</div></div>';
        echo '</div>';
        return;
    }

    // Cabecera con el evento elegido
    $ev_sel = get_post($evento_id);
    $f_sel  = get_post_meta($evento_id,'_med_fecha_evento',true);
    echo '<div class="gpe-header"><div class="gpe-header-info"><div>';
    echo '<h1 class="gpe-header-title">🎟️ ' . esc_html($ev_sel->post_title) . '</h1>';
    echo '<div class="gpe-header-sub">' . ($f_sel ? date('d/m/Y', strtotime($f_sel)) : 'Sin fecha') . ' · Gestión de inscritos</div>';
    echo '</div></div>';
    echo '<a href="' . esc_url( add_query_arg(array('exportar_csv'=>1,'evento_id'=>$evento_id,'page'=>'gpe-inscritos'), admin_url('admin.php')) ) . '" class="gpe-btn gpe-btn-primary">⬇ Exportar CSV</a>';
    echo '</div>';

    $evento = get_post($evento_id);
    $inscritos = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id = %d ORDER BY fecha_reg DESC",
        $evento_id
    ) );

    $total     = count($inscritos);
    $confirmados = count(array_filter($inscritos, fn($i) => $i->estado === 'confirmada'));
    $cancelados  = count(array_filter($inscritos, fn($i) => $i->estado === 'cancelada'));
    $aforo       = gpe_aforo_maximo($evento_id);
    ?>

    <div class="gpe-stats">
        <div class="gpe-stat-card primary">
            <div class="gpe-stat-card-num"><?php echo $confirmados; ?></div>
            <div class="gpe-stat-card-label">Confirmados</div>
        </div>
        <?php if ($aforo) : ?>
        <div class="gpe-stat-card">
            <div class="gpe-stat-card-num"><?php echo max(0, $aforo - $confirmados); ?></div>
            <div class="gpe-stat-card-label">Plazas libres</div>
        </div>
        <?php endif; ?>
        <div class="gpe-stat-card red">
            <div class="gpe-stat-card-num"><?php echo $cancelados; ?></div>
            <div class="gpe-stat-card-label">Canceladas</div>
        </div>
    </div>

    <?php
    $tab_activa = sanitize_text_field($_GET['tab_insc'] ?? 'inscritos');
    $url_ti = add_query_arg(array('page'=>'gpe-inscritos','evento_id'=>$evento_id,'tab_insc'=>'inscritos'), admin_url('admin.php'));
    $url_te = add_query_arg(array('page'=>'gpe-inscritos','evento_id'=>$evento_id,'tab_insc'=>'espera'),    admin_url('admin.php'));
    ?>
    <div class="gpe-tabs">
        <a href="<?php echo esc_url($url_ti); ?>" class="gpe-tab <?php echo $tab_activa==='inscritos'?'active':'';?>">🎟️ Inscritos (<?php echo $confirmados; ?>)</a>
        <a href="<?php echo esc_url($url_te); ?>" class="gpe-tab <?php echo $tab_activa==='espera'?'active':'';?>">⏳ Lista de espera</a>
    </div>
    <?php if ($tab_activa === 'espera') :
        gpe_render_lista_espera_tab($evento_id);
    else : ?>
    <div class="gpe-card flush">
    <table class="gpe-table">
        <thead>
            <tr>
                <th>Inscrito</th><th>Email</th><th>Teléfono</th>
                <th>CCAA</th><th>Provincia</th><th>Edad</th><th>Cómo conoció</th>
                <th>Registro</th><th>Estado</th><th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inscritos)) : ?>
                <tr><td colspan="10"><div class="gpe-table-empty">Aún no hay inscripciones para este evento.</div></td></tr>
            <?php else : foreach ($inscritos as $i) :
                $estado_color = $i->estado === 'confirmada' ? '#27ae60' : '#c0392b';
                $iniciales = strtoupper(mb_substr($i->nombre,0,1) . mb_substr($i->apellidos,0,1));
            ?>
                <tr style="opacity:<?php echo $i->estado==='cancelada' ? '0.5' : '1'; ?>">
                    <td><span class="gpe-avatar"><?php echo esc_html($iniciales); ?></span><strong><?php echo esc_html($i->nombre . ' ' . $i->apellidos); ?></strong></td>
                    <td><a href="mailto:<?php echo esc_attr($i->email); ?>" style="color:#007a87;"><?php echo esc_html($i->email); ?></a></td>
                    <td><?php echo esc_html($i->telefono ?: '—'); ?></td>
                    <td><?php echo esc_html($i->ccaa ?: '—'); ?></td>
                    <td><?php echo esc_html($i->provincia ?: '—'); ?></td>
                    <td><?php echo $i->edad ?: '—'; ?></td>
                    <td><?php echo esc_html($i->como_conocio ?: '—'); ?></td>
                    <td style="font-size:12px;color:#888;white-space:nowrap;"><?php echo date('d/m/Y H:i', strtotime($i->fecha_reg)); ?></td>
                    <td><span class="gpe-pill <?php echo $i->estado==='confirmada'?'solid-green':'solid-red'; ?>"><?php echo ucfirst($i->estado); ?></span></td>
                    <td>
                        <?php if ($i->estado === 'confirmada') : ?>
                            <a href="<?php echo esc_url( wp_nonce_url( add_query_arg(array('cancelar_id'=>$i->id,'evento_id'=>$evento_id,'page'=>'gpe-inscritos'), admin_url('admin.php')), 'gpe_cancelar_inscripcion' ) ); ?>" style="color:#c0392b;font-size:12px;font-weight:600;text-decoration:none;" onclick="return confirm('¿Cancelar esta inscripción?')">Cancelar</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
    <?php endif; // tab_activa ?>
    </div>
    <?php
}

function gpe_exportar_csv_inscritos( $evento_id ) {
    global $wpdb;
    $evento = get_post($evento_id);
    $rows   = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id = %d ORDER BY fecha_reg ASC",
        $evento_id
    ) );

    $nombre_archivo = 'inscritos-' . sanitize_title($evento->post_title) . '-' . date('Y-m-d') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');

    $out = fopen('php://output', 'w');
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
    fputcsv($out, array('ID','Nombre','Apellidos','Email','Teléfono','CCAA','Provincia','Edad','Cómo conoció','Comentario','Estado','Fecha registro'));
    foreach ($rows as $r) {
        fputcsv($out, array($r->id,$r->nombre,$r->apellidos,$r->email,$r->telefono,$r->ccaa,$r->provincia,$r->edad,$r->como_conocio,$r->comentario,$r->estado,$r->fecha_reg));
    }
    fclose($out);
}

// ── Funcionalidad de lista de espera integrada (fusionada en inscritos) ───────
// El menú independiente se elimina; accesible desde la pestaña de inscritos
// cuando se selecciona un evento con aforo completo.

function gpe_render_lista_espera_tab( $evento_id ) {
    global $wpdb;
    $lista = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_lista_espera WHERE evento_id=%d ORDER BY fecha_reg ASC",
        $evento_id
    ));
    $total = count($lista);
    if (empty($lista)) {
        echo '<div class="gpe-card"><div class="gpe-table-empty">No hay nadie en lista de espera.</div></div>';
        return;
    }
    echo '<div class="gpe-card flush"><table class="gpe-table">';
    echo '<thead><tr><th>#</th><th>Nombre</th><th>Email</th><th>Fecha</th><th>Notificado</th></tr></thead><tbody>';
    foreach ($lista as $idx => $e) {
        $ini = strtoupper(mb_substr($e->nombre,0,2));
        echo '<tr>';
        echo '<td><strong style="color:var(--gpe-1);">#' . ($idx+1) . '</strong></td>';
        echo '<td><span class="gpe-avatar">' . esc_html($ini) . '</span><strong>' . esc_html($e->nombre) . '</strong></td>';
        echo '<td><a href="mailto:' . esc_attr($e->email) . '" style="color:var(--gpe-1);">' . esc_html($e->email) . '</a></td>';
        echo '<td style="font-size:12px;color:var(--gpe-muted);white-space:nowrap;">' . date('d/m/Y H:i',strtotime($e->fecha_reg)) . '</td>';
        echo '<td>' . ($e->notificado ? '<span class="gpe-pill green">Avisado</span>' : '<span class="gpe-pill amber">Pendiente</span>') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
}
