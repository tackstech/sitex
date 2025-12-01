<?php
/**
 * Plugin Name: BotsMap - XML Sitemap Manager
 * Plugin URI: https://www.wpvedam.com/botsmap-xml-sitemap-manager/
 * Description: Control which sections of your WordPress sitemap are visible to search engines. 
 * Version: 2.2
 * Author: Bhumika Goel
 * Author URI: https://www.wpvedam.com
 *License: GPLv2 or later
 *License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *Text Domain: sitex
 */

// Prevent direct access
if ( ! defined('ABSPATH') ) {
    exit;
}

add_action('admin_enqueue_scripts', function() {
    define('BOTSMAP_VERSION', '1.3');

wp_enqueue_style(
                    'botsmap-toggle-style',
                    plugin_dir_url(__FILE__) . 'botsmap-toggle.css',
                    array(),
                    BOTSMAP_VERSION
                );
});

include_once plugin_dir_path(__FILE__) . 'exclude-posts.php';

class botsmap_Sitemap_Manager {

    public function __construct() {
        add_action('admin_init', array($this, 'botsmap_register_settings'));
        add_action('admin_menu', array($this, 'botsmap_add_admin_menu'));
     
        // Disable sitemap sections
        add_filter('wp_sitemaps_add_provider', array($this, 'filter_sitemap_providers'), 10, 2);
        add_filter('wp_sitemaps_post_types', array($this, 'filter_post_types_list'));
    }

    // Register settings
    public function botsmap_register_settings() {
       register_setting('botsmap_settings_group', 'botsmap_options', array($this, 'sanitize_options'));
    }

    public function sanitize_options($input) {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__( 'Unauthorized access.', 'sitex'));
        }

        if (!isset($_POST['botsmap_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['botsmap_nonce'])), 'botsmap_save_settings')) {
            wp_die(esc_html__( 'Security check failed.', 'sitex'));
        }

        $output = array();

        if (isset($input['checkbox_group']) && is_array($input['checkbox_group'])) {
            $valid_keys = array('option_1', 'option_2', 'option_3', 'option_4', 'option_5');
            $output['checkbox_group'] = array_map(
                                                'sanitize_text_field',
                                                array_intersect(
                                                    wp_unslash($input['checkbox_group']),
                                                    $valid_keys
                                                )
                                            );

        }

        return $output;
    }

    // Add admin menu
    public function botsmap_add_admin_menu() {
        add_options_page(
            'BotsMap XML Sitemap Manager',
            'BotsMap',
            'manage_options',
            'botsmap-sitemap-toggle-manager',
            array($this, 'botsmap_settings_page')
        );
    }


    // Render settings page
    public function botsmap_settings_page() {
        if (!current_user_can('manage_options')) {
        wp_die(esc_html__( 'You do not have sufficient permissions to access this page.', 'sitex'));
    }


        $options = get_option('botsmap_options');
        $choices = array(
            'option_1' => 'Disable Posts Sitemap',
            'option_2' => 'Disable Pages Sitemap',
            'option_3' => 'Disable Users Sitemap',
            'option_4' => 'Disable Categories Sitemap',
            'option_5' => 'Disable Tags Sitemap',
        
        );
        ?>
        <div class="wrap">
            <h1>Manage your website's Sitemap Visibility Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('botsmap_settings_group');  
                        wp_nonce_field('botsmap_save_settings', 'botsmap_nonce');
                ?>
                <table class="form-table">
                    <?php foreach ($choices as $key => $label): ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($label); ?></th>
                            <td>
                                <label class="botsmap-toggle">
                                    <input type="checkbox" name="botsmap_options[checkbox_group][]" value="<?php echo esc_attr($key); ?>"
                                        <?php if (isset($options['checkbox_group']) && in_array($key, $options['checkbox_group'])) echo 'checked="checked"'; ?> />
                                    <span class="botsmap-slider"></span>
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // Disable sitemap providers based on settings
   public function filter_sitemap_providers($provider, $name) {
    $options = get_option('botsmap_options');
    $disabled = $options['checkbox_group'] ?? [];
    // Disable Users
    if ($name === 'users' && in_array('option_3', $disabled)) {
        return false;
    }

    // Disable Taxonomies (Categories and Tags)
    if ($name === 'taxonomies') {
        $disable_category = in_array('option_4', $disabled);
        $disable_tag = in_array('option_5', $disabled);
   

        // If both are disabled, remove the entire taxonomy provider
        if ($disable_category && $disable_tag) {
            return false;
        }

        // Otherwise, filter out specific taxonomies
        add_filter('wp_sitemaps_taxonomies', function($taxonomies) use ($disable_category, $disable_tag) {
            unset($taxonomies['post_format']);
            if ($disable_category) {
                unset($taxonomies['category']);
            }
            if ($disable_tag) {
                unset($taxonomies['post_tag']);
            }
            
            return $taxonomies;
        }, 10, 1);
    }

    return $provider;
    }
    public function filter_post_types_list($post_types) {
        $options = get_option('botsmap_options');
        $disabled = $options['checkbox_group'] ?? 'on';

        if (in_array('option_1', $disabled)) {
            unset($post_types['post']);
        }
        if (in_array('option_2', $disabled)) {
            unset($post_types['page']);
        }

        return $post_types;
    }
}

// Instantiate the class
new botsmap_Sitemap_Manager();