<?php
/**
 * Vista pública: tarjetas de la agenda
 * Shortcode [gp_agenda_eventos] — con carrusel en móvil
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Cargar Font Awesome si no está ya cargado por el tema
if ( ! wp_style_is('font-awesome', 'enqueued') && ! wp_style_is('fontawesome', 'enqueued') ) {
    echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
}

if ( ! isset($eventos_query) ) {
    $eventos_query = new WP_Query( array(
        'post_type'      => 'evento_home',
        'posts_per_page' => 12,
        'post_status'    => 'publish',
        'meta_key'       => '_med_fecha_evento',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ) );
}

if ( ! $eventos_query->have_posts() ) : ?>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;900&display=swap" rel="stylesheet">
<div style="width:100%;font-family:'Inter',sans-serif;">
    <div style="background:#fbfbfb;border-radius:15px;padding:48px 40px;width:100%;box-sizing:border-box;border:2px solid #ffffff;box-shadow:0 0 27px -5px rgba(50,50,53,0.22);">
        <div style="font-size:1.75rem;font-weight:900;color:#111;line-height:1.2;margin-bottom:12px;">
            Aquí debería haber un evento.
        </div>
        <div style="font-size:0.95rem;font-weight:500;color:#888;max-width:600px;line-height:1.75;">
            Simplemente aún no hemos organizado nada, o es que ya ha pasado todo y no te has enterado (eso también es una posibilidad).<br>
            Vuelve pronto si quieres estar siempre al tanto.
        </div>
    </div>
</div>
<?php return; endif; ?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;900&display=swap" rel="stylesheet">

<div class="gpe-agenda-outer">
    <div class="gpe-agenda-track" id="gpe-agenda-track">
    <?php while ( $eventos_query->have_posts() ) : $eventos_query->the_post();
        $eid          = get_the_ID();
        $fecha_raw    = get_post_meta($eid,'_med_fecha_evento',true);
        $hora         = get_post_meta($eid,'_med_hora_evento',true);
        $provincia    = get_post_meta($eid,'_med_provincia_sitio',true);
        $codigo_prov  = get_post_meta($eid,'_gpe_codigo_provincia',true);
        $lugar        = get_post_meta($eid,'_gpe_lugar_nombre',true);
        $usar_nativa  = get_post_meta($eid,'_gpe_usar_inscripcion_nativa',true);
        $url_ext      = get_post_meta($eid,'_med_url_inscripcion',true);
        $sold_out     = gpe_evento_sold_out($eid);

        // Formato fecha: "16 JUL · BCN"
        $fecha_badge = '';
        if ( $fecha_raw ) {
            $ts    = strtotime($fecha_raw);
            $meses = array('01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC');
            $fecha_badge = date('d',$ts) . ' ' . ($meses[date('m',$ts)] ?? '') . ( $codigo_prov ? ' · '.$codigo_prov : ($provincia ? ' · '.$provincia : '') );
        }

        $tematicas       = wp_get_post_terms($eid,'tematica_evento');
        $nombre_tematica = (!empty($tematicas) && !is_wp_error($tematicas)) ? $tematicas[0]->name : 'General';
        $tematica_icono  = (!empty($tematicas) && !is_wp_error($tematicas) && function_exists('gpe_get_tematica_icono'))
            ? gpe_get_tematica_icono($tematicas[0]->term_id)
            : 'fa-solid fa-tag';
        $url_boton       = get_permalink();
        if ( !$usar_nativa && $url_ext ) $url_boton = $url_ext;
    ?>
        <div class="gpe-agenda-card <?php echo $sold_out ? 'gpe-card-sold' : ''; ?>">
            <div class="gpe-card-top">
                <span class="gpe-badge-cat"><i class="<?php echo esc_attr($tematica_icono); ?>" style="font-size:16px;color:#007a87;margin-right:5px;"></i><?php echo esc_html($nombre_tematica); ?></span>
                <?php if ($sold_out) : ?>
                    <span class="gpe-badge-sold">Aforo completado</span>
                <?php endif; ?>
            </div>
            <div class="gpe-card-fecha"><?php echo esc_html($fecha_badge ?: '—'); ?></div>
            <?php if ($hora) : ?><div class="gpe-card-hora"><?php echo esc_html($hora); ?>h</div><?php endif; ?>
            <div class="gpe-card-titulo"><?php the_title(); ?></div>
            <?php if ($lugar) : ?><div class="gpe-card-lugar"><i class="fas fa-map-marker-alt" style="color:#007a87; margin-right:5px;"></i><?php echo esc_html($lugar); ?></div><?php endif; ?>
            <?php if ($sold_out) : ?>
                <a href="<?php echo esc_url(get_permalink()); ?>" class="gpe-btn-sold"><?php echo gpe__('gpe_aforo_completado','Aforo completado'); ?></a>
            <?php else : ?>
                <a href="<?php echo esc_url($url_boton); ?>" class="gpe-btn-inscribete"><?php echo gpe__('gpe_inscribete','Inscríbete'); ?></a>
            <?php endif; ?>
        </div>
    <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <div class="gpe-carousel-dots" id="gpe-carousel-dots"></div>
    <div class="gpe-carousel-arrow-wrap">
        <svg width="28" height="16" viewBox="0 0 28 16" fill="none"><path d="M2 3L14 13L26 3" stroke="#ccc" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
</div>

<style>
.gpe-agenda-outer {
    position: relative;
    font-family: 'Inter', sans-serif;
}
.gpe-agenda-track {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
}
.gpe-agenda-card {
    background: #fbfbfb;
    border-radius: 15px;
    padding: 20px;
    width: 280px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    border: 2px solid #ffffff;
    box-shadow: 0 0 27px -5px rgba(50,50,53,0.22);
    box-sizing: border-box;
    transition: transform 0.2s, box-shadow 0.2s;
    flex-shrink: 0;
}
.gpe-agenda-card:hover { transform: translateY(-4px); box-shadow: 0 12px 35px rgba(0,122,135,0.14); }
.gpe-card-sold { opacity: 0.72; }
.gpe-card-top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
.gpe-badge-cat { background: #e0f2f5; color: #007a87; padding: 5px 13px; border-radius: 20px; font-weight: 700; font-size: 13px; }
.gpe-badge-sold { background: #c0392b; color: #fff; padding: 4px 10px; border-radius: 20px; font-weight: 800; font-size: 11px; letter-spacing: .5px; }
.gpe-card-fecha { font-size: 30px; font-weight: 900; color: #111; line-height: 1.1; }
.gpe-card-hora  { font-size: 13px; color: #888; font-weight: 600; }
.gpe-card-titulo { font-size: 18px; font-weight: 700; color: #111; line-height: 1.3; margin: 6px 0 4px; }
.gpe-card-lugar  { font-size: 12px; color: #777; }
.gpe-btn-inscribete {
    margin-top: auto;
    display: block;
    background: linear-gradient(60deg, #1495a1, #007a87);
    color: #fff !important;
    text-align: center;
    padding: 10px 0;
    border-radius: 25px;
    text-decoration: none;
    font-size: 16px;
    font-weight: 600;
    transition: opacity 0.2s;
}
.gpe-btn-inscribete:hover { opacity: 0.88; color:#fff !important; text-decoration:none; }
.gpe-btn-sold {
    margin-top: auto;
    display: block;
    background: #e0e0e0;
    color: #888 !important;
    text-align: center;
    padding: 10px 0;
    border-radius: 25px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: default;
}
.gpe-carousel-dots, .gpe-carousel-arrow-wrap { display: none; }

@media (max-width: 767px) {
    .gpe-agenda-outer { overflow: hidden; padding-bottom: 48px; }
    .gpe-agenda-track {
        flex-wrap: nowrap;
        justify-content: flex-start;
        overflow-x: hidden;
        gap: 0;
        transition: transform 0.35s cubic-bezier(.4,0,.2,1);
    }
    .gpe-agenda-card {
        min-width: calc(100vw - 48px);
        max-width: calc(100vw - 48px);
        margin: 0 12px;
        flex-shrink: 0;
    }
    .gpe-carousel-dots {
        display: flex;
        justify-content: center;
        gap: 7px;
        position: absolute;
        bottom: 20px;
        left: 0; right: 0;
    }
    .gpe-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #ddd;
        cursor: pointer;
        transition: background 0.25s, transform 0.25s;
    }
    .gpe-dot.active { background: #007a87; transform: scale(1.3); }
    .gpe-carousel-arrow-wrap {
        display: flex;
        justify-content: center;
        position: absolute;
        bottom: 4px;
        left: 0; right: 0;
        pointer-events: none;
        opacity: 0.55;
    }
}
</style>

<script>
(function(){
    var track  = document.getElementById('gpe-agenda-track');
    var dotsEl = document.getElementById('gpe-carousel-dots');
    if (!track) return;
    var cards = track.querySelectorAll('.gpe-agenda-card');
    var total = cards.length;
    var cur   = 0;
    function isMobile() { return window.innerWidth < 768; }
    for (var i = 0; i < total; i++) {
        var d = document.createElement('div');
        d.className = 'gpe-dot' + (i === 0 ? ' active' : '');
        (function(idx){ d.addEventListener('click', function(){ goTo(idx); }); })(i);
        dotsEl.appendChild(d);
    }
    function goTo(idx) {
        if (!isMobile()) return;
        cur = Math.max(0, Math.min(idx, total - 1));
        var cardW = cards[0].offsetWidth + 24;
        track.style.transform = 'translateX(-' + (cur * cardW) + 'px)';
        document.querySelectorAll('.gpe-dot').forEach(function(d,i){ d.classList.toggle('active', i === cur); });
    }
    var txStart = 0;
    track.addEventListener('touchstart', function(e){ txStart = e.touches[0].clientX; }, {passive:true});
    track.addEventListener('touchend', function(e){
        var dx = txStart - e.changedTouches[0].clientX;
        if (Math.abs(dx) > 40) goTo(dx > 0 ? cur + 1 : cur - 1);
    }, {passive:true});
    window.addEventListener('resize', function(){ if (!isMobile()) track.style.transform = ''; else goTo(cur); });
    if (isMobile()) goTo(0);
})();
</script>
