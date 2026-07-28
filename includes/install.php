<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function gpe_crear_tablas_db() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    // Inscripciones eventos públicos
    $sql_inscripciones = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gpe_inscripciones (
        id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        evento_id     BIGINT(20) UNSIGNED NOT NULL,
        nombre        VARCHAR(100) NOT NULL,
        apellidos     VARCHAR(100) NOT NULL,
        email         VARCHAR(200) NOT NULL,
        telefono      VARCHAR(30) DEFAULT '',
        ccaa          VARCHAR(80) DEFAULT '',
        provincia     VARCHAR(80) DEFAULT '',
        edad          TINYINT(3) UNSIGNED DEFAULT 0,
        como_conocio  VARCHAR(200) DEFAULT '',
        comentario    TEXT DEFAULT '',
        estado        ENUM('confirmada','pendiente','cancelada') DEFAULT 'confirmada',
        fecha_reg     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        token         VARCHAR(64) NOT NULL DEFAULT '',
        PRIMARY KEY (id),
        KEY evento_id (evento_id),
        KEY email (email),
        KEY estado (estado)
    ) $charset;";

    // Lista de espera
    $sql_espera = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gpe_lista_espera (
        id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        evento_id     BIGINT(20) UNSIGNED NOT NULL,
        nombre        VARCHAR(100) NOT NULL,
        email         VARCHAR(200) NOT NULL,
        fecha_reg     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        notificado    TINYINT(1) DEFAULT 0,
        PRIMARY KEY (id),
        KEY evento_id (evento_id),
        KEY email (email)
    ) $charset;";

    // Log emails
    $sql_emails = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gpe_emails_log (
        id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        evento_id     BIGINT(20) UNSIGNED NOT NULL,
        destinatario  VARCHAR(200) NOT NULL,
        asunto        VARCHAR(255) NOT NULL,
        enviado_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        estado        ENUM('enviado','error') DEFAULT 'enviado',
        PRIMARY KEY (id),
        KEY evento_id (evento_id)
    ) $charset;";

    // Órganos de gobierno
    $sql_organos = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gpe_organos (
        id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        nombre        VARCHAR(200) NOT NULL,
        tipo          VARCHAR(80) NOT NULL DEFAULT '',
        ambito        VARCHAR(80) NOT NULL DEFAULT '',
        ambito_valor  VARCHAR(80) NOT NULL DEFAULT '',
        descripcion   TEXT DEFAULT '',
        PRIMARY KEY (id)
    ) $charset;";

    // Miembros (embajadores/as) de cada órgano
    $sql_miembros = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gpe_organo_miembros (
        id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        organo_id  BIGINT(20) UNSIGNED NOT NULL,
        user_id    BIGINT(20) UNSIGNED NOT NULL,
        cargo      VARCHAR(100) DEFAULT '',
        activo     TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id),
        KEY organo_id (organo_id),
        KEY user_id (user_id),
        UNIQUE KEY organo_user (organo_id, user_id)
    ) $charset;";

    // Inscripciones a eventos internos
    $sql_interno = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}gpe_inscripciones_internas (
        id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        evento_id     BIGINT(20) UNSIGNED NOT NULL,
        user_id       BIGINT(20) UNSIGNED NOT NULL,
        dni_nie       VARCHAR(20) DEFAULT '',
        modalidad     ENUM('presencial','telematica','mixta') DEFAULT 'presencial',
        delegado_a    BIGINT(20) UNSIGNED DEFAULT NULL,
        delegado_por  BIGINT(20) UNSIGNED DEFAULT NULL,
        estado        ENUM('confirmada','pendiente','cancelada') DEFAULT 'confirmada',
        fecha_reg     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        token         VARCHAR(64) NOT NULL DEFAULT '',
        comentario    TEXT DEFAULT '',
        PRIMARY KEY (id),
        KEY evento_id (evento_id),
        KEY user_id (user_id),
        UNIQUE KEY evento_user (evento_id, user_id)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql_inscripciones );
    dbDelta( $sql_espera );
    dbDelta( $sql_emails );
    dbDelta( $sql_organos );
    dbDelta( $sql_miembros );
    dbDelta( $sql_interno );
}
