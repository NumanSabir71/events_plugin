<?php
defined('ABSPATH') || exit;

class MAE_Event_Meta {

    public function __construct() {
        add_action('add_meta_boxes',          [$this, 'add']);
        add_action('save_post_mae_event',     [$this, 'save']);
        add_action('save_post_mae_event',     [$this, 'save_artists']);
        add_action('admin_head',              [$this, 'admin_styles']);
        add_action('admin_enqueue_scripts',   [$this, 'admin_scripts_artists']);
    }

    public function add() {
        add_meta_box(
            'mae_event_details',
            'Event Details',
            [$this, 'render'],
            'mae_event',
            'normal',
            'high'
        );
        add_meta_box(
            'mae_event_artists',
            'Associated Artists',
            [$this, 'render_artists'],
            'mae_event',
            'normal',
            'default'
        );
    }

    public function render($post) {
        wp_nonce_field('mae_save_event', 'mae_event_nonce');

        $date     = get_post_meta($post->ID, '_mae_date',     true);
        $end_date = get_post_meta($post->ID, '_mae_end_date', true);
        $time     = get_post_meta($post->ID, '_mae_time',     true);
        $location = get_post_meta($post->ID, '_mae_location', true);
        $type       = get_post_meta($post->ID, '_mae_type',     true) ?: 'free';
        $price      = get_post_meta($post->ID, '_mae_price',    true);
        $reg_closed = get_post_meta($post->ID, '_mae_registration_closed', true);
        ?>
        <div class="mae-meta-wrap">
            <div class="mae-meta-grid">
                <div class="mae-meta-field">
                    <label for="mae_date">Start Date</label>
                    <input type="date" id="mae_date" name="mae_date" value="<?php echo esc_attr($date); ?>">
                </div>
                <div class="mae-meta-field">
                    <label for="mae_end_date">End Date <span style="font-weight:400;color:#888;">(optional)</span></label>
                    <input type="date" id="mae_end_date" name="mae_end_date" value="<?php echo esc_attr($end_date); ?>"
                           min="<?php echo esc_attr($date); ?>">
                </div>
                <div class="mae-meta-field">
                    <label for="mae_time">Event Time</label>
                    <input type="time" id="mae_time" name="mae_time" value="<?php echo esc_attr($time); ?>">
                </div>
                <div class="mae-meta-field mae-meta-full">
                    <label for="mae_location">Location / Venue</label>
                    <input type="text" id="mae_location" name="mae_location"
                           value="<?php echo esc_attr($location); ?>" placeholder="e.g. City Hall, 123 Main St, New York">
                </div>
                <div class="mae-meta-field">
                    <label for="mae_type">Event Type</label>
                    <select id="mae_type" name="mae_type">
                        <option value="free" <?php selected($type, 'free'); ?>>Free</option>
                        <option value="paid" <?php selected($type, 'paid'); ?>>Paid</option>
                    </select>
                </div>
                <div class="mae-meta-field" id="mae_price_wrap"
                     style="<?php echo $type === 'paid' ? '' : 'opacity:.4;pointer-events:none'; ?>">
                    <label for="mae_price">Ticket Price (USD)</label>
                    <input type="number" id="mae_price" name="mae_price"
                           value="<?php echo esc_attr($price); ?>" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="mae-meta-field mae-meta-full">
                    <label class="mae-meta-checkbox">
                        <input type="checkbox" id="mae_registration_closed" name="mae_registration_closed"
                               value="1" <?php checked($reg_closed, '1'); ?>>
                        <span>Registration Closed</span>
                    </label>
                    <p class="mae-meta-hint">When enabled, registration is closed for this event regardless of its dates.</p>
                </div>
            </div>
        </div>
        <script>
        (function(){
            var type     = document.getElementById('mae_type');
            var wrap     = document.getElementById('mae_price_wrap');
            var startDt  = document.getElementById('mae_date');
            var endDt    = document.getElementById('mae_end_date');
            type.addEventListener('change', function(){
                wrap.style.opacity = this.value === 'paid' ? '1' : '.4';
                wrap.style.pointerEvents = this.value === 'paid' ? '' : 'none';
            });
            startDt.addEventListener('change', function(){
                endDt.min = this.value;
                if (endDt.value && endDt.value < this.value) endDt.value = this.value;
            });
        })();
        </script>
        <?php
    }

