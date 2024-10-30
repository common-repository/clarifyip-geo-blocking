<?php

// No direct access allowed
if (!defined('ABSPATH')) exit;

class ClarifyIP_Admin
{
    public static $options;

    // Settings menu page
    public static function add_admin_page()
    {
        add_options_page('ClarifyIP Settings', 'ClarifyIP Settings', 'manage_options', 'clarifyip', array('ClarifyIP_Admin', 'options_page'));
    }

    // HTML and PHP for Plugin Admin Page
    public static function options_page()
    {
        echo '<form action="options.php" method="post">';

        settings_fields('clarifyip_plugin_options');
        do_settings_sections('clarifyip');
        submit_button();

        echo '</form>';
    }

    // Options and registers
    public static function init()
    {
        // Select2
        wp_enqueue_style("clarifyip-admin", CLARIFYIP_PLUGIN_URL . '/lib/select2/select2.min.css', array(), '4.0.13', 'all');
        wp_enqueue_script('clarifyip-admin', CLARIFYIP_PLUGIN_URL . '/lib/select2/select2.min.js', array('jquery'), '4.0.13', false);

        // Init options
        $options = get_option('clarifyip_plugin_options');
        self::$options['api_key'] = $options['api_key'] ?? '';
        self::$options['mode'] = $options['mode'] ?? 'off';
        self::$options['msg'] = $options['msg'] ?? 'Content is not available in your country.';
        self::$options['page'] = $options['page'] ?? 0;
        self::$options['country_list'] = $options['country_list'] ?? [];
        self::$options['countries'] = ClarifyIP_Countries::COUNTRIES;

        // Register database options
        register_setting('clarifyip_plugin_options', 'clarifyip_plugin_options');

        // Add page title and description
        add_settings_section('api_settings', 'ClarifyIP API Settings', array('ClarifyIP_Admin', 'clarifyip_plugin_section_text'), 'clarifyip');

        // API key
        add_settings_field('clarifyip_plugin_setting_api_key', 'API Key', array('ClarifyIP_Admin', 'clarifyip_plugin_setting_api_key'), 'clarifyip', 'api_settings');

        // Mode
        add_settings_field('clarifyip_plugin_setting_mode', 'Mode', array('ClarifyIP_Admin', 'clarifyip_plugin_setting_mode'), 'clarifyip', 'api_settings');

        // Message
        add_settings_field('clarifyip_plugin_setting_msg', 'Message', array('ClarifyIP_Admin', 'clarifyip_plugin_setting_msg'), 'clarifyip', 'api_settings');

        // Redirect to page
        add_settings_field('clarifyip_plugin_setting_page', 'Redirect to page', array('ClarifyIP_Admin', 'clarifyip_plugin_setting_page'), 'clarifyip', 'api_settings');

        // Country list
        add_settings_field('clarifyip_plugin_setting_country_list', 'Country list', array('ClarifyIP_Admin', 'clarifyip_plugin_setting_country_list'), 'clarifyip', 'api_settings');
    }

    // Page description
    public static function clarifyip_plugin_section_text()
    {
    ?>
        <p>Here you can set all the options for using the ClarifyIP API. For more detailed info please visit our <a href="https://clarifyip.com/" target="_blank">website</a>.</p>
    <?php
    }

    // Api key Field
    public static function clarifyip_plugin_setting_api_key()
    {
    ?>
        <input class="regular-text" name="clarifyip_plugin_options[api_key]" type="text" value="<?php echo esc_attr(self::$options['api_key']) ?>" />

        <p class="description">
            Here you can get your <a href='https://platform.clarifyip.com/users/register?utm_source=<?php echo esc_url(sanitize_text_field($_SERVER['SERVER_NAME'])) ?>&utm_medium=wordpress&utm_content=wordpress_link&utm_campaign=wordpress' target='_blank'>
                Free API key
            </a>
        </p>
    <?php
    }

    // Mode Field
    public static function clarifyip_plugin_setting_mode()
    {
    ?>
        <fieldset>
            <label>
                <input type='radio' name='clarifyip_plugin_options[mode]' value='off' <?php echo self::$options['mode'] == 'off' ? 'checked' : '' ?>>
                <span>Off</span>
            </label>
            <br />

            <label>
                <input type='radio' name='clarifyip_plugin_options[mode]' value='block' <?php echo self::$options['mode'] == 'block' ? 'checked' : '' ?>>
                <span>Block</span>
            </label>
            <br />

            <label>
                <input type='radio' name='clarifyip_plugin_options[mode]' value='allow' <?php echo self::$options['mode'] == 'allow' ? 'checked' : '' ?>>
                <span>Allow</span>
            </label>
            <br />

            <p class="description">*Geo Blocking will be ignored for logged in users</p>
        </fieldset>
    <?php
    }

    // Message Field
    public static function clarifyip_plugin_setting_msg()
    {
    ?>
        <textarea class="regular-text" name="clarifyip_plugin_options[msg]" rows="4"><?php echo esc_textarea(self::$options['msg']) ?></textarea>
    <?php
    }

    // Redirect Field
    public static function clarifyip_plugin_setting_page()
    {
        wp_dropdown_pages(
            array(
                'name' => 'clarifyip_plugin_options[page]',
                'selected' => esc_attr(self::$options['page']),
                'show_option_none' => 'No',
                'option_none_value' => 0,
                'class' => 'regular-text',
            )
        );
    }

    // Country list Field
    public static function clarifyip_plugin_setting_country_list()
    {
    ?>
        <select name='clarifyip_plugin_options[country_list][]' class="regular-text select2" multiple>
            <?php foreach (self::$options['countries'] as $country_code => $country_name) : ?>
                <option value="<?php echo esc_attr($country_code) ?>" <?php echo (in_array($country_code, self::$options['country_list']) ? 'selected' : '') ?>>
                    <?php echo esc_html($country_name) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br />

        <small>
            *<span id="clarifyip_total_countries"><?php echo count(self::$options['country_list']) ?></span> countrie(s) will be <?php echo (self::$options['mode'] == 'block' ? 'blocked' : 'allowed') ?> to access to your website.
        </small>

        <script>
            jQuery(document).ready(function() {
                // Init select2
                jQuery('.select2').select2();

                // Total selected countries
                jQuery('.select2').on('change', function(e) {
                    jQuery("#clarifyip_total_countries").text(jQuery('.select2 option:selected').length);
                });
            });
        </script>
<?php
    }
}
