<?php

// No direct access allowed
if (!defined('ABSPATH')) exit;

class ClarifyIP_Api
{
    public static function init()
    {
        if (!is_user_logged_in()) {
            self::check_geo();
        }
    }

    /**
     * Try to grab IP
     * @return null or string
     */
    private static function getUserIpAddr()
    {
        $ip = null;
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ",")) {
                $explode = explode(",", sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']));
                $ip = end($explode);
            } else {
                $ip = sanitize_text_field($_SERVER['HTTP_X_FORWARDED_FOR']);
            }
        } else {
            $ip = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        }

        return trim($ip);
    }

    /**
     * Call ClarifyIP api and compare with clients Geo location
     */
    public static function check_geo()
    {
        $options = get_option('clarifyip_plugin_options');

        $api_key = isset($options['api_key']) ? $options['api_key'] : null;
        $page_redirect_id = isset($options['page']) ? $options['page'] : null;
        $mode = isset($options['mode']) ? $options['mode'] : 'off';
        $country_list = isset($options['country_list']) ? $options['country_list'] : [];
        $msg = isset($options['msg']) ? $options['msg'] : 'Content is not available in your country.';
        $ip = self::getUserIpAddr();
        $current_page_id = null;
        $current_page = get_page_by_path(esc_html(sanitize_text_field($_SERVER['REQUEST_URI'])));

        if (isset($current_page->ID)) {
            $current_page_id = $current_page->ID;
        }

        //If any of params is missing return
        if (!$api_key || $mode == 'off' || empty($country_list) || !$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return;
        }

        //Prevent redirect to itself
        if ($page_redirect_id && ($page_redirect_id == $current_page_id)) {
            return;
        }

        try {
            $response = WpOrg\Requests\Requests::get(
                'https://api.clarifyip.com?key=' . esc_html(sanitize_text_field($options['api_key'])) . '&ip=' . esc_html($ip) . '&src=wp',
                array('Referer' => get_bloginfo('wpurl'))
            );

            if ($response->status_code == 200) {
                $response = json_decode($response->body, true);
                if (!isset($response['error'])) {
                    if (isset($response['country']['code'])) {
                        // Block mode
                        if ($options['mode'] == 'block') {
                            if (in_array($response['country']['code'], $country_list)) {
                                if ($page_redirect_id) {
                                    wp_redirect(get_permalink($page_redirect_id));
                                    die();
                                } else {
                                    wp_die(esc_html($msg));
                                }
                            }
                        }
                        // Allow mode
                        if ($options['mode'] == 'allow') {
                            if (!in_array($response['country']['code'], $country_list)) {
                                if ($page_redirect_id) {
                                    wp_redirect(get_permalink($page_redirect_id));
                                    die();
                                } else {
                                    wp_die(esc_html($msg));
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            // Empty because visitors must not see any errors
        }
    }
}
