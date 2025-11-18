<?php
/**
 * Plugin Name: Sitex
 * Description: Control which sections of your WordPress sitemap are visible to search engines. 
 * Version: 1.3
 * Author: Bhumika Goel
 * Author URI: https://www.wpvedam.com
 *License: GPLv2 or later
 *License URI: https://www.gnu.org/licenses/gpl-2.0.html

 */

// Prevent direct access
if ( ! defined('ABSPATH') ) {
    exit;
}
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('sitex-toggle-style', plugin_dir_url(__FILE__) . 'sitex-toggle.css');
});
class Sitex_Sitemap_Manager {

    public function __construct() {
        add_action('admin_init', array($this, 'sitex_register_settings'));
        add_action('admin_menu', array($this, 'sitex_add_admin_menu'));
     
        // Disable sitemap sections
        add_filter('wp_sitemaps_add_provider', array($this, 'filter_sitemap_providers'), 10, 2);
        add_filter('wp_sitemaps_post_types', array($this, 'filter_post_types_list'));
    }

    // Register settings
    public function sitex_register_settings() {
       register_setting('sitex_settings_group', 'sitex_options', array($this, 'sanitize_options'));
    }

    public function sanitize_options($input) {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.'));
        }

        if (!isset($_POST['sitex_nonce']) || !wp_verify_nonce($_POST['sitex_nonce'], 'sitex_save_settings')) {
            wp_die(__('Security check failed.'));
        }

        $output = array();

        if (isset($input['checkbox_group']) && is_array($input['checkbox_group'])) {
            $valid_keys = array('option_1', 'option_2', 'option_3', 'option_4', 'option_5');
            $output['checkbox_group'] = array_map('sanitize_text_field', array_intersect($input['checkbox_group'], $valid_keys));
        }

        return $output;
    }

    // Add admin menu
    public function sitex_add_admin_menu() {
        add_options_page(
            'Sitemap Toggle Manager',
            'Sitemap Manager',
            'manage_options',
            'sitemap-toggle-manager',
            array($this, 'sitex_settings_page')
        );
    }


    // Render settings page
    public function sitex_settings_page() {
        if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }


        $options = get_option('sitex_options');
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
                <?php settings_fields('sitex_settings_group');  
                        wp_nonce_field('sitex_save_settings', 'sitex_nonce');
                ?>
                <table class="form-table">
                    <?php foreach ($choices as $key => $label): ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($label); ?></th>
                            <td>
                                <label class="sitex-toggle">
                                    <input type="checkbox" name="sitex_options[checkbox_group][]" value="<?php echo esc_attr($key); ?>"
                                        <?php if (isset($options['checkbox_group']) && in_array($key, $options['checkbox_group'])) echo 'checked="checked"'; ?> />
                                    <span class="sitex-slider"></span>
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
    $options = get_option('sitex_options');
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
        $options = get_option('sitex_options');
        $disabled = $options['checkbox_group'] ?? [];

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
new Sitex_Sitemap_Manager();
