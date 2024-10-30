<?php

// No direct access allowed
if (!defined('ABSPATH')) exit;

class ClarifyIP
{
    public function __construct()
    {
        //Add settings link
        add_filter('plugin_action_links_' . CLARIFYIP_PLUGIN_BASE_FILE, array('ClarifyIP', 'settings_link'));

        // Frontend init plugin
        add_action('init', array('ClarifyIP_Api', 'init'));

        // Admin init plugin
        add_action('admin_init', array('ClarifyIP_Admin', 'init'));
        add_action('admin_menu', array('ClarifyIP_Admin', 'add_admin_page'));

        // Admin Api key check
        add_action('admin_notices', array('ClarifyIP', 'clarifyip_plugin_api_check'));

        // Plugin uninstall hook
        register_uninstall_hook(CLARIFYIP_PLUGIN_BASE_FILE, array('ClarifyIP', 'uninstall'));
    }

    // Api key check
    public static function clarifyip_plugin_api_check()
    {
        // Restrict showing only on 'clarifyip' plugin page
        if ($_SERVER['QUERY_STRING'] == 'page=clarifyip') {
            $options = get_option('clarifyip_plugin_options');

            if (!empty($options['api_key'])) {
                $response = WpOrg\Requests\Requests::get(
                    'https://api.clarifyip.com?key=' . esc_html(sanitize_text_field($options['api_key'])),
                    array('Referer' => get_bloginfo('wpurl'))
                );

                if ($response->status_code == 200) {
                    $response = json_decode($response->body, true);
                    if (isset($response['error'])) {
                        echo '<div class="notice notice-error"><p>There has been an error with your API key</p></div>';
                    }
                } else {
                    echo '<div class="notice notice-error"><p>There has been an error with your API key</p></div>';
                }
            } else {
                echo '<div class="notice notice-warning"><p>Please add your API key</p></div>';
            }
        }
    }

    // Settings link on plugin listing page
    public static function settings_link($links)
    {
        $settings_link = '<a href="/wp-admin/options-general.php?page=clarifyip">Settings</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    // Unistall plugin
    public static function uninstall()
    {
        // Delete ClarifyIP options from database
        delete_option('clarifyip_plugin_options');
    }
}
