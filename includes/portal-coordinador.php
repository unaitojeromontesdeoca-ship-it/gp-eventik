<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Portal de Coordinadores — Frontend
 * Versión multiidioma: ES, CA, GL, EU
 */

// ── Traducciones del portal por idioma ────────────────────────────────────────
function gpe_portal_i18n() {
    // Shortcodes por idioma pueden forzar el idioma via filtro
    $lang_forzado = apply_filters('gpe_portal_idioma_forzado', '');
    if ( $lang_forzado ) {
        $lang = $lang_forzado;
    } else {
        $lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'es';
    }

    $strings = array(
        'es' => array(
            'mi_territorio'     => 'Mi Territorio',
            'resumen'           => 'Resumen',
            'mis_eventos'       => 'Mis Eventos',
            'ponentes'          => 'Ponentes',
            'cerrar_sesion'     => 'Cerrar sesión',
            'nuevo_evento'      => '+ Crear nuevo evento',
            'eventos_activos'   => 'Eventos activos',
            'inscritos_totales' => 'Inscritos totales',
            'proximos_eventos_label' => 'Próximos eventos',
            'no_proximos'       => 'No hay eventos próximos en tu territorio.',
            'no_eventos'        => 'Aún no hay eventos en tu territorio.',
            'no_inscritos'      => 'Aún no hay inscritos.',
            'no_ponentes'       => 'Aún no hay ponentes creados.',
            'editar'            => 'Editar',
            'inscritos'         => 'Inscritos',
            'ver'               => 'Ver',
            'guardar'           => 'Guardar evento',
            'cancelar'          => 'Cancelar',
            'titulo_evento'     => 'Título del evento',
            'fecha'             => 'Fecha',
            'hora'              => 'Hora',
            'lugar'             => 'Lugar / Espacio',
            'direccion'         => 'Dirección',
            'claim'             => 'Claim / Subtítulo',
            'por_que'           => 'Por qué asistir',
            'aforo'             => 'Aforo máximo (0 = sin límite)',
            'estado'            => 'Estado',
            'borrador'          => 'Borrador',
            'publicado'         => 'Publicado',
            'volver_eventos'    => '← Volver a eventos',
            'sin_permisos'      => 'No tienes permisos de coordinador asignados. Contacta con el administrador.',
            'inicia_sesion'     => 'Necesitas iniciar sesión para acceder a tu portal.',
            'btn_login'         => 'Iniciar sesión',
            'nuevo_ponente'     => '+ Nuevo ponente',
            'editar_perfil'     => 'Editar perfil',
            'cargo'             => 'Cargo',
            'empresa'           => 'Empresa',
            'titulo_inscritos'  => 'Inscritos',
            'confirmados'       => 'confirmados',
            'nombre'            => 'Nombre',
            'email'             => 'Email',
            'ccaa'              => 'CCAA',
            'gestion_territorial' => 'Gestión Territorial',
            'acreditacion'      => 'Acreditación QR',
            'qr_titulo'         => 'Escanear QR de Acreditación',
            'qr_instrucciones'  => 'Apunta la cámara al código QR del asistente para verificar su entrada',
            'qr_valido'         => '✅ Acreditación válida',
            'qr_invalido'       => '❌ Código no válido o persona no encontrada',
            'qr_cerrar'         => 'Cerrar',
            'qr_escanear_otro'  => 'Escanear otro',
            'telefono'          => 'Teléfono',
            'provincia'         => 'Provincia',
            'fecha_inscripcion' => 'Inscrito el',
            'exportar_csv'      => 'Exportar CSV',
            'buscar_inscrito'   => 'Buscar por nombre o email...',
            'sin_resultados'    => 'Ningún inscrito coincide con la búsqueda.',
        ),
        'ca' => array(
            'mi_territorio'     => 'El Meu Territori',
            'resumen'           => 'Resum',
            'mis_eventos'       => 'Els Meus Esdeveniments',
            'ponentes'          => 'Ponents',
            'cerrar_sesion'     => 'Tancar sessió',
            'nuevo_evento'      => '+ Crear nou esdeveniment',
            'eventos_activos'   => 'Esdeveniments actius',
            'inscritos_totales' => 'Inscrits totals',
            'proximos_eventos_label' => 'Pròxims esdeveniments',
            'no_proximos'       => 'No hi ha esdeveniments pròxims al teu territori.',
            'no_eventos'        => 'Encara no hi ha esdeveniments al teu territori.',
            'no_inscritos'      => 'Encara no hi ha inscrits.',
            'no_ponentes'       => 'Encara no hi ha ponents creats.',
            'editar'            => 'Editar',
            'inscritos'         => 'Inscrits',
            'ver'               => 'Veure',
            'guardar'           => 'Desar esdeveniment',
            'cancelar'          => 'Cancel·lar',
            'titulo_evento'     => 'Títol de l\'esdeveniment',
            'fecha'             => 'Data',
            'hora'              => 'Hora',
            'lugar'             => 'Lloc / Espai',
            'direccion'         => 'Adreça',
            'claim'             => 'Claim / Subtítol',
            'por_que'           => 'Per què assistir',
            'aforo'             => 'Aforament màxim (0 = sense límit)',
            'estado'            => 'Estat',
            'borrador'          => 'Esborrany',
            'publicado'         => 'Publicat',
            'volver_eventos'    => '← Tornar als esdeveniments',
            'sin_permisos'      => 'No tens permisos de coordinador assignats. Contacta amb l\'administrador.',
            'inicia_sesion'     => 'Has d\'iniciar sessió per accedir al teu portal.',
            'btn_login'         => 'Iniciar sessió',
            'nuevo_ponente'     => '+ Nou ponent',
            'editar_perfil'     => 'Editar perfil',
            'cargo'             => 'Càrrec',
            'empresa'           => 'Empresa',
            'titulo_inscritos'  => 'Inscrits',
            'confirmados'       => 'confirmats',
            'nombre'            => 'Nom',
            'email'             => 'Correu electrònic',
            'ccaa'              => 'CA',
            'gestion_territorial' => 'Gestió Territorial',
            'acreditacion'      => 'Acreditació QR',
            'qr_titulo'         => 'Escanejar QR d\'Acreditació',
            'qr_instruccions'   => 'Apunta la càmera al codi QR de l\'assistent per verificar l\'entrada',
            'qr_valido'         => '✅ Acreditació vàlida',
            'qr_invalido'       => '❌ Codi no vàlid o persona no trobada',
            'qr_cerrar'         => 'Tancar',
            'qr_escanear_otro'  => 'Escanejar un altre',
            'telefono'          => 'Telèfon',
            'provincia'         => 'Província',
            'fecha_inscripcion' => 'Inscrit el',
            'exportar_csv'      => 'Exportar CSV',
            'buscar_inscrito'   => 'Cercar per nom o correu...',
            'sin_resultados'    => 'Cap inscrit no coincideix amb la cerca.',
        ),
        'gl' => array(
            'mi_territorio'     => 'O Meu Territorio',
            'resumen'           => 'Resumo',
            'mis_eventos'       => 'Os Meus Eventos',
            'ponentes'          => 'Poñentes',
            'cerrar_sesion'     => 'Pechar sesión',
            'nuevo_evento'      => '+ Crear novo evento',
            'eventos_activos'   => 'Eventos activos',
            'inscritos_totales' => 'Inscritos totais',
            'proximos_eventos_label' => 'Próximos eventos',
            'no_proximos'       => 'Non hai eventos próximos no teu territorio.',
            'no_eventos'        => 'Aínda non hai eventos no teu territorio.',
            'no_inscritos'      => 'Aínda non hai inscritos.',
            'no_ponentes'       => 'Aínda non hai poñentes creados.',
            'editar'            => 'Editar',
            'inscritos'         => 'Inscritos',
            'ver'               => 'Ver',
            'guardar'           => 'Gardar evento',
            'cancelar'          => 'Cancelar',
            'titulo_evento'     => 'Título do evento',
            'fecha'             => 'Data',
            'hora'              => 'Hora',
            'lugar'             => 'Lugar / Espazo',
            'direccion'         => 'Enderezo',
            'claim'             => 'Claim / Subtítulo',
            'por_que'           => 'Por que asistir',
            'aforo'             => 'Aforo máximo (0 = sen límite)',
            'estado'            => 'Estado',
            'borrador'          => 'Borrador',
            'publicado'         => 'Publicado',
            'volver_eventos'    => '← Volver aos eventos',
            'sin_permisos'      => 'Non tes permisos de coordinador asignados. Contacta co administrador.',
            'inicia_sesion'     => 'Necesitas iniciar sesión para acceder ao teu portal.',
            'btn_login'         => 'Iniciar sesión',
            'nuevo_ponente'     => '+ Novo poñente',
            'editar_perfil'     => 'Editar perfil',
            'cargo'             => 'Cargo',
            'empresa'           => 'Empresa',
            'titulo_inscritos'  => 'Inscritos',
            'confirmados'       => 'confirmados',
            'nombre'            => 'Nome',
            'email'             => 'Correo electrónico',
            'ccaa'              => 'CCAA',
            'gestion_territorial' => 'Xestión Territorial',
            'acreditacion'      => 'Acreditación QR',
            'qr_titulo'         => 'Escanear QR de Acreditación',
            'qr_instrucciones'  => 'Apunta a cámara ao código QR do asistente para verificar a súa entrada',
            'qr_valido'         => '✅ Acreditación válida',
            'qr_invalido'       => '❌ Código non válido ou persoa non atopada',
            'qr_cerrar'         => 'Pechar',
            'qr_escanear_otro'  => 'Escanear outro',
            'telefono'          => 'Teléfono',
            'provincia'         => 'Provincia',
            'fecha_inscripcion' => 'Inscrito o',
            'exportar_csv'      => 'Exportar CSV',
            'buscar_inscrito'   => 'Buscar por nome ou correo...',
            'sin_resultados'    => 'Ningún inscrito coincide coa busca.',
        ),
        'eu' => array(
            'mi_territorio'     => 'Nire Lurraldea',
            'resumen'           => 'Laburpena',
            'mis_eventos'       => 'Nire Ekitaldiak',
            'ponentes'          => 'Hizlariak',
            'cerrar_sesion'     => 'Saioa itxi',
            'nuevo_evento'      => '+ Ekitaldi berria sortu',
            'eventos_activos'   => 'Ekitaldi aktiboak',
            'inscritos_totales' => 'Izena emandakoak',
            'proximos_eventos_label' => 'Hurrengo ekitaldiak',
            'no_proximos'       => 'Ez dago lurraldeko hurrengo ekitaldirik.',
            'no_eventos'        => 'Oraindik ez dago ekitaldirik zure lurraldean.',
            'no_inscritos'      => 'Oraindik ez dago izena emandakorik.',
            'no_ponentes'       => 'Oraindik ez dago hizlaririk sortuta.',
            'editar'            => 'Editatu',
            'inscritos'         => 'Izena emandakoak',
            'ver'               => 'Ikusi',
            'guardar'           => 'Ekitaldia gorde',
            'cancelar'          => 'Utzi',
            'titulo_evento'     => 'Ekitaldiaren izenburua',
            'fecha'             => 'Data',
            'hora'              => 'Ordua',
            'lugar'             => 'Lekua / Espazioa',
            'direccion'         => 'Helbidea',
            'claim'             => 'Claim / Azpititulua',
            'por_que'           => 'Zergatik etorri',
            'aforo'             => 'Gehieneko aforo (0 = mugarik gabe)',
            'estado'            => 'Egoera',
            'borrador'          => 'Zirriborroa',
            'publicado'         => 'Argitaratuta',
            'volver_eventos'    => '← Ekitaldietara itzuli',
            'sin_permisos'      => 'Ez daukazu koordinatzaile-baimenik esleita. Jarri harremanetan administratzailearekin.',
            'inicia_sesion'     => 'Saioa hasi behar duzu portalera sartzeko.',
            'btn_login'         => 'Saioa hasi',
            'nuevo_ponente'     => '+ Hizlari berria',
            'editar_perfil'     => 'Profila editatu',
            'cargo'             => 'Kargua',
            'empresa'           => 'Enpresa',
            'titulo_inscritos'  => 'Izena emandakoak',
            'confirmados'       => 'berretsitak',
            'nombre'            => 'Izena',
            'email'             => 'Posta elektronikoa',
            'ccaa'              => 'AA',
            'gestion_territorial' => 'Lurralde Kudeaketa',
            'acreditacion'      => 'QR Akreditazioa',
            'qr_titulo'         => 'QR Akreditazioa eskaneatu',
            'qr_instrucciones'  => 'Zuzendu kamera laguntzailearen QR kodea sarrera egiaztatzeko',
            'qr_valido'         => '✅ Akreditazio balioduna',
            'qr_invalido'       => '❌ Kode baliogabea edo pertsona aurkitu gabe',
            'qr_cerrar'         => 'Itxi',
            'qr_escanear_otro'  => 'Beste bat eskaneatu',
            'telefono'          => 'Telefonoa',
            'provincia'         => 'Probintzia',
            'fecha_inscripcion' => 'Izena emandako data',
            'exportar_csv'      => 'CSV esportatu',
            'buscar_inscrito'   => 'Izena edo postaz bilatu...',
            'sin_resultados'    => 'Ez da bilaketarekin bat datorren inskritorik.',
        ),
    );

    return $strings[$lang] ?? $strings['es'];
}

