<?php
/**
 * Plugin Name: ITD Salgsadministration – Ledelsesdemo
 * Description: Låst WordPress-præsentation med fiktive salgsdata, vist lokalt på /administration.
 * Version: 1.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: itd-ledelsesdemo
 */
if (!defined('ABSPATH')) { exit; }

final class ITD_Ledelsesdemo {
    const SHORTCODE = 'itd_ledelsesdemo';

    public static function init() {
        add_shortcode(self::SHORTCODE, array(__CLASS__, 'shortcode'));
        add_filter('template_include', array(__CLASS__, 'template_include'), 100);
        add_action('admin_notices', array(__CLASS__, 'admin_notice'));
    }

    public static function activate() {
        // Existing /administration is never overwritten or modified.
        $existing = get_page_by_path('administration');
        if ($existing) {
            update_option('itd_ledelsesdemo_existing_page', (int) $existing->ID, false);
            return;
        }
        $page_id = wp_insert_post(array(
            'post_type' => 'page',
            'post_title' => 'Administration',
            'post_name' => 'administration',
            'post_status' => 'publish',
            'post_content' => '[' . self::SHORTCODE . ']',
            'post_author' => get_current_user_id(),
            'comment_status' => 'closed',
            'ping_status' => 'closed'
        ), true);
        if (!is_wp_error($page_id)) {
            update_post_meta($page_id, '_itd_ledelsesdemo_created', '1');
            update_option('itd_ledelsesdemo_page_id', (int) $page_id, false);
        }
    }

    private static function is_demo_page() {
        if (!is_page('administration')) { return false; }
        $page = get_queried_object();
        return ($page instanceof WP_Post) && has_shortcode($page->post_content, self::SHORTCODE);
    }

    public static function template_include($template) {
        if (self::is_demo_page()) {
            return plugin_dir_path(__FILE__) . 'templates/standalone.php';
        }
        return $template;
    }

    public static function shortcode($atts = array()) {
        $url = esc_url(plugins_url('presentation.html', __FILE__));
        $title = esc_attr('ITD Salgsadministration – låst ledelsesdemo med fiktive data');
        return '<div style="width:100%;min-height:720px;height:95vh;height:95dvh;overflow:hidden">'
            . '<iframe src="' . $url . '" title="' . $title . '" loading="eager" '
            . 'style="display:block;width:100%;height:100%;border:0;background:#e6ecf4" '
            . 'referrerpolicy="no-referrer" allow="fullscreen"></iframe></div>';
    }

    public static function admin_notice() {
        if (!current_user_can('manage_options')) { return; }
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$screen || $screen->id !== 'plugins') { return; }
        $existing = (int) get_option('itd_ledelsesdemo_existing_page', 0);
        $page = get_page_by_path('administration');
        if ($existing && $page && !has_shortcode($page->post_content, self::SHORTCODE)) {
            echo '<div class="notice notice-info"><p><strong>ITD Ledelsesdemo:</strong> Siden /administration findes allerede og er ikke ændret. Indsæt kortkoden <code>['
                . esc_html(self::SHORTCODE) . ']</code> på denne side for at vise demoen.</p></div>';
        }
    }
}
register_activation_hook(__FILE__, array('ITD_Ledelsesdemo', 'activate'));
add_action('plugins_loaded', array('ITD_Ledelsesdemo', 'init'));
