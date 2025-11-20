<?php

class Sitex_Exclude_From_Native_Sitemap {
    const META_KEY = '_sitex_exclude_from_sitemap';

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post', [$this, 'save_meta_box']);
        add_filter('wp_sitemaps_posts_query_args', [$this, 'filter_sitemap_query'], 10, 2);
    }

    /**
     * Add meta box to post and page editors.
     */
    public function add_meta_box() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        foreach (['post', 'page'] as $type) {
            add_meta_box(
                'exclude_from_sitemap',
                esc_html__('Exclude from Sitemap', 'exclude-from-sitemap'),
                [$this, 'render_meta_box'],
                $type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render the checkbox in the meta box.
     */
    public function render_meta_box($post) {
        $value = get_post_meta($post->ID, self::META_KEY, true);
        wp_nonce_field('exclude_from_sitemap_nonce_action', 'exclude_from_sitemap_nonce');
        ?>
        <label for="exclude_from_sitemap">
            <input type="checkbox" name="exclude_from_sitemap" id="exclude_from_sitemap" value="1" <?php checked(esc_attr($value), '1'); ?> />
            <?php esc_html_e('Exclude this content from the default sitemap', 'exclude-from-sitemap'); ?>
        </label>
        <?php
    }

    /**
     * Save the checkbox value securely.
     */
    public function save_meta_box($post_id) {
        if (!isset($_POST['exclude_from_sitemap_nonce']) || !wp_verify_nonce($_POST['exclude_from_sitemap_nonce'], 'exclude_from_sitemap_nonce_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $value = isset($_POST['exclude_from_sitemap']) && sanitize_text_field($_POST['exclude_from_sitemap']) === '1' ? '1' : '';
        update_post_meta($post_id, self::META_KEY, $value);
    }

    /**
     * Modify the default sitemap query to exclude marked posts/pages.
     */
    public function filter_sitemap_query($args, $post_type) {
        $args['meta_query'][] = [
            'key'     => self::META_KEY,
            'value'   => '1',
            'compare' => '!=',
        ];
        return $args;
    }
}

new Sitex_Exclude_From_Native_Sitemap();