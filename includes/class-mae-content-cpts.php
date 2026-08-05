<?php
defined('ABSPATH') || exit;

class MAE_Content_CPTs {

    // ── Type configuration ────────────────────────────────────────────────────

    private static function cfg($type_key) {
        $icons = [
            'leadership'    => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.66 0 4.8-2.14 4.8-4.8S14.66 2.4 12 2.4 7.2 4.54 7.2 7.2 9.34 12 12 12zm0 2.4c-3.2 0-9.6 1.61-9.6 4.8v2.4h19.2v-2.4c0-3.19-6.4-4.8-9.6-4.8z"/></svg>',
            'partner'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>',
            'press-release' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>',
            'recognition'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V17H9c-.55 0-1 .45-1 1v2H7v2h10v-2h-1v-2c0-.55-.45-1-1-1h-2v-2.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/></svg>',
            'artist'        => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8zm-5.5 9c-.83 0-1.5-.67-1.5-1.5S5.67 9 6.5 9 8 9.67 8 10.5 7.33 12 6.5 12zm3-4C8.67 8 8 7.33 8 6.5S8.67 5 9.5 5s1.5.67 1.5 1.5S10.33 8 9.5 8zm5 0c-.83 0-1.5-.67-1.5-1.5S13.67 5 14.5 5s1.5.67 1.5 1.5S15.33 8 14.5 8zm3 4c-.83 0-1.5-.67-1.5-1.5S16.67 9 17.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>',
        ];

        $map = [
            'leadership' => [
                'label'           => 'Leadership',
                'icon'            => $icons['leadership'],
                'meta_key'        => '_mae_cpt_position',
                'read_more'       => 'Read More',
                'archive_heading' => 'Our Leadership Team',
                'archive_sub'     => 'Meet the people driving our mission forward.',
            ],
            'partner' => [
                'label'           => 'Partner',
                'icon'            => $icons['partner'],
                'meta_key'        => '_mae_cpt_partner_type',
                'read_more'       => 'Learn More',
                'archive_heading' => 'Our Partners',
                'archive_sub'     => 'Organisations that share our commitment to cultural heritage.',
            ],
            'press-release' => [
                'label'           => 'Press Release',
                'icon'            => $icons['press-release'],
                'meta_key'        => '_mae_cpt_pr_source',
                'read_more'       => 'Read Full Release',
                'archive_heading' => 'Press &amp; Media',
                'archive_sub'     => 'News and announcements from Mithila Art Foundation.',
            ],
            'recognition' => [
                'label'           => 'Recognition',
                'icon'            => $icons['recognition'],
                'meta_key'        => '',
                'read_more'       => 'Learn More',
                'archive_heading' => 'Recognition &amp; Awards',
                'archive_sub'     => 'Organisations and institutions that have recognised our work.',
            ],
            'artist' => [
                'label'           => 'Artist',
                'icon'            => $icons['artist'],
                'meta_key'        => '',
                'read_more'       => 'View Profile',
                'archive_heading' => 'Associated Artists',
                'archive_sub'     => 'Talented artists connected with the Mithila Art Foundation.',
            ],
        ];
        return $map[$type_key] ?? $map['leadership'];
    }

    public static function cpt_icon($type_key) {
        return self::cfg($type_key)['icon'];
    }

    // ── Boot ──────────────────────────────────────────────────────────────────

    public function __construct() {
        add_action('init',             [$this, 'register_cpts']);
        add_action('add_meta_boxes',   [$this, 'add_meta_boxes']);
        add_action('save_post',        [$this, 'save_meta']);
        add_filter('template_include', [$this, 'load_templates']);

        add_shortcode('mae_leadership',            [$this, 'sc_leadership']);
        add_shortcode('mae_partners',              [$this, 'sc_partners']);
        add_shortcode('mae_press_releases',        [$this, 'sc_press_releases']);
        add_shortcode('mae_recognition',           [$this, 'sc_recognition']);
        add_shortcode('mae_artists',               [$this, 'sc_artists']);

        add_shortcode('mae_latest_leadership',     [$this, 'sc_latest_leadership']);
        add_shortcode('mae_latest_partners',       [$this, 'sc_latest_partners']);
        add_shortcode('mae_latest_press_releases', [$this, 'sc_latest_press_releases']);

        add_action('init', [$this, 'maybe_insert_dummy_data'], 99);

        add_action('admin_init', function () {
            if (!get_option('mae_cpts_v3_flushed')) {
                flush_rewrite_rules();
                update_option('mae_cpts_v3_flushed', '1');
            }
        });
    }

    // ── CPT Registration ──────────────────────────────────────────────────────

    public function register_cpts() {
        $common = [
            'public'       => true,
            'show_ui'      => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'has_archive'  => true,
            'supports'     => ['title', 'editor', 'excerpt', 'thumbnail'],
            'rewrite'      => ['with_front' => false],
        ];

        register_post_type('mae_leadership', array_merge($common, [
            'labels'    => $this->labels('Leadership Member', 'Leadership'),
            'menu_icon' => 'dashicons-businessman',
            'rewrite'   => ['slug' => 'leadership', 'with_front' => false],
        ]));

        register_post_type('mae_partner', array_merge($common, [
            'labels'    => $this->labels('Partner', 'Partners'),
            'menu_icon' => 'dashicons-groups',
            'rewrite'   => ['slug' => 'partners', 'with_front' => false],
        ]));

        register_post_type('mae_press_release', array_merge($common, [
            'labels'    => $this->labels('Press Release', 'Press Releases'),
            'menu_icon' => 'dashicons-media-document',
            'rewrite'   => ['slug' => 'press-releases', 'with_front' => false],
        ]));

        register_post_type('mae_recognition', array_merge($common, [
            'labels'    => $this->labels('Recognition', 'Recognition'),
            'menu_icon' => 'dashicons-awards',
            'rewrite'   => ['slug' => 'recognition', 'with_front' => false],
        ]));

        register_taxonomy('mae_recognition_type', 'mae_recognition', [
            'label'        => 'Recognition Type',
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'recognition-type'],
        ]);

