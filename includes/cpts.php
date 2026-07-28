<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'gpe_registrar_cpts_eventik' );
function gpe_registrar_cpts_eventik() {

    // CPT: Eventos públicos
    register_post_type( 'evento_home', array(
        'labels' => array(
            'name'               => 'Eventos',
            'singular_name'      => 'Evento',
            'menu_name'          => 'GP Eventik',
            'all_items'          => 'Todos los Eventos',
            'add_new'            => 'Nuevo Evento',
            'add_new_item'       => 'Añadir Nuevo Evento',
            'edit_item'          => 'Editar Evento',
            'not_found'          => 'No se encontraron eventos',
            'not_found_in_trash' => 'No hay eventos en la papelera',
        ),
        'public'          => true,
        'has_archive'     => false,
        'supports'        => array( 'title', 'thumbnail' ),
        'rewrite'         => array( 'slug' => 'eventos' ),
        'show_in_menu'    => 'gpe-dashboard',
        'capability_type' => 'post',
        'menu_icon'       => 'dashicons-calendar-alt',
    ) );

    // CPT: Eventos internos
    register_post_type( 'gpe_interno', array(
        'labels' => array(
            'name'          => 'Eventos Internos',
            'singular_name' => 'Evento Interno',
            'menu_name'     => 'Eventos Internos',
            'all_items'     => 'Todos los Eventos Internos',
            'add_new'       => 'Nuevo Evento Interno',
            'add_new_item'  => 'Añadir Evento Interno',
            'edit_item'     => 'Editar Evento Interno',
        ),
        'public'          => true,
        'show_ui'         => true,
        'show_in_menu'    => 'gpe-dashboard',
        'supports'        => array( 'title' ),
        'rewrite'         => array( 'slug' => 'evento-interno' ),
        'capability_type' => 'post',
        'has_archive'     => false,
    ) );

    // CPT: Ponentes
    register_post_type( 'gpe_contacto', array(
        'labels' => array(
            'name'          => 'Ponentes',
            'singular_name' => 'Ponente',
            'all_items'     => 'Ponentes',
            'add_new'       => 'Nuevo Ponente',
            'add_new_item'  => 'Añadir Ponente',
            'edit_item'     => 'Editar Ponente',
        ),
        'public'          => false,
        'show_ui'         => true,
        'show_in_menu'    => 'gpe-dashboard',
        'supports'        => array( 'none' ),
        'capability_type' => 'post',
    ) );

    // Taxonomía: Temáticas (solo eventos públicos)
    register_taxonomy( 'tematica_evento', 'evento_home', array(
        'labels' => array(
            'name'          => 'Temáticas',
            'singular_name' => 'Temática',
            'menu_name'     => 'Temáticas',
            'add_new_item'  => 'Añadir Temática',
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
    ) );
}

// ── Iconos Font Awesome para Temáticas ───────────────────────────────────────
// Icono predeterminado si no se ha asignado ninguno
function gpe_tematica_icono_default() {
    return 'fa-solid fa-tag';
}

// Mostrar campo de icono en el formulario de nueva temática
add_action('tematica_evento_add_form_fields', 'gpe_tematica_add_icono_field');
function gpe_tematica_add_icono_field() {
    echo '<div class="form-field">
        <label for="gpe_tematica_icono">Icono Font Awesome</label>
        <input type="text" name="gpe_tematica_icono" id="gpe_tematica_icono" value="" placeholder="fa-solid fa-fire">
        <p>Clase FA completa. Ej: <code>fa-solid fa-fire</code>, <code>fa-solid fa-globe</code>, <code>fa-brands fa-leaf</code>.<br>
        Ver iconos en <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com/icons</a> (versión gratuita).</p>
    </div>';
}

// Mostrar campo de icono en editar temática
add_action('tematica_evento_edit_form_fields', 'gpe_tematica_edit_icono_field');
function gpe_tematica_edit_icono_field($term) {
    $icono = get_term_meta($term->term_id, '_gpe_tematica_icono', true) ?: '';
    echo '<tr class="form-field">
        <th><label for="gpe_tematica_icono">Icono Font Awesome</label></th>
        <td>
            <input type="text" name="gpe_tematica_icono" id="gpe_tematica_icono" value="' . esc_attr($icono) . '" placeholder="fa-solid fa-fire" style="width:300px;">
            ' . ($icono ? '<span style="margin-left:8px;"><i class="' . esc_attr($icono) . '" style="color:#007a87;font-size:18px;"></i></span>' : '') . '
            <p class="description">Clase FA completa. Ej: <code>fa-solid fa-fire</code></p>
        </td>
    </tr>';
}

// Guardar el icono al crear/editar
add_action('created_tematica_evento', 'gpe_tematica_guardar_icono');
add_action('edited_tematica_evento',  'gpe_tematica_guardar_icono');
function gpe_tematica_guardar_icono($term_id) {
    if (isset($_POST['gpe_tematica_icono'])) {
        update_term_meta($term_id, '_gpe_tematica_icono', sanitize_text_field($_POST['gpe_tematica_icono']));
    }
}

// Helper público: obtener icono de una temática
function gpe_get_tematica_icono($term_id) {
    $icono = get_term_meta($term_id, '_gpe_tematica_icono', true);
    return $icono ?: gpe_tematica_icono_default();
}

// ── Cargar Font Awesome en el admin (para preview de iconos en temáticas) ─────
add_action('admin_enqueue_scripts', 'gpe_enqueue_fa_admin');
function gpe_enqueue_fa_admin($hook) {
    // Solo en páginas de taxonomía y edición de eventos
    if ( strpos($hook,'tematica_evento') !== false
         || $hook === 'edit-tags.php'
         || $hook === 'term.php'
         || $hook === 'post.php'
         || $hook === 'post-new.php' ) {
        wp_enqueue_style('gpe-fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
            array(), '6.5.0');
    }
}


// ── Quitar "Añadir Nuevo" de los submenús de eventos ─────────────────────────
add_action('admin_menu', 'gpe_reordenar_menu_principal', 9998);
function gpe_reordenar_menu_principal() {
    global $submenu;
    if (!empty($submenu['gpe-dashboard'])) {
        foreach ($submenu['gpe-dashboard'] as $i => $item) {
            if (isset($item[2]) && $item[2] === 'post-new.php?post_type=evento_home') {
                unset($submenu['gpe-dashboard'][$i]);
            }
        }
        $submenu['gpe-dashboard'] = array_values($submenu['gpe-dashboard']);
    }
}
