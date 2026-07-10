# Plantilla OTA para plugins de Generación Presente

Sistema de actualizaciones automáticas (OTA) reutilizable. Es EXACTAMENTE el
mismo OTA que usan GP Support y GP Ambassadors: solo cambian los nombres.

Para cada plugin nuevo, copias estos archivos, reemplazas 6 tokens de golpe,
pegas un snippet en tu archivo principal, y ya tienes actualizaciones solas.

---

## Los 6 tokens que hay que reemplazar

| Token            | Qué es                        | Ejemplo (GP Support)  |
|------------------|-------------------------------|-----------------------|
| `{{NAME}}`       | Nombre visible del plugin     | `GP Support`          |
| `{{SLUG}}`       | Slug / carpeta / nombre ZIP   | `gp-support`          |
| `{{PREFIX}}`     | Prefijo en minúsculas         | `gps`                 |
| `{{PREFIX_UP}}`  | Prefijo en MAYÚSCULAS         | `GPS`                 |
| `{{NAMESPACE}}`  | Namespace PHP (con guion bajo)| `GP_Support`          |
| `{{DESC}}`       | Descripción del plugin        | (una frase)           |

Regla de oro: cada plugin debe tener un **PREFIX y un SLUG únicos**, porque
todos comparten la misma carpeta `updates/` del servidor. Dos plugins con el
mismo prefijo se pisarían.

La forma más rápida y sin errores: abrir la carpeta en VS Code y usar
"Reemplazar en archivos" (Ctrl+Shift+H) una vez por cada token.

---

## Dónde va cada archivo (dentro del repo del plugin nuevo)

| Archivo de la plantilla                | Ubicación final en el plugin              |
|----------------------------------------|-------------------------------------------|
| `{{PREFIX}}-plugin.json`               | RAÍZ del repo (y renómbralo, ej. `gpx-plugin.json`) |
| `includes/update-manager.php`          | `includes/update-manager.php`             |
| `scripts/build.php`                    | `scripts/build.php`                        |
| `.github/workflows/auto-release.yml`   | `.github/workflows/auto-release.yml`       |
| `SNIPPET-archivo-principal.txt`        | NO se copia: es código para PEGAR en tu archivo principal |

Ojo: el archivo `{{PREFIX}}-plugin.json` hay que **renombrarlo** al prefijo real
(el token del nombre de archivo no se auto-reemplaza).

---

## Pasos para arrancar un plugin nuevo

1. Copia los 4 archivos a sus ubicaciones (tabla de arriba).
2. Reemplaza los 6 tokens en toda la carpeta.
3. Renombra `{{PREFIX}}-plugin.json` -> `tuprefijo-plugin.json`.
4. Pega el snippet de `SNIPPET-archivo-principal.txt` en tu archivo principal.
5. Commit + push a `main`.

El primer push crea la Release v0.0.1 con el ZIP. Descárgalo e instálalo a mano
UNA vez en WordPress (Plugins -> Añadir -> Subir). A partir de ahí, se actualiza
solo.

---

## Secrets de GitHub (IMPORTANTE)

El workflow sube al servidor por SFTP y necesita estos secrets. Como TODOS los
plugins de GP usan el mismo servidor, lo mejor es ponerlos UNA vez a nivel de
ORGANIZACIÓN en GitHub (Settings de la organización -> Secrets -> Actions), y
así cada repo nuevo los hereda sin configurar nada:

- SFTP_HOST
- SFTP_USER
- SFTP_PASSWORD
- SFTP_PORT   (opcional; si falta usa el 22)

---

## Notas

- La caché está a 60s (bien para iterar). Cuando el plugin esté estable, sube
  ese número en `includes/update-manager.php` (`$cache_expiration`) a 3600 (1h)
  o 43200 (12h) para no machacar el servidor.
- El dominio (generacionpresente.org) y los assets (iconos/banners) son
  compartidos: no hace falta cambiarlos.
