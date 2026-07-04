<?php
/**
 * Plugin Name:       Aegis Filter
 * Plugin URI:        https://github.com/UPT-FAING-EPIS/proyecto-si784-2026-i-u1-antispam
 * Description:       Filtra comentarios de spam delegando el análisis al motor antispam de Aegis Filter (canal "wordpress" autenticado por Integration Key).
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:             Aegis Filter Team
 * License:            GPL v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        aegis-filter
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AEGIS_FILTER_VERSION', '1.0.0');
define('AEGIS_FILTER_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once AEGIS_FILTER_PLUGIN_DIR . 'includes/class-aegis-filter-client.php';
require_once AEGIS_FILTER_PLUGIN_DIR . 'includes/class-aegis-filter-settings.php';

/**
 * Decide el estado inicial de un comentario nuevo consultando al
 * motor antispam de Aegis Filter.
 *
 * Se conecta al filtro `pre_comment_approved`, el mismo punto que usa
 * Akismet: si devolvemos 'spam', WordPress inserta el comentario
 * directamente en la bandeja de spam (Comentarios > Spam), sin
 * necesidad de una segunda consulta para actualizar su estado.
 *
 * Si la API no responde o no hay credenciales configuradas, el
 * comentario sigue su flujo normal (fail-open): nunca bloqueamos
 * comentarios legítimos por una caída del servicio externo.
 *
 * @param int|string|\WP_Error $approved   Estado por defecto calculado por WordPress.
 * @param array                $commentdata Datos del comentario enviado.
 * @return int|string|\WP_Error
 */
function aegis_filter_check_comment($approved, $commentdata)
{
    $settings = Aegis_Filter_Settings::get_settings();

    if (empty($settings['api_url']) || empty($settings['api_key'])) {
        return $approved;
    }

    $client = new Aegis_Filter_Client($settings['api_url'], $settings['api_key']);

    $result = $client->check_spam(
        $commentdata['comment_content'] ?? '',
        $commentdata['comment_author'] ?? ''
    );

    if ($result === null) {
        // La API no respondió o falló: no bloqueamos al usuario por eso.
        return $approved;
    }

    return $result['isSpam'] ? 'spam' : $approved;
}
add_filter('pre_comment_approved', 'aegis_filter_check_comment', 10, 2);

Aegis_Filter_Settings::init();
