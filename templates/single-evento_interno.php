<?php
/**
 * Landing evento interno — GP Eventik v3.5
 * Público para ver detalles, pero inscripción requiere login + pertenecer al órgano
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header();

while ( have_posts() ) : the_post();
    $id        = get_the_ID();
    $fecha     = get_post_meta($id,'_gpe_int_fecha',true);
    $hora      = get_post_meta($id,'_gpe_int_hora',true);
    $lugar     = get_post_meta($id,'_gpe_int_lugar',true);
    $direccion = get_post_meta($id,'_gpe_int_direccion',true);
    $claim     = get_post_meta($id,'_gpe_int_claim',true);
    $por_que   = get_post_meta($id,'_gpe_int_por_que',true);
    $timeline  = get_post_meta($id,'_gpe_int_timeline',true) ?: array();

    $fecha_badge = '';
    $fecha_iso   = '';
    if ($fecha) {
        $ts = strtotime($fecha);
        $hora_ts = $hora ? strtotime($fecha.' '.$hora) : $ts;
        $fecha_iso = $hora ? date('Y-m-d\TH:i:s',$hora_ts) : date('Y-m-d\T12:00:00',$ts);
        $meses = array('01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC');
        $fecha_badge = date('d',$ts).' '.($meses[date('m',$ts)]??'');
        $fecha_larga = date_i18n('l, j \d\e F \d\e Y',$ts);
    }
?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;}
.gpi{font-family:'Inter',sans-serif;color:#1a1a1a;background:#f5f5f5;padding-bottom:80px;}
.gpi-hero{background:linear-gradient(135deg,#1a1a1a 0%,#333 60%,#555 100%);padding:5rem 2rem 3rem;color:#fff;text-align:center;}
.gpi-hero h1{font-size:clamp(1.8rem,4vw,3rem);font-weight:900;line-height:1.15;margin:0 0 .8rem;color:#fff!important;}
.gpi-hero-claim{font-size:1.1rem;font-weight:300;opacity:.85;margin-bottom:1.5rem;}
.gpi-badge{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.25);border-radius:30px;padding:6px 16px;font-weight:600;font-size:13px;color:#fff;display:inline-block;margin:3px;}
.gpi-interno-badge{background:#e67e22;color:#fff;border-radius:20px;padding:4px 12px;font-size:11px;font-weight:800;letter-spacing:.5px;text-transform:uppercase;display:inline-block;margin-bottom:14px;}
.gpi-layout{display:grid;grid-template-columns:1fr 400px;gap:2.5rem;max-width:1200px;margin:2.5rem auto 0;padding:0 1.5rem;}
.gpi-block{background:#fff;border-radius:16px;padding:2rem 2.2rem;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:1.5rem;}
.gpi-block-title{font-size:1.3rem;font-weight:800;color:#1a1a1a;margin:0 0 1.2rem;padding-bottom:.8rem;border-bottom:2px solid #f0f0f0;}
.gpi-tl-item{display:flex;gap:16px;margin-bottom:0;}
.gpi-tl-left{display:flex;flex-direction:column;align-items:center;width:56px;flex-shrink:0;}
.gpi-tl-hora{font-size:12px;font-weight:800;color:#1a1a1a;white-space:nowrap;}
.gpi-tl-dot{width:12px;height:12px;border-radius:50%;background:#1a1a1a;margin:6px 0;flex-shrink:0;}
.gpi-tl-line{width:2px;flex:1;background:rgba(0,0,0,.1);min-height:28px;}
.gpi-tl-right{padding-bottom:24px;flex:1;}
.gpi-tl-titulo{font-weight:700;font-size:.95rem;color:#1a1a1a;margin-bottom:3px;}
.gpi-tl-desc{font-size:13px;color:#666;line-height:1.6;}
.gpi-tl-item:last-child .gpi-tl-line{display:none;}
.gpi-sidebar{background:#fff;border-radius:16px;padding:2rem;box-shadow:0 2px 12px rgba(0,0,0,0.06);position:sticky;top:90px;}
.gpi-sidebar h2{font-size:1.1rem;font-weight:800;margin:0 0 1rem;}
.gpi-countdown{background:#1a1a1a;border-radius:14px;padding:20px;display:flex;justify-content:center;gap:20px;margin-bottom:18px;}
.gpi-cd-unit{text-align:center;}
.gpi-cd-num{font-size:2rem;font-weight:900;color:#fff;line-height:1;}
.gpi-cd-lbl{font-size:10px;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:1px;margin-top:2px;}
.gpi-cd-sep{font-size:1.8rem;font-weight:900;color:rgba(255,255,255,.4);align-self:flex-start;padding-top:3px;}
.gpi-mapa iframe{width:100%;border-radius:10px;display:block;}
@media(max-width:960px){.gpi-layout{grid-template-columns:1fr;}.gpi-sidebar{position:relative;top:0;}}
@media(max-width:600px){.gpi-hero{padding:3.5rem 1rem 2.5rem;}.gpi-layout{padding:0 1rem;margin-top:1.5rem;}.gpi-countdown{gap:10px;padding:14px;}.gpi-cd-num{font-size:1.6rem;}.gpi-block{padding:1.4rem 1.2rem;}}
</style>

<div class="gpi">
    <div class="gpi-hero">
        <div class="gpi-interno-badge">🔒 Evento Interno</div>
        <h1><?php the_title(); ?></h1>
        <?php if ($claim) : ?><p class="gpi-hero-claim">"<?php echo esc_html($claim); ?>"</p><?php endif; ?>
        <div>
            <?php if ($fecha_badge) : ?><span class="gpi-badge">📅 <?php echo esc_html($fecha_badge); ?><?php if($hora) echo ' · '.esc_html($hora).'h'; ?></span><?php endif; ?>
            <?php if ($lugar) : ?><span class="gpi-badge">📍 <?php echo esc_html($lugar); ?></span><?php endif; ?>
        </div>
    </div>

    <div class="gpi-layout">
        <div>
            <?php if ($por_que) : ?>
            <div class="gpi-block">
                <div class="gpi-block-title">Sobre este evento</div>
                <div style="font-size:1rem;line-height:1.8;color:#444;"><?php echo wp_kses_post($por_que); ?></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($timeline)) : ?>
            <div class="gpi-block">
                <div class="gpi-block-title">Orden del día</div>
                <div>
                    <?php foreach ($timeline as $b) :
                        if (empty($b['titulo'])) continue; ?>
                    <div class="gpi-tl-item">
                        <div class="gpi-tl-left">
                            <div class="gpi-tl-hora"><?php echo esc_html($b['hora']??''); ?></div>
                            <div class="gpi-tl-dot"></div>
                            <div class="gpi-tl-line"></div>
                        </div>
                        <div class="gpi-tl-right">
                            <div class="gpi-tl-titulo"><?php echo esc_html($b['titulo']); ?></div>
                            <?php if (!empty($b['desc'])) : ?><div class="gpi-tl-desc"><?php echo esc_html($b['desc']); ?></div><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($direccion || $lugar) : ?>
            <div class="gpi-block">
                <div class="gpi-block-title">Localización</div>
                <?php if ($lugar) : ?><p style="font-weight:700;margin:0 0 4px;"><?php echo esc_html($lugar); ?></p><?php endif; ?>
                <?php if ($direccion) : ?><p style="color:#666;margin:0 0 14px;font-size:14px;">📍 <?php echo esc_html($direccion); ?></p><?php endif; ?>
                <?php if ($direccion) :
                    $q = urlencode(($lugar?$lugar.', ':'').$direccion);
                ?>
                <div class="gpi-mapa">
                    <iframe src="https://maps.google.com/maps?q=<?php echo esc_attr($q); ?>&output=embed&z=15" width="100%" height="260" frameborder="0" style="border:0;border-radius:10px;" allowfullscreen loading="lazy"></iframe>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="gpi-sidebar">
                <?php if ($fecha_iso) : ?>
                <div class="gpi-countdown" id="gpi-cd">
                    <div class="gpi-cd-unit"><div class="gpi-cd-num" id="gpi-cd-d">--</div><div class="gpi-cd-lbl">días</div></div>
                    <div class="gpi-cd-sep">:</div>
                    <div class="gpi-cd-unit"><div class="gpi-cd-num" id="gpi-cd-h">--</div><div class="gpi-cd-lbl">horas</div></div>
                    <div class="gpi-cd-sep">:</div>
                    <div class="gpi-cd-unit"><div class="gpi-cd-num" id="gpi-cd-m">--</div><div class="gpi-cd-lbl">min</div></div>
                    <div class="gpi-cd-sep">:</div>
                    <div class="gpi-cd-unit"><div class="gpi-cd-num" id="gpi-cd-s">--</div><div class="gpi-cd-lbl">seg</div></div>
                </div>
                <script>
                (function(){
                    var t=new Date("<?php echo esc_js($fecha_iso); ?>").getTime();
                    var e={d:document.getElementById('gpi-cd-d'),h:document.getElementById('gpi-cd-h'),m:document.getElementById('gpi-cd-m'),s:document.getElementById('gpi-cd-s')};
                    function tick(){
                        var d=t-Date.now(); if(d<=0){document.getElementById('gpi-cd').innerHTML='<p style="color:#fff;font-weight:700;text-align:center;margin:0;">¡En curso!</p>';return;}
                        e.d.textContent=String(Math.floor(d/86400000)).padStart(2,'0');
                        e.h.textContent=String(Math.floor(d%86400000/3600000)).padStart(2,'0');
                        e.m.textContent=String(Math.floor(d%3600000/60000)).padStart(2,'0');
                        e.s.textContent=String(Math.floor(d%60000/1000)).padStart(2,'0');
                    } tick(); setInterval(tick,1000);
                })();
                </script>
                <?php endif; ?>

                <h2 style="margin-bottom:8px;">Asistencia</h2>
                <p style="font-size:14px;color:#666;margin-bottom:16px;">Este es un evento interno. Solo pueden inscribirse los/las embajadores/as invitados/as.</p>

                <?php
                // Mostrar formulario o estado según login
                if (function_exists('gpe_render_formulario_interno')) {
                    gpe_render_formulario_interno($id);
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php endwhile;
get_footer();