    public function render_artists($post) {
        $artists = get_post_meta($post->ID, '_mae_artists', true) ?: [];

        ?>
        <div class="mae-artists-box">
            <div id="mae-artists-list">
                <?php foreach ($artists as $i => $artist) :
                    $art_pieces = esc_attr($artist['art_pieces'] ?? '');
                    $art_ids    = array_filter(array_map('intval', explode(',', $artist['art_pieces'] ?? '')));
                    $photo_id   = (int) ($artist['photo_id'] ?? 0);
                    $photo_url  = $photo_id ? wp_get_attachment_image_url($photo_id, [80, 80]) : '';
                ?>
                <div class="mae-ar-row">
                    <div class="mae-ar-row__header">
                        <strong>Artist <?php echo $i + 1; ?></strong>
                        <button type="button" class="button mae-ar-remove">Remove</button>
                    </div>
                    <div class="mae-ar-fields">
                        <div>
                            <label>Name</label>
                            <input type="text" name="mae_artists[<?php echo $i; ?>][name]"
                                   value="<?php echo esc_attr($artist['name'] ?? ''); ?>"
                                   placeholder="Artist name"
                                   class="mae-ar-name-input">
                        </div>
                        <div>
                            <label>
                                URL Slug
                                <span style="font-weight:400;color:#888;font-size:.85em;">(optional — leave blank to auto-generate from name)</span>
                            </label>
                            <input type="text" name="mae_artists[<?php echo $i; ?>][slug]"
                                   value="<?php echo esc_attr($artist['slug'] ?? ''); ?>"
                                   placeholder="e.g. john-smith"
                                   class="mae-ar-slug-input">
                            <?php
                                $saved_slug = sanitize_title($artist['slug'] ?? '') ?: sanitize_title($artist['name'] ?? '');
                                $event_url  = get_permalink(get_the_ID());
                            ?>
                            <?php if ($saved_slug && $event_url) : ?>
                            <p style="margin:.45rem 0 0;font-size:.82rem;color:#555;">
                                QR anchor: <code style="background:#f0f0f0;padding:2px 6px;border-radius:3px;user-select:all;"><?php echo esc_html(rtrim($event_url, '/') . '/#' . $saved_slug); ?></code>
                            </p>
                            <?php else : ?>
                            <p style="margin:.45rem 0 0;font-size:.82rem;color:#888;">Save the post to see the generated QR anchor URL.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label>Donate URL</label>
                            <input type="url" name="mae_artists[<?php echo $i; ?>][donate_url]" value="<?php echo esc_attr($artist['donate_url'] ?? ''); ?>" placeholder="https://...">
                        </div>
                        <div class="mae-ar-full">
                            <label>Bio</label>
                            <textarea name="mae_artists[<?php echo $i; ?>][bio]" rows="3" placeholder="Short artist bio..."><?php echo esc_textarea($artist['bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="mae-ar-full">
                            <label>Artist Photo</label>
                            <div class="mae-ar-photo-wrap">
                                <div class="mae-ar-photo-preview" data-index="<?php echo $i; ?>">
                                    <?php if ($photo_url) : ?>
                                    <img src="<?php echo esc_url($photo_url); ?>" alt="" class="mae-ar-photo-img">
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="mae_artists[<?php echo $i; ?>][photo_id]" value="<?php echo esc_attr($photo_id ?: ''); ?>">
                                <button type="button" class="button mae-ar-photo-select" data-index="<?php echo $i; ?>"><?php echo $photo_url ? 'Change Photo' : 'Select Photo'; ?></button>
                                <button type="button" class="button mae-ar-photo-remove" data-index="<?php echo $i; ?>" style="margin-left:6px;<?php echo $photo_url ? '' : 'display:none;'; ?>">Remove</button>
                            </div>
                        </div>
                        <div class="mae-ar-full">
                            <label>Art Pieces</label>
                            <div class="mae-ar-thumbs" data-index="<?php echo $i; ?>">
                                <?php
                                $captions     = $artist['art_captions']     ?? [];
                                $prices       = $artist['art_prices']       ?? [];
                                $descriptions = $artist['art_descriptions'] ?? [];
                                $dimensions   = $artist['art_dimensions']   ?? [];
                                foreach ($art_ids as $att_id) :
                                    $thumb = wp_get_attachment_image_url($att_id, [120, 90]);
                                    if (!$thumb) continue;
                                ?>
                                <div class="mae-ar-thumb" data-id="<?php echo $att_id; ?>">
                                    <img src="<?php echo esc_url($thumb); ?>" alt="">
                                    <button type="button" class="mae-ar-thumb__rm" data-id="<?php echo $att_id; ?>">&times;</button>
                                    <input type="text"
                                           name="mae_artists[<?php echo $i; ?>][art_captions][<?php echo $att_id; ?>]"
                                           class="mae-ar-caption-input"
                                           placeholder="Image caption..."
                                           value="<?php echo esc_attr($captions[$att_id] ?? ''); ?>">
                                    <textarea
                                           name="mae_artists[<?php echo $i; ?>][art_descriptions][<?php echo $att_id; ?>]"
                                           class="mae-ar-desc-input"
                                           rows="2"
                                           placeholder="Art description..."><?php echo esc_textarea($descriptions[$att_id] ?? ''); ?></textarea>
                                    <input type="text"
                                           name="mae_artists[<?php echo $i; ?>][art_dimensions][<?php echo $att_id; ?>]"
                                           class="mae-ar-dim-input"
                                           placeholder="Dimensions e.g. 24 × 36 in"
                                           value="<?php echo esc_attr($dimensions[$att_id] ?? ''); ?>">
                                    <input type="number"
                                           name="mae_artists[<?php echo $i; ?>][art_prices][<?php echo $att_id; ?>]"
                                           class="mae-ar-price-input"
                                           placeholder="Price (USD)"
                                           step="0.01" min="0"
                                           value="<?php echo esc_attr($prices[$att_id] ?? ''); ?>">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="mae_artists[<?php echo $i; ?>][art_pieces]" value="<?php echo $art_pieces; ?>">
                            <button type="button" class="button mae-ar-media" data-index="<?php echo $i; ?>">Add Art Pieces</button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" id="mae-ar-add" class="button button-primary" style="margin-top:12px">+ Add Artist</button>

            <script type="text/html" id="mae-ar-tmpl">
                <div class="mae-ar-row">
                    <div class="mae-ar-row__header">
                        <strong>Artist INDEX_LABEL</strong>
                        <button type="button" class="button mae-ar-remove">Remove</button>
                    </div>
                    <div class="mae-ar-fields">
                        <div>
                            <label>Name</label>
                            <input type="text" name="mae_artists[INDEX][name]" value="" placeholder="Artist name" class="mae-ar-name-input">
                        </div>
                        <div>
                            <label>
                                URL Slug
                                <span style="font-weight:400;color:#888;font-size:.85em;">(optional — leave blank to auto-generate from name)</span>
                            </label>
                            <input type="text" name="mae_artists[INDEX][slug]" value="" placeholder="e.g. john-smith" class="mae-ar-slug-input">
                            <p style="margin:.45rem 0 0;font-size:.82rem;color:#888;">Save the post to see the generated QR anchor URL.</p>
                        </div>
                        <div>
                            <label>Donate URL</label>
                            <input type="url" name="mae_artists[INDEX][donate_url]" value="" placeholder="https://...">
                        </div>
                        <div class="mae-ar-full">
                            <label>Bio</label>
                            <textarea name="mae_artists[INDEX][bio]" rows="3" placeholder="Short artist bio..."></textarea>
                        </div>
                        <div class="mae-ar-full">
                            <label>Artist Photo</label>
                            <div class="mae-ar-photo-wrap">
                                <div class="mae-ar-photo-preview" data-index="INDEX"></div>
                                <input type="hidden" name="mae_artists[INDEX][photo_id]" value="">
                                <button type="button" class="button mae-ar-photo-select" data-index="INDEX">Select Photo</button>
                                <button type="button" class="button mae-ar-photo-remove" data-index="INDEX" style="display:none;margin-left:6px;">Remove</button>
                            </div>
                        </div>
                        <div class="mae-ar-full">
                            <label>Art Pieces</label>
                            <div class="mae-ar-thumbs" data-index="INDEX"></div>
                            <input type="hidden" name="mae_artists[INDEX][art_pieces]" value="">
                            <button type="button" class="button mae-ar-media" data-index="INDEX">Add Art Pieces</button>
                        </div>
                    </div>
                </div>
            </script>
        </div>
        <?php
    }

    public function save($post_id) {
        if (!isset($_POST['mae_event_nonce'])) return;
        if (!wp_verify_nonce($_POST['mae_event_nonce'], 'mae_save_event')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $fields = [
            'mae_date'     => '_mae_date',
            'mae_end_date' => '_mae_end_date',
            'mae_time'     => '_mae_time',
            'mae_location' => '_mae_location',
            'mae_type'     => '_mae_type',
            'mae_price'    => '_mae_price',
        ];

        foreach ($fields as $input => $meta_key) {
            if (isset($_POST[$input])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$input]));
            }
        }

        // Checkbox: store '1' when ticked, '' when not (absent from $_POST when unchecked).
        update_post_meta(
            $post_id,
            '_mae_registration_closed',
            isset($_POST['mae_registration_closed']) ? '1' : ''
        );
    }

