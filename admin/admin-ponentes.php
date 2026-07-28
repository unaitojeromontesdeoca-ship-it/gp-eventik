<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'add_meta_boxes', 'gpe_registrar_metaboxes_ponente' );
function gpe_registrar_metaboxes_ponente() {
    add_meta_box( 'gpe_meta_studio', 'Perfil del Ponente', 'gpe_render_studio_ponente', 'gpe_contacto', 'normal', 'high' );
}

function gpe_render_studio_ponente( $post ) {
    wp_nonce_field( 'gpe_save_ponente_action', 'gpe_ponente_nonce' );

    $nombre      = get_post_meta( $post->ID, '_gpe_basico_nombre',    true );
    $apellidos   = get_post_meta( $post->ID, '_gpe_basico_apellidos', true );
    $nom_pub     = get_post_meta( $post->ID, '_gpe_basico_publico',   true );
    $use_pub     = get_post_meta( $post->ID, '_gpe_basico_use_pub',   true );
    $cargo       = get_post_meta( $post->ID, '_gpe_basico_cargo',     true );
    $empresa     = get_post_meta( $post->ID, '_gpe_basico_empresa',   true );
    $ciudad      = get_post_meta( $post->ID, '_gpe_basico_ciudad',    true );
    $pais        = get_post_meta( $post->ID, '_gpe_basico_pais',      true );
    $idiomas     = get_post_meta( $post->ID, '_gpe_basico_idiomas',   true );
    $foto_id     = get_post_meta( $post->ID, '_gpe_visual_foto_id',   true ); // ID adjunto WP
    $foto_url    = $foto_id ? wp_get_attachment_image_url($foto_id, 'thumbnail') : '';
    $bio         = get_post_meta( $post->ID, '_gpe_bio_texto',        true );
    $frase       = get_post_meta( $post->ID, '_gpe_quote_frase',      true );
    $debate      = get_post_meta( $post->ID, '_gpe_quote_debate',     true );
    $redes       = get_post_meta( $post->ID, '_gpe_redes_sociales',   true ) ?: array(); // array de {red, usuario}
    $social_web  = get_post_meta( $post->ID, '_gpe_social_web',       true );
    $estudios    = get_post_meta( $post->ID, '_gpe_edu_estudios',     true );
    $premios     = get_post_meta( $post->ID, '_gpe_edu_premios',      true );
    $part_tipo   = get_post_meta( $post->ID, '_gpe_part_tipo',        true ) ?: 'Speaker';
    $notas       = get_post_meta( $post->ID, '_gpe_intern_notas',     true );
    $v_perfil    = get_post_meta( $post->ID, '_gpe_cfg_v_perfil',     true ) !== '0';
    $v_redes     = get_post_meta( $post->ID, '_gpe_cfg_v_redes',      true ) !== '0';
    $p_landing   = get_post_meta( $post->ID, '_gpe_cfg_p_landing',    true ) !== '0';
    ?>

    <h3 style="border-bottom:1px solid #eee; padding-bottom:8px; margin-top:0;">Datos personales</h3>
    <table class="form-table">
        <tr>
            <th><label for="gpe_basico_nombre">Nombre <span class="required">*</span></label></th>
            <td>
                <input type="text" id="gpe_basico_nombre" name="gpe_basico_nombre" value="<?php echo esc_attr($nombre); ?>" class="regular-text" placeholder="María">
            </td>
        </tr>
        <tr>
            <th><label for="gpe_basico_apellidos">Apellidos</label></th>
            <td>
                <input type="text" id="gpe_basico_apellidos" name="gpe_basico_apellidos" value="<?php echo esc_attr($apellidos); ?>" class="regular-text" placeholder="García López">
            </td>
        </tr>
        <tr>
            <th><label>Nombre público / artístico</label></th>
            <td>
                <label><input type="checkbox" name="gpe_basico_use_pub" value="1" <?php checked($use_pub,'1'); ?> id="gpe_toggle_pub"> Usar nombre diferente al real</label>
                <div id="gpe_wrapper_publico" style="margin-top:8px; display:<?php echo $use_pub ? 'block' : 'none'; ?>;">
                    <input type="text" name="gpe_basico_publico" value="<?php echo esc_attr($nom_pub); ?>" class="regular-text" placeholder="Ej: RedJuventud">
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="gpe_basico_cargo">Cargo / Rol</label></th>
            <td><input type="text" id="gpe_basico_cargo" name="gpe_basico_cargo" value="<?php echo esc_attr($cargo); ?>" class="regular-text" placeholder="CEO & Fundador/a"></td>
        </tr>
        <tr>
            <th><label for="gpe_basico_empresa">Empresa / Proyecto</label></th>
            <td><input type="text" id="gpe_basico_empresa" name="gpe_basico_empresa" value="<?php echo esc_attr($empresa); ?>" class="regular-text" placeholder="OpenAI, Open Cosmos…"></td>
        </tr>
        <tr>
            <th><label for="gpe_basico_ciudad">Ciudad</label></th>
            <td><input type="text" id="gpe_basico_ciudad" name="gpe_basico_ciudad" value="<?php echo esc_attr($ciudad); ?>" class="regular-text" placeholder="Barcelona"></td>
        </tr>
        <tr>
            <th><label for="gpe_basico_pais">País</label></th>
            <td><input type="text" id="gpe_basico_pais" name="gpe_basico_pais" value="<?php echo esc_attr($pais); ?>" class="regular-text" placeholder="España"></td>
        </tr>
        <tr>
            <th><label for="gpe_basico_idiomas">Idiomas</label></th>
            <td><input type="text" id="gpe_basico_idiomas" name="gpe_basico_idiomas" value="<?php echo esc_attr($idiomas); ?>" class="regular-text" placeholder="Español, English"></td>
        </tr>
    </table>

    <h3 style="border-bottom:1px solid #eee; padding-bottom:8px;">Foto de perfil</h3>
    <table class="form-table">
        <tr>
            <th><label>Foto</label></th>
            <td>
                <div id="gpe-foto-preview" style="margin-bottom:10px;">
                    <?php if ($foto_url) : ?>
                        <img src="<?php echo esc_url($foto_url); ?>" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:2px solid #007a87;">
                    <?php endif; ?>
                </div>
                <input type="hidden" id="gpe_visual_foto_id" name="gpe_visual_foto_id" value="<?php echo esc_attr($foto_id); ?>">
                <button type="button" class="button" id="gpe-btn-foto">Seleccionar foto</button>
                <?php if ($foto_id) : ?>
                    <button type="button" class="button" id="gpe-btn-foto-remove" style="margin-left:5px;">Eliminar foto</button>
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <h3 style="border-bottom:1px solid #eee; padding-bottom:8px;">Perfil biográfico</h3>
    <table class="form-table">
        <tr>
            <th><label>Biografía</label></th>
            <td><?php wp_editor( $bio, 'gpe_bio_texto', array('textarea_name'=>'gpe_bio_texto','media_buttons'=>false,'textarea_rows'=>4,'teeny'=>true) ); ?></td>
        </tr>
        <tr>
            <th><label for="gpe_quote_frase">Frase destacada</label></th>
            <td>
                <input type="text" id="gpe_quote_frase" name="gpe_quote_frase" value="<?php echo esc_attr($frase); ?>" class="large-text" placeholder='"La inacción juvenil es un mito"'>
                <p class="description">Aparece en la tarjeta del ponente en la landing del evento.</p>
            </td>
        </tr>
        <tr>
            <th><label for="gpe_quote_debate">Tesis / idea a defender</label></th>
            <td><input type="text" id="gpe_quote_debate" name="gpe_quote_debate" value="<?php echo esc_attr($debate); ?>" class="large-text"></td>
        </tr>
    </table>

    <h3 style="border-bottom:1px solid #eee; padding-bottom:8px;">Redes sociales</h3>
    <table class="form-table">
        <tr>
            <th><label>Perfiles</label></th>
            <td>
                <div id="gpe-redes-container">
                    <?php
                    // Redes guardadas
                    $redes_guardadas = is_array($redes) ? $redes : array();
                    if ( empty($redes_guardadas) ) $redes_guardadas = array(array('red'=>'','usuario'=>''));
                    foreach ( $redes_guardadas as $i => $r ) :
                    ?>
                    <div class="gpe-red-fila" style="display:flex; gap:8px; margin-bottom:8px; align-items:center;">
                        <select name="gpe_redes[<?php echo $i; ?>][red]" class="gpe-red-select" style="width:150px;">
                            <option value="">— Red —</option>
                            <option value="linkedin"   <?php selected($r['red'],'linkedin');   ?>>LinkedIn</option>
                            <option value="instagram"  <?php selected($r['red'],'instagram');  ?>>Instagram</option>
                            <option value="twitter"    <?php selected($r['red'],'twitter');    ?>>X / Twitter</option>
                            <option value="tiktok"     <?php selected($r['red'],'tiktok');     ?>>TikTok</option>
                            <option value="youtube"    <?php selected($r['red'],'youtube');    ?>>YouTube</option>
                            <option value="facebook"   <?php selected($r['red'],'facebook');   ?>>Facebook</option>
                            <option value="web"        <?php selected($r['red'],'web');        ?>>Web propia</option>
                        </select>
                        <input type="text" name="gpe_redes[<?php echo $i; ?>][usuario]" value="<?php echo esc_attr($r['usuario']); ?>" placeholder="usuario o URL" style="flex:1; padding:6px 10px; border:1px solid #8c8f94; border-radius:3px;">
                        <button type="button" class="button gpe-red-remove" title="Eliminar">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="button" id="gpe-add-red">+ Añadir red social</button>
                <p class="description">Para LinkedIn, Instagram, Twitter y TikTok escribe solo el nombre de usuario (sin @). Para YouTube o web escribe la URL completa.</p>
            </td>
        </tr>
        <tr>
            <th><label for="gpe_social_web">Web / Portfolio</label></th>
            <td><input type="url" id="gpe_social_web" name="gpe_social_web" value="<?php echo esc_url($social_web); ?>" class="large-text" placeholder="https://"></td>
        </tr>
    </table>

    <h3 style="border-bottom:1px solid #eee; padding-bottom:8px;">Formación y reconocimientos</h3>
    <table class="form-table">
        <tr>
            <th><label for="gpe_edu_estudios">Estudios</label></th>
            <td><textarea id="gpe_edu_estudios" name="gpe_edu_estudios" rows="3" class="large-text" placeholder="Máster en…"><?php echo esc_textarea($estudios); ?></textarea></td>
        </tr>
        <tr>
            <th><label for="gpe_edu_premios">Premios y reconocimientos</label></th>
            <td><textarea id="gpe_edu_premios" name="gpe_edu_premios" rows="3" class="large-text" placeholder="Premio Nacional…"><?php echo esc_textarea($premios); ?></textarea></td>
        </tr>
    </table>

    <h3 style="border-bottom:1px solid #eee; padding-bottom:8px;">Configuración interna</h3>
    <table class="form-table">
        <tr>
            <th><label for="gpe_part_tipo">Rol de participación</label></th>
            <td>
                <select id="gpe_part_tipo" name="gpe_part_tipo" class="regular-text">
                    <?php foreach(array('Speaker','Moderador','Panelista','Facilitador','Invitado VIP') as $rol) : ?>
                        <option value="<?php echo $rol; ?>" <?php selected($part_tipo, $rol); ?>><?php echo $rol; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>Visibilidad</th>
            <td>
                <label style="display:block; margin-bottom:6px;"><input type="checkbox" name="gpe_cfg_v_perfil" value="1" <?php checked($v_perfil); ?>> Mostrar foto en eventos</label>
                <label style="display:block; margin-bottom:6px;"><input type="checkbox" name="gpe_cfg_v_redes"  value="1" <?php checked($v_redes); ?>>  Mostrar redes sociales en eventos</label>
                <label style="display:block;"><input type="checkbox" name="gpe_cfg_p_landing" value="1" <?php checked($p_landing); ?>> Incluir en landing pública</label>
            </td>
        </tr>
        <tr>
            <th><label for="gpe_intern_notas">Notas internas</label></th>
            <td>
                <textarea id="gpe_intern_notas" name="gpe_intern_notas" rows="3" class="large-text" placeholder="Solo visible para el equipo…"><?php echo esc_textarea($notas); ?></textarea>
            </td>
        </tr>
    </table>

    <script>
    jQuery(document).ready(function($){
        // Toggle nombre público
        $('#gpe_toggle_pub').change(function(){
            $(this).is(':checked') ? $('#gpe_wrapper_publico').slideDown(200) : $('#gpe_wrapper_publico').slideUp(200);
        });

        // Media uploader foto
        var fotoUploader;
        $('#gpe-btn-foto').click(function(e){
            e.preventDefault();
            if (fotoUploader) { fotoUploader.open(); return; }
            fotoUploader = wp.media({ title:'Seleccionar foto del ponente', button:{text:'Usar esta foto'}, multiple:false, library:{type:'image'} });
            fotoUploader.on('select', function(){
                var att = fotoUploader.state().get('selection').first().toJSON();
                $('#gpe_visual_foto_id').val(att.id);
                $('#gpe-foto-preview').html('<img src="'+att.url+'" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #007a87;">');
            });
            fotoUploader.open();
        });
        $(document).on('click','#gpe-btn-foto-remove',function(){
            $('#gpe_visual_foto_id').val('');
            $('#gpe-foto-preview').html('');
        });

        // Añadir red social
        var redIdx = <?php echo count($redes_guardadas); ?>;
        $('#gpe-add-red').click(function(){
            var html = '<div class="gpe-red-fila" style="display:flex;gap:8px;margin-bottom:8px;align-items:center;">' +
                '<select name="gpe_redes['+redIdx+'][red]" style="width:150px;">' +
                '<option value="">— Red —</option>' +
                '<option value="linkedin">LinkedIn</option><option value="instagram">Instagram</option>' +
                '<option value="twitter">X / Twitter</option><option value="tiktok">TikTok</option>' +
                '<option value="youtube">YouTube</option><option value="facebook">Facebook</option>' +
                '<option value="web">Web propia</option>' +
                '</select>' +
                '<input type="text" name="gpe_redes['+redIdx+'][usuario]" placeholder="usuario o URL" style="flex:1;padding:6px 10px;border:1px solid #8c8f94;border-radius:3px;">' +
                '<button type="button" class="button gpe-red-remove" title="Eliminar">✕</button>' +
                '</div>';
            $('#gpe-redes-container').append(html);
            redIdx++;
        });
        $(document).on('click','.gpe-red-remove',function(){
            $(this).closest('.gpe-red-fila').remove();
        });
    });
    </script>
    <?php
}

