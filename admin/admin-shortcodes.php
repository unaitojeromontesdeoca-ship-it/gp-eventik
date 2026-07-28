<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gpe_render_shortcodes_ref() {
    $shortcodes = array(
        array(
            'titulo' => 'Agenda de eventos públicos',
            'sc'     => '[gp_agenda_eventos]',
            'desc'   => 'Muestra las tarjetas de eventos públicos próximos en formato carrusel (móvil) o grid (escritorio).',
            'params' => array(
                'ccaa="Galicia"'       => 'Filtra por Comunidad Autónoma',
                'provincia="Lugo"'     => 'Filtra por provincia',
                'limite="8"'           => 'Número máximo de eventos (por defecto 12)',
            ),
            'ejemplo' => '[gp_agenda_eventos ccaa="Galicia" limite="6"]',
        ),
        array(
            'titulo' => 'Agenda general completa',
            'sc'     => '[gp_agenda_general]',
            'desc'   => 'Agenda completa con vista grid o lista. Útil para páginas de agenda global.',
            'params' => array(
                'ccaa="Madrid"'           => 'Filtra por CCAA',
                'mostrar_pasados="si"'    => 'Incluye eventos pasados (por defecto "no")',
                'vista="lista"'           => 'Vista alternativa: grid (defecto) | lista',
            ),
            'ejemplo' => '[gp_agenda_general mostrar_pasados="no"]',
        ),
        array(
            'titulo' => 'Portal coordinador — Castellano',
            'sc'     => '[gpe_portal_es]',
            'desc'   => 'Portal de coordinación territorial completo en castellano. Requiere login y rol de coordinador.',
            'params' => array(),
            'ejemplo' => '[gpe_portal_es]',
        ),
        array(
            'titulo' => 'Portal coordinador — Català',
            'sc'     => '[gpe_portal_ca]',
            'desc'   => 'Portal de coordinació territorial complet en català.',
            'params' => array(),
            'ejemplo' => '[gpe_portal_ca]',
        ),
        array(
            'titulo' => 'Portal coordinador — Galego',
            'sc'     => '[gpe_portal_gl]',
            'desc'   => 'Portal de coordinación territorial completo en galego.',
            'params' => array(),
            'ejemplo' => '[gpe_portal_gl]',
        ),
        array(
            'titulo' => 'Portal coordinador — Euskara',
            'sc'     => '[gpe_portal_eu]',
            'desc'   => 'Koordinazio-portal osoa euskaraz.',
            'params' => array(),
            'ejemplo' => '[gpe_portal_eu]',
        ),
        array(
            'titulo' => 'Formulario inscripción evento interno',
            'sc'     => '[gpe_inscripcion_interna]',
            'desc'   => 'Formulario de inscripción para eventos internos (requiere login y pertenecer al órgano invitado). Incluye delegación de invitación.',
            'params' => array(
                'evento_id="123"' => 'ID del evento interno (opcional si está en la página del evento)',
            ),
            'ejemplo' => '[gpe_inscripcion_interna evento_id="123"]',
        ),
        array(
            'titulo' => 'Formulario inscripción pública',
            'sc'     => '[gpe_formulario_inscripcion]',
            'desc'   => 'Formulario de inscripción a evento público (se inserta automáticamente en la landing del evento, pero puede usarse en cualquier página).',
            'params' => array(
                'evento_id="123"' => 'ID del evento público',
            ),
            'ejemplo' => '[gpe_formulario_inscripcion evento_id="123"]',
        ),
    );
    ?>
    <style>
    .gpe-sc-page{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;}
    .gpe-sc-header{background:linear-gradient(135deg,#007a87,#00b4cc);border-radius:12px;padding:22px 28px;color:#fff;margin-bottom:24px;}
    .gpe-sc-header h1{margin:0;font-size:1.4rem;font-weight:800;color:#fff!important;}
    .gpe-sc-card{background:#fff;border:1px solid #e8e8e8;border-radius:10px;padding:20px 22px;margin-bottom:16px;box-shadow:0 1px 4px rgba(0,0,0,0.04);}
    .gpe-sc-card h3{margin:0 0 8px;font-size:1rem;font-weight:800;color:#1a1a1a;}
    .gpe-sc-code{display:inline-block;background:#f0fafb;border:1px solid #cce8ea;border-radius:6px;padding:5px 12px;font-family:monospace;font-size:14px;font-weight:700;color:#007a87;cursor:pointer;user-select:all;margin-bottom:10px;}
    .gpe-sc-code:hover{background:#007a87;color:#fff;border-color:#007a87;}
    .gpe-sc-desc{font-size:13px;color:#555;line-height:1.6;margin-bottom:10px;}
    .gpe-sc-params{background:#f9f9f9;border-radius:6px;padding:10px 14px;font-size:12px;}
    .gpe-sc-params dt{font-weight:700;color:#007a87;font-family:monospace;}
    .gpe-sc-params dd{color:#666;margin:0 0 6px 12px;}
    .gpe-sc-ejemplo{margin-top:10px;background:#1a1a1a;color:#00b4cc;border-radius:6px;padding:8px 14px;font-family:monospace;font-size:12px;word-break:break-all;}
    </style>
    <div class="wrap gpe-sc-page">
        <div class="gpe-sc-header">
            <h1>📋 Referencia de Shortcodes</h1>
            <p style="margin:4px 0 0;opacity:.85;font-size:13px;">Haz clic en cualquier shortcode para copiarlo al portapapeles.</p>
        </div>
        <p style="font-size:13px;color:#888;margin-bottom:20px;">Puedes usar estos shortcodes en cualquier página o entrada de WordPress desde el editor de bloques o el editor clásico.</p>

        <?php foreach ($shortcodes as $s) : ?>
        <div class="gpe-sc-card">
            <h3><?php echo esc_html($s['titulo']); ?></h3>
            <div class="gpe-sc-code" onclick="gpeScCopiar(this)" title="Clic para copiar"><?php echo esc_html($s['sc']); ?></div>
            <div class="gpe-sc-desc"><?php echo esc_html($s['desc']); ?></div>
            <?php if (!empty($s['params'])) : ?>
            <div class="gpe-sc-params">
                <strong style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#888;display:block;margin-bottom:6px;">Parámetros</strong>
                <dl style="margin:0;">
                    <?php foreach ($s['params'] as $param => $pdesc) : ?>
                    <dt><?php echo esc_html($param); ?></dt>
                    <dd><?php echo esc_html($pdesc); ?></dd>
                    <?php endforeach; ?>
                </dl>
            </div>
            <?php endif; ?>
            <?php if ($s['ejemplo'] !== $s['sc']) : ?>
            <div class="gpe-sc-ejemplo"><?php echo esc_html($s['ejemplo']); ?></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <script>
    function gpeScCopiar(el) {
        var txt = el.textContent.trim();
        if (navigator.clipboard) {
            navigator.clipboard.writeText(txt).then(function(){
                var orig = el.textContent;
                el.textContent = '✅ Copiado!';
                setTimeout(function(){ el.textContent = orig; }, 1500);
            });
        }
    }
    </script>
    <?php
}