// ── Crear páginas del portal en los 4 idiomas ─────────────────────────────────
function gpe_crear_pagina_portal() {
    $paginas = array(
        'es' => array(
            'slug'    => 'mi-territorio',
            'titulo'  => 'Mi Territorio',
            'lang'    => 'es',
        ),
        'ca' => array(
            'slug'    => 'el-meu-territori',
            'titulo'  => 'El Meu Territori',
            'lang'    => 'ca',
        ),
        'gl' => array(
            'slug'    => 'o-meu-territorio',
            'titulo'  => 'O Meu Territorio',
            'lang'    => 'gl',
        ),
        'eu' => array(
            'slug'    => 'nire-lurraldea',
            'titulo'  => 'Nire Lurraldea',
            'lang'    => 'eu',
        ),
    );

    $ids_creados = array();

    foreach ( $paginas as $lang => $datos ) {
        // Comprobar si ya existe por slug
        $existe = get_page_by_path( $datos['slug'] );
        if ( $existe ) {
            $ids_creados[$lang] = $existe->ID;
            continue;
        }

        $id = wp_insert_post( array(
            'post_title'     => $datos['titulo'],
            'post_name'      => $datos['slug'],
            'post_content'   => '[gpe_portal_coordinador]',
            'post_status'    => 'publish',
            'post_type'      => 'page',
            'comment_status' => 'closed',
        ) );

        if ( $id && ! is_wp_error($id) ) {
            $ids_creados[$lang] = $id;

            // Asociar idioma con Polylang si está activo
            if ( function_exists('pll_set_post_language') ) {
                pll_set_post_language( $id, $lang );
            }
        }
    }

    // Vincular traducciones entre sí en Polylang
    if ( function_exists('pll_save_post_translations') && count($ids_creados) > 1 ) {
        pll_save_post_translations( $ids_creados );
    }

    // Guardar IDs para uso futuro
    update_option( 'gpe_portal_page_ids', $ids_creados );
}

