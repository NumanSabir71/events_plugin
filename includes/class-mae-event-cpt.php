<?php
defined('ABSPATH') || exit;

class MAE_Event_CPT {

    public function __construct() {
        add_action('init',                           [$this, 'register']);
        add_filter('single_template',                [$this, 'template']);
        add_filter('archive_template',               [$this, 'archive_template']);
        add_filter('taxonomy_template',              [$this, 'taxonomy_template']);
        add_action('wp_enqueue_scripts',             [$this, 'enqueue_styles']);
        add_filter('get_the_archive_title',          [$this, 'filter_archive_title']);
        add_filter('body_class',                     [$this, 'add_body_class']);
        // Taxonomy image
        add_action('mae_event_cat_add_form_fields',  [$this, 'tax_image_add_field']);
        add_action('mae_event_cat_edit_form_fields', [$this, 'tax_image_edit_field']);
        add_action('created_mae_event_cat',          [$this, 'tax_image_save']);
        add_action('edited_mae_event_cat',           [$this, 'tax_image_save']);
        add_action('admin_enqueue_scripts',          [$this, 'admin_scripts']);
    }

    /** Body class for single event pages so CSS can expand the theme container. */
    public function add_body_class($classes) {
        if (is_singular('mae_event')) {
            $classes[] = 'mae-event-single-page';
        }
        return $classes;
    }

    public function filter_archive_title($title) {
        if (is_tax('mae_event_cat')) {
            return get_queried_object()->name;
        }
        return $title;
    }

    public function admin_scripts($hook) {
        if (!in_array($hook, ['edit-tags.php', 'term.php'], true)) return;
        if (empty($_GET['taxonomy']) || $_GET['taxonomy'] !== 'mae_event_cat') return;

        wp_enqueue_media();
        wp_add_inline_script('jquery-core', "
jQuery(function($){
    function makeFrame(title, selectId, previewId, inputId, removeId) {
        var frame;
        $(document).on('click', '#' + selectId, function(e){
            e.preventDefault();
            if (frame) { frame.open(); return; }
            frame = wp.media({title: title, button: {text: 'Use this image'}, multiple: false});
            frame.on('select', function(){
                var att = frame.state().get('selection').first().toJSON();
                var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
                $('#' + inputId).val(att.id);
                $('#' + previewId).html('<img src=\"' + url + '\" style=\"max-width:200px;height:auto;display:block;margin-top:8px;\">');
                $('#' + removeId).show();
            });
            frame.open();
        });
        $(document).on('click', '#' + removeId, function(e){
            e.preventDefault();
            $('#' + inputId).val('');
            $('#' + previewId).empty();
            $(this).hide();
        });
    }
    makeFrame('Choose Category Hero Image',  'mae-cat-img-select',       'mae-cat-img-preview',       'mae-cat-image-id',       'mae-cat-img-remove');
    makeFrame('Choose About Section Image',  'mae-cat-about-img-select', 'mae-cat-about-img-preview', 'mae-cat-about-image-id', 'mae-cat-about-img-remove');
});
        ");
    }

    public function tax_image_add_field() {
        ?>
        <div class="form-field">
            <label>Category Hero Image</label>
            <div id="mae-cat-img-preview"></div>
            <input type="hidden" name="mae_cat_image_id" id="mae-cat-image-id" value="">
            <button type="button" id="mae-cat-img-select" class="button">Select Image</button>
            <button type="button" id="mae-cat-img-remove" class="button" style="display:none;margin-left:6px;">Remove</button>
            <p class="description">Shown as the hero banner background. Falls back to the first event's thumbnail if not set.</p>
        </div>
        <div class="form-field">
            <label>About Section Image</label>
            <div id="mae-cat-about-img-preview"></div>
            <input type="hidden" name="mae_cat_about_image_id" id="mae-cat-about-image-id" value="">
            <button type="button" id="mae-cat-about-img-select" class="button">Select Image</button>
            <button type="button" id="mae-cat-about-img-remove" class="button" style="display:none;margin-left:6px;">Remove</button>
            <p class="description">Shown on the right side of the "About" section (replaces the statistics grid).</p>
        </div>
        <?php
    }

    public function tax_image_edit_field($term) {
        $img_id       = (int) get_term_meta($term->term_id, '_mae_cat_image_id',       true);
        $about_img_id = (int) get_term_meta($term->term_id, '_mae_cat_about_image_id', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label>Category Hero Image</label></th>
            <td>
                <div id="mae-cat-img-preview">
                    <?php if ($img_id) echo wp_get_attachment_image($img_id, 'medium'); ?>
                </div>
                <input type="hidden" name="mae_cat_image_id" id="mae-cat-image-id" value="<?php echo esc_attr($img_id ?: ''); ?>">
                <button type="button" id="mae-cat-img-select" class="button" style="margin-top:8px;">Select Image</button>
                <button type="button" id="mae-cat-img-remove" class="button" style="margin-top:8px;margin-left:6px;<?php echo $img_id ? '' : 'display:none;'; ?>">Remove</button>
                <p class="description">Shown as the hero banner background. Falls back to the first event's thumbnail if not set.</p>
            </td>
        </tr>
        <tr class="form-field">
            <th scope="row"><label>About Section Image</label></th>
            <td>
                <div id="mae-cat-about-img-preview">
                    <?php if ($about_img_id) echo wp_get_attachment_image($about_img_id, 'medium'); ?>
                </div>
                <input type="hidden" name="mae_cat_about_image_id" id="mae-cat-about-image-id" value="<?php echo esc_attr($about_img_id ?: ''); ?>">
                <button type="button" id="mae-cat-about-img-select" class="button" style="margin-top:8px;">Select Image</button>
                <button type="button" id="mae-cat-about-img-remove" class="button" style="margin-top:8px;margin-left:6px;<?php echo $about_img_id ? '' : 'display:none;'; ?>">Remove</button>
                <p class="description">Shown on the right side of the "About" section (replaces the statistics grid).</p>
            </td>
        </tr>
        <?php
    }

    public function tax_image_save($term_id) {
        if (isset($_POST['mae_cat_image_id'])) {
            $img_id = absint($_POST['mae_cat_image_id']);
            if ($img_id) {
                update_term_meta($term_id, '_mae_cat_image_id', $img_id);
            } else {
                delete_term_meta($term_id, '_mae_cat_image_id');
            }
        }
        if (isset($_POST['mae_cat_about_image_id'])) {
            $about_id = absint($_POST['mae_cat_about_image_id']);
            if ($about_id) {
                update_term_meta($term_id, '_mae_cat_about_image_id', $about_id);
            } else {
                delete_term_meta($term_id, '_mae_cat_about_image_id');
            }
        }
    }

    public function register() {

        // â”€â”€ Event category taxonomy â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        register_taxonomy('mae_event_cat', 'mae_event', [
            'labels' => [
                'name'              => 'Event Categories',
                'singular_name'     => 'Event Category',
                'add_new_item'      => 'Add New Category',
                'edit_item'         => 'Edit Category',
                'all_items'         => 'All Categories',
                'search_items'      => 'Search Categories',
                'not_found'         => 'No categories found',
            ],
            'public'            => true,
            'hierarchical'      => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => ['slug' => 'events/category'],
        ]);

        // â”€â”€ Event post type â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
        register_post_type('mae_event', [
            'labels' => [
                'name'               => 'Events',
                'singular_name'      => 'Event',
                'add_new'            => 'Add New Event',
                'add_new_item'       => 'Add New Event',
                'edit_item'          => 'Edit Event',
                'all_items'          => 'All Events',
                'view_item'          => 'View Event',
                'search_items'       => 'Search Events',
                'not_found'          => 'No events found',
                'not_found_in_trash' => 'No events in trash',
            ],
            'public'        => true,
            'has_archive'   => true,
            'rewrite'       => ['slug' => 'events'],
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
            'menu_icon'     => 'dashicons-calendar-alt',
            'show_in_rest'  => true,
            'menu_position' => 5,
            'taxonomies'    => ['mae_event_cat'],
        ]);
    }

