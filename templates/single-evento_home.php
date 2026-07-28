<?php
/**
 * Landing inmersiva del evento — GP Eventik v3.1
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
    $id           = get_the_ID();
    $fecha        = get_post_meta($id,'_med_fecha_evento',true);
    $hora         = get_post_meta($id,'_med_hora_evento',true);
    $provincia    = get_post_meta($id,'_med_provincia_sitio',true);
    $codigo_prov  = get_post_meta($id,'_gpe_codigo_provincia',true);
    $lugar        = get_post_meta($id,'_gpe_lugar_nombre',true);
    $direccion    = get_post_meta($id,'_gpe_direccion',true);
    $enlace_ext   = get_post_meta($id,'_med_url_inscripcion',true);
    $usar_nativa  = get_post_meta($id,'_gpe_usar_inscripcion_nativa',true);
    $claim        = get_post_meta($id,'_gpe_claim',true);
    $foto_hero_id = get_post_meta($id,'_gpe_foto_hero',true);
    $foto_hero_url= $foto_hero_id ? wp_get_attachment_image_url($foto_hero_id,'full') : '';
    $por_que      = get_post_meta($id,'_gpe_por_que',true);
    $programa     = get_post_meta($id,'_gpe_programa_detalle',true);
    $timeline     = get_post_meta($id,'_gpe_timeline_bloques',true) ?: array();
    $agenda_texto = get_post_meta($id,'_gpe_agenda_timeline',true);
    $preguntas    = get_post_meta($id,'_gpe_preguntas_debate',true);
    $faqs         = get_post_meta($id,'_gpe_faqs_acordeon',true);
    $mapa_embed   = get_post_meta($id,'_gpe_mapa_embed',true);
    $transporte   = get_post_meta($id,'_gpe_transporte',true);
    $ponentes_ids = get_post_meta($id,'_gpe_ponentes_ids',true) ?: array();
    $sold_out     = gpe_evento_sold_out($id);
    $inscritos    = gpe_inscritos_count($id);
    $aforo        = gpe_aforo_maximo($id);
    $libres       = gpe_plazas_libres($id);

    // Fecha badge
    $fecha_badge = '';
    $fecha_iso   = '';
    if ( $fecha ) {
        $ts    = strtotime($fecha);
        $hora_ts = $hora ? strtotime($fecha . ' ' . $hora) : $ts;
        $fecha_iso = $hora ? date('Y-m-d\TH:i:s', $hora_ts) : date('Y-m-d\T12:00:00', $ts);
        $meses = array('01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC');
        $fecha_badge = date('d',$ts).' '.($meses[date('m',$ts)]??'').($codigo_prov?' · '.$codigo_prov:($provincia?' · '.$provincia:''));
        $fecha_larga = date_i18n(get_option('date_format'), $ts);
    }

    // Hero BG
    $hero_style = $foto_hero_url
        ? 'background: linear-gradient(135deg, rgba(0,80,90,0.82) 0%, rgba(0,122,135,0.75) 100%), url('.esc_url($foto_hero_url).') center/cover no-repeat;'
        : 'background: linear-gradient(135deg, #007a87 0%, #009da8 40%, #00b4cc 100%);';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;}
.gpl{font-family:'Inter',sans-serif;color:#1a1a1a;background:#f5f5f5;padding-bottom:80px;}
/* HERO */
.gpl-hero{<?php echo $hero_style;?> padding:6rem 2rem 4rem;color:#fff;text-align:center;position:relative;}
.gpl-hero h1{font-size:clamp(2rem,5vw,3.8rem);font-weight:900;line-height:1.1;margin:0 0 1rem;letter-spacing:-1px;color:#fff!important;}
.gpl-hero-claim{font-size:1.25rem;font-weight:300;opacity:.92;margin-bottom:2rem;}
.gpl-badges{display:flex;flex-wrap:wrap;justify-content:center;gap:10px;margin-top:1.5rem;}
.gpl-badge{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);border-radius:30px;padding:7px 18px;font-weight:600;font-size:14px;color:#fff;}
.gpl-badge.sold{background:rgba(192,57,43,.75);border-color:rgba(192,57,43,.5);}
/* CUENTA ATRÁS */
.gpl-countdown{background:#fff;border-radius:16px;padding:24px;display:flex;justify-content:center;gap:24px;max-width:480px;margin:-28px auto 0;position:relative;z-index:10;box-shadow:0 8px 30px rgba(0,0,0,0.1);}
.gpl-countdown-unit{text-align:center;}
.gpl-countdown-num{font-size:2.2rem;font-weight:900;color:#007a87;line-height:1;}
.gpl-countdown-lbl{font-size:11px;font-weight:600;color:#999;text-transform:uppercase;letter-spacing:1px;margin-top:2px;}
.gpl-countdown-sep{font-size:2rem;font-weight:900;color:#ccc;align-self:flex-start;padding-top:4px;}
.gpl-countdown-msg{font-weight:700;color:#007a87;font-size:1.1rem;align-self:center;}
/* LAYOUT */
.gpl-layout{display:grid;grid-template-columns:1fr 420px;gap:2.5rem;max-width:1240px;margin:3rem auto 0;padding:0 1.5rem;}
/* BLOQUES */
.gpl-block{background:#fff;border-radius:16px;padding:2rem 2.2rem;box-shadow:0 2px 12px rgba(0,0,0,0.05);}
.gpl-block + .gpl-block{margin-top:1.5rem;}
.gpl-block-title{font-size:1.4rem;font-weight:800;color:#1a1a1a;margin:0 0 1.2rem;padding-bottom:.8rem;border-bottom:2px solid #f0f0f0;}
/* TIMELINE */
.gpl-timeline{position:relative;padding-left:0;}
.gpl-timeline-item{display:flex;gap:20px;margin-bottom:0;position:relative;}
.gpl-timeline-item:last-child .gpl-tl-line{display:none;}
.gpl-tl-left{display:flex;flex-direction:column;align-items:center;width:60px;flex-shrink:0;}
.gpl-tl-hora{font-size:13px;font-weight:800;color:#007a87;white-space:nowrap;}
.gpl-tl-dot{width:14px;height:14px;border-radius:50%;background:linear-gradient(135deg,#007a87,#00b4cc);margin:6px 0;flex-shrink:0;box-shadow:0 0 0 3px rgba(0,122,135,0.15);}
.gpl-tl-line{width:2px;flex:1;background:linear-gradient(180deg,rgba(0,122,135,0.25),transparent);min-height:30px;}
.gpl-tl-right{padding-bottom:28px;flex:1;}
.gpl-tl-titulo{font-weight:700;font-size:1rem;color:#1a1a1a;margin-bottom:4px;}
.gpl-tl-desc{font-size:14px;color:#666;line-height:1.6;}
/* PONENTES */
.gpl-speakers{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:1.2rem;}
.gpl-speaker{background:#f8fafb;border:1px solid rgba(0,122,135,0.08);border-radius:12px;padding:1.5rem;text-align:center;transition:all .25s;}
.gpl-speaker:hover{border-color:#00b4cc;transform:translateY(-3px);box-shadow:0 8px 24px rgba(0,0,0,0.07);}
.gpl-speaker-img{width:80px;height:80px;border-radius:50%;object-fit:cover;margin:0 auto .8rem;display:block;border:3px solid #00b4cc;background:#eee;}
.gpl-speaker-rol{font-size:10px;font-weight:700;text-transform:uppercase;color:#00b4cc;letter-spacing:1px;margin-bottom:4px;}
.gpl-speaker-name{font-weight:800;font-size:1rem;color:#1a1a1a;margin-bottom:3px;}
.gpl-speaker-cargo{font-size:12px;font-weight:600;color:#007a87;margin-bottom:8px;}
.gpl-speaker-quote{font-style:italic;color:#666;font-size:13px;line-height:1.5;background:rgba(0,122,135,0.04);padding:8px 10px;border-radius:8px;border-left:3px solid #007a87;text-align:left;margin-bottom:8px;}
.gpl-speaker-redes{display:flex;justify-content:center;gap:8px;margin-top:8px;}
.gpl-speaker-red{font-size:12px;color:#007a87;font-weight:600;text-decoration:none;}
.gpl-speaker-red:hover{text-decoration:underline;}
/* PREGUNTAS */
.gpl-pregunta{background:#fff;border:1px solid rgba(0,122,135,0.1);padding:14px 18px;border-radius:10px;margin-bottom:10px;font-weight:600;font-size:15px;display:flex;align-items:center;gap:12px;}
.gpl-pregunta-icon{width:28px;height:28px;background:linear-gradient(135deg,#007a87,#00b4cc);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;font-size:13px;}
/* SIDEBAR */
.gpl-sidebar-card{background:#fff;border-radius:16px;padding:2rem;box-shadow:0 2px 12px rgba(0,0,0,0.06);text-align:center;position:sticky;top:100px;}
.gpl-sidebar-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#007a87;margin-bottom:4px;}
.gpl-sidebar-title{font-size:1.15rem;font-weight:800;color:#1a1a1a;margin-bottom:1.2rem;line-height:1.3;}
.gpl-aforo-barra-wrap{margin-bottom:16px;}
.gpl-aforo-barra-bg{background:#eee;border-radius:20px;height:7px;overflow:hidden;}
.gpl-aforo-barra-fill{height:100%;background:linear-gradient(90deg,#007a87,#00b4cc);border-radius:20px;}
.gpl-aforo-labels{display:flex;justify-content:space-between;font-size:12px;font-weight:600;color:#888;margin-bottom:5px;}
.gpl-btn-primary{display:block;width:100%;padding:14px;background:linear-gradient(135deg,#007a87,#00b4cc);color:#fff!important;font-weight:700;font-size:16px;border-radius:30px;text-decoration:none;text-align:center;border:none;cursor:pointer;transition:all .3s;box-shadow:0 6px 20px rgba(0,122,135,0.28);}
.gpl-btn-primary:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(0,180,204,0.4);}
.gpl-sold-box{background:linear-gradient(135deg,#c0392b,#e74c3c);color:#fff;border-radius:12px;padding:20px;}
.gpl-sold-title{font-size:1.2rem;font-weight:800;margin-bottom:4px;}
.gpl-sold-sub{font-size:13px;opacity:.85;}
.gpl-proximamente{background:rgba(0,122,135,0.05);border:1px dashed #007a87;color:#007a87;padding:1rem;border-radius:30px;font-weight:700;font-size:14px;}
/* MAPA */
.gpl-mapa iframe{width:100%;border-radius:10px;display:block;}
/* RESPONSIVE */
@media(max-width:960px){
    .gpl-layout{grid-template-columns:1fr;}
    .gpl-sidebar-card{position:relative;top:0;}
    .gpl-speakers{grid-template-columns:1fr 1fr;}
}
@media(max-width:600px){
    .gpl-hero{padding:4rem 1rem 3rem;}
    .gpl-layout{padding:0 1rem;}
    .gpl-countdown{gap:12px;padding:16px;margin-left:1rem;margin-right:1rem;max-width:calc(100% - 2rem);}
    .gpl-countdown-num{font-size:1.6rem;}
    .gpl-block{padding:1.4rem 1.2rem;}
    .gpl-speakers{grid-template-columns:1fr;}
}
</style>

<div class="gpl">

    <!-- HERO -->
    <div class="gpl-hero">
        <h1><?php the_title(); ?></h1>
        <?php if ($claim) : ?>
            <p class="gpl-hero-claim">"<?php echo esc_html($claim); ?>"</p>
        <?php endif; ?>
        <div class="gpl-badges">
            <?php if ($fecha_badge) : ?><span class="gpl-badge">📅 <?php echo esc_html($fecha_badge); ?><?php if($hora) echo ' · '.esc_html($hora).'h'; ?></span><?php endif; ?>
            <?php if ($lugar) : ?><span class="gpl-badge">📍 <?php echo esc_html($lugar); ?></span><?php elseif($provincia) : ?><span class="gpl-badge">📍 <?php echo esc_html($provincia); ?></span><?php endif; ?>
            <?php if ($sold_out) : ?><span class="gpl-badge sold">🔴 <?php echo gpe__('gpe_aforo_completado','Aforo completado'); ?></span>
            <?php elseif ($aforo > 0) : ?><span class="gpl-badge">🎟️ <?php echo $libres; ?> <?php echo gpe__('gpe_plazas_disponibles','plazas disponibles'); ?></span><?php endif; ?>
        </div>
    </div>

    <!-- CUENTA ATRÁS -->
    <?php if ($fecha_iso) : ?>
    <div class="gpl-countdown" id="gpl-countdown" data-fecha="<?php echo esc_attr($fecha_iso); ?>">
        <div class="gpl-countdown-unit"><div class="gpl-countdown-num" id="gpl-cd-d">--</div><div class="gpl-countdown-lbl"><?php echo gpe__('gpe_cuenta_atras_dias','días'); ?></div></div>
        <div class="gpl-countdown-sep">:</div>
        <div class="gpl-countdown-unit"><div class="gpl-countdown-num" id="gpl-cd-h">--</div><div class="gpl-countdown-lbl"><?php echo gpe__('gpe_cuenta_atras_horas','horas'); ?></div></div>
        <div class="gpl-countdown-sep">:</div>
        <div class="gpl-countdown-unit"><div class="gpl-countdown-num" id="gpl-cd-m">--</div><div class="gpl-countdown-lbl"><?php echo gpe__('gpe_cuenta_atras_min','min'); ?></div></div>
        <div class="gpl-countdown-sep">:</div>
        <div class="gpl-countdown-unit"><div class="gpl-countdown-num" id="gpl-cd-s">--</div><div class="gpl-countdown-lbl"><?php echo gpe__('gpe_cuenta_atras_seg','seg'); ?></div></div>
    </div>
    <script>
    (function(){
        var target = new Date("<?php echo esc_js($fecha_iso); ?>").getTime();
        var el = {d:document.getElementById('gpl-cd-d'),h:document.getElementById('gpl-cd-h'),m:document.getElementById('gpl-cd-m'),s:document.getElementById('gpl-cd-s')};
        var wrap = document.getElementById('gpl-countdown');
        function tick(){
            var diff = target - Date.now();
            if (diff <= 0) {
                wrap.innerHTML = '<div class="gpl-countdown-msg"><?php echo esc_js(gpe__('gpe_evento_comenzado','¡El evento ha comenzado!')); ?></div>';
                return;
            }
            var d=Math.floor(diff/86400000), h=Math.floor((diff%86400000)/3600000), m=Math.floor((diff%3600000)/60000), s=Math.floor((diff%60000)/1000);
            el.d.textContent=String(d).padStart(2,'0');
            el.h.textContent=String(h).padStart(2,'0');
            el.m.textContent=String(m).padStart(2,'0');
            el.s.textContent=String(s).padStart(2,'0');
        }
        tick(); setInterval(tick,1000);
    })();
    </script>
    <?php endif; ?>

    <!-- LAYOUT PRINCIPAL -->
    <div class="gpl-layout">
        <div><!-- Columna principal -->

            <?php if ($por_que) : ?>
            <div class="gpl-block">
                <div class="gpl-block-title"><?php echo gpe__('gpe_por_que_asistir','¿Por qué asistir?'); ?></div>
                <div style="font-size:1rem;line-height:1.8;color:#444;"><?php echo wp_kses_post($por_que); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($ponentes_ids)) :
                $ponentes_validos = array_filter($ponentes_ids, function($pid){ return get_post($pid) && get_post_meta($pid,'_gpe_cfg_p_landing',true) !== '0'; });
                if (!empty($ponentes_validos)) : ?>
            <div class="gpl-block">
                <div class="gpl-block-title"><?php echo gpe__('gpe_ponentes_titulo','Ponentes'); ?></div>
                <div class="gpl-speakers">
                    <?php foreach ($ponentes_validos as $pid) :
                        $sp       = get_post($pid);
                        $cargo    = get_post_meta($pid,'_gpe_basico_cargo',true);
                        $empresa  = get_post_meta($pid,'_gpe_basico_empresa',true);
                        $frase    = get_post_meta($pid,'_gpe_quote_frase',true);
                        $debate   = get_post_meta($pid,'_gpe_quote_debate',true);
                        $foto_id  = get_post_meta($pid,'_gpe_visual_foto_id',true);
                        $foto_url = $foto_id ? wp_get_attachment_image_url($foto_id,'thumbnail') : 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
                        $rol      = get_post_meta($pid,'_gpe_part_tipo',true) ?: 'Speaker';
                        $use_pub  = get_post_meta($pid,'_gpe_basico_use_pub',true);
                        $nom_pub  = get_post_meta($pid,'_gpe_basico_publico',true);
                        $nombre_m = ($use_pub && $nom_pub) ? $nom_pub : $sp->post_title;
                        $redes    = get_post_meta($pid,'_gpe_redes_sociales',true) ?: array();
                        $web      = get_post_meta($pid,'_gpe_social_web',true);
                        $v_perfil = get_post_meta($pid,'_gpe_cfg_v_perfil',true) !== '0';
                        $v_redes  = get_post_meta($pid,'_gpe_cfg_v_redes',true) !== '0';
                        $nets_url = array('linkedin'=>'https://linkedin.com/in/','instagram'=>'https://instagram.com/','twitter'=>'https://twitter.com/','tiktok'=>'https://tiktok.com/@','youtube'=>'','facebook'=>'https://facebook.com/','web'=>'');
                    ?>
                    <div class="gpl-speaker">
                        <?php if ($v_perfil) : ?>
                            <img src="<?php echo esc_url($foto_url); ?>" class="gpl-speaker-img" alt="<?php echo esc_attr($nombre_m); ?>">
                        <?php endif; ?>
                        <div class="gpl-speaker-rol"><?php echo esc_html($rol); ?></div>
                        <div class="gpl-speaker-name"><?php echo esc_html($nombre_m); ?></div>
                        <?php if ($cargo || $empresa) : ?>
                            <div class="gpl-speaker-cargo"><?php echo esc_html($cargo); ?><?php if ($cargo&&$empresa) echo ' · '; echo esc_html($empresa); ?></div>
                        <?php endif; ?>
                        <?php if ($frase) : ?><div class="gpl-speaker-quote">"<?php echo esc_html($frase); ?>"</div><?php endif; ?>
                        <?php if ($debate) : ?><div style="font-size:13px;color:#555;text-align:left;margin-top:6px;"><strong>Tesis:</strong> <?php echo esc_html($debate); ?></div><?php endif; ?>
                        <?php if ($v_redes && (!empty($redes) || $web)) : ?>
                        <div class="gpl-speaker-redes">
                            <?php foreach ($redes as $r) :
                                $base = $nets_url[$r['red']] ?? '';
                                $href = ($r['red']==='web'||$r['red']==='youtube') ? $r['usuario'] : $base.$r['usuario'];
                                $labels = array('linkedin'=>'LinkedIn','instagram'=>'Instagram','twitter'=>'Twitter','tiktok'=>'TikTok','youtube'=>'YouTube','facebook'=>'Facebook','web'=>'Web');
                                $label  = $labels[$r['red']] ?? $r['red'];
                            ?>
                                <a href="<?php echo esc_url($href); ?>" target="_blank" rel="noopener" class="gpl-speaker-red"><?php echo esc_html($label); ?></a>
                            <?php endforeach; ?>
                            <?php if ($web) : ?><a href="<?php echo esc_url($web); ?>" target="_blank" rel="noopener" class="gpl-speaker-red">Web</a><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; endif; ?>

            <?php
            // TIMELINE — primero intenta bloques estructurados, luego texto libre
            $tiene_timeline = !empty($timeline) && is_array($timeline);
            $tiene_agenda   = !empty($agenda_texto);
            if ($tiene_timeline || $tiene_agenda || !empty($programa)) : ?>
            <div class="gpl-block">
                <div class="gpl-block-title"><?php echo gpe__('gpe_agenda_titulo','Agenda'); ?></div>

                <?php if ($tiene_timeline) : ?>
                <div class="gpl-timeline">
                    <?php foreach ($timeline as $bloque) :
                        $b_hora  = $bloque['hora']  ?? '';
                        $b_tit   = $bloque['titulo'] ?? '';
                        $b_desc  = $bloque['desc']   ?? '';
                        if (!$b_tit) continue;
                    ?>
                    <div class="gpl-timeline-item">
                        <div class="gpl-tl-left">
                            <div class="gpl-tl-hora"><?php echo esc_html($b_hora); ?></div>
                            <div class="gpl-tl-dot"></div>
                            <div class="gpl-tl-line"></div>
                        </div>
                        <div class="gpl-tl-right">
                            <div class="gpl-tl-titulo"><?php echo esc_html($b_tit); ?></div>
                            <?php if ($b_desc) : ?><div class="gpl-tl-desc"><?php echo esc_html($b_desc); ?></div><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php elseif ($tiene_agenda) : ?>
                    <div style="font-size:1rem;line-height:1.8;color:#444;"><?php echo wp_kses_post($agenda_texto); ?></div>
                <?php endif; ?>

                <?php /* programa_detalle suprimido: el timeline lo reemplaza */ ?>
            </div>
            <?php endif; ?>

            <?php if ($preguntas) : ?>
            <div class="gpl-block" style="border-left:4px solid #1fc4a8;">
                <div class="gpl-block-title"><?php echo gpe__('gpe_preguntas_titulo','¿Qué ideas se debatirán?'); ?></div>
                <?php foreach (explode("\n", $preguntas) as $linea) :
                    $linea = trim($linea);
                    if ($linea) : ?>
                    <div class="gpl-pregunta"><div class="gpl-pregunta-icon">⚡</div><?php echo esc_html($linea); ?></div>
                <?php endif; endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($mapa_embed || $direccion) : ?>
            <div class="gpl-block">
                <div class="gpl-block-title"><?php echo gpe__('gpe_localizacion_titulo','Cómo llegar'); ?></div>
                <?php if ($lugar) : ?><p style="font-weight:700;font-size:1.05rem;margin:0 0 4px;"><?php echo esc_html($lugar); ?></p><?php endif; ?>
                <?php if ($direccion) : ?><p style="color:#666;margin:0 0 14px;font-size:14px;">📍 <?php echo esc_html($direccion); ?></p><?php endif; ?>
                <?php if ($mapa_embed) : ?>
                    <div class="gpl-mapa"><?php echo wp_kses($mapa_embed, array('iframe'=>array('src'=>true,'width'=>true,'height'=>true,'frameborder'=>true,'allowfullscreen'=>true,'style'=>true,'loading'=>true))); ?></div>
                <?php elseif ($direccion) :
                    $q = urlencode( ($lugar ? $lugar.', ' : '') . $direccion );
                ?>
                    <div class="gpl-mapa">
                        <iframe src="https://maps.google.com/maps?q=<?php echo esc_attr($q); ?>&output=embed&z=15" width="100%" height="300" frameborder="0" style="border:0;border-radius:10px;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    </div>
                <?php endif; ?>
                <?php if ($transporte) : ?><div style="margin-top:16px;color:#555;font-size:15px;line-height:1.7;"><?php echo wp_kses_post($transporte); ?></div><?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($faqs) : ?>
            <div class="gpl-block">
                <div class="gpl-block-title"><?php echo gpe__('gpe_faqs_titulo','Preguntas frecuentes'); ?></div>
                <div style="color:#555;line-height:1.7;"><?php echo wp_kses_post($faqs); ?></div>
            </div>
            <?php endif; ?>

        </div><!-- /col principal -->

        <!-- SIDEBAR -->
        <div>
            <div class="gpl-sidebar-card">
                <div class="gpl-sidebar-label"><?php echo gpe__('gpe_participacion','Participación juvenil'); ?></div>
                <div class="gpl-sidebar-title"><?php echo gpe__('gpe_acreditacion','Reserva de plaza y acreditación'); ?></div>

                <?php if ($aforo > 0) : ?>
                <div class="gpl-aforo-barra-wrap">
                    <div class="gpl-aforo-labels">
                        <span><?php echo $inscritos; ?> inscritos</span>
                        <span><?php echo $aforo; ?> plazas</span>
                    </div>
                    <div class="gpl-aforo-barra-bg">
                        <?php $pct = $aforo > 0 ? min(100,round($inscritos/$aforo*100)) : 0; ?>
                        <div class="gpl-aforo-barra-fill" style="width:<?php echo $pct; ?>%;"></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($sold_out) : ?>
                    <div class="gpl-sold-box">
                        <div class="gpl-sold-title">🔴 <?php echo gpe__('gpe_aforo_completado','Aforo completado'); ?></div>
                        <div class="gpl-sold-sub">No quedan plazas disponibles</div>
                    </div>
                    <?php echo gpe_render_formulario_espera($id); ?>

                <?php elseif ($usar_nativa === '1') : ?>
                    <?php echo do_shortcode('[gpe_formulario_inscripcion evento_id="'.$id.'"]'); ?>

                <?php else : ?>
                    <div class="gpl-proximamente">🔒 <?php echo gpe__('gpe_proximamente','Inscripciones próximamente'); ?></div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /layout -->

</div><!-- /gpl -->

<?php endwhile;
get_footer();
