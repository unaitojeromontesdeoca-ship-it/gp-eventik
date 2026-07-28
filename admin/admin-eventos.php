<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Tabla de provincias con su código para la tarjeta
function gpe_provincias_con_codigo() {
    return array(
        // Andalucía
        'Almería'       => 'ALM', 'Cádiz'        => 'CAD', 'Córdoba'     => 'COR',
        'Granada'       => 'GRA', 'Huelva'        => 'HUE', 'Jaén'        => 'JAE',
        'Málaga'        => 'MAL', 'Sevilla'       => 'SEV',
        // Aragón
        'Huesca'        => 'HUE', 'Teruel'        => 'TER', 'Zaragoza'    => 'ZGZ',
        // Asturias
        'Asturias'      => 'AST',
        // Baleares
        'Mallorca'      => 'MAL', 'Menorca'       => 'MEN', 'Ibiza'       => 'IBZ', 'Formentera' => 'FMT',
        // Canarias
        'Gran Canaria'  => 'LPA', 'Tenerife'      => 'TFE', 'Lanzarote'   => 'ACE',
        'Fuerteventura' => 'FUE', 'La Palma'      => 'SPC', 'La Gomera'   => 'GMZ', 'El Hierro' => 'VDE',
        // Cantabria
        'Cantabria'     => 'STD',
        // Castilla-La Mancha
        'Albacete'      => 'ABZ', 'Ciudad Real'   => 'CRE', 'Cuenca'      => 'CUE',
        'Guadalajara'   => 'GDL', 'Toledo'        => 'TOL',
        // Castilla y León
        'Ávila'         => 'AVI', 'Burgos'        => 'BUR', 'León'        => 'LEO',
        'Palencia'      => 'PAL', 'Salamanca'     => 'SAL', 'Segovia'     => 'SEG',
        'Soria'         => 'SOR', 'Valladolid'    => 'VLL', 'Zamora'      => 'ZAM',
        // Cataluña
        'Barcelona'     => 'BCN', 'Girona'        => 'GRO', 'Lleida'      => 'LLE', 'Tarragona' => 'TGN',
        // Extremadura
        'Badajoz'       => 'BJZ', 'Cáceres'       => 'CCS',
        // Galicia
        'A Coruña'      => 'LCG', 'Lugo'          => 'LGO', 'Ourense'     => 'OUR', 'Pontevedra' => 'PGZ',
        // La Rioja
        'La Rioja'      => 'RJO',
        // Madrid
        'Madrid'        => 'MAD',
        // Murcia
        'Murcia'        => 'MJV',
        // Navarra
        'Navarra'       => 'PNA',
        // País Vasco
        'Álava'         => 'VIT', 'Gipuzkoa'      => 'EAS', 'Bizkaia'     => 'BIO',
        // Valencia
        'Alicante'      => 'ALC', 'Castellón'     => 'CAS', 'Valencia'    => 'VLC',
        // Ciudades autónomas
        'Ceuta'         => 'CEU', 'Melilla'        => 'MLN',
    );
}

// Registrar metaboxes
add_action( 'add_meta_boxes', 'gpe_registrar_metaboxes_evento' );
function gpe_registrar_metaboxes_evento() {
    add_meta_box( 'gpe_meta_portada',     'Tarjeta de Agenda',        'gpe_render_meta_portada',    'evento_home', 'normal', 'high' );
    add_meta_box( 'gpe_meta_territorio',  'Territorio',               'gpe_render_meta_territorio', 'evento_home', 'side',   'high' );
    add_meta_box( 'gpe_meta_landing',     'Contenido de la Landing',  'gpe_render_meta_landing',    'evento_home', 'normal', 'high' );
    add_meta_box( 'gpe_meta_inscripcion', 'Control de Inscripciones', 'gpe_render_meta_inscripcion','evento_home', 'side',   'default' );
}

