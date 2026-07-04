<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Página de ajustes (Settings > Aegis Filter) para configurar la URL del
 * sitio Aegis Filter y la Integration Key del canal "wordpress".
 */
class Aegis_Filter_Settings
{
    private const OPTION_NAME = 'aegis_filter_settings';

    public static function init(): void
    {
        add_action('admin_menu', [self::class, 'register_menu']);
        add_action('admin_init', [self::class, 'register_settings']);
    }

    public static function register_menu(): void
    {
        add_options_page(
            'Aegis Filter',
            'Aegis Filter',
            'manage_options',
            'aegis-filter',
            [self::class, 'render_settings_page']
        );
    }

    public static function register_settings(): void
    {
        register_setting('aegis_filter_settings_group', self::OPTION_NAME, [
            'sanitize_callback' => [self::class, 'sanitize_settings'],
        ]);

        add_settings_section(
            'aegis_filter_main_section',
            'Conexión con Aegis Filter',
            function () {
                echo '<p>Genera una Integration Key para el canal <code>wordpress</code> desde el panel de administración de Aegis Filter (Admin &gt; Integration Keys), y pégala aquí.</p>';
            },
            'aegis-filter'
        );

        add_settings_field(
            'api_url',
            'URL del sitio Aegis Filter',
            [self::class, 'render_api_url_field'],
            'aegis-filter',
            'aegis_filter_main_section'
        );

        add_settings_field(
            'api_key',
            'Integration Key',
            [self::class, 'render_api_key_field'],
            'aegis-filter',
            'aegis_filter_main_section'
        );
    }

    public static function sanitize_settings($input): array
    {
        return [
            'api_url' => isset($input['api_url']) ? sanitize_text_field(rtrim($input['api_url'], '/')) : '',
            'api_key' => isset($input['api_key']) ? sanitize_text_field($input['api_key']) : '',
        ];
    }

    /**
     * @return array{api_url: string, api_key: string}
     */
    public static function get_settings(): array
    {
        $defaults = ['api_url' => '', 'api_key' => ''];
        $stored = get_option(self::OPTION_NAME, []);

        return array_merge($defaults, is_array($stored) ? $stored : []);
    }

    public static function render_api_url_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<input type="url" name="%1$s[api_url]" value="%2$s" class="regular-text" placeholder="https://aegis-filter.sytes.net" />',
            esc_attr(self::OPTION_NAME),
            esc_attr($settings['api_url'])
        );
    }

    public static function render_api_key_field(): void
    {
        $settings = self::get_settings();
        printf(
            '<input type="password" name="%1$s[api_key]" value="%2$s" class="regular-text" autocomplete="off" />',
            esc_attr(self::OPTION_NAME),
            esc_attr($settings['api_key'])
        );
    }

    public static function render_settings_page(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Aegis Filter</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('aegis_filter_settings_group');
                do_settings_sections('aegis-filter');
                submit_button('Guardar cambios');
                ?>
            </form>
        </div>
        <?php
    }
}
