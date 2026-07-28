<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ── Render página de invitaciones ─────────────────────────────────────────────
function gpe_render_invitaciones() {
    $eventos = get_posts(array(
        'post_type'      => 'evento_home',
        'post_status'    => array('publish','draft'),
        'posts_per_page' => 100,
        'meta_key'       => '_med_fecha_evento',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
    ));

    $evento_id = intval($_GET['evento_inv'] ?? ((!empty($eventos)) ? $eventos[0]->ID : 0));
    $subtab    = sanitize_text_field($_GET['inv_tab'] ?? 'preview');

    // Datos del evento seleccionado
    $ev_titulo = $ev_claim = $ev_lugar = $ev_fecha_badge = $ev_fecha_larga = $ev_hora = '';
    $ev_url = $ev_img = '';
    $ev_ponentes_list = '';
    if ($evento_id) {
        $ev = get_post($evento_id);
        $ev_titulo    = $ev ? $ev->post_title : '';
        $ev_claim     = get_post_meta($evento_id,'_gpe_claim',true);
        $ev_lugar     = get_post_meta($evento_id,'_gpe_lugar_nombre',true) ?: get_post_meta($evento_id,'_med_provincia_sitio',true);
        $fecha_raw    = get_post_meta($evento_id,'_med_fecha_evento',true);
        $ev_hora      = get_post_meta($evento_id,'_med_hora_evento',true);
        $cod_prov     = get_post_meta($evento_id,'_gpe_codigo_provincia',true);
        $prov         = get_post_meta($evento_id,'_med_provincia_sitio',true);
        $por_que      = get_post_meta($evento_id,'_gpe_por_que',true);
        $img_id       = get_post_meta($evento_id,'_gpe_foto_hero',true);
        $ev_img       = $img_id ? wp_get_attachment_image_url($img_id,'large') : '';
        $ev_url       = get_permalink($evento_id) ?: '';
        $meses = array('01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC');
        if ($fecha_raw) {
            $ts = strtotime($fecha_raw);
            $ev_fecha_badge = date('d',$ts) . ' ' . ($meses[date('m',$ts)]??'') . ($cod_prov ? ' · '.$cod_prov : ($prov ? ' · '.$prov : ''));
            $ev_fecha_larga = date_i18n('l, j \d\e F \d\e Y', $ts);
        }
        $pon_ids = get_post_meta($evento_id,'_gpe_ponentes_ids',true) ?: array();
        $pon_names = array();
        foreach (array_slice($pon_ids,0,4) as $pid) {
            $pp = get_post($pid); if (!$pp) continue;
            $cargo = get_post_meta($pid,'_gpe_basico_cargo',true);
            $pon_names[] = $pp->post_title . ($cargo ? ', '.$cargo : '');
        }
        if ($pon_names) $ev_ponentes_list = implode('<br>', array_map('esc_html', $pon_names));
    }

    // Nonce para AJAX
    $nonce_inv = wp_create_nonce('gpe_inv_send_nonce');
    ?>
    <style>
    .gpeinv-layout{display:grid;grid-template-columns:240px 1fr;gap:20px;align-items:start;}
    .gpeinv-sidebar h3{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gpe-muted);margin:0 0 10px;}
    .gpeinv-tab-list{display:flex;flex-direction:column;gap:4px;}
    .gpeinv-tab{display:block;padding:8px 12px;border-radius:6px;background:#f5f5f5;color:#333;font-size:13px;font-weight:600;text-decoration:none;}
    .gpeinv-tab:hover,.gpeinv-tab.activo{background:var(--gpe-1);color:#fff;}
    .gpeinv-main h2{margin-top:0;font-size:1.1rem;font-weight:800;}
    /* Tarjeta post IG (4:5) */
    .gpeinv-card-ig{width:300px;height:375px;background:linear-gradient(155deg,#003d44 0%,#007a87 55%,#00b4cc 100%);border-radius:14px;position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:flex-end;padding:22px;box-sizing:border-box;color:#fff;font-family:'Inter',sans-serif;flex-shrink:0;}
    .gpeinv-card-ig-bg{position:absolute;inset:0;background-size:cover;background-position:center;opacity:.22;}
    .gpeinv-card-ig .logo{position:absolute;top:18px;left:20px;font-size:11px;font-weight:900;letter-spacing:1px;text-transform:uppercase;opacity:.85;}
    .gpeinv-card-ig .badge{background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.3);border-radius:20px;padding:3px 12px;font-size:10px;font-weight:700;letter-spacing:.4px;display:inline-block;margin-bottom:9px;}
    .gpeinv-card-ig .fecha{font-size:1.9rem;font-weight:900;line-height:1;margin-bottom:5px;}
    .gpeinv-card-ig .titulo{font-size:1rem;font-weight:800;line-height:1.3;margin-bottom:7px;}
    .gpeinv-card-ig .claim{font-size:.75rem;opacity:.8;margin-bottom:10px;font-style:italic;}
    .gpeinv-card-ig .lugar{font-size:.72rem;opacity:.75;}
    /* Historia IG (9:16) */
    .gpeinv-card-story{width:188px;height:333px;background:linear-gradient(180deg,#003d44 0%,#007a87 45%,#00b4cc 100%);border-radius:14px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;padding:18px;box-sizing:border-box;color:#fff;font-family:'Inter',sans-serif;text-align:center;flex-shrink:0;}
    .gpeinv-card-story-bg{position:absolute;inset:0;background-size:cover;background-position:center;opacity:.18;}
    .gpeinv-card-story-inner{position:relative;z-index:1;}
    .gpeinv-card-story .org{font-size:9px;font-weight:900;letter-spacing:2px;text-transform:uppercase;opacity:.8;margin-bottom:10px;}
    .gpeinv-card-story .fecha{font-size:1.65rem;font-weight:900;line-height:1;margin-bottom:7px;}
    .gpeinv-card-story .titulo{font-size:.88rem;font-weight:800;line-height:1.3;margin-bottom:6px;}
    .gpeinv-card-story .lugar{font-size:.68rem;opacity:.8;margin-top:8px;}
    .gpeinv-card-story .cta{margin-top:14px;background:rgba(255,255,255,.22);border:1px solid rgba(255,255,255,.35);border-radius:20px;padding:6px 16px;font-size:.72rem;font-weight:700;display:inline-block;}
    /* Email preview */
    .gpeinv-email-box{background:#f9f9f9;border:1px solid #ddd;border-radius:10px;padding:22px;font-family:Arial,sans-serif;font-size:14px;line-height:1.7;color:#333;max-width:520px;}
    .gpeinv-email-box h2{color:var(--gpe-1);margin-top:0;}
    .gpeinv-cards-row{display:flex;gap:20px;flex-wrap:wrap;align-items:flex-start;}
    .gpeinv-card-label{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--gpe-muted);font-weight:700;margin:0 0 7px;}
    @media(max-width:900px){.gpeinv-layout{grid-template-columns:1fr;}}
    </style>

    <div class="wrap gpe-wrap">
        <div class="gpe-header">
            <div class="gpe-header-info"><div>
                <h1 class="gpe-header-title">✉️ Invitaciones</h1>
                <div class="gpe-header-sub">Genera y comparte invitaciones para tus eventos</div>
            </div></div>
        </div>

        <?php if (empty($eventos)) : ?>
            <div class="gpe-card"><div class="gpe-table-empty">
                No hay eventos. <a href="<?php echo esc_url(admin_url('post-new.php?post_type=evento_home')); ?>" style="color:var(--gpe-1);">Crea uno primero</a>.
            </div></div>
        <?php else : ?>
        <div class="gpeinv-layout">

            <!-- Sidebar -->
            <div class="gpe-card gpeinv-sidebar">
                <h3>Evento</h3>
                <form method="get">
                    <input type="hidden" name="post_type" value="evento_home">
                    <input type="hidden" name="page" value="gpe-invitaciones">
                    <select name="evento_inv" class="gpe-select" style="width:100%;margin-bottom:14px;" onchange="this.form.submit()">
                        <?php foreach ($eventos as $evx) :
                            $fx = get_post_meta($evx->ID,'_med_fecha_evento',true);
                        ?>
                            <option value="<?php echo $evx->ID; ?>" <?php selected($evento_id,$evx->ID); ?>>
                                <?php echo esc_html($evx->post_title . ($fx ? ' (' . date('d/m/Y',strtotime($fx)) . ')' : '')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <h3>Sección</h3>
                <div class="gpeinv-tab-list">
                    <?php
                    $tabs = array(
                        'preview'    => '👁 Vista previa',
                        'email_inst' => '📧 Email institucional',
                        'email_inf'  => '✌️ Email informal',
                        'enviar'     => '📤 Enviar',
                        'historial'  => '📨 Historial',
                    );
                    foreach ($tabs as $tk => $tl) :
                        $url_tab = add_query_arg(array('post_type'=>'evento_home','page'=>'gpe-invitaciones','evento_inv'=>$evento_id,'inv_tab'=>$tk), admin_url('edit.php'));
                    ?>
                    <a href="<?php echo esc_url($url_tab); ?>" class="gpeinv-tab <?php echo $subtab===$tk?'activo':''; ?>"><?php echo esc_html($tl); ?></a>
                    <?php endforeach; ?>
                </div>

                <?php if ($ev_url) : ?>
                <div style="margin-top:18px;">
                    <h3>Enlace directo</h3>
                    <input type="text" value="<?php echo esc_attr($ev_url); ?>" readonly onclick="this.select()" style="width:100%;padding:6px 8px;border:1px solid #ddd;border-radius:5px;font-size:11px;background:#f9f9f9;box-sizing:border-box;">
                </div>
                <?php endif; ?>
            </div>

            <!-- Main -->
            <div class="gpe-card gpeinv-main">

                <?php if ($subtab === 'preview') : ?>
                <h2>Vista previa de formatos</h2>
                <div style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;">
                    <button onclick="gpeinvImprimir('post')" class="gpe-btn gpe-btn-primary">🖨 Post IG (4:5)</button>
                    <button onclick="gpeinvImprimir('story')" class="gpe-btn">🖨 Historia IG</button>
                    <?php if ($ev_url) : ?><button onclick="gpeinvCopiarEnlace()" class="gpe-btn">🔗 Copiar enlace</button><?php endif; ?>
                </div>
                <div class="gpeinv-cards-row">
                    <div>
                        <p class="gpeinv-card-label">Post Instagram (4:5)</p>
                        <div class="gpeinv-card-ig" id="gpeinv-card-post">
                            <?php if ($ev_img) : ?><div class="gpeinv-card-ig-bg" style="background-image:url('<?php echo esc_url($ev_img); ?>');"></div><?php endif; ?>
                            <div class="logo">Generación Presente</div>
                            <span class="badge">📅 EVENTO</span>
                            <div class="fecha"><?php echo esc_html($ev_fecha_badge ?: '—'); ?></div>
                            <div class="titulo"><?php echo esc_html($ev_titulo); ?></div>
                            <?php if ($ev_claim) : ?><div class="claim">"<?php echo esc_html($ev_claim); ?>"</div><?php endif; ?>
                            <div class="lugar">📍 <?php echo esc_html($ev_lugar ?: '—'); ?></div>
                        </div>
                    </div>
                    <div>
                        <p class="gpeinv-card-label">Historia Instagram (9:16)</p>
                        <div class="gpeinv-card-story" id="gpeinv-card-story">
                            <?php if ($ev_img) : ?><div class="gpeinv-card-story-bg" style="background-image:url('<?php echo esc_url($ev_img); ?>');"></div><?php endif; ?>
                            <div class="gpeinv-card-story-inner">
                                <div class="org">Generación Presente</div>
                                <div class="fecha"><?php echo esc_html($ev_fecha_badge ?: '—'); ?></div>
                                <div class="titulo"><?php echo esc_html($ev_titulo); ?></div>
                                <?php if ($ev_lugar) : ?><div class="lugar">📍 <?php echo esc_html($ev_lugar); ?></div><?php endif; ?>
                                <div class="cta">Únete →</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php elseif ($subtab === 'email_inst') : ?>
                <h2>Email institucional — preview</h2>
                <p style="color:#888;font-size:13px;margin-top:0;">Tono formal. Para contactos institucionales, medios o difusión oficial.</p>
                <div class="gpeinv-email-box">
                    <h2>Invitación: <?php echo esc_html($ev_titulo); ?></h2>
                    <p>Estimado/a,</p>
                    <p>Nos complace invitarle al próximo evento organizado por <strong>Generación Presente</strong>:</p>
                    <div style="background:#f0fafb;border-left:4px solid #007a87;padding:14px;border-radius:6px;margin:14px 0;">
                        <strong style="font-size:1.05em;color:#007a87;"><?php echo esc_html($ev_titulo); ?></strong><br>
                        <?php if ($ev_fecha_larga) echo '📅 <strong>' . esc_html($ev_fecha_larga) . ($ev_hora ? ' — ' . esc_html($ev_hora) . 'h' : '') . '</strong><br>'; ?>
                        <?php if ($ev_lugar)       echo '📍 ' . esc_html($ev_lugar) . '<br>'; ?>
                        <?php if ($ev_claim)       echo '<em style="color:#007a87;">«' . esc_html($ev_claim) . '»</em>'; ?>
                    </div>
                    <?php if ($ev_ponentes_list) : ?><p>Entre los participantes: <?php echo $ev_ponentes_list; ?></p><?php endif; ?>
                    <p style="text-align:center;margin:20px 0;">
                        <a href="<?php echo esc_url($ev_url); ?>" style="background:#007a87;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:700;">Inscribirse al evento</a>
                    </p>
                    <p>Atentamente,<br><strong>Generación Presente</strong></p>
                </div>

                <?php elseif ($subtab === 'email_inf') : ?>
                <h2>Email informal — preview</h2>
                <p style="color:#888;font-size:13px;margin-top:0;">Tono directo y cercano. Para la comunidad y conocidos.</p>
                <div class="gpeinv-email-box">
                    <h2 style="color:#1a1a1a;">Oye, ¿tienes <?php echo esc_html($ev_fecha_badge ?: 'la fecha'); ?> libre?</h2>
                    <p>Porque te queremos ahí.</p>
                    <p>Organizamos <strong><?php echo esc_html($ev_titulo); ?></strong><?php if ($ev_lugar) echo ' en <strong>' . esc_html($ev_lugar) . '</strong>'; ?><?php if ($ev_fecha_larga) echo ' el ' . esc_html($ev_fecha_larga); ?><?php if ($ev_hora) echo ' a las ' . esc_html($ev_hora) . 'h'; ?>.</p>
                    <?php if ($ev_claim) : ?><p style="font-size:1.1em;font-weight:700;color:#007a87;border-left:3px solid #007a87;padding-left:12px;"><?php echo esc_html($ev_claim); ?></p><?php endif; ?>
                    <?php if ($ev_ponentes_list) : ?><p>Vas a poder escuchar a <?php echo $ev_ponentes_list; ?>. No son relleno. Son gente que tiene algo que decir.</p><?php endif; ?>
                    <p>No hace falta que traigas nada. Solo ganas de pensar.</p>
                    <p style="text-align:center;margin:20px 0;">
                        <a href="<?php echo esc_url($ev_url); ?>" style="background:#007a87;color:#fff;padding:12px 28px;border-radius:20px;text-decoration:none;font-weight:700;">Me apunto →</a>
                    </p>
                    <p style="font-size:12px;color:#999;">Si no puedes venir, no te preocupes. Habrá más. Siempre hay más.</p>
                </div>

                <?php elseif ($subtab === 'enviar') : ?>
                <h2>Enviar por email</h2>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">
                    <div style="background:#f8fafb;border:1px solid #e0e0e0;border-radius:10px;padding:18px;">
                        <h4 style="margin-top:0;color:var(--gpe-1);">📧 Email institucional</h4>
                        <p style="font-size:13px;color:#777;margin-top:0;">Tono formal para contactos y medios.</p>
                        <textarea id="gpeinv-emails-inst" rows="6" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;box-sizing:border-box;resize:vertical;" placeholder="Un email por línea:&#10;persona@ejemplo.com&#10;otro@ejemplo.com"></textarea>
                        <button onclick="gpeinvEnviar('inst')" class="gpe-btn gpe-btn-primary" style="margin-top:10px;width:100%;justify-content:center;">📤 Enviar institucional</button>
                        <div id="gpeinv-resp-inst" style="margin-top:8px;font-size:13px;display:none;padding:8px;border-radius:6px;"></div>
                    </div>
                    <div style="background:#f8fafb;border:1px solid #e0e0e0;border-radius:10px;padding:18px;">
                        <h4 style="margin-top:0;color:var(--gpe-1);">✌️ Email informal</h4>
                        <p style="font-size:13px;color:#777;margin-top:0;">Tono cercano para la comunidad.</p>
                        <textarea id="gpeinv-emails-inf" rows="6" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:6px;font-size:13px;box-sizing:border-box;resize:vertical;" placeholder="Un email por línea:&#10;persona@ejemplo.com&#10;otro@ejemplo.com"></textarea>
                        <button onclick="gpeinvEnviar('inf')" class="gpe-btn gpe-btn-primary" style="margin-top:10px;width:100%;justify-content:center;background:#1a1a1a;">📤 Enviar informal</button>
                        <div id="gpeinv-resp-inf" style="margin-top:8px;font-size:13px;display:none;padding:8px;border-radius:6px;"></div>
                    </div>
                </div>

                <?php elseif ($subtab === 'historial') : ?>
                <h2>Historial de envíos</h2>
                <p style="color:var(--gpe-muted);font-size:13px;margin-top:0;">Todos los emails enviados para este evento.</p>
                <?php gpe_render_inst_log_tabla($evento_id); ?>
                <?php endif; ?>

            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    var _gpeinvEvId  = <?php echo intval($evento_id); ?>;
    var _gpeinvAjax  = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
    var _gpeinvNonce = '<?php echo esc_js($nonce_inv); ?>';
    var _gpeinvUrl   = '<?php echo esc_js($ev_url); ?>';

    function gpeinvCopiarEnlace() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(_gpeinvUrl).then(function(){ alert('¡Enlace copiado al portapapeles!'); });
        } else { prompt('Copia el enlace:', _gpeinvUrl); }
    }

    function gpeinvImprimir(id) {
        var el = document.getElementById(id === 'post' ? 'gpeinv-card-post' : 'gpeinv-card-story');
        if (!el) { alert('Ve a Vista previa para ver las tarjetas.'); return; }
        var win = window.open('','_blank','width=600,height=700');
        win.document.write('<!DOCTYPE html><html><head><title>Invitación</title>');
        win.document.write('<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">');
        win.document.write('<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f0f0f0;flex-direction:column;gap:20px;padding:20px;}');
        // Incluir los estilos de las tarjetas
        var styles = document.querySelectorAll('style');
        styles.forEach(function(s){ win.document.write('<style>'+s.innerHTML+'</style>'); });
        win.document.write('</style></head><body>');
        win.document.write(el.outerHTML);
        win.document.write('<p style="font-family:sans-serif;font-size:13px;color:#888;text-align:center;">Ctrl+P → Guardar como PDF / Imprimir</p>');
        win.document.write('</body></html>');
        win.document.close();
    }

    function gpeinvEnviar(tipo) {
        var ta   = document.getElementById('gpeinv-emails-' + tipo);
        var resp = document.getElementById('gpeinv-resp-' + tipo);
        var emails = ta ? ta.value.trim() : '';
        if (!emails) { alert('Introduce al menos un email.'); return; }
        resp.style.display = 'block';
        resp.style.background = '#fff3cd';
        resp.style.color = '#856404';
        resp.textContent = 'Enviando...';
        var xhr = new XMLHttpRequest();
        xhr.open('POST', _gpeinvAjax, true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onload = function() {
            try {
                var r = JSON.parse(xhr.responseText);
                if (r.success) {
                    resp.style.background = '#d4edda'; resp.style.color = '#155724';
                    resp.textContent = '✅ ' + r.data.msg;
                } else {
                    resp.style.background = '#f8d7da'; resp.style.color = '#721c24';
                    resp.textContent = '❌ ' + (r.data ? r.data.msg : 'Error desconocido');
                }
            } catch(e) { resp.textContent = '❌ Error inesperado'; }
        };
        xhr.onerror = function() { resp.textContent = '❌ Error de red'; };
        xhr.send('action=gpe_inv_enviar_email&nonce='+_gpeinvNonce+'&evento_id='+_gpeinvEvId+'&tipo='+tipo+'&emails='+encodeURIComponent(emails));
    }
    </script>
    <?php
}

// ── AJAX: Enviar email de invitación ─────────────────────────────────────────
add_action('wp_ajax_gpe_inv_enviar_email', 'gpe_ajax_inv_enviar_email');
function gpe_ajax_inv_enviar_email() {
    check_ajax_referer('gpe_inv_send_nonce','nonce');
    if (!current_user_can('edit_posts')) wp_send_json_error(array('msg'=>'Sin permisos.'));

    $evento_id = intval($_POST['evento_id'] ?? 0);
    $tipo      = sanitize_text_field($_POST['tipo'] ?? 'inst');
    $emails    = array_filter(array_map('sanitize_email', explode("\n", $_POST['emails'] ?? '')));

    if (!$evento_id || empty($emails)) wp_send_json_error(array('msg'=>'Datos incompletos.'));

    $ev         = get_post($evento_id);
    $titulo     = $ev ? $ev->post_title : '';
    $claim      = get_post_meta($evento_id,'_gpe_claim',true);
    $lugar      = get_post_meta($evento_id,'_gpe_lugar_nombre',true);
    $fecha_raw  = get_post_meta($evento_id,'_med_fecha_evento',true);
    $hora       = get_post_meta($evento_id,'_med_hora_evento',true);
    $por_que    = get_post_meta($evento_id,'_gpe_por_que',true);
    $url        = get_permalink($evento_id) ?: home_url();
    $fecha_larga= $fecha_raw ? date_i18n('l, j \d\e F \d\e Y', strtotime($fecha_raw)) : '';
    $meses = array('01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC');
    $fecha_badge = '';
    if ($fecha_raw) {
        $ts = strtotime($fecha_raw);
        $fecha_badge = date('d',$ts) . ' ' . ($meses[date('m',$ts)]??'');
    }
    $pon_ids = get_post_meta($evento_id,'_gpe_ponentes_ids',true) ?: array();
    $pon_txt = '';
    foreach (array_slice($pon_ids,0,4) as $pid) {
        $pp = get_post($pid); if (!$pp) continue;
        $cargo = get_post_meta($pid,'_gpe_basico_cargo',true);
        $pon_txt .= '<li>' . esc_html($pp->post_title) . ($cargo?', '.esc_html($cargo):'') . '</li>';
    }

    $headers = array('Content-Type: text/html; charset=UTF-8');

    if ($tipo === 'inst') {
        $asunto = 'Invitación: ' . $titulo;
        $body  = '<html><body style="font-family:Arial,sans-serif;color:#333;max-width:580px;margin:0 auto;">';
        $body .= '<div style="background:linear-gradient(135deg,#007a87,#00b4cc);padding:24px 28px;"><h1 style="color:#fff;margin:0;font-size:1.3rem;">' . esc_html($titulo) . '</h1></div>';
        $body .= '<div style="padding:24px 28px;">';
        $body .= '<p>Estimado/a,</p><p>Nos complace invitarle al siguiente evento de <strong>Generación Presente</strong>:</p>';
        $body .= '<div style="background:#f0fafb;border-left:4px solid #007a87;padding:14px;border-radius:6px;margin:16px 0;">';
        if ($fecha_larga) $body .= '<p style="margin:4px 0;"><strong>📅 ' . esc_html($fecha_larga) . ($hora?' — '.esc_html($hora).'h':'') . '</strong></p>';
        if ($lugar)       $body .= '<p style="margin:4px 0;">📍 ' . esc_html($lugar) . '</p>';
        if ($claim)       $body .= '<p style="margin:4px 0;font-style:italic;color:#007a87;">«' . esc_html($claim) . '»</p>';
        $body .= '</div>';
        if ($pon_txt)   $body .= '<p>Participantes: <ul>' . $pon_txt . '</ul></p>';
        $body .= '<p style="text-align:center;margin:24px 0;"><a href="' . esc_url($url) . '" style="background:#007a87;color:#fff;padding:13px 30px;border-radius:6px;text-decoration:none;font-weight:700;font-size:15px;">Inscribirse</a></p>';
        $body .= '<p>Atentamente,<br><strong>Generación Presente</strong></p></div></body></html>';
    } else {
        $asunto = '¿Tienes ' . ($fecha_badge ?: 'la fecha') . ' libre? 👀';
        $body  = '<html><body style="font-family:Arial,sans-serif;color:#333;max-width:580px;margin:0 auto;padding:24px;">';
        $body .= '<h2 style="color:#1a1a1a;">Oye, apunta esto.</h2>';
        $body .= '<p>Organizamos <strong>' . esc_html($titulo) . '</strong>';
        if ($lugar)      $body .= ' en <strong>' . esc_html($lugar) . '</strong>';
        if ($fecha_larga)$body .= ' el ' . esc_html($fecha_larga);
        if ($hora)       $body .= ' a las ' . esc_html($hora) . 'h';
        $body .= '.</p>';
        if ($claim)     $body .= '<p style="font-size:1.05em;font-weight:700;color:#007a87;border-left:3px solid #007a87;padding-left:12px;">' . esc_html($claim) . '</p>';
        if ($pon_txt)   $body .= '<p>Vas a escuchar a:</p><ul>' . $pon_txt . '</ul>';
        $body .= '<p>No hace falta que traigas nada. Solo ganas de pensar.</p>';
        $body .= '<p style="text-align:center;margin:24px 0;"><a href="' . esc_url($url) . '" style="background:#007a87;color:#fff;padding:13px 30px;border-radius:20px;text-decoration:none;font-weight:700;font-size:15px;">Me apunto →</a></p>';
        $body .= '<p style="font-size:12px;color:#999;">Si no puedes, no pasa nada. Habrá más.</p>';
        $body .= '</body></html>';
    }

    $ok = $err = 0;
    foreach ($emails as $mail) {
        wp_mail($mail, $asunto, $body, $headers) ? $ok++ : $err++;
    }
    wp_send_json_success(array(
        'msg' => "Enviados: {$ok}" . ($err ? " | Errores: {$err}" : ''),
    ));
}