        register_post_type('mae_artist', array_merge($common, [
            'labels'    => $this->labels('Artist', 'Artists'),
            'menu_icon' => 'dashicons-art',
            'rewrite'   => ['slug' => 'artists', 'with_front' => false],
        ]));

        register_taxonomy('mae_art_style', 'mae_artist', [
            'label'        => 'Art Style / Specialty',
            'hierarchical' => false,
            'show_in_rest' => true,
            'rewrite'      => ['slug' => 'art-style'],
        ]);
    }

    private function labels($singular, $plural) {
        return [
            'name'               => $plural,
            'singular_name'      => $singular,
            'add_new_item'       => "Add New {$singular}",
            'edit_item'          => "Edit {$singular}",
            'new_item'           => "New {$singular}",
            'view_item'          => "View {$singular}",
            'search_items'       => "Search {$plural}",
            'not_found'          => "No {$plural} found",
            'not_found_in_trash' => "No {$plural} in Trash",
            'all_items'          => "All {$plural}",
        ];
    }

    // ── Meta Boxes ────────────────────────────────────────────────────────────

    public function add_meta_boxes() {
        add_meta_box('mae_leadership_meta', 'Leadership Details',    [$this, 'mb_leadership'],   'mae_leadership',   'side');
        add_meta_box('mae_partner_meta',    'Partner Details',       [$this, 'mb_partner'],      'mae_partner',      'side');
        add_meta_box('mae_pr_meta',         'Press Release Details', [$this, 'mb_press_release'],'mae_press_release','side');
        add_meta_box('mae_rec_meta',        'Recognition Details',   [$this, 'mb_recognition'],  'mae_recognition',  'side');
        add_meta_box('mae_artist_meta',     'Artist Details',        [$this, 'mb_artist'],       'mae_artist',       'side');
    }

    public function mb_leadership($post) {
        wp_nonce_field('mae_cpt_meta', 'mae_cpt_nonce');
        $position = get_post_meta($post->ID, '_mae_cpt_position', true);
        ?>
        <p><label><strong>Position / Role</strong><br>
        <input type="text" name="_mae_cpt_position" value="<?php echo esc_attr($position); ?>"
               class="widefat" placeholder="e.g. Executive Director"></label></p>
        <?php
    }

    public function mb_partner($post) {
        wp_nonce_field('mae_cpt_meta', 'mae_cpt_nonce');
        $type    = get_post_meta($post->ID, '_mae_cpt_partner_type', true);
        $website = get_post_meta($post->ID, '_mae_cpt_website',      true);
        ?>
        <p><label><strong>Organisation Type</strong><br>
        <input type="text" name="_mae_cpt_partner_type" value="<?php echo esc_attr($type); ?>"
               class="widefat" placeholder="e.g. NGO / Non-profit"></label></p>
        <p><label><strong>Website URL</strong><br>
        <input type="url" name="_mae_cpt_website" value="<?php echo esc_attr($website); ?>"
               class="widefat" placeholder="https://"></label></p>
        <?php
    }

    public function mb_press_release($post) {
        wp_nonce_field('mae_cpt_meta', 'mae_cpt_nonce');
        $date   = get_post_meta($post->ID, '_mae_cpt_pr_date',   true);
        $source = get_post_meta($post->ID, '_mae_cpt_pr_source', true);
        ?>
        <p><label><strong>Publication Date</strong><br>
        <input type="date" name="_mae_cpt_pr_date" value="<?php echo esc_attr($date); ?>"
               class="widefat"></label></p>
        <p><label><strong>Media Source / Outlet</strong><br>
        <input type="text" name="_mae_cpt_pr_source" value="<?php echo esc_attr($source); ?>"
               class="widefat" placeholder="e.g. UNESCO Nepal"></label></p>
        <?php
    }

    public function mb_recognition($post) {
        wp_nonce_field('mae_cpt_meta', 'mae_cpt_nonce');
        $url = get_post_meta($post->ID, '_mae_rec_url', true);
        ?>
        <p><label><strong>Website / Award URL</strong><br>
        <input type="url" name="_mae_rec_url" value="<?php echo esc_attr($url); ?>"
               class="widefat" placeholder="https://"></label></p>
        <p style="color:#666;font-size:12px;margin:6px 0 0">
            Use the <strong>Recognition Type</strong> taxonomy panel to categorise this item (e.g. Award, Partnership).
        </p>
        <?php
    }

    public function mb_artist($post) {
        wp_nonce_field('mae_cpt_meta', 'mae_cpt_nonce');
        $instagram = get_post_meta($post->ID, '_mae_artist_instagram', true);
        $website   = get_post_meta($post->ID, '_mae_artist_website',   true);
        ?>
        <p><label><strong>Instagram URL</strong><br>
        <input type="url" name="_mae_artist_instagram" value="<?php echo esc_attr($instagram); ?>"
               class="widefat" placeholder="https://instagram.com/handle"></label></p>
        <p><label><strong>Personal Website</strong><br>
        <input type="url" name="_mae_artist_website" value="<?php echo esc_attr($website); ?>"
               class="widefat" placeholder="https://"></label></p>
        <p style="color:#666;font-size:12px;margin:6px 0 0">
            Use the <strong>Art Style / Specialty</strong> taxonomy panel to tag this artist's style.
        </p>
        <?php
    }

    public function save_meta($post_id) {
        if (
            !isset($_POST['mae_cpt_nonce']) ||
            !wp_verify_nonce($_POST['mae_cpt_nonce'], 'mae_cpt_meta') ||
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ||
            !current_user_can('edit_post', $post_id)
        ) return;

        $url_fields  = ['_mae_cpt_website', '_mae_rec_url', '_mae_artist_instagram', '_mae_artist_website'];
        $text_fields = ['_mae_cpt_position', '_mae_cpt_partner_type', '_mae_cpt_pr_source'];

        foreach ($url_fields as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, esc_url_raw($_POST[$key]));
            }
        }
        foreach ($text_fields as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
            }
        }
        if (isset($_POST['_mae_cpt_pr_date'])) {
            update_post_meta($post_id, '_mae_cpt_pr_date', sanitize_text_field($_POST['_mae_cpt_pr_date']));
        }
    }

    // ── Template Loading ──────────────────────────────────────────────────────

    public function load_templates($template) {
        $types = ['mae_leadership', 'mae_partner', 'mae_press_release', 'mae_recognition', 'mae_artist'];

        if (is_singular($types)) {
            $f = MAE_PATH . 'templates/single-mae_content.php';
            if (file_exists($f)) return $f;
        }
        if (is_post_type_archive($types)) {
            // Check for type-specific archive template first
            $queried = get_queried_object();
            if ($queried && isset($queried->name)) {
                $specific = MAE_PATH . 'templates/archive-' . $queried->name . '.php';
                if (file_exists($specific)) return $specific;
            }
            $f = MAE_PATH . 'templates/archive-mae_content.php';
            if (file_exists($f)) return $f;
        }
        return $template;
    }

    // ── Shortcodes ────────────────────────────────────────────────────────────

    public function sc_leadership($atts) {
        return $this->render_grid('mae_leadership', 'leadership', $atts);
    }
    public function sc_partners($atts) {
        return $this->render_partners_carousel($atts);
    }
    public function sc_press_releases($atts) {
        return $this->render_grid('mae_press_release', 'press-release', $atts);
    }
    public function sc_recognition($atts) {
        return $this->render_recognition($atts);
    }
    public function sc_artists($atts) {
        return $this->render_artists($atts);
    }
    public function sc_latest_leadership($atts) {
        return $this->render_grid('mae_leadership', 'leadership',
            array_merge(['count' => 3, 'orderby' => 'date', 'order' => 'DESC'], (array) $atts));
    }
    public function sc_latest_partners($atts) {
        return $this->render_grid('mae_partner', 'partner',
            array_merge(['count' => 3, 'orderby' => 'date', 'order' => 'DESC'], (array) $atts));
    }
    public function sc_latest_press_releases($atts) {
        return $this->render_grid('mae_press_release', 'press-release',
            array_merge(['count' => 3, 'orderby' => 'date', 'order' => 'DESC'], (array) $atts));
    }

    // ── Partner carousel ──────────────────────────────────────────────────────

    private function render_partners_carousel($atts) {
        $a   = shortcode_atts(['count' => 8, 'title' => '', 'orderby' => 'menu_order', 'order' => 'ASC'], $atts);
        $cfg = self::cfg('partner');

        $posts = get_posts([
            'post_type'      => 'mae_partner',
            'posts_per_page' => max(1, (int) $a['count']),
            'post_status'    => 'publish',
            'orderby'        => $a['orderby'],
            'order'          => $a['order'],
        ]);

        if (empty($posts)) return '<p class="mae-no-events">No items available at this time.</p>';

        ob_start(); ?>
        <div class="pwu-partners-wrap">
            <?php if (!empty($a['title'])) : ?>
            <h2 class="mae-cpt-sc-title"><?php echo esc_html($a['title']); ?></h2>
            <?php endif; ?>
            <div class="pwu-partners-slider-wrap">
                <button class="pwu-pc-nav pwu-pc-nav--prev" aria-label="Previous partners">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="pwu-partners-slider">
                    <div class="pwu-partners-track" id="pwu-pc-track">
                        <?php foreach ($posts as $post) :
                            $meta_val = get_post_meta($post->ID, $cfg['meta_key'], true);
                            $excerpt  = get_the_excerpt($post);
                        ?>
                        <article class="mae-cpt-card mae-cpt-card-partner">
                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="mae-cpt-card-img-link" tabindex="-1" aria-hidden="true">
                                <?php if (has_post_thumbnail($post->ID)) :
                                    echo get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'mae-cpt-card-img']);
                                else : ?>
                                <div class="mae-cpt-ph-img mae-ph-partner">
                                    <span class="mae-ph-icon" aria-hidden="true"><?php echo self::cpt_icon('partner'); ?></span>
                                </div>
                                <?php endif; ?>
                                <span class="mae-cpt-type-badge mae-cpt-badge-partner">
                                    <?php echo esc_html($cfg['label']); ?>
                                </span>
                            </a>
                            <div class="mae-cpt-card-body">
                                <h3 class="mae-cpt-card-title">
                                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"><?php echo esc_html($post->post_title); ?></a>
                                </h3>
                                <?php if ($meta_val) : ?>
                                <div class="mae-cpt-card-meta">
                                    <span class="mae-cpt-meta-tag"><?php echo esc_html($meta_val); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if ($excerpt) : ?>
                                <p class="mae-cpt-card-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 18, '...')); ?></p>
                                <?php endif; ?>
                                <div class="mae-cpt-card-footer">
                                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                                       class="mae-cpt-card-btn mae-cpt-btn-partner">
                                        <?php echo esc_html($cfg['read_more']); ?> →
                                    </a>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button class="pwu-pc-nav pwu-pc-nav--next" aria-label="Next partners">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
            <div class="pwu-pc-dots"></div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Generic card grid ─────────────────────────────────────────────────────

    private function render_grid($post_type, $type_key, $atts) {
        $a   = shortcode_atts(['count' => 6, 'title' => '', 'orderby' => 'menu_order', 'order' => 'ASC'], $atts);
        $cfg = self::cfg($type_key);

        $posts = get_posts([
            'post_type'      => $post_type,
            'posts_per_page' => max(1, (int) $a['count']),
            'post_status'    => 'publish',
            'orderby'        => $a['orderby'],
            'order'          => $a['order'],
        ]);

        if (empty($posts)) return '<p class="mae-no-events">No items available at this time.</p>';

        ob_start(); ?>
        <div class="mae-cpt-wrap">
            <?php if (!empty($a['title'])) : ?>
            <h2 class="mae-cpt-sc-title"><?php echo esc_html($a['title']); ?></h2>
            <?php endif; ?>
            <div class="mae-cpt-grid">
                <?php foreach ($posts as $post) :
                    $meta_val = get_post_meta($post->ID, $cfg['meta_key'], true);
                    $excerpt  = get_the_excerpt($post);
                    $pr_date  = ($post_type === 'mae_press_release')
                        ? get_post_meta($post->ID, '_mae_cpt_pr_date', true) : '';
                ?>
                <article class="mae-cpt-card mae-cpt-card-<?php echo esc_attr($type_key); ?>">
                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="mae-cpt-card-img-link" tabindex="-1" aria-hidden="true">
                        <?php if (has_post_thumbnail($post->ID)) :
                            echo get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'mae-cpt-card-img']);
                        else : ?>
                        <div class="mae-cpt-ph-img mae-ph-<?php echo esc_attr($type_key); ?>">
                            <span class="mae-ph-icon" aria-hidden="true"><?php echo self::cpt_icon($type_key); ?></span>
                        </div>
                        <?php endif; ?>
                        <span class="mae-cpt-type-badge mae-cpt-badge-<?php echo esc_attr($type_key); ?>">
                            <?php echo esc_html($cfg['label']); ?>
                        </span>
                    </a>
                    <div class="mae-cpt-card-body">
                        <h3 class="mae-cpt-card-title">
                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"><?php echo esc_html($post->post_title); ?></a>
                        </h3>
                        <?php if ($meta_val || $pr_date) : ?>
                        <div class="mae-cpt-card-meta">
                            <?php if ($pr_date) : ?>
                            <span class="mae-cpt-meta-date">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <?php echo esc_html(date('F j, Y', strtotime($pr_date))); ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($meta_val) : ?>
                            <span class="mae-cpt-meta-tag"><?php echo esc_html($meta_val); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($excerpt) : ?>
                        <p class="mae-cpt-card-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 18, '...')); ?></p>
                        <?php endif; ?>
                        <div class="mae-cpt-card-footer">
                            <a href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                               class="mae-cpt-card-btn mae-cpt-btn-<?php echo esc_attr($type_key); ?>">
                                <?php echo esc_html($cfg['read_more']); ?> →
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Recognition logo grid ─────────────────────────────────────────────────

    private function render_recognition($atts) {
        $a = shortcode_atts(['count' => 12, 'title' => '', 'type' => ''], $atts);

        $args = [
            'post_type'      => 'mae_recognition',
            'posts_per_page' => max(1, (int) $a['count']),
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ];
        if (!empty($a['type'])) {
            $args['tax_query'] = [[
                'taxonomy' => 'mae_recognition_type',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($a['type']),
            ]];
        }

        $posts = get_posts($args);
        if (empty($posts)) return '<p class="mae-no-events">No recognition items available at this time.</p>';

        ob_start(); ?>
        <div class="mae-recognition-wrap">
            <?php if (!empty($a['title'])) : ?>
            <h2 class="mae-rec-section-title"><?php echo esc_html($a['title']); ?></h2>
            <?php endif; ?>
            <div class="mae-rec-grid">
                <?php foreach ($posts as $post) :
                    $url     = get_post_meta($post->ID, '_mae_rec_url', true);
                    $excerpt = get_the_excerpt($post);
                    $terms   = get_the_terms($post->ID, 'mae_recognition_type');
                    $type_label = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
                    $tag    = $url ? 'a' : 'div';
                ?>
                <div class="mae-rec-item">
                    <<?php echo $tag; ?> class="mae-rec-inner"
                        <?php if ($url) : ?>
                            href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"
                            <?php if ($excerpt) echo 'title="' . esc_attr(wp_trim_words($excerpt, 15, '...')) . '"'; ?>
                        <?php endif; ?>>
                        <div class="mae-rec-logo-wrap">
                            <?php if (has_post_thumbnail($post->ID)) :
                                echo get_the_post_thumbnail($post->ID, 'medium', ['class' => 'mae-rec-logo', 'alt' => esc_attr($post->post_title)]);
                            else : ?>
                            <div class="mae-rec-logo-ph">
                                <?php echo self::cpt_icon('recognition'); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="mae-rec-info">
                            <span class="mae-rec-name"><?php echo esc_html($post->post_title); ?></span>
                            <?php if ($type_label) : ?>
                            <span class="mae-rec-type-tag"><?php echo esc_html($type_label); ?></span>
                            <?php endif; ?>
                            <?php if ($url) : ?>
                            <span class="mae-rec-visit-hint">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="11" height="11"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Visit
                            </span>
                            <?php endif; ?>
                        </div>
                    </<?php echo $tag; ?>>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Artist profile grid ───────────────────────────────────────────────────

    private function render_artists($atts) {
        $a = shortcode_atts(['count' => 8, 'title' => '', 'style' => ''], $atts);

        $args = [
            'post_type'      => 'mae_artist',
            'posts_per_page' => max(1, (int) $a['count']),
            'post_status'    => 'publish',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ];
        if (!empty($a['style'])) {
            $args['tax_query'] = [[
                'taxonomy' => 'mae_art_style',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($a['style']),
            ]];
        }

        $posts = get_posts($args);
        if (empty($posts)) return '<p class="mae-no-events">No artists available at this time.</p>';

        ob_start(); ?>
        <div class="mae-artists-wrap">
            <?php if (!empty($a['title'])) : ?>
            <h2 class="mae-artists-section-title"><?php echo esc_html($a['title']); ?></h2>
            <?php endif; ?>
            <div class="mae-artists-grid">
                <?php foreach ($posts as $post) :
                    $instagram   = get_post_meta($post->ID, '_mae_artist_instagram', true);
                    $website     = get_post_meta($post->ID, '_mae_artist_website',   true);
                    $excerpt     = get_the_excerpt($post);
                    $terms       = get_the_terms($post->ID, 'mae_art_style');
                    $style_label = (!is_wp_error($terms) && !empty($terms)) ? $terms[0]->name : '';
                    $permalink   = get_permalink($post->ID);
                ?>
                <article class="mae-artist-card">
                    <div class="mae-artist-img-wrap">
                        <?php if (has_post_thumbnail($post->ID)) :
                            echo get_the_post_thumbnail($post->ID, 'medium_large', ['class' => 'mae-artist-img']);
                        else : ?>
                        <div class="mae-artist-img-ph">
                            <?php echo self::cpt_icon('artist'); ?>
                        </div>
                        <?php endif; ?>
                        <?php if ($style_label) : ?>
                        <span class="mae-artist-style-badge"><?php echo esc_html($style_label); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="mae-artist-body">
                        <h3 class="mae-artist-name">
                            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($post->post_title); ?></a>
                        </h3>
                        <?php if ($excerpt) : ?>
                        <p class="mae-artist-bio"><?php echo esc_html(wp_trim_words($excerpt, 20, '...')); ?></p>
                        <?php endif; ?>
                        <?php if ($instagram || $website) : ?>
                        <div class="mae-artist-social">
                            <?php if ($instagram) : ?>
                            <a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener"
                               class="mae-artist-social-link mae-social-instagram" aria-label="Instagram">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                Instagram
                            </a>
                            <?php endif; ?>
                            <?php if ($website) : ?>
                            <a href="<?php echo esc_url($website); ?>" target="_blank" rel="noopener"
                               class="mae-artist-social-link mae-social-website" aria-label="Website">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                                Website
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        <div class="mae-artist-footer">
                            <a href="<?php echo esc_url($permalink); ?>" class="mae-cpt-card-btn mae-cpt-btn-artist">
                                View Profile →
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Dummy Data ────────────────────────────────────────────────────────────

    public function maybe_insert_dummy_data() {
        if (!post_type_exists('mae_leadership')) return;

        if (!get_option('mae_dummy_content_inserted')) {
            update_option('mae_dummy_content_inserted', '1');
            $this->insert_leadership();
            $this->insert_partners();
            $this->insert_press_releases();
        }
        if (!get_option('mae_dummy_recognition_inserted')) {
            update_option('mae_dummy_recognition_inserted', '1');
            $this->insert_recognition();
        }
        if (!get_option('mae_dummy_artists_inserted')) {
            update_option('mae_dummy_artists_inserted', '1');
            $this->insert_artists();
        }
    }

    private function insert_post($args) {
        return wp_insert_post(array_merge(['post_status' => 'publish', 'post_author' => 1], $args));
    }

    private function insert_leadership() {
        $items = [
            [
                'title'      => 'Rajendra Prasad Singh',
                'position'   => 'Executive Director',
                'excerpt'    => 'A visionary cultural advocate with over 20 years of experience in heritage preservation and international arts diplomacy.',
                'content'    => '<p>Rajendra Prasad Singh has led the Mithila Art Foundation for over 20 years, championing the preservation and global promotion of traditional Madhubani art. Under his leadership, the foundation has expanded its presence to 18 countries and forged landmark collaborations with UNESCO, the Smithsonian Institution, and multiple national ministries of culture.</p><p>A practicing Madhubani artist himself, Rajendra bridges deep artistic tradition with modern organizational leadership. He holds a Master\'s degree in Cultural Management from Jawaharlal Nehru University and has been recognised with the National Cultural Excellence Award by the Government of Nepal.</p>',
                'menu_order' => 1,
            ],
            [
                'title'      => 'Dr. Sunita Devi Jha',
                'position'   => 'Programs Director',
                'excerpt'    => 'An accomplished Madhubani artist and educator who designs and leads all cultural programs and community learning initiatives.',
                'content'    => '<p>Dr. Sunita Devi Jha brings together her identity as a practicing Madhubani artist and a trained cultural educator to lead the Foundation\'s entire portfolio of programs. She holds a PhD in Fine Arts with a specialisation in traditional Indian folk art from Banaras Hindu University.</p><p>Under her stewardship, the Foundation\'s programs have reached over 50,000 participants across Nepal, India, and 12 other countries. She pioneered the Foundation\'s school-integration curriculum, which has introduced Mithila art to more than 200 schools and colleges across the Mithila region.</p>',
                'menu_order' => 2,
            ],
            [
                'title'      => 'Amit Kumar Mishra',
                'position'   => 'Cultural Affairs Lead',
                'excerpt'    => 'International relations specialist who coordinates cultural exchange programmes and diplomatic partnerships across 30+ countries.',
                'content'    => '<p>Amit Kumar Mishra coordinates the Foundation\'s international cultural exchange activities and diplomatic partnerships. With a background in international relations and over a decade of experience in cultural diplomacy, he has facilitated Mithila art exhibitions and workshops in more than 30 countries.</p>',
                'menu_order' => 3,
            ],
            [
                'title'      => 'Priya Thakur',
                'position'   => 'Community Outreach Head',
                'excerpt'    => 'Grassroots development professional dedicated to economically empowering Mithila artisan communities through sustainable programmes.',
                'content'    => '<p>Priya Thakur leads the Foundation\'s community outreach and livelihood programmes, working directly with artisan families in the Mithila heartland. Since joining in 2018, Priya has directly supported over 600 artisan families, helping them establish producer cooperatives, access microfinance, and adopt digital platforms for direct sales.</p>',
                'menu_order' => 4,
            ],
        ];
        foreach ($items as $item) {
            $id = $this->insert_post([
                'post_type' => 'mae_leadership', 'post_title' => $item['title'],
                'post_excerpt' => $item['excerpt'], 'post_content' => $item['content'],
                'menu_order' => $item['menu_order'],
            ]);
            if ($id) update_post_meta($id, '_mae_cpt_position', $item['position']);
        }
    }

    private function insert_partners() {
        $items = [
            [
                'title'      => 'UNESCO Nepal',
                'type'       => 'International Organisation',
                'website'    => 'https://nepal.unesco.org',
                'excerpt'    => 'UNESCO supports the Foundation\'s heritage documentation and safeguarding initiatives as part of its global Intangible Cultural Heritage programme.',
                'content'    => '<p>UNESCO Nepal has been a cornerstone partner of the Mithila Art Foundation since 2015. The partnership focuses on the documentation, safeguarding, and international promotion of Mithila art as an Intangible Cultural Heritage under the UNESCO 2003 Convention.</p>',
                'menu_order' => 1,
            ],
            [
                'title'      => 'Ministry of Culture, Tourism & Civil Aviation – Nepal',
                'type'       => 'Government Body',
                'website'    => 'https://moct.gov.np',
                'excerpt'    => 'The Ministry provides institutional support, policy frameworks, and funding for the Foundation\'s national cultural promotion programmes.',
                'content'    => '<p>The Ministry of Culture, Tourism and Civil Aviation of Nepal is the Foundation\'s primary government partner, providing essential institutional support, co-funding, and policy guidance for programmes that align with Nepal\'s national cultural heritage strategy.</p>',
                'menu_order' => 2,
            ],
            [
                'title'      => 'Janakpur Art Institute',
                'type'       => 'Educational Institution',
                'website'    => 'https://janakpur.edu.np',
                'excerpt'    => 'The leading regional art institute providing academic research, curriculum development, and student engagement in support of Mithila arts education.',
                'content'    => '<p>Janakpur Art Institute is the leading fine arts institution in the Mithila heartland and a vital academic partner for the Foundation.</p>',
                'menu_order' => 3,
            ],
            [
                'title'      => 'Madhubani Cultural Foundation',
                'type'       => 'NGO / Non-profit',
                'website'    => 'https://madhubani.org',
                'excerpt'    => 'A sister organisation on the Indian side of the Mithila cultural region, partnering to promote cross-border heritage collaboration.',
                'content'    => '<p>The Madhubani Cultural Foundation is the Foundation\'s primary cross-border partner, working in the Indian districts of the historical Mithila region — Bihar and surrounding areas.</p>',
                'menu_order' => 4,
            ],
            [
                'title'      => 'NY Chamber of Commerce',
                'type'       => 'Business Association',
                'website'    => 'https://www.nychamber.com',
                'excerpt'    => 'New York\'s premier business association supporting cultural initiatives and economic development across the five boroughs.',
                'content'    => '<p>The NY Chamber of Commerce partners with the Mithila Art Foundation to promote cultural entrepreneurship and support artisan-led businesses in the New York metropolitan area.</p>',
                'menu_order' => 5,
            ],
            [
                'title'      => 'United Nations',
                'type'       => 'International Organisation',
                'website'    => 'https://www.un.org',
                'excerpt'    => 'The United Nations supports our Art for SDGs initiative, aligning Mithila art education with the 2030 Agenda for Sustainable Development.',
                'content'    => '<p>The United Nations collaboration focuses on advancing the Sustainable Development Goals through cultural education and art-based community development programmes.</p>',
                'menu_order' => 6,
            ],
            [
                'title'      => 'European Union',
                'type'       => 'International Organisation',
                'website'    => 'https://europa.eu',
                'excerpt'    => 'EU cultural programmes support cross-continental exchange, bringing Mithila art to European audiences and institutions.',
                'content'    => '<p>The European Union\'s Creative Europe programme has funded exhibitions and artist residencies, fostering cultural dialogue between Mithila tradition and European contemporary art spaces.</p>',
                'menu_order' => 7,
            ],
            [
                'title'      => 'Embassy of Nepal, Washington D.C.',
                'type'       => 'Diplomatic Mission',
                'website'    => 'https://us.nepalembassy.gov.np',
                'excerpt'    => 'Nepal\'s diplomatic mission in the United States champions the global promotion of Nepalese cultural heritage and the arts.',
                'content'    => '<p>The Embassy of Nepal serves as a key diplomatic partner, facilitating cultural programming and governmental support for the Foundation\'s US-based initiatives including the Museum of Mithila Heritage.</p>',
                'menu_order' => 8,
            ],
            [
                'title'      => 'NYC Mayor\'s Office',
                'type'       => 'Government Body',
                'website'    => 'https://www.nyc.gov/mayor',
                'excerpt'    => 'The Mayor\'s Office of Immigrant Affairs supports cultural representation and community programming for New York\'s South Asian diaspora.',
                'content'    => '<p>The NYC Mayor\'s Office partners on community events, cultural festivals, and diversity initiatives that celebrate the rich heritage of New York\'s Nepali and Mithila communities.</p>',
                'menu_order' => 9,
            ],
            [
                'title'      => 'Indian Consulate, New York',
                'type'       => 'Diplomatic Mission',
                'website'    => 'https://www.cginewyork.gov.in',
                'excerpt'    => 'India\'s consular mission in New York supports cultural programmes celebrating the shared Mithila heritage across India and Nepal.',
                'content'    => '<p>The Indian Consulate in New York is a vital partner for cross-border cultural initiatives, supporting exhibitions and programmes that highlight Madhubani art\'s significance in Bihar and the broader Mithila tradition.</p>',
                'menu_order' => 10,
            ],
            [
                'title'      => 'NY State Governor\'s Office',
                'type'       => 'Government Body',
                'website'    => 'https://www.governor.ny.gov',
                'excerpt'    => 'The Governor\'s Office supports arts and cultural programming that enriches community life and celebrates New York\'s extraordinary diversity.',
                'content'    => '<p>The New York State Governor\'s Office has recognised the Foundation\'s contributions to cultural enrichment and supports state-level arts funding and policy initiatives that benefit diaspora communities.</p>',
                'menu_order' => 11,
            ],
        ];
        foreach ($items as $item) {
            $id = $this->insert_post([
                'post_type' => 'mae_partner', 'post_title' => $item['title'],
                'post_excerpt' => $item['excerpt'], 'post_content' => $item['content'],
                'menu_order' => $item['menu_order'],
            ]);
            if ($id) {
                update_post_meta($id, '_mae_cpt_partner_type', $item['type']);
                update_post_meta($id, '_mae_cpt_website', $item['website']);
            }
        }
    }

    private function insert_press_releases() {
        $items = [
            [
                'title'      => 'Mithila Art Festival 2026 Announced — A Landmark Celebration of Living Heritage',
                'source'     => 'Mithila Art Foundation',
                'date'       => '2026-04-15',
                'excerpt'    => 'The Mithila Art Foundation announces its flagship annual festival, bringing together artists, scholars, and cultural enthusiasts from over 25 countries.',
                'content'    => '<p><strong>Kathmandu, Nepal – 15 April 2026:</strong> The Mithila Art Foundation today announced the Mithila Art Festival 2026, scheduled to take place from 14–18 June 2026 at multiple venues across Janakpur and Kathmandu. This year\'s festival, themed <em>"Living Lines: Art as Resilience,"</em> is expected to be the largest in the event\'s 12-year history.</p>',
                'menu_order' => 1,
            ],
            [
                'title'      => 'UNESCO and Mithila Art Foundation Launch Joint Heritage Documentation Initiative',
                'source'     => 'UNESCO Nepal',
                'date'       => '2026-03-08',
                'excerpt'    => 'A new three-year initiative will create the world\'s most comprehensive digital archive of Mithila art motifs, patterns, and oral traditions.',
                'content'    => '<p><strong>Kathmandu, Nepal – 8 March 2026:</strong> UNESCO Nepal and the Mithila Art Foundation today announced a landmark three-year joint initiative to create the world\'s most comprehensive digital archive of Mithila art, supported by a USD 450,000 grant from the UNESCO International Fund for Cultural Diversity.</p>',
                'menu_order' => 2,
            ],
            [
                'title'      => 'Art for SDGs Initiative Expands to Three New Cities, Reaching 15,000 Young People',
                'source'     => 'Press Trust of Nepal',
                'date'       => '2026-02-20',
                'excerpt'    => 'The Foundation\'s acclaimed Art for SDGs programme extends its reach to Pokhara, Biratnagar, and Bharatpur with a new curriculum linking Mithila art to the UN SDGs.',
                'content'    => '<p><strong>Kathmandu, Nepal – 20 February 2026:</strong> The Mithila Art Foundation today announced the expansion of its Art for SDGs initiative to three new cities — Pokhara, Biratnagar, and Bharatpur — bringing the programme\'s total coverage to seven cities across Nepal and India.</p>',
                'menu_order' => 3,
            ],
            [
                'title'      => 'International Mithila Museum Receives UNESCO Asia-Pacific Heritage Award',
                'source'     => 'Ministry of Culture, Tourism & Civil Aviation – Nepal',
                'date'       => '2026-01-12',
                'excerpt'    => 'The Foundation\'s flagship museum project in Janakpur has received the prestigious UNESCO Asia-Pacific Award for Cultural Heritage Conservation.',
                'content'    => '<p><strong>Bangkok, Thailand – 12 January 2026:</strong> The Mithila Art Foundation\'s International Mithila Museum in Janakpur has been awarded the UNESCO Asia-Pacific Award for Cultural Heritage Conservation in the category of Innovation in Heritage Interpretation.</p>',
                'menu_order' => 4,
            ],
        ];
        foreach ($items as $item) {
            $id = $this->insert_post([
                'post_type' => 'mae_press_release', 'post_title' => $item['title'],
                'post_excerpt' => $item['excerpt'], 'post_content' => $item['content'],
                'post_date' => $item['date'] . ' 09:00:00', 'menu_order' => $item['menu_order'],
            ]);
            if ($id) {
                update_post_meta($id, '_mae_cpt_pr_source', $item['source']);
                update_post_meta($id, '_mae_cpt_pr_date',   $item['date']);
            }
        }
    }

    private function insert_recognition() {
        foreach (['International Award', 'Partner Recognition', 'Digital Partnership', 'Media Recognition'] as $term) {
            if (!term_exists($term, 'mae_recognition_type')) wp_insert_term($term, 'mae_recognition_type');
        }
        $items = [
            ['title' => 'UNESCO Intangible Cultural Heritage', 'type' => 'International Award',  'url' => 'https://ich.unesco.org',                  'excerpt' => 'Recognised by UNESCO for outstanding contribution to the preservation and promotion of Intangible Cultural Heritage traditions of the Mithila region.', 'menu_order' => 1],
            ['title' => 'Smithsonian Institution',             'type' => 'Partner Recognition',  'url' => 'https://www.si.edu',                       'excerpt' => 'Acknowledged by the Smithsonian Institution for excellence in folk art preservation and cross-cultural exchange programming.',                    'menu_order' => 2],
            ['title' => 'Google Arts & Culture',               'type' => 'Digital Partnership',  'url' => 'https://artsandculture.google.com',        'excerpt' => 'Featured partner on Google Arts & Culture, showcasing Mithila art to global audiences through immersive digital experiences.',                  'menu_order' => 3],
            ['title' => 'National Geographic Society',         'type' => 'Media Recognition',    'url' => 'https://www.nationalgeographic.org',       'excerpt' => 'Recognised by National Geographic for excellence in documenting the visual storytelling traditions of the Mithila region.',                     'menu_order' => 4],
        ];
        foreach ($items as $item) {
            $id = $this->insert_post([
                'post_type' => 'mae_recognition', 'post_title' => $item['title'],
                'post_excerpt' => $item['excerpt'], 'post_content' => '<p>' . esc_html($item['excerpt']) . '</p>',
                'menu_order' => $item['menu_order'],
            ]);
            if ($id) {
                update_post_meta($id, '_mae_rec_url', $item['url']);
                $term = get_term_by('name', $item['type'], 'mae_recognition_type');
                if ($term) wp_set_post_terms($id, [$term->term_id], 'mae_recognition_type');
            }
        }
    }

    private function insert_artists() {
        foreach (['Traditional Madhubani', 'Narrative Mithila', 'Contemporary Madhubani', 'Experimental Mithila'] as $style) {
            if (!term_exists($style, 'mae_art_style')) wp_insert_term($style, 'mae_art_style');
        }
        $items = [
            [
                'title' => 'Sita Devi Singh', 'style' => 'Traditional Madhubani',
                'instagram' => 'https://instagram.com/sitadeviart', 'website' => '',
                'excerpt' => 'A celebrated master of traditional Madhubani painting with over four decades of practice, renowned for intricate Kobar compositions rendered in natural pigments.',
                'content' => '<p>Sita Devi Singh is widely regarded as one of the finest living practitioners of traditional Madhubani painting. Born in Jitwarpur village in the heart of the Mithila region, she learned the art from her mother at the age of seven — a tradition stretching back countless generations in her family.</p><p>Over her 40-year career, Sita Devi has exhibited in Nepal, India, Japan, Germany, and the United States. She is a recipient of the National Cultural Excellence Award from the Government of Nepal and was honoured by UNESCO as a Living Heritage Artist in 2022.</p>',
                'menu_order' => 1,
            ],
            [
                'title' => 'Ram Chandra Mandal', 'style' => 'Narrative Mithila',
                'instagram' => 'https://instagram.com/ramchandraart', 'website' => 'https://ramchandramandal.com',
                'excerpt' => 'Known for narrative Mithila compositions depicting the Ramayana and Mahabharata, weaving epic stories into richly patterned visual tapestries of extraordinary complexity.',
                'content' => '<p>Ram Chandra Mandal\'s work occupies a unique space at the intersection of ancient narrative tradition and contemporary visual art. His paintings are distinguished by their narrative complexity — a single work may tell an entire chapter of the Ramayana, with dozens of scenes interwoven in a richly patterned tapestry.</p><p>His work has been acquired by major collections including the Rubin Museum of Art, the Victoria & Albert Museum, and the National Gallery of Nepal.</p>',
                'menu_order' => 2,
            ],
            [
                'title' => 'Pushpa Kumari', 'style' => 'Contemporary Madhubani',
                'instagram' => 'https://instagram.com/pushpakumariart', 'website' => 'https://pushpakumari.art',
                'excerpt' => 'Pushpa bridges traditional Madhubani techniques with contemporary themes of ecology and gender justice, creating art that speaks powerfully to urgent modern audiences worldwide.',
                'content' => '<p>Pushpa Kumari represents a new generation of Mithila artists using the traditional art form as a vehicle for engaging with urgent contemporary issues — environmental degradation, women\'s rights, and cultural displacement.</p><p>Her series <em>Fading Waters</em> has been exhibited at climate conferences in six countries. Her series <em>Daughters of Mithila</em> re-imagines the stories of women in Mithila society through the lens of the traditional Kobar room painting.</p>',
                'menu_order' => 3,
            ],
            [
                'title' => 'Dilip Kumar Jha', 'style' => 'Experimental Mithila',
                'instagram' => 'https://instagram.com/dilipjhaart', 'website' => 'https://dilipjha.studio',
                'excerpt' => 'Dilip pushes Mithila art into new territory by integrating traditional motifs with mixed media and digital processes, making the ancient visual language speak in new registers.',
                'content' => '<p>Dilip Kumar Jha is one of the most innovative artists working in the Mithila tradition today. His practice spans traditional hand-painting, large-format murals, screen printing, and digital art — reaching audiences that have never encountered Mithila art in its traditional context.</p><p>He collaborates with artists and institutions from Japan, Germany, Brazil, and the United States, embedding Mithila visual language in global contemporary art conversations.</p>',
                'menu_order' => 4,
            ],
        ];
        foreach ($items as $item) {
            $id = $this->insert_post([
                'post_type' => 'mae_artist', 'post_title' => $item['title'],
                'post_excerpt' => $item['excerpt'], 'post_content' => $item['content'],
                'menu_order' => $item['menu_order'],
            ]);
            if ($id) {
                if ($item['instagram']) update_post_meta($id, '_mae_artist_instagram', $item['instagram']);
                if ($item['website'])   update_post_meta($id, '_mae_artist_website',   $item['website']);
                $term = get_term_by('name', $item['style'], 'mae_art_style');
                if ($term) wp_set_post_terms($id, [$term->term_id], 'mae_art_style');
            }
        }
    }
}
