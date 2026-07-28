<?php
/**
 * GP Eventik Update Manager
 *
 * Sistema de actualizaciones OTA del plugin GP Eventik.
 * Réplica del sistema de GP Ambassadors, con nombres propios de Support.
 *
 * @package GP_Eventik
 * @subpackage Update_Manager
 */

namespace GP_Eventik\Update_Manager;

defined('ABSPATH') || exit;

/**
 * Class Update_Manager
 *
 * Gestiona las actualizaciones del plugin desde el servidor de updates.
 */
class Update_Manager {
    /** @var string Slug del plugin. */
    private $slug = 'gp-eventik';

    /** @var string plugin_basename() calculado dinámicamente. */
    private $basename;

    /** @var string Versión instalada actualmente. */
    private $current_version = '0.0.0';

    /** @var string URL del gpe-plugin.json remoto (ruta plana con nombre por plugin). */
    private $update_server_url = 'https://generacionpresente.org/wp-content/updates/gpe-plugin.json';

    /** @var string Clave del transient de caché. */
    private $cache_key = 'gpe_update_check';

    /** @var int Segundos de caché (producción: 43200 = 12h; desarrollo: 60). */
    private $cache_expiration = 43200;

    /** @var string|null Token de seguridad opcional para el servidor de updates. */
    private $security_token = null;

    /**
     * Constructor
     */
    public function __construct() {
        if (!function_exists('get_plugin_data')) {
            require_once \ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // La versión sale de la cabecera del archivo principal (fuente única de verdad).
        $plugin_data = get_plugin_data(GPE_DIR . 'gp-eventik.php', false, false);
        $this->current_version = $plugin_data['Version'] ?? '0.0.0';

        // Respaldo por la constante si fuera mayor.
        if (defined('GPE_VERSION') && version_compare(GPE_VERSION, $this->current_version, '>')) {
            $this->current_version = GPE_VERSION;
        }

        $this->basename = plugin_basename(GPE_DIR . 'gp-eventik.php');

        // En modo desarrollo, la caché baja a 60s (aunque el early-return la salte igualmente).
        if (defined('GPE_DEV_MODE') && GPE_DEV_MODE) {
            $this->cache_expiration = 60;
        }

        $this->init_hooks();
    }

    /**
     * Registra los hooks de WordPress.
     */
    private function init_hooks() {
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_updates']);
        add_filter('plugins_api', [$this, 'get_plugin_info'], 10, 3);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Registra las opciones de configuración del servidor de updates.
     */
    public function register_settings() {
        register_setting('gpe_settings', 'gpe_update_server_url', [
            'type' => 'string',
            'default' => $this->update_server_url,
            'sanitize_callback' => 'esc_url_raw',
        ]);

        register_setting('gpe_settings', 'gpe_update_security_token', [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        ]);

        $saved_url = get_option('gpe_update_server_url');
        if (!empty($saved_url)) {
            $this->update_server_url = $saved_url;
        }

        $saved_token = get_option('gpe_update_security_token');
        if (!empty($saved_token)) {
            $this->security_token = $saved_token;
        }
    }

    /**
     * Comprueba si hay actualizaciones.
     *
     * @param object $transient Transient de actualizaciones.
     * @return object Transient modificado.
     */
    public function check_for_updates($transient) {

        // Modo desarrollo: salir sin comprobar si GPS_DEV_MODE está activo.
        if (defined('GPS_DEV_MODE') && GPS_DEV_MODE) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[GPE-DEBUG] Modo desarrollo activo - se omite la comprobación OTA');
            }
            return $transient;
        }

        if (empty($transient->checked)) {
            return $transient;
        }

        if (!isset($transient->checked[$this->basename])) {
            error_log('GP SUPPORT OTA - Basename no encontrado. Esperado: ' . $this->basename);
            error_log('GP SUPPORT OTA - Disponibles: ' . implode(', ', array_keys((array) $transient->checked)));
            return $transient;
        }

        $cached_update = get_transient($this->cache_key);

        if (false === $cached_update) {
            $remote_data = $this->fetch_remote_update_data();

            if (is_wp_error($remote_data) || empty($remote_data)) {
                return $transient;
            }

            set_transient($this->cache_key, $remote_data, $this->cache_expiration);
            $cached_update = $remote_data;
        }

        if (version_compare($this->current_version, $cached_update['version'], '<')) {
            $transient->response[$this->basename] = (object) [
                'slug' => $this->slug,
                'plugin' => $this->basename,
                'new_version' => $cached_update['version'],
                'url' => $cached_update['url'] ?? '',
                'package' => $cached_update['download_url'],
                'icons' => [
                    '2x' => $cached_update['icons']['2x'] ?? '',
                    '1x' => $cached_update['icons']['1x'] ?? '',
                ],
                'banners' => [
                    '2x' => $cached_update['banners']['2x'] ?? '',
                    '1x' => $cached_update['banners']['1x'] ?? '',
                ],
                'requires' => $cached_update['requires'] ?? '6.0',
                'tested' => $cached_update['tested'] ?? '6.8',
                'requires_php' => $cached_update['requires_php'] ?? '8.0',
            ];
        }

        return $transient;
    }

