<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gpe_render_estadisticas() {
    global $wpdb;

    $evento_id = intval($_GET['evento_id'] ?? 0);
    $eventos   = get_posts(array('post_type'=>'evento_home','posts_per_page'=>-1,'orderby'=>'meta_value','meta_key'=>'_med_fecha_evento','order'=>'DESC','post_status'=>array('publish','draft','gpe_cancelado','gpe_pospuesto')));

    if ( ! current_user_can('manage_options') && ! current_user_can('edit_others_posts') ) {
        $territorio = gpe_territorio_usuario();
        $eventos = array_filter($eventos, fn($ev) => get_post_meta($ev->ID,'_gpe_ccaa_evento',true) === $territorio['ccaa']);
    }

    echo '<div class="wrap gpe-wrap">';

    // Selector de evento
    echo '<form method="get" class="gpe-filters"><input type="hidden" name="page" value="gpe-estadisticas">';
    echo '<div class="gpe-filter-row"><label style="font-weight:600;font-size:13px;">Evento:</label>';
    echo '<select name="evento_id" class="gpe-select" onchange="this.form.submit()">';
    echo '<option value="">— Selecciona un evento —</option>';
    foreach ($eventos as $ev) {
        $fecha = get_post_meta($ev->ID,'_med_fecha_evento',true);
        printf('<option value="%d" %s>%s%s</option>', $ev->ID, selected($evento_id,$ev->ID,false), esc_html($ev->post_title), $fecha?' ('.date('d/m/Y',strtotime($fecha)).')':'');
    }
    echo '</select></div></form>';

    if ( ! $evento_id ) {
        echo '<div class="gpe-card"><div class="gpe-table-empty">Selecciona un evento para ver sus estadísticas.</div></div>';
        echo '</div>';
        return;
    }

    // Cabecera del evento
    $ev_sel = get_post($evento_id);
    $f_sel  = get_post_meta($evento_id,'_med_fecha_evento',true);
    echo '<div class="gpe-header"><div class="gpe-header-info"><div>';
    echo '<h1 class="gpe-header-title">📊 ' . esc_html($ev_sel->post_title) . '</h1>';
    echo '<div class="gpe-header-sub">' . ($f_sel ? date('d/m/Y', strtotime($f_sel)) : 'Sin fecha') . ' · Estadísticas del evento</div>';
    echo '</div></div></div>';

    // Datos base
    $inscritos = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id = %d ORDER BY fecha_reg ASC",
        $evento_id
    ));

    $confirmados = array_filter($inscritos, fn($i) => $i->estado === 'confirmada');
    $cancelados  = array_filter($inscritos, fn($i) => $i->estado === 'cancelada');
    $aforo       = gpe_aforo_maximo($evento_id);
    $total_conf  = count($confirmados);
    $espera_count = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}gpe_lista_espera WHERE evento_id = %d",$evento_id));

    // Inscripciones por día
    $por_dia = array();
    foreach ($confirmados as $i) {
        $dia = date('d/m', strtotime($i->fecha_reg));
        $por_dia[$dia] = ($por_dia[$dia] ?? 0) + 1;
    }

    // Por CCAA
    $por_ccaa = array();
    foreach ($confirmados as $i) {
        $cc = $i->ccaa ?: 'Sin especificar';
        $por_ccaa[$cc] = ($por_ccaa[$cc] ?? 0) + 1;
    }
    arsort($por_ccaa);

    // Por cómo conoció
    $por_origen = array();
    foreach ($confirmados as $i) {
        $o = $i->como_conocio ?: 'Sin especificar';
        $por_origen[$o] = ($por_origen[$o] ?? 0) + 1;
    }
    arsort($por_origen);

    // Edad media
    $edades = array_filter(array_map(fn($i)=>intval($i->edad), $confirmados));
    $edad_media = !empty($edades) ? round(array_sum($edades)/count($edades),1) : null;

    // Velocidad media (inscritos por día desde el primero)
    $primer_ins = !empty($confirmados) ? strtotime(reset($confirmados)->fecha_reg) : null;
    $ultimo_ins = !empty($confirmados) ? strtotime(end($confirmados)->fecha_reg)   : null;
    $dias_transcurridos = ($primer_ins && $ultimo_ins && $ultimo_ins > $primer_ins)
        ? max(1, round(($ultimo_ins - $primer_ins) / 86400))
        : 1;
    $vel_media = round($total_conf / $dias_transcurridos, 1);
    ?>

    <!-- KPIs -->
    <div class="gpe-stats">
        <?php
        $kpis = array(
            array($total_conf,                                    'Inscritos',         'primary'),
            array(count($cancelados),                             'Cancelaciones',     'red'),
            array($espera_count,                                  'En espera',         'amber'),
            array($aforo > 0 ? max(0,$aforo-$total_conf) : '∞',   'Plazas libres',     ''),
            array($edad_media ?? '—',                             'Edad media',        ''),
            array($vel_media . '/día',                            'Ritmo inscripción', ''),
        );
        foreach ($kpis as $k) : ?>
            <div class="gpe-stat-card <?php echo $k[2]; ?>">
                <div class="gpe-stat-card-num"><?php echo is_numeric($k[0]) ? $k[0] : esc_html($k[0]); ?></div>
                <div class="gpe-stat-card-label"><?php echo esc_html($k[1]); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($aforo > 0) : ?>
    <!-- Barra de ocupación -->
    <div class="gpe-card">
        <strong style="font-size:14px;">Ocupación del aforo</strong>
        <?php $pct_aforo = min(100, round($total_conf/$aforo*100)); ?>
        <div class="gpe-progress" style="margin:10px 0 5px;">
            <div class="gpe-progress-fill<?php echo $pct_aforo>=100?' full':''; ?>" style="width:<?php echo $pct_aforo; ?>%;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--gpe-muted);">
            <span><?php echo $pct_aforo; ?>% ocupado</span>
            <span><?php echo $total_conf; ?> / <?php echo $aforo; ?> plazas</span>
        </div>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

        <!-- Inscripciones por día -->
        <?php if (!empty($por_dia)) : ?>
        <div class="gpe-card">
            <strong style="font-size:14px;display:block;margin-bottom:14px;">Inscripciones por día</strong>
            <?php $max_dia = max($por_dia); foreach ($por_dia as $dia => $n) :
                $pct = $max_dia > 0 ? round($n/$max_dia*100) : 0; ?>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:13px;">
                    <span style="width:52px;text-align:right;color:#666;"><?php echo esc_html($dia); ?></span>
                    <div style="flex:1;background:#f0f0f0;border-radius:10px;height:18px;overflow:hidden;">
                        <div style="width:<?php echo $pct; ?>%;height:100%;background:linear-gradient(90deg,#007a87,#00b4cc);border-radius:10px;display:flex;align-items:center;padding-left:6px;">
                            <?php if ($pct > 20) echo '<span style="color:#fff;font-size:11px;font-weight:700;">'.$n.'</span>'; ?>
                        </div>
                    </div>
                    <?php if ($pct <= 20) echo '<span style="font-size:11px;font-weight:700;color:#007a87;">'.$n.'</span>'; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Por CCAA -->
        <?php if (!empty($por_ccaa)) : ?>
        <div class="gpe-card">
            <strong style="font-size:14px;display:block;margin-bottom:14px;">Por CCAA</strong>
            <?php $max_cc = max($por_ccaa); foreach (array_slice($por_ccaa,0,10,true) as $cc => $n) :
                $pct = $max_cc > 0 ? round($n/$max_cc*100) : 0; ?>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:13px;">
                    <span style="width:90px;text-align:right;color:#666;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($cc); ?></span>
                    <div style="flex:1;background:#f0f0f0;border-radius:10px;height:18px;overflow:hidden;">
                        <div style="width:<?php echo $pct; ?>%;height:100%;background:linear-gradient(90deg,#1fc4a8,#007a87);border-radius:10px;display:flex;align-items:center;padding-left:6px;">
                            <?php if ($pct > 20) echo '<span style="color:#fff;font-size:11px;font-weight:700;">'.$n.'</span>'; ?>
                        </div>
                    </div>
                    <?php if ($pct <= 20) echo '<span style="font-size:11px;font-weight:700;color:#007a87;">'.$n.'</span>'; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>

    <!-- Cómo conocieron el evento -->
    <?php if (!empty($por_origen)) : ?>
    <div class="gpe-card">
        <strong style="font-size:14px;display:block;margin-bottom:14px;">¿Cómo conocieron el evento?</strong>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php $max_or = max($por_origen); foreach ($por_origen as $origen => $n) :
                $pct = $max_or > 0 ? round($n/$max_or*100) : 0;
                $opacidad = max(0.25, $pct/100); ?>
                <div style="background:rgba(0,122,135,<?php echo $opacidad; ?>);color:<?php echo $opacidad > 0.5 ? '#fff' : '#007a87'; ?>;padding:8px 16px;border-radius:20px;font-size:13px;font-weight:700;">
                    <?php echo esc_html($origen); ?> <span style="opacity:.75;">(<?php echo $n; ?>)</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    </div>
    <?php
}
