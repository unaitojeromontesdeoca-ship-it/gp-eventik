<?php
/**
 * {{NAME}} Build Script
 *
 * Réplica del build de GP Ambassadors, adaptado a {{NAME}}.
 * Genera {{PREFIX}}-plugin.json y el ZIP {{SLUG}}.zip (carpeta interna {{SLUG}}/),
 * e inyecta la versión en la cabecera del plugin y en la constante GPS_VERSION.
 *
 * Uso:  php scripts/build.php 0.0.1
 */

defined('STDIN') || define('STDIN', fopen('php://stdin', 'r'));

class {{NAMESPACE}}_Builder {
    private $plugin_dir;
    private $version;
    private $plugin_slug = '{{SLUG}}';
    private $plugin_file = '{{SLUG}}.php';
    private $json_file   = '{{PREFIX}}-plugin.json';
    private $output_dir  = 'dist';
    private $zip_file    = '{{SLUG}}.zip';

    public function __construct() {
        $this->plugin_dir = dirname(__DIR__);
        $this->version = $this->get_version_from_args();
    }

    private function get_version_from_args() {
        global $argv;

        if (isset($argv[1]) && preg_match('/^\d+\.\d+\.\d+$/', $argv[1])) {
            return $argv[1];
        }

        $env_version = getenv('GITHUB_REF');
        if ($env_version && preg_match('/refs\/tags\/v(\d+\.\d+\.\d+)/', $env_version, $matches)) {
            return $matches[1];
        }

        $this->error('No se ha indicado una versión válida. Uso: php build.php 0.0.1');
        exit(1);
    }

    public function build() {
        $this->echo_step('Iniciando build de {{NAME}}...');
        $this->echo_step("Versión: {$this->version}");

        $this->create_output_directory();
        $this->copy_plugin_files();
        $this->update_plugin_header();
        $this->update_version_constant();
        $this->generate_plugin_json();

        // Copia del JSON en la raíz de dist para el despliegue.
        $json_source = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->plugin_slug . '/' . $this->json_file;
        $json_dest   = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->json_file;
        copy($json_source, $json_dest);
        $this->echo_step("Copiado {$this->json_file} a la raíz de dist para despliegue");

        $this->create_zip_file();

        $this->echo_step('Build completado correctamente.');
        $this->echo_step("ZIP: {$this->output_dir}/{$this->zip_file}");
    }

    private function create_output_directory() {
        $output_path = $this->plugin_dir . '/' . $this->output_dir;
        if (file_exists($output_path)) {
            $this->remove_directory($output_path);
        }
        if (!mkdir($output_path, 0755, true)) {
            $this->error("No se pudo crear el directorio de salida: $output_path");
            exit(1);
        }
        $this->echo_step("Directorio de salida creado: $output_path");
    }

    private function copy_plugin_files() {
        $source = $this->plugin_dir;
        $dest   = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->plugin_slug;

        if (!mkdir($dest, 0755, true)) {
            $this->error("No se pudo crear el directorio del plugin: $dest");
            exit(1);
        }

        // Solo archivos de producción.
        $items_to_copy = [
            '{{SLUG}}.php',
            'includes',
            'admin',
            'assets',
            'composer.json',
            'README.md',
            'LICENSE',
        ];

        foreach ($items_to_copy as $item) {
            $source_path = $source . '/' . $item;
            $dest_path   = $dest . '/' . $item;

            if (file_exists($source_path)) {
                if (is_dir($source_path)) {
                    $this->copy_directory($source_path, $dest_path);
                } else {
                    copy($source_path, $dest_path);
                }
                $this->echo_step("Copiado: $item");
            }
        }
    }