// Box 1: Tarjeta de Agenda
function gpe_render_meta_portada( $post ) {
    wp_nonce_field( 'gpe_guardar_evento_action', 'gpe_evento_nonce' );
    $fecha         = get_post_meta( $post->ID, '_med_fecha_evento',    true );
    $hora          = get_post_meta( $post->ID, '_med_hora_evento',     true );
    $provincia     = get_post_meta( $post->ID, '_med_provincia_sitio', true ); // nombre completo
    $codigo_prov   = get_post_meta( $post->ID, '_gpe_codigo_provincia', true ); // código 3 letras
    $url_ins       = get_post_meta( $post->ID, '_med_url_inscripcion', true );
    $direccion     = get_post_meta( $post->ID, '_gpe_direccion',       true );
    $lugar_nombre  = get_post_meta( $post->ID, '_gpe_lugar_nombre',    true );
    $email_coord   = get_post_meta( $post->ID, '_gpe_email_coordinador', true );
    $tematicas_asig = wp_get_post_terms( $post->ID, 'tematica_evento', array('fields'=>'ids') );
    $tematica_actual = !empty($tematicas_asig) ? $tematicas_asig[0] : 0;
$provincias    = gpe_provincias_con_codigo();
    ?>
    <div class="gpe-wrap">
    <div class="gpe-form-grid">
        <div class="gpe-field">
            <label class="gpe-label" for="med_fecha_evento">Fecha del evento *</label>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <input type="date" id="med_fecha_evento" name="med_fecha_evento" value="<?php echo esc_attr($fecha); ?>" class="gpe-input" style="max-width:190px;">
                <?php
                $meses = array('01'=>'ENE','02'=>'FEB','03'=>'MAR','04'=>'ABR','05'=>'MAY','06'=>'JUN','07'=>'JUL','08'=>'AGO','09'=>'SEP','10'=>'OCT','11'=>'NOV','12'=>'DIC');
                $preview = ($fecha && $codigo_prov) ? date('d', strtotime($fecha)) . ' ' . $meses[date('m',strtotime($fecha))] . ' · ' . $codigo_prov : '';
                ?>
                <span id="gpe-preview-badge" class="gpe-badge" style="font-family:monospace;font-size:13px;padding:6px 12px;<?php echo $preview?'':'display:none;'; ?>"><?php echo esc_html($preview); ?></span>
            </div>
            <p class="gpe-field-hint">Formato en tarjeta: <strong>16 JUL · BCN</strong></p>
        </div>

        <div class="gpe-field">
            <label class="gpe-label" for="med_hora_evento">Hora de inicio</label>
            <input type="time" id="med_hora_evento" name="med_hora_evento" value="<?php echo esc_attr($hora); ?>" class="gpe-input" style="max-width:190px;">
        </div>

        <div class="gpe-field">
            <label class="gpe-label" for="gpe_provincia_select">Provincia / Isla</label>
            <select id="gpe_provincia_select" name="med_provincia_sitio" class="gpe-input" onchange="gpe_set_codigo(this)">
                <option value="">— Selecciona —</option>
                <?php foreach ( $provincias as $nombre => $codigo ) : ?>
                    <option value="<?php echo esc_attr($nombre); ?>" data-codigo="<?php echo esc_attr($codigo); ?>" <?php selected($provincia, $nombre); ?>>
                        <?php echo esc_html($nombre . ' (' . $codigo . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" id="gpe_codigo_provincia" name="gpe_codigo_provincia" value="<?php echo esc_attr($codigo_prov); ?>">
        </div>

        <div class="gpe-field">
            <label class="gpe-label" for="med_tematica_id">Temática</label>
            <select id="med_tematica_id" name="med_tematica_id" class="gpe-input">
                <option value="">— Selecciona —</option>
                <?php foreach ( get_terms(array('taxonomy'=>'tematica_evento','hide_empty'=>false)) as $t ) : ?>
                    <option value="<?php echo $t->term_id; ?>" <?php selected($tematica_actual, $t->term_id); ?>><?php echo esc_html($t->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="gpe-field" style="grid-column:1 / -1;">
            <label class="gpe-label" for="gpe_lugar_nombre">Nombre del espacio</label>
            <input type="text" id="gpe_lugar_nombre" name="gpe_lugar_nombre" value="<?php echo esc_attr($lugar_nombre); ?>" class="gpe-input" placeholder="Ej: CaixaForum Barcelona">
        </div>

        <div class="gpe-field" style="grid-column:1 / -1;">
            <label class="gpe-label" for="gpe_direccion">Dirección completa</label>
            <input type="text" id="gpe_direccion" name="gpe_direccion" value="<?php echo esc_attr($direccion); ?>" class="gpe-input" placeholder="Calle, número, ciudad, CP">
        </div>

        <div class="gpe-field" style="grid-column:1 / -1;">
            <label class="gpe-label" for="gpe_email_coordinador">Email del coordinador</label>
            <input type="email" id="gpe_email_coordinador" name="gpe_email_coordinador" value="<?php echo esc_attr($email_coord); ?>" class="gpe-input" placeholder="coordinador@ejemplo.com">
            <p class="gpe-field-hint">Recibe una notificación por cada inscripción nueva.</p>
        </div>
    </div>
    </div>

    <script>
    var gpe_meses = {1:'ENE',2:'FEB',3:'MAR',4:'ABR',5:'MAY',6:'JUN',7:'JUL',8:'AGO',9:'SEP',10:'OCT',11:'NOV',12:'DIC'};

    function gpe_set_codigo(sel) {
        var codigo = sel.options[sel.selectedIndex].getAttribute('data-codigo') || '';
        document.getElementById('gpe_codigo_provincia').value = codigo;
        gpe_actualizar_preview();
    }

    function gpe_actualizar_preview() {
        var fecha  = document.getElementById('med_fecha_evento').value;
        var codigo = document.getElementById('gpe_codigo_provincia').value;
        var badge  = document.getElementById('gpe-preview-badge');
        if (fecha && codigo) {
            var d = new Date(fecha + 'T12:00:00');
            var dia = String(d.getDate()).padStart(2,'0');
            var mes = gpe_meses[d.getMonth() + 1];
            badge.textContent = dia + ' ' + mes + ' · ' + codigo;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }

    document.getElementById('med_fecha_evento').addEventListener('change', gpe_actualizar_preview);
    </script>
    <?php
}

// Box 2: Territorio
function gpe_render_meta_territorio( $post ) {
    $ccaa_ev  = get_post_meta( $post->ID, '_gpe_ccaa_evento',      true );
    $prov_ev  = get_post_meta( $post->ID, '_gpe_provincia_evento', true );
    $territorios = gpe_territorios_espana();
    ?>
    <div class="gpe-wrap">
        <div class="gpe-field">
            <label class="gpe-label" for="gpe_ccaa_evento">Comunidad Autónoma</label>
            <select id="gpe_ccaa_evento" name="gpe_ccaa_evento" class="gpe-input" onchange="gpe_admin_provincias(this.value)">
                <option value="">— Selecciona —</option>
                <?php foreach ( $territorios as $cc => $provs ) : ?>
                    <option value="<?php echo esc_attr($cc); ?>" <?php selected($ccaa_ev, $cc); ?>><?php echo esc_html($cc); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="gpe-field">
            <label class="gpe-label" for="gpe_prov_evento">Provincia</label>
            <select id="gpe_prov_evento" name="gpe_provincia_evento" class="gpe-input">
                <option value="">Toda la comunidad</option>
                <?php if ( $ccaa_ev && isset($territorios[$ccaa_ev]) ) :
                    foreach ( $territorios[$ccaa_ev] as $p ) : ?>
                        <option value="<?php echo esc_attr($p); ?>" <?php selected($prov_ev, $p); ?>><?php echo esc_html($p); ?></option>
                    <?php endforeach;
                endif; ?>
            </select>
        </div>
    </div>
    <script>
    var gpe_t = <?php echo json_encode($territorios); ?>;
    function gpe_admin_provincias(ccaa) {
        var s = document.getElementById('gpe_prov_evento');
        s.innerHTML = '<option value="">Toda la comunidad</option>';
        if (ccaa && gpe_t[ccaa]) gpe_t[ccaa].forEach(function(p){ s.innerHTML += '<option value="'+p+'">'+p+'</option>'; });
    }
    </script>
    <?php
}

// Box 3: Contenido de la Landing
function gpe_render_meta_landing( $post ) {
    $claim      = get_post_meta( $post->ID, '_gpe_claim',            true );
    $foto_hero  = get_post_meta( $post->ID, '_gpe_foto_hero',        true ); // ID adjunto WP
    $por_que    = get_post_meta( $post->ID, '_gpe_por_que',          true );
    $programa   = get_post_meta( $post->ID, '_gpe_programa_detalle', true );
    $agenda     = get_post_meta( $post->ID, '_gpe_agenda_timeline',  true );
    $preguntas  = get_post_meta( $post->ID, '_gpe_preguntas_debate', true );
    $faqs       = get_post_meta( $post->ID, '_gpe_faqs_acordeon',    true );
    $mapa_embed = get_post_meta( $post->ID, '_gpe_mapa_embed',       true );
    $transport  = get_post_meta( $post->ID, '_gpe_transporte',       true );

    $ponentes_seleccionados = get_post_meta( $post->ID, '_gpe_ponentes_ids', true ) ?: array();
    $todos_ponentes = get_posts(array('post_type'=>'gpe_contacto','posts_per_page'=>-1,'orderby'=>'title','order'=>'ASC'));

    // Para mostrar miniatura si ya hay foto
    $foto_hero_url = $foto_hero ? wp_get_attachment_image_url( $foto_hero, 'thumbnail' ) : '';
    ?>
    <div class="gpe-wrap">

    <h3 class="gpe-landing-sec">1. Hero principal</h3>
    <div class="gpe-field">
        <label class="gpe-label" for="gpe_claim">Claim / subtítulo</label>
        <input type="text" id="gpe_claim" name="gpe_claim" value="<?php echo esc_attr($claim); ?>" class="gpe-input" placeholder="Ideas que incomodan.">
        <p class="gpe-field-hint">Aparece bajo el título en la cabecera de la landing.</p>
    </div>
    <div class="gpe-field">
        <label class="gpe-label">Imagen de cabecera</label>
        <div id="gpe-hero-preview" style="margin-bottom:8px;">
            <?php if ($foto_hero_url) : ?>
                <img src="<?php echo esc_url($foto_hero_url); ?>" style="max-width:200px; height:auto; border-radius:8px; border:1px solid #ddd;">
            <?php endif; ?>
        </div>
        <input type="hidden" id="gpe_foto_hero" name="gpe_foto_hero" value="<?php echo esc_attr($foto_hero); ?>">
        <div style="display:flex;gap:8px;">
            <button type="button" class="gpe-btn" id="gpe-btn-hero">Seleccionar imagen</button>
            <?php if ($foto_hero) : ?>
                <button type="button" class="gpe-btn gpe-btn-danger" id="gpe-btn-hero-remove">Eliminar imagen</button>
            <?php endif; ?>
        </div>
        <p class="gpe-field-hint">Se mostrará como fondo del hero de la landing.</p>
    </div>

    <h3 class="gpe-landing-sec">2. Por qué asistir</h3>
    <?php wp_editor( $por_que, 'gpe_por_que', array('textarea_name'=>'gpe_por_que','media_buttons'=>false,'textarea_rows'=>4,'teeny'=>true) ); ?>

    <h3 class="gpe-landing-sec">3. Ponentes del evento</h3>
    <input type="text" id="gpe-buscar-ponente" placeholder="Buscar ponente por nombre o cargo…" class="gpe-search" style="margin-bottom:8px;width:100%;box-sizing:border-box;" autocomplete="off">
    <div id="gpe-listado-check-ponentes" style="max-height:220px; overflow-y:auto; border:1px solid var(--gpe-border); padding:12px; background:#fff; border-radius:8px; margin-bottom:10px;">
        <?php if ( !empty($todos_ponentes) ) :
            foreach ( $todos_ponentes as $p ) :
                $cargo = get_post_meta($p->ID, '_gpe_basico_cargo', true); ?>
                <label class="gpe-pon-row" style="display:block; margin-bottom:6px; cursor:pointer;" data-nombre="<?php echo esc_attr(strtolower($p->post_title)); ?>" data-cargo="<?php echo esc_attr(strtolower($cargo)); ?>">
                    <input type="checkbox" name="gpe_ponentes_ids[]" value="<?php echo $p->ID; ?>" <?php checked( in_array($p->ID, $ponentes_seleccionados) ); ?>>
                    <strong><?php echo esc_html($p->post_title); ?></strong>
                    <?php if ($cargo) echo ' &mdash; <span style="color:#666;">' . esc_html($cargo) . '</span>'; ?>
                </label>
            <?php endforeach;
        else : ?>
            <p id="gpe-no-pon" style="color:#999; margin:0;">Aún no hay ponentes creados.</p>
        <?php endif; ?>
    </div>
    <button type="button" class="gpe-btn gpe-btn-primary" onclick="document.getElementById('gpe-modal-ponente').style.display='flex'">+ Crear ponente al instante</button>

    <?php /* Programa detallado eliminado: el timeline lo sustituye */ ?>

    <h3 class="gpe-landing-sec">5. Timeline de la agenda</h3>
    <p class="gpe-field-hint" style="margin-bottom:10px;">Añade los bloques horarios del evento. Se mostrarán como un timeline vertical en la landing.</p>
    <?php
    $timeline_bloques = get_post_meta( $post->ID, '_gpe_timeline_bloques', true ) ?: array();
    if ( empty($timeline_bloques) ) $timeline_bloques = array( array('hora'=>'','titulo'=>'','desc'=>'') );
    ?>
    <div id="gpe-timeline-wrap">
        <?php foreach ( $timeline_bloques as $i => $bloque ) : ?>
        <div class="gpe-tl-bloque" style="display:flex; gap:8px; align-items:flex-start; margin-bottom:10px; background:#f9f9f9; padding:10px; border-radius:4px; border:1px solid #e0e0e0;">
            <div style="flex-shrink:0;">
                <label style="font-size:11px; font-weight:600; display:block; margin-bottom:3px; color:#555;">Hora</label>
                <input type="text" name="gpe_timeline[<?php echo $i; ?>][hora]" value="<?php echo esc_attr($bloque['hora']??''); ?>" placeholder="10:00" style="width:72px; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px;">
            </div>
            <div style="flex:1;">
                <label style="font-size:11px; font-weight:600; display:block; margin-bottom:3px; color:#555;">Título del bloque</label>
                <input type="text" name="gpe_timeline[<?php echo $i; ?>][titulo]" value="<?php echo esc_attr($bloque['titulo']??''); ?>" placeholder="Apertura institucional" style="width:100%; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px; margin-bottom:5px;">
                <label style="font-size:11px; font-weight:600; display:block; margin-bottom:3px; color:#555;">Descripción (opcional)</label>
                <input type="text" name="gpe_timeline[<?php echo $i; ?>][desc]" value="<?php echo esc_attr($bloque['desc']??''); ?>" placeholder="Bienvenida a cargo de la presidencia" style="width:100%; padding:6px 8px; border:1px solid #8c8f94; border-radius:3px;">
            </div>
            <div style="flex-shrink:0; padding-top:20px;">
                <button type="button" class="button gpe-tl-remove" title="Eliminar bloque">✕</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="gpe-btn" id="gpe-tl-add">+ Añadir bloque horario</button>

    <h3 class="gpe-landing-sec">6. Preguntas del debate</h3>
    <div class="gpe-field">
        <label class="gpe-label" for="gpe_preguntas_debate">Una pregunta por línea</label>
        <textarea id="gpe_preguntas_debate" name="gpe_preguntas_debate" rows="4" class="gpe-textarea" placeholder="¿Está la juventud comprometida políticamente?"><?php echo esc_textarea($preguntas); ?></textarea>
    </div>

    <h3 class="gpe-landing-sec">7. FAQs</h3>
    <?php wp_editor( $faqs, 'gpe_faqs_acordeon', array('textarea_name'=>'gpe_faqs_acordeon','media_buttons'=>false,'textarea_rows'=>4,'teeny'=>true) ); ?>

    <h3 class="gpe-landing-sec">8. Localización y cómo llegar</h3>
    <div class="gpe-field">
        <label class="gpe-label" for="gpe_mapa_embed">Embed de Google Maps</label>
        <textarea id="gpe_mapa_embed" name="gpe_mapa_embed" rows="3" class="gpe-textarea" placeholder='<iframe src="https://maps.google.com/..." width="100%" height="300" frameborder="0"></iframe>'><?php echo esc_textarea($mapa_embed); ?></textarea>
        <p class="gpe-field-hint">En Google Maps: Compartir → Insertar mapa → copia el &lt;iframe&gt;</p>
    </div>
    <div class="gpe-field">
        <label class="gpe-label" for="gpe_transporte">Cómo llegar / Transporte</label>
        <?php wp_editor( $transport, 'gpe_transporte', array('textarea_name'=>'gpe_transporte','media_buttons'=>false,'textarea_rows'=>3,'teeny'=>true) ); ?>
    </div>
    </div><!-- /gpe-wrap -->

    <!-- Modal crear ponente rápido -->
    <div id="gpe-modal-ponente" style="display:none; position:fixed; z-index:99999; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;">
        <div class="gpe-wrap" style="background:#fff; padding:25px; border-radius:12px; width:100%; max-width:460px; box-shadow:0 5px 30px rgba(0,0,0,0.3);">
            <h3 style="margin-top:0;">Crear ponente al instante</h3>
            <div class="gpe-field"><label class="gpe-label">Nombre *</label><input type="text" id="gpe_m_nombre" class="gpe-input"></div>
            <div class="gpe-field"><label class="gpe-label">Apellidos</label><input type="text" id="gpe_m_apellidos" class="gpe-input"></div>
            <div class="gpe-field"><label class="gpe-label">Cargo</label><input type="text" id="gpe_m_cargo" class="gpe-input"></div>
            <div class="gpe-field"><label class="gpe-label">Frase destacada</label><input type="text" id="gpe_m_frase" class="gpe-input"></div>
            <div class="gpe-field"><label class="gpe-label">Tesis</label><input type="text" id="gpe_m_tema" class="gpe-input"></div>
            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:15px;">
                <button type="button" class="gpe-btn" onclick="document.getElementById('gpe-modal-ponente').style.display='none'">Cancelar</button>
                <button type="button" class="gpe-btn gpe-btn-primary" id="gpe-btn-guardar-ajax">Guardar ponente</button>
            </div>
        </div>
    </div>

    <script>
    // Buscador de ponentes
    jQuery(document).ready(function($){
        $('#gpe-buscar-ponente').on('input', function(){
            var q = $(this).val().toLowerCase();
            $('.gpe-pon-row').each(function(){
                var nombre = $(this).data('nombre') || '';
                var cargo  = $(this).data('cargo')  || '';
                $(this).toggle( !q || nombre.includes(q) || cargo.includes(q) );
            });
        });
    });

    // Timeline repeater
    jQuery(document).ready(function($){
        var tlIdx = <?php echo count($timeline_bloques); ?>;
        $('#gpe-tl-add').click(function(){
            var html = '<div class="gpe-tl-bloque" style="display:flex;gap:8px;align-items:flex-start;margin-bottom:10px;background:#f9f9f9;padding:10px;border-radius:4px;border:1px solid #e0e0e0;">' +
                '<div style="flex-shrink:0;"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;color:#555;">Hora</label>' +
                '<input type="text" name="gpe_timeline['+tlIdx+'][hora]" placeholder="10:00" style="width:72px;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;"></div>' +
                '<div style="flex:1;"><label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;color:#555;">Título del bloque</label>' +
                '<input type="text" name="gpe_timeline['+tlIdx+'][titulo]" placeholder="Apertura institucional" style="width:100%;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;margin-bottom:5px;">' +
                '<label style="font-size:11px;font-weight:600;display:block;margin-bottom:3px;color:#555;">Descripción (opcional)</label>' +
                '<input type="text" name="gpe_timeline['+tlIdx+'][desc]" placeholder="Bienvenida a cargo de la presidencia" style="width:100%;padding:6px 8px;border:1px solid #8c8f94;border-radius:3px;"></div>' +
                '<div style="flex-shrink:0;padding-top:20px;"><button type="button" class="button gpe-tl-remove" title="Eliminar bloque">✕</button></div></div>';
            $('#gpe-timeline-wrap').append(html);
            tlIdx++;
        });
        $(document).on('click', '.gpe-tl-remove', function(){ $(this).closest('.gpe-tl-bloque').remove(); });
    });

    // Media uploader para imagen de hero
    jQuery(document).ready(function($){
        var uploader;
        $('#gpe-btn-hero').click(function(e){
            e.preventDefault();
            if (uploader) { uploader.open(); return; }
            uploader = wp.media({
                title: 'Seleccionar imagen de cabecera',
                button: { text: 'Usar esta imagen' },
                multiple: false,
                library: { type: 'image' }
            });
            uploader.on('select', function(){
                var att = uploader.state().get('selection').first().toJSON();
                $('#gpe_foto_hero').val(att.id);
                $('#gpe-hero-preview').html('<img src="'+att.url+'" style="max-width:200px; height:auto; border-radius:4px; border:1px solid #ddd;">');
            });
            uploader.open();
        });
        $(document).on('click', '#gpe-btn-hero-remove', function(){
            $('#gpe_foto_hero').val('');
            $('#gpe-hero-preview').html('');
        });

        // Crear ponente AJAX
        $('#gpe-btn-guardar-ajax').click(function(){
            var nom = $('#gpe_m_nombre').val();
            if (!nom) { alert('El nombre es obligatorio'); return; }
            $(this).prop('disabled', true).text('Guardando…');
            $.ajax({
                url: gpe_ajax.url, type:'POST',
                data: { action:'gpe_crear_ponente_rapido', nonce: gpe_ajax.nonce,
                        nombre: nom, apellidos: $('#gpe_m_apellidos').val(),
                        cargo: $('#gpe_m_cargo').val(), frase: $('#gpe_m_frase').val(),
                        tema: $('#gpe_m_tema').val() },
                success: function(r){
                    if (r.success) {
                        $('#gpe-no-pon').remove();
                        $('#gpe-listado-check-ponentes').append(
                            '<label style="display:block;margin-bottom:6px;cursor:pointer;">' +
                            '<input type="checkbox" name="gpe_ponentes_ids[]" value="'+r.data.id+'" checked>' +
                            ' <strong>'+r.data.nombre+'</strong>' +
                            (r.data.cargo ? ' &mdash; <span style="color:#666;">'+r.data.cargo+'</span>' : '') +
                            '</label>'
                        );
                        document.getElementById('gpe-modal-ponente').style.display = 'none';
                        $('#gpe_m_nombre,#gpe_m_apellidos,#gpe_m_cargo,#gpe_m_frase,#gpe_m_tema').val('');
                    }
                    $('#gpe-btn-guardar-ajax').prop('disabled', false).text('Guardar ponente');
                }
            });
        });
    });
    </script>
    <?php
}

// Box 4: Control de Inscripciones
function gpe_render_meta_inscripcion( $post ) {
    $aforo       = get_post_meta( $post->ID, '_gpe_aforo',                   true );
    $usar_nativa = get_post_meta( $post->ID, '_gpe_usar_inscripcion_nativa', true );
    $inscritos   = gpe_inscritos_count( $post->ID );
    $sold_out    = gpe_evento_sold_out( $post->ID );
    ?>
    <div class="gpe-wrap">
        <div class="gpe-setting-row" style="padding-top:0;">
            <div>
                <div class="gpe-setting-label">Inscripción nativa</div>
                <div class="gpe-setting-desc">Muestra el formulario de inscripción en la landing.</div>
            </div>
            <label class="gpe-toggle">
                <input type="checkbox" name="gpe_usar_inscripcion_nativa" value="1" <?php checked($usar_nativa, '1'); ?>>
                <span class="gpe-toggle-slider"></span>
            </label>
        </div>

        <div class="gpe-field" style="margin-top:16px;">
            <label class="gpe-label" for="gpe_aforo">Aforo máximo</label>
            <input type="number" id="gpe_aforo" name="gpe_aforo" value="<?php echo esc_attr($aforo); ?>" min="0" class="gpe-input" style="max-width:140px;">
            <p class="gpe-field-hint">Déjalo en 0 para sin límite de plazas.</p>
        </div>

        <?php if ( $post->post_status === 'publish' ) : ?>
        <div class="gpe-card" style="margin-top:16px;padding:16px;">
            <div class="gpe-setting-label" style="margin-bottom:8px;">Inscritos actuales</div>
            <div style="display:flex;align-items:baseline;gap:8px;">
                <strong style="font-size:1.6em;color:<?php echo $sold_out ? '#dc2626' : 'var(--gpe-1)'; ?>;"><?php echo $inscritos; ?></strong>
                <?php if ( $aforo ) echo '<span class="gpe-muted">/ ' . $aforo . ' plazas</span>'; ?>
                <?php if ( $sold_out ) echo '<span class="gpe-pill solid-red">SOLD OUT</span>'; ?>
            </div>
            <a href="<?php echo esc_url( admin_url('admin.php?page=gpe-inscritos&evento_id=' . $post->ID) ); ?>" class="gpe-btn gpe-btn-sm" style="margin-top:12px;">Ver lista de inscritos</a>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

// Guardar todos los metadatos del evento
add_action( 'save_post_evento_home', 'gpe_guardar_evento', 10, 1 );
function gpe_guardar_evento( $post_id ) {
    if ( ! isset($_POST['gpe_evento_nonce']) || ! wp_verify_nonce($_POST['gpe_evento_nonce'], 'gpe_guardar_evento_action') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( ! current_user_can('edit_post', $post_id) ) return;

    $campos_texto = array(
        'med_fecha_evento'        => '_med_fecha_evento',
        'med_hora_evento'         => '_med_hora_evento',
        'med_provincia_sitio'     => '_med_provincia_sitio',
        'gpe_codigo_provincia'    => '_gpe_codigo_provincia',
        'gpe_lugar_nombre'        => '_gpe_lugar_nombre',
        'gpe_direccion'           => '_gpe_direccion',
        'gpe_claim'               => '_gpe_claim',
        'gpe_email_coordinador'   => '_gpe_email_coordinador',
        'gpe_preguntas_debate'    => '_gpe_preguntas_debate',
        'gpe_ccaa_evento'         => '_gpe_ccaa_evento',
        'gpe_provincia_evento'    => '_gpe_provincia_evento',
    );
    foreach ( $campos_texto as $pk => $mk ) {
        if ( isset($_POST[$pk]) ) update_post_meta( $post_id, $mk, sanitize_text_field($_POST[$pk]) );
    }

    if ( isset($_POST['med_url_inscripcion']) ) update_post_meta( $post_id, '_med_url_inscripcion', esc_url_raw($_POST['med_url_inscripcion']) );
    if ( isset($_POST['gpe_foto_hero']) )       update_post_meta( $post_id, '_gpe_foto_hero',       intval($_POST['gpe_foto_hero']) );

    $campos_html = array('gpe_por_que','gpe_programa_detalle','gpe_agenda_timeline','gpe_faqs_acordeon','gpe_transporte');
    foreach ( $campos_html as $f ) {
        if ( isset($_POST[$f]) ) update_post_meta( $post_id, '_' . $f, wp_kses_post($_POST[$f]) );
    }

    if ( isset($_POST['gpe_aforo']) ) update_post_meta( $post_id, '_gpe_aforo', intval($_POST['gpe_aforo']) );
    update_post_meta( $post_id, '_gpe_usar_inscripcion_nativa', isset($_POST['gpe_usar_inscripcion_nativa']) ? '1' : '0' );

    if ( isset($_POST['gpe_mapa_embed']) ) {
        update_post_meta( $post_id, '_gpe_mapa_embed', wp_kses( $_POST['gpe_mapa_embed'], array('iframe'=>array('src'=>true,'width'=>true,'height'=>true,'frameborder'=>true,'allowfullscreen'=>true,'style'=>true,'loading'=>true)) ) );
    }

    $ids = isset($_POST['gpe_ponentes_ids']) ? array_map('intval', $_POST['gpe_ponentes_ids']) : array();
    update_post_meta( $post_id, '_gpe_ponentes_ids', $ids );

    // Timeline bloques
    $tl_raw   = isset($_POST['gpe_timeline']) ? (array)$_POST['gpe_timeline'] : array();
    $tl_clean = array();
    foreach ( $tl_raw as $b ) {
        $titulo = sanitize_text_field($b['titulo'] ?? '');
        if ( $titulo ) {
            $tl_clean[] = array(
                'hora'   => sanitize_text_field($b['hora']  ?? ''),
                'titulo' => $titulo,
                'desc'   => sanitize_text_field($b['desc']  ?? ''),
            );
        }
    }
    update_post_meta( $post_id, '_gpe_timeline_bloques', $tl_clean );

    if ( isset($_POST['med_tematica_id']) ) {
        wp_set_post_terms( $post_id, array(intval($_POST['med_tematica_id'])), 'tematica_evento' );
    }
}

// ── Metabox: Auditoría de creación (solo admins) ──────────────────────────────
add_action( 'add_meta_boxes', 'gpe_metabox_auditoria_creacion' );
function gpe_metabox_auditoria_creacion() {
    if ( ! current_user_can('manage_options') ) return;
    add_meta_box( 'gpe_meta_auditoria', '🔐 Quién creó este evento', 'gpe_render_meta_auditoria_creacion', 'evento_home', 'side', 'low' );
}
function gpe_render_meta_auditoria_creacion( $post ) {
    $autor  = get_userdata( $post->post_author );
    $nombre = $autor ? $autor->display_name : '—';
    $email  = $autor ? $autor->user_email   : '—';
    $fecha  = date_i18n( 'd/m/Y \a \l\a\s H:i', strtotime($post->post_date) );
    ?>
    <table style="width:100%;font-size:13px;">
        <tr><td style="color:#555;font-weight:600;padding:4px 0;width:55px;">Usuario</td><td style="padding:4px 0;"><strong style="color:var(--gpe-1);"><?php echo esc_html($nombre); ?></strong></td></tr>
        <tr><td style="color:#555;font-weight:600;padding:4px 0;">Email</td><td style="padding:4px 0;font-size:12px;color:#666;"><?php echo esc_html($email); ?></td></tr>
        <tr><td style="color:#555;font-weight:600;padding:4px 0;">Fecha</td><td style="padding:4px 0;font-size:12px;color:#666;"><?php echo esc_html($fecha); ?></td></tr>
    </table>
    <p style="margin:8px 0 0;font-size:11px;color:var(--gpe-muted);">Solo lectura.</p>
    <?php
}