    public function save_artists($post_id) {
        if (!isset($_POST['mae_event_nonce'])) return;
        if (!wp_verify_nonce($_POST['mae_event_nonce'], 'mae_save_event')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $artists = [];
        foreach (($_POST['mae_artists'] ?? []) as $a) {
            $name = sanitize_text_field($a['name'] ?? '');
            if (!$name) continue; // skip blank rows

            $raw_captions = (array) ($a['art_captions'] ?? []);
            $captions = [];
            foreach ($raw_captions as $att_id => $cap) {
                $att_id = absint($att_id);
                if ($att_id) {
                    $captions[$att_id] = sanitize_text_field($cap);
                }
            }

            $raw_prices = (array) ($a['art_prices'] ?? []);
            $prices = [];
            foreach ($raw_prices as $att_id => $price) {
                $att_id = absint($att_id);
                if ($att_id) {
                    $prices[$att_id] = round((float) $price, 2);
                }
            }

            $raw_descriptions = (array) ($a['art_descriptions'] ?? []);
            $descriptions = [];
            foreach ($raw_descriptions as $att_id => $desc) {
                $att_id = absint($att_id);
                if ($att_id) {
                    $descriptions[$att_id] = sanitize_textarea_field($desc);
                }
            }

            $raw_dimensions = (array) ($a['art_dimensions'] ?? []);
            $dimensions = [];
            foreach ($raw_dimensions as $att_id => $dim) {
                $att_id = absint($att_id);
                if ($att_id) {
                    $dimensions[$att_id] = sanitize_text_field($dim);
                }
            }

            $artists[] = [
                'name'             => $name,
                'slug'             => sanitize_title($a['slug'] ?? ''),
                'bio'              => sanitize_textarea_field($a['bio'] ?? ''),
                'art_pieces'       => sanitize_text_field($a['art_pieces'] ?? ''),
                'art_captions'     => $captions,
                'art_descriptions' => $descriptions,
                'art_dimensions'   => $dimensions,
                'art_prices'       => $prices,
                'donate_url'       => esc_url_raw($a['donate_url'] ?? ''),
                'photo_id'         => absint($a['photo_id'] ?? 0),
            ];
        }
        update_post_meta($post_id, '_mae_artists', $artists);
    }

    public function admin_scripts_artists($hook) {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'mae_event') return;

        wp_enqueue_style('thickbox');
        wp_enqueue_script(
            'mae-admin-artists',
            MAE_URL . 'assets/js/mae-admin-artists.js',
            ['jquery', 'media-upload', 'thickbox'],
            MAE_VERSION,
            true
        );
    }