    private function copy_directory($source, $dest) {
        if (!file_exists($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (scandir($source) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $source_path = $source . '/' . $item;
            $dest_path   = $dest . '/' . $item;
            if (is_dir($source_path)) {
                $this->copy_directory($source_path, $dest_path);
            } else {
                copy($source_path, $dest_path);
            }
        }
    }

    private function remove_directory($path) {
        if (!file_exists($path)) {
            return;
        }
        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $item_path = $path . '/' . $item;
            if (is_dir($item_path)) {
                $this->remove_directory($item_path);
            } else {
                unlink($item_path);
            }
        }
        rmdir($path);
    }

    private function update_plugin_header() {
        $plugin_file = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->plugin_slug . '/' . $this->plugin_file;
        $content = file_get_contents($plugin_file);

        $content = preg_replace(
            '/\* Version:\s*(?:\d+\.\d+\.\d+-dev|\d+\.\d+\.\d+)/',
            '* Version:            ' . $this->version,
            $content
        );

        file_put_contents($plugin_file, $content);
        $this->echo_step("Cabecera Version actualizada a: {$this->version}");
    }

    private function update_version_constant() {
        $plugin_file = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->plugin_slug . '/' . $this->plugin_file;
        $content = file_get_contents($plugin_file);

        $content = preg_replace(
            '/define\(\'GPS_VERSION\', \'(?:\d+\.\d+\.\d+-dev|\d+\.\d+\.\d+)\'\)/',
            "define('GPS_VERSION', '{$this->version}')",
            $content
        );

        file_put_contents($plugin_file, $content);
        $this->echo_step("Constante GPS_VERSION actualizada a: {$this->version}");
    }

    private function generate_plugin_json() {
        $json_data = [
            'name' => '{{NAME}}',
            'slug' => $this->plugin_slug,
            'version' => $this->version,
            'download_url' => 'https://generacionpresente.org/wp-content/updates/' . $this->zip_file,
            'url' => 'https://generacionpresente.org',
            'author' => 'Generación Presente',
            'author_profile' => 'https://generacionpresente.org',
            'requires' => '6.0',
            'tested' => '6.8',
            'requires_php' => '8.0',
            'last_updated' => date('Y-m-d'),
            'icons' => [
                '1x' => 'https://generacionpresente.org/wp-content/updates/assets/icon-128x128.png',
                '2x' => 'https://generacionpresente.org/wp-content/updates/assets/icon-256x256.png',
            ],
            'banners' => [
                'low'  => 'https://generacionpresente.org/wp-content/updates/assets/banner-772x250.png',
                'high' => 'https://generacionpresente.org/wp-content/updates/assets/banner-1544x500.png',
            ],
            'sections' => [
                'description' => '{{DESC}}',
                'changelog' => '=== ' . $this->version . ' ===' . "\n" .
                               '- Actualización automática desde GitHub Actions' . "\n" .
                               '- Mejoras en el sistema de actualizaciones' . "\n" .
                               '- Corrección de bugs menores',
            ],
        ];

        $json_content = json_encode($json_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $json_path = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->plugin_slug . '/' . $this->json_file;

        file_put_contents($json_path, $json_content);
        $this->echo_step("Generado {$this->json_file} con versión: {$this->version}");
    }

    private function create_zip_file() {
        $zip = new ZipArchive();
        $zip_path   = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->zip_file;
        $source_dir = $this->plugin_dir . '/' . $this->output_dir . '/' . $this->plugin_slug;

        if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("No se pudo crear el ZIP: $zip_path");
            exit(1);
        }

        $zip->addEmptyDir($this->plugin_slug);
        $this->add_to_zip($zip, $source_dir, $this->plugin_slug . '/');
        $zip->close();

        $this->echo_step("ZIP creado: " . $this->zip_file);
        $this->echo_step("Tamaño ZIP: " . filesize($zip_path) . " bytes");
    }

    private function add_to_zip($zip, $source, $relative_path) {
        $source = rtrim($source, '/') . '/';
        $relative_path = rtrim($relative_path, '/') . '/';

        foreach (scandir($source) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $item_path = $source . $item;
            $zip_dest  = $relative_path . $item;

            if (is_dir($item_path)) {
                $zip->addEmptyDir($zip_dest);
                $this->add_to_zip($zip, $item_path, $zip_dest);
            } else {
                $zip->addFile($item_path, $zip_dest);
            }
        }
    }

    private function error($message) {
        fwrite(STDERR, "ERROR: $message\n");
    }

    private function echo_step($message) {
        echo "📦 $message\n";
    }
}

$builder = new {{NAMESPACE}}_Builder();
$builder->build();
