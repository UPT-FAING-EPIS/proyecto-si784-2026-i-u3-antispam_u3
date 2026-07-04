# Aegis Filter – Plugin de WordPress

Filtra comentarios de spam en WordPress delegando el análisis al motor
antispam de [Aegis Filter](https://github.com/UPT-FAING-EPIS/proyecto-si784-2026-i-u1-antispam),
de la misma forma que Akismet: cada comentario nuevo se envía al endpoint
`/api/integrations/check-spam` y, si se detecta como spam, se inserta
directamente en la bandeja de spam de WordPress.

Si la API de Aegis Filter no responde (caída, timeout, credenciales mal
configuradas), el plugin **no bloquea comentarios legítimos**: el
comentario sigue su flujo normal de moderación de WordPress.

## Requisitos

- WordPress 5.8 o superior.
- PHP 7.4 o superior.
- Un sitio Aegis Filter accesible por HTTPS, con una Integration Key
  emitida para el canal `wordpress`.

## Instalación

1. Copia la carpeta `aegis-filter/` dentro de `wp-content/plugins/` de tu
   instalación de WordPress.
2. En el admin de WordPress, ve a **Plugins** y activa "Aegis Filter".
3. Ve a **Ajustes > Aegis Filter** y completa:
   - **URL del sitio Aegis Filter**: ej. `https://aegis-filter.sytes.net`.
   - **Integration Key**: generada desde el panel de Aegis Filter en
     **Admin > Integration Keys**, eligiendo el canal `wordpress`.
4. Guarda los cambios. A partir de ese momento, todo comentario nuevo se
   analiza automáticamente.

## Cómo emitir la Integration Key

En el panel de administración de Aegis Filter (`/admin/integration-keys`,
requiere sesión de admin):

1. Selecciona el canal `wordpress`.
2. (Opcional) Agrega una etiqueta, ej. "Blog producción".
3. Genera la key y cópiala de inmediato — solo se muestra una vez.

## Revocar acceso

Si necesitas desactivar la integración (ej. cambio de sitio, sospecha de
filtración de la key), revócala desde `/admin/integration-keys` sin
necesidad de desinstalar el plugin: las llamadas empezarán a fallar de
forma segura (fail-open, sin bloquear comentarios) hasta que configures
una key nueva.

## Estructura

```
aegis-filter/
├── aegis-filter.php                          # Bootstrap del plugin, hook pre_comment_approved
├── includes/
│   ├── class-aegis-filter-client.php         # Cliente HTTP hacia la API
│   └── class-aegis-filter-settings.php       # Página de ajustes (Settings > Aegis Filter)
└── README.md
```