    public function admin_styles() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'mae_event') return;
        ?>
        <style>
        .mae-meta-wrap { padding: 6px 0; }
        .mae-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .mae-meta-full { grid-column: 1 / -1; }
        .mae-meta-field { display: flex; flex-direction: column; gap: 6px; }
        .mae-meta-field label { font-weight: 600; font-size: 13px; color: #1e1e1e; }
        .mae-meta-field input,
        .mae-meta-field select {
            padding: 7px 10px;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            font-size: 14px;
        }
        .mae-meta-field input:focus,
        .mae-meta-field select:focus {
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
            outline: none;
        }
        .mae-meta-checkbox {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            font-size: 13px;
            color: #1e1e1e;
        }
        .mae-meta-checkbox input { width: 16px; height: 16px; margin: 0; }
        .mae-meta-hint { margin: 4px 0 0; font-size: 12px; color: #646970; }

        /* Artists meta box */
        .mae-artists-box { padding: 4px 0; }
        .mae-ar-row { background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 14px; overflow: hidden; }
        .mae-ar-row__header { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #f0f0f0; border-bottom: 1px solid #ddd; }
        .mae-ar-row__header strong { font-size: 13px; }
        .mae-ar-remove { color: #b32d2e !important; border-color: #b32d2e !important; }
        .mae-ar-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding: 14px; }
        .mae-ar-full { grid-column: 1/-1; }
        .mae-ar-fields label { font-weight: 600; font-size: 12px; display: block; margin-bottom: 4px; }
        .mae-ar-fields input[type=text], .mae-ar-fields input[type=url], .mae-ar-fields textarea { width: 100%; padding: 6px 9px; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 13px; box-sizing: border-box; }
        .mae-ar-thumbs { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; min-height: 10px; }
        .mae-ar-thumb { position: relative; width: 160px; display: flex; flex-direction: column; gap: 5px; }
        .mae-ar-thumb img { width: 160px; height: 110px; object-fit: cover; border-radius: 6px; display: block; border: 1px solid #ddd; }
        .mae-ar-thumb__rm { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; border-radius: 50%; background: #b32d2e; color: #fff; border: none; font-size: 12px; line-height: 20px; text-align: center; cursor: pointer; padding: 0; z-index: 1; }
        .mae-ar-caption-input { width: 100%; padding: 5px 7px; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 11px; box-sizing: border-box; color: #1e1e1e; }
        .mae-ar-caption-input:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
        .mae-ar-desc-input { width: 100%; padding: 5px 7px; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 11px; box-sizing: border-box; color: #1e1e1e; margin-top: 3px; resize: vertical; min-height: 42px; font-family: inherit; }
        .mae-ar-desc-input:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
        .mae-ar-dim-input { width: 100%; padding: 4px 7px; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 11px; box-sizing: border-box; color: #1e1e1e; margin-top: 3px; }
        .mae-ar-dim-input:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
        .mae-ar-price-input { width: 100%; padding: 4px 7px; border: 1px solid #c3c4c7; border-radius: 4px; font-size: 11px; box-sizing: border-box; color: #1e1e1e; margin-top: 3px; background: #fffbf5; }
        .mae-ar-price-input:focus { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; outline: none; }
        .mae-ar-photo-wrap { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .mae-ar-photo-preview { width: 80px; height: 80px; border-radius: 50%; overflow: hidden; background: #e8e8e8; border: 2px solid #ddd; flex-shrink: 0; }
        .mae-ar-photo-img { width: 100%; height: 100%; object-fit: cover; display: block; }
        </style>
        <?php
    }
}