// ── Obtener URL del portal según idioma activo ────────────────────────────────
function gpe_portal_url() {
    $ids = get_option('gpe_portal_page_ids', array());
    // Shortcodes por idioma pueden forzar el idioma via filtro
    $lang_forzado = apply_filters('gpe_portal_idioma_forzado', '');
    if ( $lang_forzado ) {
        $lang = $lang_forzado;
    } else {
        $lang = function_exists('pll_current_language') ? pll_current_language('slug') : 'es';
    }
    $id = $ids[$lang] ?? $ids['es'] ?? null;
    return $id ? get_permalink($id) : home_url('/mi-territorio/');
}

// ── Crear menú de navegación "Gestión Territorial" ────────────────────────────
function gpe_crear_menu_navegacion() {
    $nombre_menu = 'Gestión Territorial';

    // Comprobar si ya existe
    if ( wp_get_nav_menu_object($nombre_menu) ) return;

    $menu_id = wp_create_nav_menu($nombre_menu);
    if ( is_wp_error($menu_id) ) return;

    $ids_paginas = get_option('gpe_portal_page_ids', array());

    // Página principal del portal (ES como raíz)
    $id_portal_es = $ids_paginas['es'] ?? null;
    if ( ! $id_portal_es ) return;

    // Ítem raíz: Mi Territorio
    $id_raiz = wp_update_nav_menu_item($menu_id, 0, array(
        'menu-item-title'     => 'Mi Territorio',
        'menu-item-object'    => 'page',
        'menu-item-object-id' => $id_portal_es,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
    ));

    // Subítems del portal
    $subitems = array(
        array('title'=>'Resumen',      'seccion'=>'dashboard'),
        array('title'=>'Mis Eventos',  'seccion'=>'eventos'),
        array('title'=>'Ponentes',     'seccion'=>'ponentes'),
    );

    $url_portal = get_permalink($id_portal_es);
    foreach ($subitems as $sub) {
        wp_update_nav_menu_item($menu_id, 0, array(
            'menu-item-title'     => $sub['title'],
            'menu-item-url'       => add_query_arg('seccion', $sub['seccion'], $url_portal),
            'menu-item-type'      => 'custom',
            'menu-item-parent-id' => $id_raiz,
            'menu-item-status'    => 'publish',
        ));
    }

    update_option('gpe_menu_gestion_id', $menu_id);
}