// Guardar metas del ponente
add_action( 'save_post_gpe_contacto', 'gpe_guardar_ponente', 10, 1 );
function gpe_guardar_ponente( $post_id ) {
    if ( ! isset($_POST['gpe_ponente_nonce']) || ! wp_verify_nonce($_POST['gpe_ponente_nonce'], 'gpe_save_ponente_action') ) return;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;

    $campos = array(
        'gpe_basico_nombre','gpe_basico_apellidos','gpe_basico_publico','gpe_basico_use_pub',
        'gpe_basico_cargo','gpe_basico_empresa','gpe_basico_ciudad','gpe_basico_pais','gpe_basico_idiomas',
        'gpe_quote_frase','gpe_quote_debate',
        'gpe_edu_estudios','gpe_edu_premios',
        'gpe_part_tipo','gpe_intern_notas',
    );
    foreach ( $campos as $f ) {
        $val = isset($_POST[$f]) ? sanitize_textarea_field($_POST[$f]) : '';
        update_post_meta( $post_id, '_' . $f, $val );
    }

    // Checkboxes
    update_post_meta( $post_id, '_gpe_basico_use_pub',   isset($_POST['gpe_basico_use_pub'])   ? '1' : '0' );
    update_post_meta( $post_id, '_gpe_cfg_v_perfil',     isset($_POST['gpe_cfg_v_perfil'])     ? '1' : '0' );
    update_post_meta( $post_id, '_gpe_cfg_v_redes',      isset($_POST['gpe_cfg_v_redes'])      ? '1' : '0' );
    update_post_meta( $post_id, '_gpe_cfg_p_landing',    isset($_POST['gpe_cfg_p_landing'])    ? '1' : '0' );

    // Foto (ID de adjunto)
    if ( isset($_POST['gpe_visual_foto_id']) ) update_post_meta( $post_id, '_gpe_visual_foto_id', intval($_POST['gpe_visual_foto_id']) );

    // URL web
    if ( isset($_POST['gpe_social_web']) ) update_post_meta( $post_id, '_gpe_social_web', esc_url_raw($_POST['gpe_social_web']) );

    // Redes sociales — array limpio
    $redes_raw = isset($_POST['gpe_redes']) ? (array)$_POST['gpe_redes'] : array();
    $redes_clean = array();
    foreach ( $redes_raw as $r ) {
        $red     = sanitize_text_field($r['red']     ?? '');
        $usuario = sanitize_text_field($r['usuario'] ?? '');
        if ( $red && $usuario ) $redes_clean[] = array('red'=>$red,'usuario'=>$usuario);
    }
    update_post_meta( $post_id, '_gpe_redes_sociales', $redes_clean );

    // Actualizar título
    $nombre_completo = trim( sanitize_text_field($_POST['gpe_basico_nombre'] ?? '') . ' ' . sanitize_text_field($_POST['gpe_basico_apellidos'] ?? '') );
    if ( $nombre_completo ) {
        remove_action( 'save_post_gpe_contacto', 'gpe_guardar_ponente' );
        wp_update_post( array('ID'=>$post_id,'post_title'=>$nombre_completo) );
        add_action( 'save_post_gpe_contacto', 'gpe_guardar_ponente', 10, 1 );
    }
}

// Buscador de ponentes en el listado admin
add_action( 'restrict_manage_posts', 'gpe_ponentes_busqueda_live' );
function gpe_ponentes_busqueda_live() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->post_type !== 'gpe_contacto' ) return;
    ?>
    <style>
    #gpe-live-search-wrap { display:inline-flex; align-items:center; gap:6px; margin-left:8px; }
    #gpe-live-search-wrap input { padding:4px 10px; border:1px solid #8c8f94; border-radius:3px; font-size:13px; width:220px; }
    .gpe-pon-row-hidden { display:none !important; }
    </style>
    <span id="gpe-live-search-wrap">
        <input type="text" id="gpe-pon-search" placeholder="Buscar ponente…" autocomplete="off">
    </span>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        var inp = document.getElementById('gpe-pon-search');
        if (!inp) return;
        inp.addEventListener('input', function(){
            var q = this.value.toLowerCase();
            document.querySelectorAll('#the-list tr').forEach(function(row){
                var txt = (row.textContent || '').toLowerCase();
                row.classList.toggle('gpe-pon-row-hidden', q.length > 0 && !txt.includes(q));
            });
        });
    });
    </script>
    <?php
}
