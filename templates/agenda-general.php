<?php
/**
 * Plantilla: Agenda General de la Asociación
 * Shortcode: [gp_agenda_general]
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! isset($eventos_query) ) return;
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;900&display=swap" rel="stylesheet">

<?php if ( ! $eventos_query->have_posts() ) : ?>
    <p style="text-align:center; color:#999; font-family:'Inter',sans-serif; padding:30px;">No hay eventos próximos programados.</p>
    <?php return; ?>
<?php endif; ?>

<div class="gpe-agenda-general-wrapper">

    <div class="gpe-agenda-grid">
    <?php while ( $eventos_query->have_posts() ) : $eventos_query->the_post();
        $eid          = get_the_ID();
        $fecha_raw    = get_post_meta($eid, '_med_fecha_evento',    true);
        $hora         = get_post_meta($eid, '_med_hora_evento',     true);
        $provincia    = get_post_meta($eid, '_med_provincia_sitio', true);
        $lugar        = get_post_meta($eid, '_gpe_lugar_nombre',    true);
        $ccaa         = get_post_meta($eid, '_gpe_ccaa_evento',     true);
        $usar_nativa  = get_post_meta($eid, '_gpe_usar_inscripcion_nativa', true);
        $url_ext      = get_post_meta($eid, '_med_url_inscripcion', true);
        $sold_out     = gpe_evento_sold_out($eid);
        $libres       = gpe_plazas_libres($eid);
        $aforo        = gpe_aforo_maximo($eid);

        $timestamp    = $fecha_raw ? strtotime($fecha_raw) : 0;
        $meses_es     = array('01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC');
        $dia          = $timestamp ? date('d', $timestamp) : '—';
        $mes          = $timestamp ? ($meses_es[date('m',$timestamp)] ?? '') : '';
        $dia_semana   = $timestamp ? date_i18n('l', $timestamp) : '';

        $tematicas       = wp_get_post_terms($eid, 'tematica_evento');
        $nombre_tematica = !empty($tematicas) && !is_wp_error($tematicas) ? $tematicas[0]->name : 'General';

        $url_boton = get_permalink();
        if ( ! $usar_nativa && $url_ext ) $url_boton = $url_ext;
    ?>
        <article class="gpe-agenda-item <?php echo $sold_out ? 'gpe-agd-sold' : ''; ?>">

            <div class="gpe-agd-fecha">
                <span class="gpe-agd-dia"><?php echo $dia; ?></span>
                <span class="gpe-agd-mes"><?php echo $mes; ?></span>
            </div>

            <div class="gpe-agd-cuerpo">
                <span class="gpe-agd-categoria"><?php echo esc_html($nombre_tematica); ?></span>
                <h3 class="gpe-agd-titulo"><a href="<?php echo esc_url(get_permalink()); ?>"><?php the_title(); ?></a></h3>

                <div class="gpe-agd-meta">
                    <?php if ( $hora ) : ?>
                        <span>🕐 <?php echo esc_html($hora); ?>h</span>
                    <?php endif; ?>
                    <?php if ( $lugar ) : ?>
                        <span>📍 <?php echo esc_html($lugar); ?></span>
                    <?php elseif ( $provincia ) : ?>
                        <span>📍 <?php echo esc_html($provincia); ?></span>
                    <?php endif; ?>
                    <?php if ( $ccaa ) : ?>
                        <span>🗺️ <?php echo esc_html($ccaa); ?></span>
                    <?php endif; ?>
                </div>

                <?php if ( $aforo > 0 ) : ?>
                    <div class="gpe-agd-aforo">
                        <div class="gpe-agd-aforo-barra">
                            <?php $pct = min(100, round((gpe_inscritos_count($eid)/$aforo)*100)); ?>
                            <div style="width:<?php echo $pct; ?>%;"></div>
                        </div>
                        <span><?php echo $sold_out ? '🔴 Aforo completo' : $libres . ' plazas libres'; ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="gpe-agd-accion">
                <?php if ( $sold_out ) : ?>
                    <span class="gpe-agd-btn-sold">SOLD OUT</span>
                <?php else : ?>
                    <a href="<?php echo esc_url($url_boton); ?>" class="gpe-agd-btn">Inscríbete</a>
                <?php endif; ?>
            </div>

        </article>
    <?php endwhile; wp_reset_postdata(); ?>
    </div>

</div>

<style>
.gpe-agenda-general-wrapper { font-family:'Inter',sans-serif; max-width:900px; margin:0 auto; }
.gpe-agenda-grid { display:flex; flex-direction:column; gap:16px; }
.gpe-agenda-item { display:flex; align-items:center; gap:20px; background:#fff; border:2px solid rgba(0,122,135,0.08); border-radius:16px; padding:20px 24px; box-shadow:0 4px 15px rgba(0,0,0,0.04); transition:all 0.25s ease; }
.gpe-agenda-item:hover { border-color:#00b4cc; box-shadow:0 8px 25px rgba(0,122,135,0.12); transform:translateY(-2px); }
.gpe-agd-sold { opacity:0.65; }
.gpe-agd-fecha { text-align:center; min-width:60px; background:linear-gradient(135deg,#007a87,#00b4cc); border-radius:12px; padding:10px 14px; color:#fff; flex-shrink:0; }
.gpe-agd-dia { display:block; font-size:2rem; font-weight:900; line-height:1; }
.gpe-agd-mes { display:block; font-size:11px; font-weight:700; letter-spacing:1.5px; opacity:0.9; margin-top:2px; }
.gpe-agd-cuerpo { flex:1; }
.gpe-agd-categoria { display:inline-block; background:#e0f2f5; color:#007a87; padding:3px 12px; border-radius:20px; font-size:12px; font-weight:700; margin-bottom:6px; }
.gpe-agd-titulo { margin:0 0 8px 0; font-size:1.1rem; font-weight:800; color:#1a1a1a; }
.gpe-agd-titulo a { color:#1a1a1a; text-decoration:none; }
.gpe-agd-titulo a:hover { color:#007a87; }
.gpe-agd-meta { display:flex; flex-wrap:wrap; gap:12px; font-size:13px; color:#666; }
.gpe-agd-aforo { margin-top:8px; display:flex; align-items:center; gap:10px; font-size:12px; color:#666; }
.gpe-agd-aforo-barra { flex:1; height:5px; background:#eee; border-radius:10px; overflow:hidden; max-width:120px; }
.gpe-agd-aforo-barra div { height:100%; background:linear-gradient(90deg,#007a87,#00b4cc); border-radius:10px; }
.gpe-agd-accion { flex-shrink:0; }
.gpe-agd-btn { display:inline-block; background:linear-gradient(135deg,#007a87,#00b4cc); color:#fff !important; padding:10px 22px; border-radius:25px; font-weight:700; font-size:14px; text-decoration:none; transition:all 0.2s; white-space:nowrap; }
.gpe-agd-btn:hover { opacity:0.88; transform:translateY(-1px); }
.gpe-agd-btn-sold { display:inline-block; background:#e0e0e0; color:#888; padding:10px 20px; border-radius:25px; font-weight:700; font-size:13px; letter-spacing:1px; }
@media(max-width:600px) { .gpe-agenda-item { flex-direction:column; align-items:flex-start; } .gpe-agd-accion { width:100%; } .gpe-agd-btn, .gpe-agd-btn-sold { display:block; text-align:center; width:100%; box-sizing:border-box; } }
</style>