// ── Shortcode principal del portal ────────────────────────────────────────────
add_shortcode( 'gpe_portal_coordinador', 'gpe_render_portal_coordinador' );
function gpe_render_portal_coordinador() {
    $t = gpe_portal_i18n();

    if ( ! is_user_logged_in() ) {
        return '<div style="text-align:center;padding:60px 20px;font-family:\'Inter\',sans-serif;">
            <p style="font-size:1.1rem;color:#555;margin-bottom:20px;">' . esc_html($t['inicia_sesion']) . '</p>
            <a href="' . esc_url(wp_login_url(get_permalink())) . '" style="background:linear-gradient(135deg,#007a87,#00b4cc);color:#fff;padding:12px 28px;border-radius:30px;text-decoration:none;font-weight:700;">' . esc_html($t['btn_login']) . '</a>
        </div>';
    }

    $user = wp_get_current_user();

    if ( ! current_user_can('access_gpe_panel') && ! current_user_can('manage_options') ) {
        return '<div style="text-align:center;padding:60px 20px;font-family:\'Inter\',sans-serif;color:#c0392b;">
            <p>' . esc_html($t['sin_permisos']) . '</p>
        </div>';
    }

    $territorio = gpe_territorio_usuario();
    $seccion    = sanitize_text_field($_GET['seccion'] ?? 'dashboard');
    $portal_url = get_permalink();

    ob_start();
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
    .gpe-portal{font-family:'Inter',sans-serif;max-width:1100px;margin:0 auto;padding:20px;}
    .gpe-portal-header{background:linear-gradient(135deg,#007a87,#00b4cc);border-radius:16px;padding:28px 32px;color:#fff;display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px;}
    .gpe-portal-header h1{font-size:1.6rem;font-weight:900;margin:0;color:#ffffff !important;}
    .gpe-portal-header p{margin:4px 0 0;opacity:.85;font-size:14px;color:#ffffff !important;}
    .gpe-portal-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
    .gpe-portal-nav a{padding:9px 18px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;background:#f0f0f0;color:#1a1a1a;transition:all .2s;}
    .gpe-portal-nav a:hover,.gpe-portal-nav a.activo{background:#007a87;color:#fff;}
    .gpe-portal-card{background:#fff;border:1px solid #eee;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,0.04);}
    .gpe-portal-card h2{font-size:1.2rem;font-weight:800;margin:0 0 16px;border-bottom:1px solid #f0f0f0;padding-bottom:10px;}
    .gpe-portal-table{width:100%;border-collapse:collapse;}
    .gpe-portal-table th{background:#f8f8f8;padding:10px 14px;text-align:left;font-size:13px;color:#555;font-weight:700;border-bottom:2px solid #eee;}
    .gpe-portal-table td{padding:12px 14px;border-bottom:1px solid #f5f5f5;font-size:14px;}
    .gpe-portal-table tr:hover td{background:#fafafa;}
    .gpe-btn{display:inline-block;padding:8px 18px;border-radius:6px;font-weight:700;font-size:13px;text-decoration:none;transition:all .2s;cursor:pointer;border:none;}
    .gpe-btn-primary{background:#007a87;color:#fff;}
    .gpe-btn-primary:hover{background:#005f69;color:#fff;}
    .gpe-btn-secondary{background:#f0f0f0;color:#1a1a1a;}
    .gpe-btn-secondary:hover{background:#e0e0e0;}
    .gpe-form-field{margin-bottom:16px;}
    .gpe-form-field label{display:block;font-weight:600;font-size:13px;margin-bottom:5px;color:#333;}
    .gpe-form-input{width:100%;padding:9px 13px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-sizing:border-box;font-family:'Inter',sans-serif;}
    .gpe-form-input:focus{border-color:#007a87;outline:none;box-shadow:0 0 0 3px rgba(0,122,135,0.1);}
    .gpe-form-2col{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .gpe-alerta{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;font-size:14px;}
    .gpe-alerta-ok{background:#d4edda;color:#155724;}
    .gpe-alerta-err{background:#f8d7da;color:#721c24;}
    .gpe-kpis{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:20px;}
    .gpe-kpi{background:#fff;border:1px solid #eee;border-radius:12px;padding:16px 22px;text-align:center;min-width:100px;}
    .gpe-kpi-num{font-size:2rem;font-weight:900;color:#007a87;line-height:1;}
    .gpe-kpi-lbl{font-size:12px;color:#888;margin-top:5px;}
    @media(max-width:600px){.gpe-form-2col{grid-template-columns:1fr;}.gpe-portal-header{flex-direction:column;}.gpe-kpis{gap:10px;}}
    </style>

    <div class="gpe-portal">
        <div class="gpe-portal-header">
            <div>
                <h1><?php echo esc_html($t['mi_territorio']); ?></h1>
                <p><?php echo esc_html($user->display_name); ?>
                    <?php if ($territorio['ccaa'])      echo ' · ' . esc_html($territorio['ccaa']); ?>
                    <?php if ($territorio['provincia']) echo ' · ' . esc_html($territorio['provincia']); ?>
                </p>
            </div>
            <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" style="background:rgba(255,255,255,.2);color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;"><?php echo esc_html($t['cerrar_sesion']); ?></a>
        </div>

        <nav class="gpe-portal-nav">
            <?php
            $secciones = array(
                'dashboard'    => $t['resumen'],
                'eventos'      => $t['mis_eventos'],
                'ponentes'     => $t['ponentes'],
                'acreditacion' => $t['acreditacion'] ?? 'Acreditación QR',
            );
            foreach ($secciones as $s => $label) :
                $url = add_query_arg('seccion', $s, $portal_url);
            ?>
                <a href="<?php echo esc_url($url); ?>" class="<?php echo $seccion === $s ? 'activo' : ''; ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </nav>

        <?php
        switch ($seccion) {
            case 'dashboard':    gpe_portal_dashboard($territorio, $user, $portal_url, $t);                break;
            case 'eventos':      gpe_portal_eventos($territorio, $portal_url, $t);                         break;
            case 'nuevo':        gpe_portal_form_evento($territorio, 0, $portal_url, $t);                  break;
            case 'editar':       gpe_portal_form_evento($territorio, intval($_GET['id']??0), $portal_url, $t); break;
            case 'inscritos':    gpe_portal_inscritos($territorio, $portal_url, $t);                       break;
            case 'ponentes':     gpe_portal_ponentes($territorio, $portal_url, $t);                        break;
            case 'acreditacion': gpe_portal_acreditacion_qr($territorio, $portal_url, $t);                break;
            default:             gpe_portal_dashboard($territorio, $user, $portal_url, $t);
        }
        ?>
    </div>
    <?php
    return ob_get_clean();
}

// ── Dashboard ─────────────────────────────────────────────────────────────────
function gpe_portal_dashboard($territorio, $user, $portal_url, $t) {
    global $wpdb;

    $meta_q = array();
    if ($territorio['ccaa']) $meta_q[] = array('key'=>'_gpe_ccaa_evento','value'=>$territorio['ccaa']);

    $proximos = new WP_Query(array(
        'post_type'      => 'evento_home',
        'posts_per_page' => 5,
        'post_status'    => 'publish',
        'meta_key'       => '_med_fecha_evento',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array_merge(
            array(array('key'=>'_med_fecha_evento','value'=>date('Y-m-d'),'compare'=>'>=','type'=>'DATE')),
            $meta_q
        ),
    ));
    $total_eventos = (new WP_Query(array('post_type'=>'evento_home','post_status'=>'publish','fields'=>'ids','meta_query'=>$meta_q,'posts_per_page'=>-1)))->found_posts;

    $ids_eventos = !empty($territorio['ccaa']) ? $wpdb->get_col($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} pm ON pm.post_id=p.ID AND pm.meta_key='_gpe_ccaa_evento'
         WHERE p.post_type='evento_home' AND p.post_status='publish' AND pm.meta_value=%s",
        $territorio['ccaa']
    )) : array();

    $total_inscritos = 0;
    if (!empty($ids_eventos)) {
        $in = implode(',', array_map('intval', $ids_eventos));
        $total_inscritos = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id IN($in) AND estado='confirmada'");
    }
    ?>
    <div class="gpe-kpis">
        <div class="gpe-kpi"><div class="gpe-kpi-num"><?php echo $total_eventos; ?></div><div class="gpe-kpi-lbl"><?php echo esc_html($t['eventos_activos']); ?></div></div>
        <div class="gpe-kpi"><div class="gpe-kpi-num"><?php echo $total_inscritos; ?></div><div class="gpe-kpi-lbl"><?php echo esc_html($t['inscritos_totales']); ?></div></div>
        <div class="gpe-kpi"><div class="gpe-kpi-num"><?php echo $proximos->found_posts; ?></div><div class="gpe-kpi-lbl"><?php echo esc_html($t['proximos_eventos_label']); ?></div></div>
    </div>

    <div class="gpe-portal-card">
        <h2><?php echo esc_html($t['proximos_eventos_label']); ?></h2>
        <?php if (!$proximos->have_posts()) : ?>
            <p style="color:#999;"><?php echo esc_html($t['no_proximos']); ?></p>
        <?php else : ?>
        <table class="gpe-portal-table">
            <thead><tr>
                <th><?php echo esc_html($t['titulo_evento']); ?></th>
                <th><?php echo esc_html($t['fecha']); ?></th>
                <th><?php echo esc_html($t['inscritos']); ?></th>
                <th><?php echo esc_html($t['estado']); ?></th>
            </tr></thead>
            <tbody>
            <?php while ($proximos->have_posts()) : $proximos->the_post();
                $eid  = get_the_ID();
                $fech = get_post_meta($eid,'_med_fecha_evento',true);
                $ins  = gpe_inscritos_count($eid);
                $afor = gpe_aforo_maximo($eid);
                $url_editar    = add_query_arg(array('seccion'=>'editar','id'=>$eid), $portal_url);
                $url_inscritos = add_query_arg(array('seccion'=>'inscritos','evento'=>$eid), $portal_url);
            ?>
            <tr>
                <td><strong><?php the_title(); ?></strong></td>
                <td><?php echo $fech ? date('d/m/Y',strtotime($fech)) : '—'; ?></td>
                <td><?php echo $ins; ?><?php if($afor) echo ' / '.$afor; ?></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;padding-top:14px;">
                    <a href="<?php echo esc_url($url_editar); ?>" class="gpe-btn gpe-btn-secondary"><?php echo esc_html($t['editar']); ?></a>
                    <a href="<?php echo esc_url($url_inscritos); ?>" class="gpe-btn gpe-btn-primary"><?php echo esc_html($t['inscritos']); ?></a>
                </td>
            </tr>
            <?php endwhile; wp_reset_postdata(); ?>
            </tbody>
        </table>
        <?php endif; ?>
        <div style="margin-top:14px;">
            <a href="<?php echo esc_url(add_query_arg('seccion','nuevo',$portal_url)); ?>" class="gpe-btn gpe-btn-primary"><?php echo esc_html($t['nuevo_evento']); ?></a>
        </div>
    </div>
    <?php
}

// ── Lista de eventos ──────────────────────────────────────────────────────────
function gpe_portal_eventos($territorio, $portal_url, $t) {
    $meta_q = array();
    if ($territorio['ccaa']) $meta_q[] = array('key'=>'_gpe_ccaa_evento','value'=>$territorio['ccaa']);

    $eventos = new WP_Query(array(
        'post_type'      => 'evento_home',
        'posts_per_page' => 50,
        'post_status'    => array('publish','draft'),
        'meta_key'       => '_med_fecha_evento',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
        'meta_query'     => $meta_q,
    ));
    ?>
    <div class="gpe-portal-card">
        <h2><?php echo esc_html($t['mis_eventos']); ?>
            <a href="<?php echo esc_url(add_query_arg('seccion','nuevo',$portal_url)); ?>" class="gpe-btn gpe-btn-primary" style="float:right;font-size:13px;"><?php echo esc_html($t['nuevo_evento']); ?></a>
        </h2>
        <?php if (!$eventos->have_posts()) : ?>
            <p style="color:#999;"><?php echo esc_html($t['no_eventos']); ?></p>
        <?php else : ?>
        <table class="gpe-portal-table">
            <thead><tr>
                <th><?php echo esc_html($t['titulo_evento']); ?></th>
                <th><?php echo esc_html($t['fecha']); ?></th>
                <th><?php echo esc_html($t['estado']); ?></th>
                <th><?php echo esc_html($t['inscritos']); ?></th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php while ($eventos->have_posts()) : $eventos->the_post();
                $eid    = get_the_ID();
                $fech   = get_post_meta($eid,'_med_fecha_evento',true);
                $ins    = gpe_inscritos_count($eid);
                $afor   = gpe_aforo_maximo($eid);
                $estado = get_post_status();
                $est_labels = array(
                    'publish'       => array('#27ae60', $t['publicado']),
                    'draft'         => array('#e67e22', $t['borrador']),
                    'gpe_cancelado' => array('#c0392b', 'Cancelado'),
                    'gpe_pospuesto' => array('#e67e22', 'Pospuesto'),
                );
                $est_info      = $est_labels[$estado] ?? array('#999', ucfirst($estado));
                $url_editar    = add_query_arg(array('seccion'=>'editar','id'=>$eid), $portal_url);
                $url_inscritos = add_query_arg(array('seccion'=>'inscritos','evento'=>$eid), $portal_url);
            ?>
            <tr>
                <td><strong><?php the_title(); ?></strong></td>
                <td><?php echo $fech ? date('d/m/Y',strtotime($fech)) : '—'; ?></td>
                <td><span style="background:<?php echo $est_info[0]; ?>;color:#fff;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;"><?php echo esc_html($est_info[1]); ?></span></td>
                <td><?php echo $ins; ?><?php if($afor) echo ' / '.$afor; ?></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap;">
                    <a href="<?php echo esc_url($url_editar); ?>" class="gpe-btn gpe-btn-secondary"><?php echo esc_html($t['editar']); ?></a>
                    <a href="<?php echo esc_url($url_inscritos); ?>" class="gpe-btn gpe-btn-primary"><?php echo esc_html($t['inscritos']); ?></a>
                    <?php if ($estado === 'publish') : ?>
                        <a href="<?php echo esc_url(get_the_permalink($eid)); ?>" target="_blank" class="gpe-btn gpe-btn-secondary"><?php echo esc_html($t['ver']); ?></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; wp_reset_postdata(); ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}

// ── Formulario evento ─────────────────────────────────────────────────────────
function gpe_portal_form_evento($territorio, $evento_id, $portal_url, $t) {
    if (isset($_POST['gpe_portal_evento_nonce']) && wp_verify_nonce($_POST['gpe_portal_evento_nonce'],'gpe_portal_guardar_evento')) {
        $titulo    = sanitize_text_field($_POST['titulo']    ?? '');
        $fecha     = sanitize_text_field($_POST['fecha']     ?? '');
        $hora      = sanitize_text_field($_POST['hora']      ?? '');
        $lugar     = sanitize_text_field($_POST['lugar']     ?? '');
        $direccion = sanitize_text_field($_POST['direccion'] ?? '');
        $claim     = sanitize_text_field($_POST['claim']     ?? '');
        $por_que   = wp_kses_post($_POST['por_que']          ?? '');
        $aforo     = intval($_POST['aforo']                  ?? 0);
        $estado_n  = in_array($_POST['estado']??'', array('publish','draft')) ? $_POST['estado'] : 'draft';

        if (!$titulo) {
            echo '<div class="gpe-alerta gpe-alerta-err">El título es obligatorio.</div>';
        } else {
            if ($evento_id) {
                if (!gpe_puede_editar_evento($evento_id)) {
                    echo '<div class="gpe-alerta gpe-alerta-err">No tienes permisos para editar este evento.</div>';
                    return;
                }
                wp_update_post(array('ID'=>$evento_id,'post_title'=>$titulo,'post_status'=>$estado_n));
            } else {
                $evento_id = wp_insert_post(array('post_title'=>$titulo,'post_type'=>'evento_home','post_status'=>$estado_n));
            }

            if ($evento_id && !is_wp_error($evento_id)) {
                update_post_meta($evento_id,'_med_fecha_evento',   $fecha);
                update_post_meta($evento_id,'_med_hora_evento',    $hora);
                update_post_meta($evento_id,'_gpe_lugar_nombre',   $lugar);
                update_post_meta($evento_id,'_gpe_direccion',      $direccion);
                update_post_meta($evento_id,'_gpe_claim',          $claim);
                update_post_meta($evento_id,'_gpe_por_que',        $por_que);
                update_post_meta($evento_id,'_gpe_aforo',          $aforo);
                update_post_meta($evento_id,'_gpe_ccaa_evento',    $territorio['ccaa']);
                update_post_meta($evento_id,'_gpe_provincia_evento',$territorio['provincia']);
                echo '<div class="gpe-alerta gpe-alerta-ok">✅ ' . esc_html($t['guardar']) . ' — OK</div>';
            }
        }
    }

    $titulo    = $evento_id ? get_the_title($evento_id) : '';
    $fecha     = get_post_meta($evento_id,'_med_fecha_evento',true);
    $hora      = get_post_meta($evento_id,'_med_hora_evento',true);
    $lugar     = get_post_meta($evento_id,'_gpe_lugar_nombre',true);
    $direccion = get_post_meta($evento_id,'_gpe_direccion',true);
    $claim     = get_post_meta($evento_id,'_gpe_claim',true);
    $por_que   = get_post_meta($evento_id,'_gpe_por_que',true);
    $aforo     = get_post_meta($evento_id,'_gpe_aforo',true);
    $estado    = $evento_id ? get_post_status($evento_id) : 'draft';
    ?>
    <div class="gpe-portal-card">
        <h2><?php echo $evento_id ? esc_html($t['editar']) . ' ' . esc_html($t['titulo_evento']) : esc_html($t['nuevo_evento']); ?></h2>
        <form method="post">
            <?php wp_nonce_field('gpe_portal_guardar_evento','gpe_portal_evento_nonce'); ?>
            <input type="hidden" name="evento_id" value="<?php echo intval($evento_id); ?>">

            <div class="gpe-form-field">
                <label><?php echo esc_html($t['titulo_evento']); ?> *</label>
                <input type="text" name="titulo" value="<?php echo esc_attr($titulo); ?>" class="gpe-form-input" required>
            </div>
            <div class="gpe-form-2col">
                <div class="gpe-form-field">
                    <label><?php echo esc_html($t['fecha']); ?></label>
                    <input type="date" name="fecha" value="<?php echo esc_attr($fecha); ?>" class="gpe-form-input">
                </div>
                <div class="gpe-form-field">
                    <label><?php echo esc_html($t['hora']); ?></label>
                    <input type="time" name="hora" value="<?php echo esc_attr($hora); ?>" class="gpe-form-input">
                </div>
            </div>
            <div class="gpe-form-field">
                <label><?php echo esc_html($t['lugar']); ?></label>
                <input type="text" name="lugar" value="<?php echo esc_attr($lugar); ?>" class="gpe-form-input">
            </div>
            <div class="gpe-form-field">
                <label><?php echo esc_html($t['direccion']); ?></label>
                <input type="text" name="direccion" value="<?php echo esc_attr($direccion); ?>" class="gpe-form-input">
            </div>
            <div class="gpe-form-field">
                <label><?php echo esc_html($t['claim']); ?></label>
                <input type="text" name="claim" value="<?php echo esc_attr($claim); ?>" class="gpe-form-input">
            </div>
            <div class="gpe-form-field">
                <label><?php echo esc_html($t['por_que']); ?></label>
                <textarea name="por_que" rows="4" class="gpe-form-input"><?php echo esc_textarea(wp_strip_all_tags($por_que)); ?></textarea>
            </div>
            <div class="gpe-form-2col">
                <div class="gpe-form-field">
                    <label><?php echo esc_html($t['aforo']); ?></label>
                    <input type="number" name="aforo" value="<?php echo esc_attr($aforo); ?>" min="0" class="gpe-form-input">
                </div>
                <div class="gpe-form-field">
                    <label><?php echo esc_html($t['estado']); ?></label>
                    <select name="estado" class="gpe-form-input">
                        <option value="draft"   <?php selected($estado,'draft');   ?>><?php echo esc_html($t['borrador']); ?></option>
                        <option value="publish" <?php selected($estado,'publish'); ?>><?php echo esc_html($t['publicado']); ?></option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="gpe-btn gpe-btn-primary" style="font-size:15px;padding:11px 24px;"><?php echo esc_html($t['guardar']); ?></button>
                <a href="<?php echo esc_url(add_query_arg('seccion','eventos',$portal_url)); ?>" class="gpe-btn gpe-btn-secondary" style="padding:11px 24px;"><?php echo esc_html($t['cancelar']); ?></a>
            </div>
        </form>
    </div>
    <?php
}

// ── Inscritos — mejorado con búsqueda, teléfono, provincia y CSV ──────────────
function gpe_portal_inscritos($territorio, $portal_url, $t) {
    global $wpdb;
    $evento_id = intval($_GET['evento'] ?? 0);
    if (!$evento_id || !gpe_puede_editar_evento($evento_id)) {
        echo '<div class="gpe-alerta gpe-alerta-err">Evento no válido.</div>';
        return;
    }

    // Exportar CSV
    if (isset($_GET['gpe_csv']) && $_GET['gpe_csv'] == '1') {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id=%d AND estado='confirmada' ORDER BY fecha_reg DESC",
            $evento_id
        ));
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="inscritos-'.intval($evento_id).'.csv"');
        $fp = fopen('php://output','w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($fp, array('Nombre','Apellidos','Email','Teléfono','CCAA','Provincia','Fecha inscripción'));
        foreach ($rows as $r) {
            fputcsv($fp, array($r->nombre,$r->apellidos,$r->email,$r->telefono??'',$r->ccaa??'',$r->provincia??'',date('d/m/Y H:i',strtotime($r->fecha_reg))));
        }
        fclose($fp);
        exit;
    }

    $evento   = get_post($evento_id);
    $inscritos = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE evento_id=%d AND estado='confirmada' ORDER BY fecha_reg DESC",
        $evento_id
    ));
    $url_csv = add_query_arg(array('seccion'=>'inscritos','evento'=>$evento_id,'gpe_csv'=>1), $portal_url);
    $placeholder_buscar = $t['buscar_inscrito'] ?? 'Buscar...';
    $sin_resultados_txt = $t['sin_resultados']  ?? 'Sin resultados.';
    $tel_lbl  = $t['telefono']          ?? 'Teléfono';
    $prov_lbl = $t['provincia']         ?? 'Provincia';
    $csv_lbl  = $t['exportar_csv']      ?? 'Exportar CSV';
    $fi_lbl   = $t['fecha_inscripcion'] ?? 'Inscrito el';
    ?>
    <div class="gpe-portal-card">
        <h2 style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span><?php echo esc_html($t['titulo_inscritos']); ?>: <?php echo esc_html($evento->post_title); ?>
                <span style="font-size:1rem;font-weight:400;color:#888;margin-left:10px;"><?php echo count($inscritos); ?> <?php echo esc_html($t['confirmados']); ?></span>
            </span>
            <?php if (!empty($inscritos)) : ?>
            <a href="<?php echo esc_url($url_csv); ?>" class="gpe-btn gpe-btn-secondary" style="font-size:12px;">⬇ <?php echo esc_html($csv_lbl); ?></a>
            <?php endif; ?>
        </h2>
        <?php if (empty($inscritos)) : ?>
            <p style="color:#999;"><?php echo esc_html($t['no_inscritos']); ?></p>
        <?php else : ?>
        <input type="text" id="gpe-buscar-inscritos" placeholder="<?php echo esc_attr($placeholder_buscar); ?>"
               style="width:100%;padding:9px 13px;border:1px solid #ddd;border-radius:6px;font-size:14px;box-sizing:border-box;margin-bottom:14px;font-family:inherit;">
        <p id="gpe-inscritos-sin-result" style="display:none;color:#aaa;text-align:center;padding:16px 0;"><?php echo esc_html($sin_resultados_txt); ?></p>
        <table class="gpe-portal-table" id="gpe-tabla-inscritos">
            <thead><tr>
                <th><?php echo esc_html($t['nombre']); ?></th>
                <th><?php echo esc_html($t['email']); ?></th>
                <th><?php echo esc_html($tel_lbl); ?></th>
                <th><?php echo esc_html($t['ccaa']); ?></th>
                <th><?php echo esc_html($prov_lbl); ?></th>
                <th><?php echo esc_html($fi_lbl); ?></th>
            </tr></thead>
            <tbody>
            <?php foreach ($inscritos as $i) : ?>
            <tr class="gpe-insc-fila" data-busca="<?php echo esc_attr(strtolower($i->nombre.' '.$i->apellidos.' '.$i->email)); ?>">
                <td><strong><?php echo esc_html($i->nombre . ' ' . $i->apellidos); ?></strong></td>
                <td><?php echo esc_html($i->email); ?></td>
                <td style="font-size:13px;"><?php echo esc_html(isset($i->telefono) ? $i->telefono : '—'); ?></td>
                <td><?php echo esc_html($i->ccaa ?: '—'); ?></td>
                <td style="font-size:13px;"><?php echo esc_html(isset($i->provincia) ? ($i->provincia ?: '—') : '—'); ?></td>
                <td style="font-size:12px;color:#888;"><?php echo date('d/m/Y H:i',strtotime($i->fecha_reg)); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <script>
        document.getElementById('gpe-buscar-inscritos').addEventListener('input',function(){
            var q=this.value.toLowerCase(), vis=0;
            document.querySelectorAll('.gpe-insc-fila').forEach(function(r){
                var ok=!q||r.dataset.busca.includes(q);
                r.style.display=ok?'':'none';
                if(ok)vis++;
            });
            document.getElementById('gpe-inscritos-sin-result').style.display=vis===0?'block':'none';
        });
        </script>
        <?php endif; ?>
        <div style="margin-top:14px;">
            <a href="<?php echo esc_url(add_query_arg('seccion','eventos',$portal_url)); ?>" class="gpe-btn gpe-btn-secondary"><?php echo esc_html($t['volver_eventos']); ?></a>
        </div>
    </div>
    <?php
}

// ── Ponentes ──────────────────────────────────────────────────────────────────
function gpe_portal_ponentes($territorio, $portal_url, $t) {
    $ponentes = get_posts(array('post_type'=>'gpe_contacto','posts_per_page'=>50,'orderby'=>'title','order'=>'ASC'));
    ?>
    <div class="gpe-portal-card">
        <h2><?php echo esc_html($t['ponentes']); ?>
            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=gpe_contacto')); ?>" class="gpe-btn gpe-btn-primary" style="float:right;font-size:13px;"><?php echo esc_html($t['nuevo_ponente']); ?></a>
        </h2>
        <?php if (empty($ponentes)) : ?>
            <p style="color:#999;"><?php echo esc_html($t['no_ponentes']); ?></p>
        <?php else : ?>
        <table class="gpe-portal-table">
            <thead><tr>
                <th><?php echo esc_html($t['nombre']); ?></th>
                <th><?php echo esc_html($t['cargo']); ?></th>
                <th><?php echo esc_html($t['empresa']); ?></th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($ponentes as $p) :
                $cargo   = get_post_meta($p->ID,'_gpe_basico_cargo',true);
                $empresa = get_post_meta($p->ID,'_gpe_basico_empresa',true);
            ?>
            <tr>
                <td><strong><?php echo esc_html($p->post_title); ?></strong></td>
                <td><?php echo esc_html($cargo   ?: '—'); ?></td>
                <td><?php echo esc_html($empresa ?: '—'); ?></td>
                <td><a href="<?php echo esc_url(get_edit_post_link($p->ID)); ?>" class="gpe-btn gpe-btn-secondary"><?php echo esc_html($t['editar_perfil']); ?></a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    <?php
}

// ── Acreditación QR — Escáner con cámara ──────────────────────────────────────
function gpe_portal_acreditacion_qr($territorio, $portal_url, $t) {
    $nonce        = wp_create_nonce('gpe_qr_portal_nonce');
    $ajax_url     = admin_url('admin-ajax.php');
    $lbl_titulo   = $t['qr_titulo']        ?? 'Escanear QR de Acreditación';
    $lbl_instruc  = $t['qr_instrucciones'] ?? 'Apunta la cámara al código QR del asistente';
    $lbl_valido   = $t['qr_valido']        ?? '✅ Acreditación válida';
    $lbl_invalido = $t['qr_invalido']      ?? '❌ Código no válido';
    $lbl_cerrar   = $t['qr_cerrar']        ?? 'Cerrar';
    $lbl_otro     = $t['qr_escanear_otro'] ?? 'Escanear otro';
    ?>
    <div class="gpe-portal-card">
        <h2><?php echo esc_html($lbl_titulo); ?></h2>
        <p style="color:#666;margin-bottom:20px;"><?php echo esc_html($lbl_instruc); ?></p>

        <div style="max-width:480px;margin:0 auto;">
            <div id="gpe-qr-reader" style="border-radius:12px;overflow:hidden;border:2px solid #007a87;"></div>
            <p style="text-align:center;margin-top:8px;font-size:12px;color:#bbb;">Requiere permiso de cámara en el navegador</p>
        </div>

        <!-- Popup VÁLIDO -->
        <div id="gpe-qr-popup-ok" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:99999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:20px;padding:36px 32px;max-width:380px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <div style="font-size:3.5rem;line-height:1;margin-bottom:12px;">✅</div>
                <h3 style="color:#155724;font-size:1.3rem;margin:0 0 16px;"><?php echo esc_html($lbl_valido); ?></h3>
                <div id="gpe-qr-aviso-escaneos" style="display:none;background:#e67e22;color:#fff;border-radius:8px;padding:8px 14px;font-size:13px;font-weight:700;margin-bottom:12px;"></div>
                <div id="gpe-qr-datos" style="background:#f4fdf5;border:1px solid #c3e6cb;border-radius:10px;padding:16px;text-align:left;margin-bottom:20px;font-size:14px;line-height:1.8;"></div>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                    <button onclick="gpeQrReset()" style="background:#007a87;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-weight:700;cursor:pointer;"><?php echo esc_html($lbl_otro); ?></button>
                    <button onclick="gpeQrCerrar('ok')" style="background:#f0f0f0;color:#333;border:none;padding:10px 20px;border-radius:6px;font-weight:700;cursor:pointer;"><?php echo esc_html($lbl_cerrar); ?></button>
                </div>
            </div>
        </div>

        <!-- Popup INVÁLIDO -->
        <div id="gpe-qr-popup-err" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.75);z-index:99999;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:20px;padding:36px 32px;max-width:380px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <div style="font-size:3.5rem;line-height:1;margin-bottom:12px;">❌</div>
                <h3 style="color:#721c24;font-size:1.3rem;margin:0 0 20px;"><?php echo esc_html($lbl_invalido); ?></h3>
                <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                    <button onclick="gpeQrReset()" style="background:#007a87;color:#fff;border:none;padding:10px 20px;border-radius:6px;font-weight:700;cursor:pointer;"><?php echo esc_html($lbl_otro); ?></button>
                    <button onclick="gpeQrCerrar('err')" style="background:#f0f0f0;color:#333;border:none;padding:10px 20px;border-radius:6px;font-weight:700;cursor:pointer;"><?php echo esc_html($lbl_cerrar); ?></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
    var _gpeQr = null, _gpeQrPaused = false;

    (function init(){
        _gpeQr = new Html5Qrcode('gpe-qr-reader');
        _gpeQr.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 240, height: 240 } },
            function(text) {
                if (_gpeQrPaused) return;
                _gpeQrPaused = true;
                gpeQrVerificar(text);
            },
            function() {}
        ).catch(function() {
            document.getElementById('gpe-qr-reader').innerHTML =
                '<p style="padding:30px;text-align:center;color:#c0392b;">No se pudo acceder a la cámara.<br>Comprueba que has dado permiso al navegador.</p>';
        });
    })();

    function gpeQrVerificar(texto) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '<?php echo esc_js($ajax_url); ?>', true);
        xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
        xhr.onload = function() {
            try {
                var r = JSON.parse(xhr.responseText);
                if (r.success && r.data) {
                    var d = r.data;
                    var html = '<strong style="font-size:1.05rem;">'+gpeEsc(d.nombre)+'</strong><br>';
                    if (d.evento)    html += '<span style="color:#007a87;font-weight:700;">'+gpeEsc(d.evento)+'</span><br>';
                    if (d.email)     html += '✉️ '+gpeEsc(d.email)+'<br>';
                    if (d.ccaa)      html += '📍 '+gpeEsc(d.ccaa)+(d.provincia?' — '+gpeEsc(d.provincia):'')+'<br>';
                    if (d.fecha_reg) html += '<span style="font-size:12px;color:#aaa;">Inscrito el '+gpeEsc(d.fecha_reg)+'</span>';
                    document.getElementById('gpe-qr-datos').innerHTML = html;
                    // Aviso naranja si ya fue escaneado antes
                    var avisoEl = document.getElementById('gpe-qr-aviso-escaneos');
                    if (d.ya_escaneado && d.scan_count) {
                        avisoEl.textContent = '⚠️ Atención: este QR ya fue escaneado ' + d.scan_count + ' veces';
                        avisoEl.style.display = 'block';
                    } else {
                        avisoEl.style.display = 'none';
                    }
                    gpeQrMostrar('ok');
                } else {
                    gpeQrMostrar('err');
                }
            } catch(e) { gpeQrMostrar('err'); }
        };
        xhr.onerror = function() { gpeQrMostrar('err'); };
        xhr.send('action=gpe_verificar_qr_portal&nonce=<?php echo esc_js($nonce); ?>&qr_text='+encodeURIComponent(texto));
    }

    function gpeEsc(s) {
        var d = document.createElement('div');
        d.textContent = String(s || '');
        return d.innerHTML;
    }
    function gpeQrMostrar(tipo) {
        document.getElementById('gpe-qr-popup-'+tipo).style.display = 'flex';
    }
    function gpeQrCerrar(tipo) {
        document.getElementById('gpe-qr-popup-'+tipo).style.display = 'none';
    }
    function gpeQrReset() {
        document.getElementById('gpe-qr-popup-ok').style.display  = 'none';
        document.getElementById('gpe-qr-popup-err').style.display = 'none';
        _gpeQrPaused = false;
    }
    document.addEventListener('keydown', function(e){ if(e.key==='Escape') gpeQrReset(); });
    </script>
    <?php
}

// ── AJAX: Verificar QR desde el portal ───────────────────────────────────────
add_action('wp_ajax_gpe_verificar_qr_portal',        'gpe_ajax_verificar_qr_portal');
add_action('wp_ajax_nopriv_gpe_verificar_qr_portal', 'gpe_ajax_verificar_qr_portal');
function gpe_ajax_verificar_qr_portal() {
    check_ajax_referer('gpe_qr_portal_nonce', 'nonce');
    if (!is_user_logged_in() || (!current_user_can('access_gpe_panel') && !current_user_can('manage_options'))) {
        wp_send_json_error(array('msg'=>'Sin permisos.'));
    }

    $qr_text = sanitize_text_field($_POST['qr_text'] ?? '');
    if (!$qr_text) wp_send_json_error(array('msg'=>'QR vacío.'));

    $token = null;
    if (strpos($qr_text, 'gpe_acred') !== false) {
        $parsed = array();
        parse_str(parse_url($qr_text, PHP_URL_QUERY) ?? '', $parsed);
        $raw = isset($parsed['data']) ? base64_decode($parsed['data']) : '';
        $dat = $raw ? json_decode($raw, true) : null;
        if ($dat && !empty($dat['token'])) $token = $dat['token'];
    } else {
        $token = $qr_text;
    }

    if (!$token) wp_send_json_error(array('msg'=>'QR no reconocido.'));

    global $wpdb;
    $insc = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}gpe_inscripciones WHERE token = %s",
        $token
    ));

    if (!$insc || $insc->estado !== 'confirmada') {
        wp_send_json_error(array('msg'=>'No encontrado o no confirmado.'));
    }

    // Comprobar caducidad (5 días después del evento)
    $fecha_evento = get_post_meta($insc->evento_id, '_med_fecha_evento', true);
    if ($fecha_evento && time() > strtotime($fecha_evento) + (5 * 86400)) {
        wp_send_json_error(array('msg'=>'QR caducado — han pasado más de 5 días desde el evento.'));
    }

    // Contador de escaneos
    $meta_key   = '_gpe_qr_scans_' . $token;
    $scan_count = (int) get_option($meta_key, 0);
    $scan_count++;
    update_option($meta_key, $scan_count, false);

    $evento = get_post($insc->evento_id);
    wp_send_json_success(array(
        'nombre'      => trim($insc->nombre . ' ' . $insc->apellidos),
        'email'       => $insc->email,
        'evento'      => $evento ? $evento->post_title : '—',
        'ccaa'        => $insc->ccaa      ?? '',
        'provincia'   => $insc->provincia ?? '',
        'fecha_reg'   => date('d/m/Y H:i', strtotime($insc->fecha_reg)),
        'scan_count'  => $scan_count,
        'ya_escaneado'=> $scan_count > 1,
    ));
}

// ── Herramienta de admin: (re)crear y vincular páginas del portal por idioma ──
// Accesible en: /wp-admin/?gpe_repare_portal=1  (solo admins)
add_action('admin_init', 'gpe_repare_portal_pages');
function gpe_repare_portal_pages() {
    if ( ! isset($_GET['gpe_repare_portal']) || ! current_user_can('manage_options') ) return;
    if ( ! check_admin_referer('gpe_repare_portal') ) return;

    $paginas = array(
        'es' => array('slug' => 'mi-territorio',   'titulo' => 'Mi Territorio'),
        'ca' => array('slug' => 'el-meu-territori', 'titulo' => 'El Meu Territori'),
        'gl' => array('slug' => 'o-meu-territorio', 'titulo' => 'O Meu Territorio'),
        'eu' => array('slug' => 'nire-lurraldea',   'titulo' => 'Nire Lurraldea'),
    );

    $ids = array();
    foreach ($paginas as $lang => $datos) {
        $page = get_page_by_path($datos['slug']);
        if (!$page) {
            $id = wp_insert_post(array(
                'post_title'     => $datos['titulo'],
                'post_name'      => $datos['slug'],
                'post_content'   => '[gpe_portal_coordinador]',
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
            ));
        } else {
            $id = $page->ID;
        }
        if ($id && !is_wp_error($id)) {
            $ids[$lang] = $id;
            if (function_exists('pll_set_post_language')) {
                pll_set_post_language($id, $lang);
            }
        }
    }
    if (function_exists('pll_save_post_translations') && count($ids) > 1) {
        pll_save_post_translations($ids);
    }
    update_option('gpe_portal_page_ids', $ids);

    wp_redirect(add_query_arg('gpe_repare_ok', '1', admin_url('admin.php?page=gpe-dashboard')));
    exit;
}

// ── Aviso en el dashboard si las páginas del portal no están vinculadas ────────
add_action('admin_notices', 'gpe_notice_portal_idiomas');
function gpe_notice_portal_idiomas() {
    if (!current_user_can('manage_options')) return;
    if (!function_exists('pll_the_languages')) return; // Solo si Polylang está activo
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'gpe-dashboard') === false) return;

    $ids = get_option('gpe_portal_page_ids', array());
    $idiomas = array('es','ca','gl','eu');
    $faltan = array_diff($idiomas, array_keys($ids));

    if (!empty($faltan) || isset($_GET['gpe_repare_ok'])) {
        if (isset($_GET['gpe_repare_ok'])) {
            echo '<div class="notice notice-success is-dismissible"><p>✅ Páginas del portal territorial creadas y vinculadas en Polylang correctamente.</p></div>';
        } else {
            $url = wp_nonce_url(add_query_arg('gpe_repare_portal','1', admin_url()), 'gpe_repare_portal');
            echo '<div class="notice notice-warning"><p><strong>GP Eventik:</strong> Las páginas del portal territorial no están todas vinculadas por idioma en Polylang. <a href="' . esc_url($url) . '" class="button button-small">Crear y vincular ahora</a></p></div>';
        }
    }
}
