<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gpe_render_dashboard() {
    global $wpdb;

    // ── Datos globales ────────────────────────────────────────────────────────
    $total_eventos    = wp_count_posts('evento_home')->publish ?? 0;
    $total_borradores = wp_count_posts('evento_home')->draft   ?? 0;
    $total_cancelados = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='evento_home' AND post_status='gpe_cancelado'");
    $total_pospuestos = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='evento_home' AND post_status='gpe_pospuesto'");
    $total_ponentes   = wp_count_posts('gpe_contacto')->publish ?? 0;
    $total_coords     = count(get_users(array('role'=>'gpe_coordinador')));
    $total_inscritos  = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gpe_inscripciones WHERE estado='confirmada'");
    $total_espera     = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gpe_lista_espera WHERE notificado=0");

    // ── Próximos eventos ──────────────────────────────────────────────────────
    $proximos = get_posts(array(
        'post_type'      => 'evento_home',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'meta_key'       => '_med_fecha_evento',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(array(
            'key'     => '_med_fecha_evento',
            'value'   => date('Y-m-d'),
            'compare' => '>=',
            'type'    => 'DATE',
        )),
    ));

    // ── Últimas inscripciones ─────────────────────────────────────────────────
    $ultimas_insc = $wpdb->get_results(
        "SELECT i.*, p.post_title as evento_titulo
         FROM {$wpdb->prefix}gpe_inscripciones i
         LEFT JOIN {$wpdb->posts} p ON p.ID = i.evento_id
         WHERE i.estado = 'confirmada'
         ORDER BY i.fecha_reg DESC
         LIMIT 8"
    );

    // ── Inscripciones por mes (últimos 6 meses) ───────────────────────────────
    $meses_data = $wpdb->get_results(
        "SELECT DATE_FORMAT(fecha_reg, '%Y-%m') as mes, COUNT(*) as total
         FROM {$wpdb->prefix}gpe_inscripciones
         WHERE estado = 'confirmada'
         AND fecha_reg >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY mes
         ORDER BY mes ASC"
    );
    $chart_labels = array();
    $chart_values = array();
    $meses_es = array('01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic');
    foreach ($meses_data as $m) {
        $parts = explode('-', $m->mes);
        $chart_labels[] = ($meses_es[$parts[1]] ?? $parts[1]) . ' ' . $parts[0];
        $chart_values[] = (int)$m->total;
    }
    ?>

    <style>
    .gpe-dash-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
    .gpe-dash-kpi-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .gpe-dash-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .gpe-dash-row-3 { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px; }
    .gpe-dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .gpe-dash-table th { color: var(--gpe-muted); font-weight: 600; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; padding: 0 0 8px; text-align: left; border-bottom: 1px solid #f0f0f0; }
    .gpe-dash-table td { padding: 10px 0; border-bottom: 1px solid #f8f8f8; color: #333; vertical-align: middle; }
    .gpe-dash-table tr:last-child td { border-bottom: none; }
    .gpe-aforo-mini { display: flex; align-items: center; gap: 6px; font-size: 12px; }
    .gpe-aforo-bar { flex: 1; height: 5px; background: #eee; border-radius: 5px; overflow: hidden; min-width: 50px; }
    .gpe-aforo-fill { height: 100%; background: var(--gpe-grad); border-radius: 5px; }
    .gpe-quick-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .gpe-quick-btn { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 8px; background: #f8fafb; border: 1px solid rgba(0,122,135,0.1); text-decoration: none; color: #1a1a1a; font-size: 13px; font-weight: 600; transition: all .2s; }
    .gpe-quick-btn:hover { background: var(--gpe-1); color: #fff; border-color: var(--gpe-1); }
    .gpe-quick-btn:hover .gpe-quick-icon { background: rgba(255,255,255,.2); color: #fff; }
    .gpe-quick-icon { width: 32px; height: 32px; border-radius: 6px; background: rgba(0,122,135,.1); color: var(--gpe-1); display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
    @media(max-width:1200px){ .gpe-dash-grid{grid-template-columns:repeat(2,1fr);} }
    @media(max-width:900px){ .gpe-dash-row,.gpe-dash-row-3{grid-template-columns:1fr;} }
    </style>

    <div class="wrap gpe-wrap">

        <!-- Header -->
        <div class="gpe-header">
            <div class="gpe-header-info"><div>
                <h1 class="gpe-header-title">GP Eventik — Inicio</h1>
                <div class="gpe-header-sub">Vista general · <?php echo date_i18n('l, j \d\e F \d\e Y'); ?></div>
            </div></div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=evento_home')); ?>" class="gpe-btn">+ Nuevo evento</a>
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=gpe_contacto')); ?>" class="gpe-btn">+ Nuevo ponente</a>
            </div>
        </div>

        <!-- KPIs -->
        <div class="gpe-dash-grid">
            <div class="gpe-card" style="display:flex;align-items:center;gap:14px;margin-bottom:0;">
                <div class="gpe-dash-kpi-icon" style="background:rgba(0,122,135,.1);">📅</div>
                <div>
                    <div class="gpe-stat-card-num"><?php echo $total_eventos; ?></div>
                    <div class="gpe-stat-card-label"><?php echo $total_borradores; ?> borradores · <?php echo $total_cancelados + $total_pospuestos; ?> incidencias</div>
                </div>
            </div>
            <div class="gpe-card" style="display:flex;align-items:center;gap:14px;margin-bottom:0;">
                <div class="gpe-dash-kpi-icon" style="background:rgba(39,174,96,.1);">🎟️</div>
                <div>
                    <div class="gpe-stat-card-num"><?php echo $total_inscritos; ?></div>
                    <div class="gpe-stat-card-label"><?php echo $total_espera; ?> en lista de espera</div>
                </div>
            </div>
            <div class="gpe-card" style="display:flex;align-items:center;gap:14px;margin-bottom:0;">
                <div class="gpe-dash-kpi-icon" style="background:rgba(155,89,182,.1);">🎤</div>
                <div>
                    <div class="gpe-stat-card-num"><?php echo $total_ponentes; ?></div>
                    <div class="gpe-stat-card-label">Ponentes activos</div>
                </div>
            </div>
            <div class="gpe-card" style="display:flex;align-items:center;gap:14px;margin-bottom:0;">
                <div class="gpe-dash-kpi-icon" style="background:rgba(230,126,34,.1);">🗺️</div>
                <div>
                    <div class="gpe-stat-card-num"><?php echo $total_coords; ?></div>
                    <div class="gpe-stat-card-label">Coordinadores</div>
                </div>
            </div>
        </div>

        <!-- Gráfico + Acciones rápidas -->
        <div class="gpe-dash-row">
            <div class="gpe-card">
                <div class="gpe-card-title">Inscripciones por mes <span style="font-size:11px;color:var(--gpe-muted);font-weight:400;">últimos 6 meses</span></div>
                <?php if (empty($chart_values)) : ?>
                    <p style="color:#aaa;text-align:center;padding:30px 0;font-size:13px;">Aún no hay datos de inscripciones.</p>
                <?php else :
                    $max_val = max($chart_values) ?: 1;
                ?>
                <div style="display:flex; align-items:flex-end; gap:12px; height:140px; padding-top:10px;">
                    <?php foreach ($chart_values as $i => $val) :
                        $height_pct = round($val / $max_val * 100);
                        $is_last    = $i === count($chart_values) - 1;
                    ?>
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; height:100%; justify-content:flex-end;">
                        <span style="font-size:11px;font-weight:700;color:<?php echo $is_last ? '#007a87' : '#aaa'; ?>;"><?php echo $val; ?></span>
                        <div style="width:100%; background:<?php echo $is_last ? 'linear-gradient(180deg,#007a87,#00b4cc)' : '#e8f4f5'; ?>; border-radius:4px 4px 0 0; height:<?php echo max(4,$height_pct); ?>%;"></div>
                        <span style="font-size:10px;color:#aaa;white-space:nowrap;"><?php echo esc_html($chart_labels[$i]); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="gpe-card">
                <div class="gpe-card-title">Accesos rápidos</div>
                <div class="gpe-quick-actions">
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=evento_home')); ?>" class="gpe-quick-btn">
                        <span class="gpe-quick-icon">+</span> Nuevo evento
                    </a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=gpe_contacto')); ?>" class="gpe-quick-btn">
                        <span class="gpe-quick-icon">🎤</span> Nuevo ponente
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gpe-inscritos')); ?>" class="gpe-quick-btn">
                        <span class="gpe-quick-icon">🎟️</span> Ver inscritos
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gpe-estadisticas')); ?>" class="gpe-quick-btn">
                        <span class="gpe-quick-icon">📊</span> Estadísticas
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gpe-invitaciones')); ?>" class="gpe-quick-btn">
                        <span class="gpe-quick-icon">✉️</span> Invitaciones
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gpe-shortcodes')); ?>" class="gpe-quick-btn">
                        <span class="gpe-quick-icon">📋</span> Shortcodes
                    </a>
                </div>
            </div>
        </div>

        <!-- Próximos eventos + últimas inscripciones -->
        <div class="gpe-dash-row-3">
            <div class="gpe-card">
                <div class="gpe-card-title">
                    Próximos eventos
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=evento_home')); ?>">Ver todos</a>
                </div>
                <?php if (empty($proximos)) : ?>
                    <p style="color:#aaa;font-size:13px;">No hay eventos próximos.</p>
                <?php else : ?>
                <table class="gpe-dash-table">
                    <thead>
                        <tr>
                            <th>Evento</th>
                            <th>Fecha</th>
                            <th>CCAA</th>
                            <th>Creado por</th>
                            <th>Ocupación</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($proximos as $ev) :
                        $fecha_ev  = get_post_meta($ev->ID,'_med_fecha_evento',true);
                        $ccaa_ev   = get_post_meta($ev->ID,'_gpe_ccaa_evento',true);
                        $aforo_ev  = gpe_aforo_maximo($ev->ID);
                        $insc_ev   = gpe_inscritos_count($ev->ID);
                        $sold      = gpe_evento_sold_out($ev->ID);
                        $pct       = $aforo_ev > 0 ? min(100,round($insc_ev/$aforo_ev*100)) : 0;
                        $dias_rest = $fecha_ev ? (int)floor((strtotime($fecha_ev) - time()) / 86400) : null;
                        $autor_ev  = get_userdata($ev->post_author);
                        $autor_nom = $autor_ev ? $autor_ev->display_name : '—';
                        $creado_en = date_i18n('d/m/Y H:i', strtotime($ev->post_date));
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($ev->ID)); ?>" style="color:#007a87;text-decoration:none;font-weight:600;">
                                <?php echo esc_html($ev->post_title); ?>
                            </a>
                            <?php if ($dias_rest !== null && $dias_rest <= 7) : ?>
                                <span style="background:#fff3cd;color:#856404;padding:1px 6px;border-radius:4px;font-size:10px;font-weight:700;margin-left:4px;">
                                    <?php echo $dias_rest === 0 ? 'HOY' : 'en '.$dias_rest.'d'; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap;color:#555;"><?php echo $fecha_ev ? date('d/m/Y',strtotime($fecha_ev)) : '—'; ?></td>
                        <td style="font-size:12px;color:#888;"><?php echo esc_html($ccaa_ev ?: '—'); ?></td>
                        <td><span style="display:inline-flex;align-items:center;gap:4px;background:#f0fafb;border:1px solid #cce8ea;border-radius:20px;padding:2px 8px;font-size:11px;color:#007a87;font-weight:700;" title="Creado el <?php echo esc_attr($creado_en); ?>">👤 <?php echo esc_html($autor_nom); ?></span></td>
                        <td>
                            <?php if ($aforo_ev > 0) : ?>
                            <div class="gpe-aforo-mini">
                                <div class="gpe-aforo-bar"><div class="gpe-aforo-fill" style="width:<?php echo $pct; ?>%;<?php echo $sold?'background:#c0392b;':''; ?>"></div></div>
                                <span style="color:<?php echo $sold?'#c0392b':'#007a87'; ?>;font-weight:700;"><?php echo $insc_ev; ?>/<?php echo $aforo_ev; ?></span>
                            </div>
                            <?php else : ?>
                                <span style="font-size:12px;color:#007a87;font-weight:700;"><?php echo $insc_ev; ?> inscritos</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $pills = array(
                                'publish'      => array('#27ae60','Publicado'),
                                'draft'        => array('#e67e22','Borrador'),
                                'gpe_cancelado'=> array('#c0392b','Cancelado'),
                                'gpe_pospuesto'=> array('#e67e22','Pospuesto'),
                            );
                            $pill = $pills[$ev->post_status] ?? array('#999',ucfirst($ev->post_status));
                            echo '<span class="gpe-status-pill" style="background:' . $pill[0] . ';color:#fff;">' . esc_html($pill[1]) . '</span>';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="gpe-card">
                <div class="gpe-card-title">
                    Últimas inscripciones
                    <a href="<?php echo esc_url(admin_url('admin.php?page=gpe-inscritos')); ?>">Ver todas</a>
                </div>
                <?php if (empty($ultimas_insc)) : ?>
                    <p style="color:#aaa;font-size:13px;">Aún no hay inscripciones.</p>
                <?php else : ?>
                    <?php foreach ($ultimas_insc as $ins) :
                        $iniciales = strtoupper(substr($ins->nombre,0,1) . substr($ins->apellidos,0,1));
                        $hace      = human_time_diff(strtotime($ins->fecha_reg), current_time('timestamp'));
                    ?>
                    <div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid #f8f8f8;">
                        <span class="gpe-avatar"><?php echo esc_html($iniciales); ?></span>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:600;color:#1a1a1a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo esc_html($ins->nombre . ' ' . $ins->apellidos); ?>
                            </div>
                            <div style="font-size:11px;color:#aaa;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?php echo esc_html($ins->evento_titulo); ?>
                            </div>
                        </div>
                        <span style="font-size:11px;color:#bbb;white-space:nowrap;">hace <?php echo $hace; ?></span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
    <?php
}
