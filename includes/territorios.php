<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Lista canónica de CCAA y sus provincias.
 * Tabla de datos geográficos usada por los selectores de territorio de
 * eventos e inscripciones. No tiene relación con los órganos de gobierno
 * (esos los gestiona GP Ambassadors).
 */
function gpe_territorios_espana() {
    return array(
        'Andalucía'          => array('Almería','Cádiz','Córdoba','Granada','Huelva','Jaén','Málaga','Sevilla'),
        'Aragón'             => array('Huesca','Teruel','Zaragoza'),
        'Asturias'           => array('Asturias'),
        'Islas Baleares'     => array('Mallorca','Menorca','Ibiza','Formentera'),
        'Canarias'           => array('Gran Canaria','Tenerife','Lanzarote','Fuerteventura','La Palma','La Gomera','El Hierro'),
        'Cantabria'          => array('Cantabria'),
        'Castilla-La Mancha' => array('Albacete','Ciudad Real','Cuenca','Guadalajara','Toledo'),
        'Castilla y León'    => array('Ávila','Burgos','León','Palencia','Salamanca','Segovia','Soria','Valladolid','Zamora'),
        'Cataluña'           => array('Barcelona','Girona','Lleida','Tarragona'),
        'Extremadura'        => array('Badajoz','Cáceres'),
        'Galicia'            => array('A Coruña','Lugo','Ourense','Pontevedra'),
        'La Rioja'           => array('La Rioja'),
        'Madrid'             => array('Madrid'),
        'Murcia'             => array('Murcia'),
        'Navarra'            => array('Navarra'),
        'País Vasco'         => array('Álava','Gipuzkoa','Bizkaia'),
        'Valencia'           => array('Alicante','Castellón','Valencia'),
        'Ceuta'              => array('Ceuta'),
        'Melilla'            => array('Melilla'),
    );
}