    /**
     * Descarga los datos de actualización del servidor remoto.
     *
     * @return array|false
     */
    private function fetch_remote_update_data() {
        $url = $this->update_server_url;

        if (!empty($this->security_token)) {
            $url = add_query_arg('token', $this->security_token, $url);
        }

        // Cache-busting para saltar cachés de servidor/CDN.
        $url = add_query_arg([
            '_gpe_nocache' => time(),
            '_gpe_version' => $this->current_version,
        ], $url);

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => [
                'Accept' => 'application/json',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('GP Eventik Update Check Error: ' . $response->get_error_message());
            return false;
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            error_log('GP Eventik Update Check Error: código de respuesta ' . wp_remote_retrieve_response_code($response));
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('GP Eventik Update Check Error: JSON inválido - ' . json_last_error_msg());
            return false;
        }

        if (empty($data) || !isset($data['version'])) {
            error_log('GP Eventik Update Check Error: datos de actualización inválidos');
            return false;
        }

        return $data;
    }

    /**
     * Proporciona la información del modal "Ver detalles de la versión".
     */
    public function get_plugin_info($result, $action, $args) {
        if ('plugin_information' !== $action) {
            return $result;
        }

        if (empty($args->slug) || $args->slug !== $this->slug) {
            return $result;
        }

        $cached_update = get_transient($this->cache_key);

        if (false === $cached_update) {
            $remote_data = $this->fetch_remote_update_data();

            if (is_wp_error($remote_data) || empty($remote_data)) {
                return $result;
            }

            set_transient($this->cache_key, $remote_data, $this->cache_expiration);
            $cached_update = $remote_data;
        }

        $plugin_info = [
            'name' => $cached_update['name'] ?? 'GP Eventik',
            'slug' => $this->slug,
            'version' => $cached_update['version'],
            'author' => $cached_update['author'] ?? 'Generación Presente',
            'author_profile' => $cached_update['author_profile'] ?? 'https://generacionpresente.org',
            'last_updated' => $cached_update['last_updated'] ?? '',
            'requires' => $cached_update['requires'] ?? '6.0',
            'tested' => $cached_update['tested'] ?? '6.8',
            'requires_php' => $cached_update['requires_php'] ?? '8.0',
            'download_link' => $cached_update['download_url'],
            'trunk' => $cached_update['download_url'],
            'sections' => [
                'description' => $cached_update['sections']['description'] ?? '',
                'changelog' => $cached_update['sections']['changelog'] ?? '',
            ],
            'banners' => [
                'low' => $cached_update['banners']['low'] ?? '',
                'high' => $cached_update['banners']['high'] ?? '',
            ],
        ];

        return (object) $plugin_info;
    }

    /**
     * Limpia la caché de actualizaciones (fuerza un check nuevo).
     */
    public function clear_update_cache() {
        delete_transient($this->cache_key);
    }

    public function get_cache_key() {
        return $this->cache_key;
    }

    public function get_update_server_url() {
        return $this->update_server_url;
    }

    public function get_current_version() {
        return $this->current_version;
    }
}
