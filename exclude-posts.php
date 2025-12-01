<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class botsmap_Exclude_From_Native_Sitemap {
    const META_KEY = '_botsmap_exclude_from_sitemap';

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'save_meta_box']);
        add_filter('wp_sitemaps_posts_query_args', [$this, 'filter_sitemap_query'], 10, 2);
    }

    /** Add meta box to post and page editors */
    public function add_meta_box() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        foreach (['post', 'page'] as $type) {
            add_meta_box(
                'exclude_from_sitemap',
                esc_html__('Exclude from Sitemap', 'sitex'),
                [$this, 'render_meta_box'],
                $type,
                'side',
                'default'
            );
        }
    }

    /** Render the checkbox in the meta box */
    public function render_meta_box($post) {
        $value = get_post_meta($post->ID, self::META_KEY, true);
        wp_nonce_field('exclude_from_sitemap_nonce_action', 'exclude_from_sitemap_nonce');
        ?>
        <label for="exclude_from_sitemap">
            <input type="checkbox" name="exclude_from_sitemap" id="exclude_from_sitemap" value="1" <?php checked($value, '1'); ?> />
            <?php esc_html__('Exclude this content from the default sitemap', 'sitex'); ?>
        </label>
        <?php
    }

    /** Save the checkbox value securely */
    public function save_meta_box($post_id) {
        if (!isset($_POST['exclude_from_sitemap_nonce']) || !wp_verify_nonce(sanitize_text_field( wp_unslash($_POST['exclude_from_sitemap_nonce'])), 'exclude_from_sitemap_nonce_action')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $value = (!empty($_POST['exclude_from_sitemap']) && $_POST['exclude_from_sitemap'] === '1') ? '1' : '';
        update_post_meta($post_id, self::META_KEY, $value);
    }

    /** Modify the default sitemap query to exclude marked posts/pages */
    public function filter_sitemap_query($args, $post_type) {
        // Only apply to posts and pages
        if (in_array($post_type, ['post', 'page'], true)) {
            // Exclude only if the meta key exists and is set to '1'
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => self::META_KEY,
                    'compare' => 'NOT EXISTS', // allow posts without the meta key
                ],
                [
                    'key'     => self::META_KEY,
                    'value'   => '1',
                    'compare' => '!=', // exclude only those explicitly marked
                ],
            ];
        }
        return $args;
    }
}
new botsmap_Exclude_From_Native_Sitemap();