    public function template($template) {
        if (is_singular('mae_event')) {
            $t = MAE_PATH . 'templates/single-event.php';
            if (file_exists($t)) return $t;
        }
        return $template;
    }

    public function archive_template($template) {
        if (is_post_type_archive('mae_event')) {
            $t = MAE_PATH . 'templates/archive-events.php';
            if (file_exists($t)) return $t;
        }
        return $template;
    }

    public function taxonomy_template($template) {
        if (is_tax('mae_event_cat')) {
            $term = get_queried_object();
            // Check for term-specific template first (mirrors WordPress hierarchy)
            if ($term && $term->slug) {
                $specific = MAE_PATH . 'templates/taxonomy-mae_event_cat-' . $term->slug . '.php';
                if (file_exists($specific)) return $specific;
            }
            // Fall back to generic category template
            $generic = MAE_PATH . 'templates/taxonomy-mae_event_cat.php';
            if (file_exists($generic)) return $generic;
        }
        return $template;
    }

    public function enqueue_styles() {
        // Load on all frontend pages â€” conditional checks are unreliable on live servers
        // with caching plugins or unflushed rewrite rules.
        if (is_admin()) return;
        wp_enqueue_style('mae-events', MAE_URL . 'assets/css/mae-events.css', [], MAE_VERSION);
        // Counter animation for archive stats â€” only enqueue when not already registered
        // by the theme (homepage / our-story templates register it; archive does not).
        if (!wp_script_is('mithila-counter', 'enqueued')) {
            wp_enqueue_script(
                'mithila-counter',
                get_stylesheet_directory_uri() . '/assets/js/counter.js',
                [],
                '1.1.8',
                true
            );
        }
    }
}

