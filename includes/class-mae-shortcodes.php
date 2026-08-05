<?php
defined('ABSPATH') || exit;

class MAE_Shortcodes {

    public function __construct() {
        add_shortcode('mae_upcoming_events',          [$this, 'upcoming_events']);
        add_shortcode('mae_upcoming_events_vertical', [$this, 'upcoming_events_vertical']);
        add_shortcode('mae_volunteer_form',           [$this, 'volunteer_form']);
        add_shortcode('mae_sponsor_form',             [$this, 'sponsor_form']);
        add_shortcode('mae_volunteer_events',         [$this, 'volunteer_events']);
        add_shortcode('mae_sponsor_events',           [$this, 'sponsor_events']);
    }

    /**
     * Build the WP query args for an "upcoming" event listing.
     *
     * An event is included while the current date is on or before its end date
     * (the start date is used as a fallback for single-day events). This keeps
     * ongoing events visible and only drops them once they have fully ended.
     */
    private function upcoming_query_args($count, $category = '') {
        $today = date('Y-m-d');

        $args = [
            'post_type'      => 'mae_event',
            'posts_per_page' => max(1, (int) $count),
            'meta_key'       => '_mae_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => [
                'relation' => 'OR',
                // Events with an end date that has not yet passed (covers upcoming
                // multi-day events and currently ongoing events).
                [
                    'key'     => '_mae_end_date',
                    'value'   => $today,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ],
                // Events with no end date whose start date has not yet passed.
                [
                    'relation' => 'AND',
                    [
                        'key'     => '_mae_date',
                        'value'   => $today,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ],
                    [
                        'relation' => 'OR',
                        ['key' => '_mae_end_date', 'compare' => 'NOT EXISTS'],
                        ['key' => '_mae_end_date', 'value' => '', 'compare' => '='],
                    ],
                ],
            ],
        ];

        if (!empty($category)) {
            $args['tax_query'] = [[
                'taxonomy' => 'mae_event_cat',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($category),
            ]];
        }

        return $args;
    }

    /**
     * [mae_upcoming_events count="3" category="slug" title="Upcoming Programs"]
     */
    public function upcoming_events($atts) {
        $atts = shortcode_atts([
            'count'    => 3,
            'category' => '',
            'title'    => '',
        ], $atts, 'mae_upcoming_events');

        $today = date('Y-m-d');
        $count = max(1, (int) $atts['count']);

        $args   = $this->upcoming_query_args($count, $atts['category']);
        $events = get_posts($args);

        if (empty($events)) {
            return '<p class="mae-no-events">No upcoming events at this time.</p>';
        }

        ob_start();
        ?>
        <div class="mae-shortcode-wrap">

            <?php if (!empty($atts['title'])) : ?>
            <h2 class="mae-shortcode-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>

            <div class="mae-events-grid">
                <?php foreach ($events as $ev) :
                    $id       = $ev->ID;
                    $date     = get_post_meta($id, '_mae_date',     true);
                    $end_date = get_post_meta($id, '_mae_end_date', true);
                    $time     = get_post_meta($id, '_mae_time',     true);
                    $loc      = get_post_meta($id, '_mae_location', true);
                    $type     = get_post_meta($id, '_mae_type',     true) ?: 'free';
                    $price    = (float) get_post_meta($id, '_mae_price', true);
                    $is_paid  = $type === 'paid';
                    $excerpt  = get_the_excerpt($ev);
                    $is_today = $date === $today;
                    $is_ongoing = mae_event_is_ongoing($id);
                ?>
                <article class="mae-event-card">

                    <a href="<?php echo get_permalink($id); ?>" class="mae-card-img-link">
                        <?php if (has_post_thumbnail($id)) :
                            echo get_the_post_thumbnail($id, 'medium_large', ['class' => 'mae-card-img']);
                        else : ?>
                        <div class="mae-card-img-placeholder"></div>
                        <?php endif; ?>
                        <?php if ($is_ongoing) : ?>
                        <span class="mae-card-today mae-card-ongoing">Event Ongoing</span>
                        <?php elseif ($is_today) : ?>
                        <span class="mae-card-today">Today</span>
                        <?php endif; ?>
                        <div class="mae-card-badge <?php echo $is_paid ? 'mae-badge-paid' : 'mae-badge-free'; ?>">
                            <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                        </div>
                    </a>

                    <div class="mae-card-body">
                        <?php if ($date) : ?>
                        <div class="mae-card-date-strip">
                            <?php
                            $s = strtotime($date);
                            $e = $end_date ? strtotime($end_date) : 0;
                            $same_month = $e && date('n Y', $s) === date('n Y', $e);
                            $diff_month = $e && !$same_month;
                            ?>
                            <span class="mae-card-month"><?php echo date('M', $s); ?><?php if ($diff_month) echo ' – ' . date('M', $e); ?></span>
                            <span class="mae-card-day"><?php echo date('j', $s); ?><?php if ($same_month) echo '–' . date('j', $e); ?></span>
                        </div>
                        <?php endif; ?>

                        <div class="mae-card-content">
                            <h3 class="mae-card-title">
                                <a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a>
                            </h3>

                            <div class="mae-card-meta">
                                <?php if ($date) : ?>
                                <div class="mae-card-meta-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <?php echo mae_format_date_range($date, $end_date, 'l, F j, Y'); ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($time) : ?>
                                <div class="mae-card-meta-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    <?php echo date('g:i A', strtotime($time)); ?>
                                </div>
                                <?php endif; ?>
                                <?php if ($loc) : ?>
                                <div class="mae-card-meta-item">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <?php echo esc_html($loc); ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($excerpt) : ?>
                            <p class="mae-card-excerpt"><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>

                            <div class="mae-card-footer">
                                <div class="mae-card-price-tag <?php echo $is_paid ? 'paid' : 'free'; ?>">
                                    <?php if ($is_paid) : ?>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                    $<?php echo number_format($price, 2); ?> per ticket
                                    <?php else : ?>
                                    Free Entry
                                    <?php endif; ?>
                                </div>
                                <?php if (mae_is_registration_closed($id)) : ?>
                                <span class="mae-card-btn mae-card-btn--closed">Registration Closed</span>
                                <?php else : ?>
                                <a href="<?php echo get_permalink($id); ?>" class="mae-card-btn">
                                    View &amp; Register →
                                </a>
                                <?php endif; ?>
                            </div>
                            <?php if ($is_ongoing && mae_is_registration_closed($id)) : ?>
                            <p class="mae-event-ongoing-note">This is an ongoing event</p>
                            <?php endif; ?>
                        </div>
                    </div>

                </article>
                <?php endforeach; ?>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * [mae_upcoming_events_vertical count="5" category="slug" title="Upcoming Events"]
     */
    public function upcoming_events_vertical($atts) {
        $atts = shortcode_atts([
            'count'    => 5,
            'category' => '',
            'title'    => '',
        ], $atts, 'mae_upcoming_events_vertical');

        $today = date('Y-m-d');
        $count = max(1, (int) $atts['count']);

        $args   = $this->upcoming_query_args($count, $atts['category']);
        $events = get_posts($args);
        if (empty($events)) {
            return '<p class="mae-no-events">No upcoming events at this time.</p>';
        }

        ob_start();
        ?>
        <div class="mae-upcoming-vertical-wrap">
            <?php if (!empty($atts['title'])) : ?>
            <h2 class="mae-shortcode-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>

            <div class="mae-upcoming-vertical-list">
                <?php foreach ($events as $ev) :
                    $id       = $ev->ID;
                    $date     = get_post_meta($id, '_mae_date',     true);
                    $end_date = get_post_meta($id, '_mae_end_date', true);
                    $time     = get_post_meta($id, '_mae_time',     true);
                    $loc      = get_post_meta($id, '_mae_location', true);
                    $type     = get_post_meta($id, '_mae_type',     true) ?: 'free';
                    $price    = (float) get_post_meta($id, '_mae_price', true);
                    $is_paid  = $type === 'paid';
                    $excerpt  = get_the_excerpt($ev);
                    $is_today = $date === $today;
                    $is_ongoing = mae_event_is_ongoing($id);
                ?>
                <article class="mae-upcoming-v-card">
                    <a href="<?php echo get_permalink($id); ?>" class="mae-upcoming-v-thumb-link">
                        <?php if (has_post_thumbnail($id)) :
                            echo get_the_post_thumbnail($id, 'medium', ['class' => 'mae-upcoming-v-thumb']);
                        else : ?>
                        <div class="mae-upcoming-v-thumb-placeholder"></div>
                        <?php endif; ?>
                        <?php if ($is_ongoing) : ?>
                        <span class="mae-upcoming-v-today mae-upcoming-v-ongoing">Event Ongoing</span>
                        <?php elseif ($is_today) : ?>
                        <span class="mae-upcoming-v-today">Today</span>
                        <?php endif; ?>
                    </a>

                    <div class="mae-upcoming-v-content">
                        <div class="mae-upcoming-v-top">
                            <h3 class="mae-upcoming-v-title">
                                <a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a>
                            </h3>
                            <span class="mae-upcoming-v-price <?php echo $is_paid ? 'paid' : 'free'; ?>">
                                <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                            </span>
                        </div>

                        <div class="mae-upcoming-v-meta">
                            <?php if ($date) : ?>
                            <div><?php echo mae_format_date_range($date, $end_date, 'l, F j, Y'); ?></div>
                            <?php endif; ?>
                            <?php if ($time) : ?>
                            <div><?php echo date('g:i A', strtotime($time)); ?></div>
                            <?php endif; ?>
                            <?php if ($loc) : ?>
                            <div><?php echo esc_html($loc); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($excerpt) : ?>
                        <p class="mae-upcoming-v-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 22, '...')); ?></p>
                        <?php endif; ?>

                        <div class="mae-upcoming-v-actions">
                            <?php if (mae_is_registration_closed($id)) : ?>
                            <span class="mae-upcoming-v-btn mae-upcoming-v-btn--closed">Registration Closed</span>
                            <?php else : ?>
                            <a href="<?php echo get_permalink($id); ?>" class="mae-upcoming-v-btn">View &amp; Register</a>
                            <?php endif; ?>
                        </div>
                        <?php if ($is_ongoing && mae_is_registration_closed($id)) : ?>
                        <p class="mae-event-ongoing-note">This is an ongoing event</p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * [mae_volunteer_form event_id="0" button_text="Apply as Volunteer"]
     */
    public function volunteer_form($atts) {
        static $count = 0;
        $count++;

        $atts = shortcode_atts([
            'event_id'    => 0,
            'button_text' => 'Apply as Volunteer',
        ], $atts, 'mae_volunteer_form');

        $event_id   = absint($atts['event_id']);
        $overlay_id = 'mae-vol-overlay-' . $count;

        ob_start();
        ?>
        <div class="mae-shortcode-form-wrap">
            <button type="button" class="mae-popup-trigger mae-btn-apply"
                    data-target="#<?php echo $overlay_id; ?>">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
        </div>

        <div id="<?php echo $overlay_id; ?>" class="mae-popup-overlay" aria-hidden="true" style="display:none;">
            <div class="mae-popup-modal" role="dialog" aria-modal="true" aria-label="Volunteer Application">

                <div class="mae-popup-header">
                    <h2 class="mae-popup-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Volunteer Application
                    </h2>
                    <button type="button" class="mae-popup-close" aria-label="Close">&times;</button>
                </div>

                <div class="mae-popup-body">
                    <form class="mae-vol-form" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="registration_type" value="volunteer">
                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">

                        <div class="mae-form-banner mae-vol-banner">
                            <div class="mae-form-banner-icon">🙌</div>
                            <div>
                                <div class="mae-form-banner-title">We'd love to have you on the team!</div>
                                <div class="mae-form-banner-sub">Fill in the form below and we'll be in touch.</div>
                            </div>
                        </div>

                        <!-- About You -->
                        <div class="mae-form-block-label">About You</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Full Name <span class="mae-req">*</span></label>
                                <input type="text" name="volunteer_name" class="mae-input" placeholder="e.g. Priya Sharma" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Email Address <span class="mae-req">*</span></label>
                                <input type="email" name="volunteer_email" class="mae-input" placeholder="priya@university.edu" required>
                            </div>
                        </div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Phone Number <span class="mae-req">*</span></label>
                                <input type="tel" name="volunteer_phone" class="mae-input" placeholder="+1 555 000 0000" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">City / Town <span class="mae-req">*</span></label>
                                <input type="text" name="volunteer_city" class="mae-input" placeholder="e.g. Kathmandu" required>
                            </div>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">College / University <span class="mae-optional">(optional)</span></label>
                            <input type="text" name="volunteer_university" class="mae-input" placeholder="e.g. Tribhuvan University, Class of 2025">
                        </div>

                        <!-- Your Role -->
                        <div class="mae-form-block-label">Your Role</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">I'd like to help with <span class="mae-req">*</span></label>
                                <select name="volunteer_help_area" class="mae-select" required>
                                    <option value="">Pick an area…</option>
                                    <option value="event-setup">Event Setup &amp; Decoration</option>
                                    <option value="guest-registration">Guest Registration &amp; Welcome</option>
                                    <option value="photography">Photography / Videography</option>
                                    <option value="social-media">Social Media &amp; Live Coverage</option>
                                    <option value="coordination">On-ground Coordination</option>
                                    <option value="food-beverages">Food &amp; Beverages</option>
                                    <option value="technical">Technical &amp; AV Support</option>
                                    <option value="general">General Support</option>
                                </select>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Availability <span class="mae-req">*</span></label>
                                <select name="volunteer_availability" class="mae-select" required>
                                    <option value="">Select…</option>
                                    <option value="full-day">Full Day</option>
                                    <option value="morning">Morning Only</option>
                                    <option value="afternoon">Afternoon Only</option>
                                    <option value="weekend">Weekends Only</option>
                                    <option value="flexible">Flexible / As Needed</option>
                                </select>
                            </div>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">Your Skills &amp; Strengths <span class="mae-req">*</span></label>
                            <textarea name="volunteer_skills" class="mae-textarea" rows="3"
                                      placeholder="e.g. Photography, social media, good with people…" required></textarea>
                        </div>

                        <!-- Your Story -->
                        <div class="mae-form-block-label">Your Story</div>
                        <div class="mae-field-group">
                            <label class="mae-label">Why do you want to volunteer? <span class="mae-req">*</span></label>
                            <textarea name="volunteer_motivation" class="mae-textarea" rows="4"
                                      placeholder="Tell us what draws you to this opportunity…" required></textarea>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">What do you hope to gain? <span class="mae-optional">(optional)</span></label>
                            <textarea name="volunteer_gain" class="mae-textarea" rows="3"
                                      placeholder="Skills, connections, community experience…"></textarea>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">Prior volunteering experience <span class="mae-optional">(optional)</span></label>
                            <textarea name="volunteer_experience" class="mae-textarea" rows="3"
                                      placeholder="Any relevant past experience…"></textarea>
                        </div>

                        <!-- Commitment -->
                        <div class="mae-commitment-box">
                            <div class="mae-commitment-title">Volunteer Commitment</div>
                            <div class="mae-commitment-text">
                                By applying, you agree to show up on time, follow organiser instructions,
                                treat all guests and team members with respect, and fulfil the duties of your chosen role.
                            </div>
                            <label class="mae-checkbox-row">
                                <input type="checkbox" name="volunteer_agreement" value="1" required>
                                <span>I understand and accept these responsibilities. <span class="mae-req">*</span></span>
                            </label>
                        </div>

                        <div class="mae-form-msg mae-msg" style="display:none;"></div>

                        <div class="mae-actions">
                            <button type="submit" class="mae-btn-submit">
                                <span class="mae-submit-label">Submit Application</span>
                                <span class="mae-submit-spinner mae-spinner" style="display:none;"></span>
                            </button>
                        </div>

                    </form>
                </div><!-- .mae-popup-body -->

            </div><!-- .mae-popup-modal -->
        </div><!-- .mae-popup-overlay -->
        <?php
        return ob_get_clean();
    }

    /**
     * [mae_volunteer_events count="5" category="slug" title="Volunteer Opportunities"]
     * Horizontal event list – each card has an "Apply as Volunteer" button that
     * opens a single shared popup whose event_id and event name are set by JS.
     */
    public function volunteer_events($atts) {
        static $count = 0;
        $count++;

        $atts = shortcode_atts([
            'count'    => 5,
            'category' => '',
            'title'    => '',
        ], $atts, 'mae_volunteer_events');

        $today      = date('Y-m-d');
        $num        = max(1, (int) $atts['count']);
        $overlay_id = 'mae-vol-ev-overlay-' . $count;

        $args   = $this->upcoming_query_args($num, $atts['category']);
        $events = get_posts($args);
        if (empty($events)) {
            return '<p class="mae-no-events">No upcoming events at this time.</p>';
        }

        ob_start();
        ?>
        <div class="mae-upcoming-vertical-wrap">
            <?php if (!empty($atts['title'])) : ?>
            <h2 class="mae-shortcode-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>

            <div class="mae-upcoming-vertical-list">
                <?php foreach ($events as $ev) :
                    $id       = $ev->ID;
                    $date     = get_post_meta($id, '_mae_date',     true);
                    $end_date = get_post_meta($id, '_mae_end_date', true);
                    $time     = get_post_meta($id, '_mae_time',     true);
                    $loc      = get_post_meta($id, '_mae_location', true);
                    $type     = get_post_meta($id, '_mae_type',     true) ?: 'free';
                    $price    = (float) get_post_meta($id, '_mae_price', true);
                    $is_paid  = $type === 'paid';
                    $excerpt  = get_the_excerpt($ev);
                    $is_today = $date === $today;
                    $is_ongoing = mae_event_is_ongoing($id);
                ?>
                <article class="mae-upcoming-v-card">
                    <a href="<?php echo get_permalink($id); ?>" class="mae-upcoming-v-thumb-link">
                        <?php if (has_post_thumbnail($id)) :
                            echo get_the_post_thumbnail($id, 'medium', ['class' => 'mae-upcoming-v-thumb']);
                        else : ?>
                        <div class="mae-upcoming-v-thumb-placeholder"></div>
                        <?php endif; ?>
                        <?php if ($is_ongoing) : ?>
                        <span class="mae-upcoming-v-today mae-upcoming-v-ongoing">Event Ongoing</span>
                        <?php elseif ($is_today) : ?>
                        <span class="mae-upcoming-v-today">Today</span>
                        <?php endif; ?>
                    </a>

                    <div class="mae-upcoming-v-content">
                        <div class="mae-upcoming-v-top">
                            <h3 class="mae-upcoming-v-title">
                                <a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a>
                            </h3>
                            <span class="mae-upcoming-v-price <?php echo $is_paid ? 'paid' : 'free'; ?>">
                                <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                            </span>
                        </div>

                        <div class="mae-upcoming-v-meta">
                            <?php if ($date) : ?>
                            <div><?php echo mae_format_date_range($date, $end_date, 'l, F j, Y'); ?></div>
                            <?php endif; ?>
                            <?php if ($time) : ?>
                            <div><?php echo date('g:i A', strtotime($time)); ?></div>
                            <?php endif; ?>
                            <?php if ($loc) : ?>
                            <div><?php echo esc_html($loc); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($excerpt) : ?>
                        <p class="mae-upcoming-v-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 22, '...')); ?></p>
                        <?php endif; ?>

                        <div class="mae-upcoming-v-actions">
                            <button type="button"
                                    class="mae-upcoming-v-btn mae-popup-trigger"
                                    data-target="#<?php echo esc_attr($overlay_id); ?>"
                                    data-event-id="<?php echo $id; ?>"
                                    data-event-name="<?php echo esc_attr($ev->post_title); ?>">
                                Apply as Volunteer
                            </button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Volunteer popup (event_id + name injected dynamically by JS) -->
        <div id="<?php echo esc_attr($overlay_id); ?>" class="mae-popup-overlay" aria-hidden="true" style="display:none;">
            <div class="mae-popup-modal" role="dialog" aria-modal="true" aria-label="Volunteer Application">

                <div class="mae-popup-header">
                    <h2 class="mae-popup-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Volunteer Application<span class="mae-popup-event-name"></span>
                    </h2>
                    <button type="button" class="mae-popup-close" aria-label="Close">&times;</button>
                </div>

                <div class="mae-popup-body">
                    <form class="mae-vol-form" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="registration_type" value="volunteer">
                        <input type="hidden" name="event_id" value="0">

                        <div class="mae-form-banner mae-vol-banner">
                            <div class="mae-form-banner-icon">🙌</div>
                            <div>
                                <div class="mae-form-banner-title">We'd love to have you on the team!</div>
                                <div class="mae-form-banner-sub">Applying for: <strong class="mae-popup-event-label"></strong></div>
                            </div>
                        </div>

                        <!-- About You -->
                        <div class="mae-form-block-label">About You</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Full Name <span class="mae-req">*</span></label>
                                <input type="text" name="volunteer_name" class="mae-input" placeholder="e.g. Priya Sharma" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Email Address <span class="mae-req">*</span></label>
                                <input type="email" name="volunteer_email" class="mae-input" placeholder="priya@university.edu" required>
                            </div>
                        </div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Phone Number <span class="mae-req">*</span></label>
                                <input type="tel" name="volunteer_phone" class="mae-input" placeholder="+1 555 000 0000" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">City / Town <span class="mae-req">*</span></label>
                                <input type="text" name="volunteer_city" class="mae-input" placeholder="e.g. Kathmandu" required>
                            </div>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">College / University <span class="mae-optional">(optional)</span></label>
                            <input type="text" name="volunteer_university" class="mae-input" placeholder="e.g. Tribhuvan University, Class of 2025">
                        </div>

                        <!-- Your Role -->
                        <div class="mae-form-block-label">Your Role</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">I'd like to help with <span class="mae-req">*</span></label>
                                <select name="volunteer_help_area" class="mae-select" required>
                                    <option value="">Pick an area…</option>
                                    <option value="event-setup">Event Setup &amp; Decoration</option>
                                    <option value="guest-registration">Guest Registration &amp; Welcome</option>
                                    <option value="photography">Photography / Videography</option>
                                    <option value="social-media">Social Media &amp; Live Coverage</option>
                                    <option value="coordination">On-ground Coordination</option>
                                    <option value="food-beverages">Food &amp; Beverages</option>
                                    <option value="technical">Technical &amp; AV Support</option>
                                    <option value="general">General Support</option>
                                </select>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Availability <span class="mae-req">*</span></label>
                                <select name="volunteer_availability" class="mae-select" required>
                                    <option value="">Select…</option>
                                    <option value="full-day">Full Day</option>
                                    <option value="morning">Morning Only</option>
                                    <option value="afternoon">Afternoon Only</option>
                                    <option value="weekend">Weekends Only</option>
                                    <option value="flexible">Flexible / As Needed</option>
                                </select>
                            </div>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">Your Skills &amp; Strengths <span class="mae-req">*</span></label>
                            <textarea name="volunteer_skills" class="mae-textarea" rows="3"
                                      placeholder="e.g. Photography, social media, good with people…" required></textarea>
                        </div>

                        <!-- Your Story -->
                        <div class="mae-form-block-label">Your Story</div>
                        <div class="mae-field-group">
                            <label class="mae-label">Why do you want to volunteer? <span class="mae-req">*</span></label>
                            <textarea name="volunteer_motivation" class="mae-textarea" rows="4"
                                      placeholder="Tell us what draws you to this opportunity…" required></textarea>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">What do you hope to gain? <span class="mae-optional">(optional)</span></label>
                            <textarea name="volunteer_gain" class="mae-textarea" rows="3"
                                      placeholder="Skills, connections, community experience…"></textarea>
                        </div>
                        <div class="mae-field-group">
                            <label class="mae-label">Prior volunteering experience <span class="mae-optional">(optional)</span></label>
                            <textarea name="volunteer_experience" class="mae-textarea" rows="3"
                                      placeholder="Any relevant past experience…"></textarea>
                        </div>

                        <!-- Commitment -->
                        <div class="mae-commitment-box">
                            <div class="mae-commitment-title">Volunteer Commitment</div>
                            <div class="mae-commitment-text">
                                By applying, you agree to show up on time, follow organiser instructions,
                                treat all guests and team members with respect, and fulfil the duties of your chosen role.
                            </div>
                            <label class="mae-checkbox-row">
                                <input type="checkbox" name="volunteer_agreement" value="1" required>
                                <span>I understand and accept these responsibilities. <span class="mae-req">*</span></span>
                            </label>
                        </div>

                        <div class="mae-form-msg mae-msg" style="display:none;"></div>

                        <div class="mae-actions">
                            <button type="submit" class="mae-btn-submit">
                                <span class="mae-submit-label">Submit Application</span>
                                <span class="mae-submit-spinner mae-spinner" style="display:none;"></span>
                            </button>
                        </div>

                    </form>
                </div><!-- .mae-popup-body -->

            </div><!-- .mae-popup-modal -->
        </div><!-- .mae-popup-overlay -->
        <?php
        return ob_get_clean();
    }

    /**
     * [mae_sponsor_events count="5" category="slug" title="Sponsorship Opportunities"]
     * Horizontal event list – each card has an "Apply for Sponsorship" button that
     * opens a single shared popup whose event_id and event name are set by JS.
     */
    public function sponsor_events($atts) {
        static $count = 0;
        $count++;

        $atts = shortcode_atts([
            'count'    => 5,
            'category' => '',
            'title'    => '',
        ], $atts, 'mae_sponsor_events');

        $today      = date('Y-m-d');
        $num        = max(1, (int) $atts['count']);
        $overlay_id = 'mae-spon-ev-overlay-' . $count;
        $gateway    = get_option('mae_payment_gateway', 'stripe');

        if ($gateway === 'stripe' && get_option('mae_stripe_publishable_key', '')) {
            wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
        } elseif ($gateway === 'paypal' && get_option('mae_paypal_client_id', '')) {
            $cid = get_option('mae_paypal_client_id', '');
            wp_enqueue_script('paypal-sdk', "https://www.paypal.com/sdk/js?client-id={$cid}&currency=USD", [], null, true);
        }

        $args   = $this->upcoming_query_args($num, $atts['category']);
        $events = get_posts($args);
        if (empty($events)) {
            return '<p class="mae-no-events">No upcoming events at this time.</p>';
        }

        ob_start();
        ?>
        <div class="mae-upcoming-vertical-wrap">
            <?php if (!empty($atts['title'])) : ?>
            <h2 class="mae-shortcode-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php endif; ?>

            <div class="mae-upcoming-vertical-list">
                <?php foreach ($events as $ev) :
                    $id       = $ev->ID;
                    $date     = get_post_meta($id, '_mae_date',     true);
                    $end_date = get_post_meta($id, '_mae_end_date', true);
                    $time     = get_post_meta($id, '_mae_time',     true);
                    $loc      = get_post_meta($id, '_mae_location', true);
                    $type     = get_post_meta($id, '_mae_type',     true) ?: 'free';
                    $price    = (float) get_post_meta($id, '_mae_price', true);
                    $is_paid  = $type === 'paid';
                    $excerpt  = get_the_excerpt($ev);
                    $is_today = $date === $today;
                    $is_ongoing = mae_event_is_ongoing($id);
                ?>
                <article class="mae-upcoming-v-card">
                    <a href="<?php echo get_permalink($id); ?>" class="mae-upcoming-v-thumb-link">
                        <?php if (has_post_thumbnail($id)) :
                            echo get_the_post_thumbnail($id, 'medium', ['class' => 'mae-upcoming-v-thumb']);
                        else : ?>
                        <div class="mae-upcoming-v-thumb-placeholder"></div>
                        <?php endif; ?>
                        <?php if ($is_ongoing) : ?>
                        <span class="mae-upcoming-v-today mae-upcoming-v-ongoing">Event Ongoing</span>
                        <?php elseif ($is_today) : ?>
                        <span class="mae-upcoming-v-today">Today</span>
                        <?php endif; ?>
                    </a>

                    <div class="mae-upcoming-v-content">
                        <div class="mae-upcoming-v-top">
                            <h3 class="mae-upcoming-v-title">
                                <a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a>
                            </h3>
                            <span class="mae-upcoming-v-price <?php echo $is_paid ? 'paid' : 'free'; ?>">
                                <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                            </span>
                        </div>

                        <div class="mae-upcoming-v-meta">
                            <?php if ($date) : ?>
                            <div><?php echo mae_format_date_range($date, $end_date, 'l, F j, Y'); ?></div>
                            <?php endif; ?>
                            <?php if ($time) : ?>
                            <div><?php echo date('g:i A', strtotime($time)); ?></div>
                            <?php endif; ?>
                            <?php if ($loc) : ?>
                            <div><?php echo esc_html($loc); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if ($excerpt) : ?>
                        <p class="mae-upcoming-v-excerpt"><?php echo esc_html(wp_trim_words($excerpt, 22, '...')); ?></p>
                        <?php endif; ?>

                        <div class="mae-upcoming-v-actions">
                            <button type="button"
                                    class="mae-upcoming-v-btn mae-popup-trigger"
                                    data-target="#<?php echo esc_attr($overlay_id); ?>"
                                    data-event-id="<?php echo $id; ?>"
                                    data-event-name="<?php echo esc_attr($ev->post_title); ?>">
                                Apply for Sponsorship
                            </button>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sponsorship popup (event_id + name injected dynamically by JS) -->
        <div id="<?php echo esc_attr($overlay_id); ?>" class="mae-popup-overlay" aria-hidden="true" style="display:none;">
            <div class="mae-popup-modal" role="dialog" aria-modal="true" aria-label="Sponsorship Enquiry">

                <div class="mae-popup-header">
                    <h2 class="mae-popup-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        Partnership &amp; Sponsorship<span class="mae-popup-event-name"></span>
                    </h2>
                    <button type="button" class="mae-popup-close" aria-label="Close">&times;</button>
                </div>

                <div class="mae-popup-body">
                    <form class="mae-spon-form" enctype="multipart/form-data" novalidate
                          data-gateway="<?php echo esc_attr($gateway); ?>">
                        <input type="hidden" name="registration_type" value="sponsor">
                        <input type="hidden" name="event_id" value="0">
                        <input type="hidden" name="payment_id" class="mae-spon-payment-id" value="">

                        <div class="mae-form-banner mae-spon-banner">
                            <div class="mae-form-banner-icon">🤝</div>
                            <div>
                                <div class="mae-form-banner-title">Partner with us!</div>
                                <div class="mae-form-banner-sub">Sponsoring: <strong class="mae-popup-event-label"></strong></div>
                            </div>
                        </div>

                        <!-- Organisation Details -->
                        <div class="mae-form-block-label">Organisation Details</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Organisation Name <span class="mae-req">*</span></label>
                                <input type="text" name="company_name" class="mae-input" placeholder="e.g. Ministry of Culture" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Organisation Type <span class="mae-req">*</span></label>
                                <select name="org_type" class="mae-select" required>
                                    <option value="">Select type…</option>
                                    <option value="private-business">Private Business / Corporation</option>
                                    <option value="government">Government Office / Department</option>
                                    <option value="ngo">NGO / Non-profit</option>
                                    <option value="educational">Educational Institution</option>
                                    <option value="media">Media / Press</option>
                                    <option value="embassy">Embassy / Consulate</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Primary Contact Person <span class="mae-req">*</span></label>
                                <input type="text" name="contact_person" class="mae-input" placeholder="Full name &amp; designation" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Official Email <span class="mae-req">*</span></label>
                                <input type="email" name="sponsor_email" class="mae-input" placeholder="name@organisation.org" required>
                            </div>
                        </div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Phone / Office Number <span class="mae-req">*</span></label>
                                <input type="tel" name="sponsor_phone" class="mae-input" placeholder="+977 01 000 0000" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Website <span class="mae-optional">(optional)</span></label>
                                <input type="url" name="website" class="mae-input" placeholder="https://organisation.org">
                            </div>
                        </div>

                        <!-- Participation -->
                        <div class="mae-form-block-label">
                            How would you like to participate? <span class="mae-req">*</span>
                            <span class="mae-block-hint">Select all that apply</span>
                        </div>
                        <div class="mae-participation-grid">
                            <?php
                            $opts = [
                                'financial-sponsorship' => ['💰', 'Financial Sponsorship',    'Monetary contribution to support the event'],
                                'venue-space'           => ['🏛️', 'Venue / Space',             'Provide a venue or outdoor space'],
                                'in-kind-support'       => ['📦', 'In-Kind Support',            'Goods, materials, or services'],
                                'media-coverage'        => ['📰', 'Media &amp; Press Coverage', 'Press, broadcast, or digital coverage'],
                                'food-beverages'        => ['🍽️', 'Food &amp; Beverages',      'Catering or refreshments'],
                                'technical-support'     => ['🎙️', 'Technical / AV Support',    'Sound, lighting, or equipment'],
                                'equipment-resources'   => ['🖨️', 'Equipment / Resources',     'Printers, vehicles, supplies'],
                                'other'                 => ['✏️', 'Other',                     'Something not listed above'],
                            ];
                            foreach ($opts as $val => [$emoji, $label, $desc]) : ?>
                            <label class="mae-participation-option">
                                <input type="checkbox" name="participation_interests[]" value="<?php echo esc_attr($val); ?>">
                                <div class="mae-part-inner">
                                    <span class="mae-part-emoji"><?php echo $emoji; ?></span>
                                    <div>
                                        <div class="mae-part-label"><?php echo $label; ?></div>
                                        <div class="mae-part-desc"><?php echo $desc; ?></div>
                                    </div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Sponsorship Details -->
                        <div class="mae-form-block-label">Sponsorship Details</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Partnership Level <span class="mae-req">*</span></label>
                                <select name="sponsorship_type" class="mae-select" required>
                                    <option value="">Select level…</option>
                                    <option value="platinum">🏆 Platinum Partner</option>
                                    <option value="gold">🥇 Gold Partner</option>
                                    <option value="silver">🥈 Silver Partner</option>
                                    <option value="bronze">🥉 Bronze Partner</option>
                                    <option value="in-kind">📦 In-Kind Partner</option>
                                    <option value="custom">✏️ Custom / To be discussed</option>
                                </select>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Estimated Budget / Contribution <span class="mae-req">*</span></label>
                                <select name="budget" class="mae-select" required>
                                    <option value="">Select range…</option>
                                    <option value="under-500">Under $500</option>
                                    <option value="500-1000">$500 – $1,000</option>
                                    <option value="1000-5000">$1,000 – $5,000</option>
                                    <option value="5000-10000">$5,000 – $10,000</option>
                                    <option value="over-10000">Over $10,000</option>
                                    <option value="in-kind">In-Kind (non-monetary)</option>
                                    <option value="tbd">To be discussed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Logo -->
                        <div class="mae-form-block-label">Branding</div>
                        <div class="mae-field-group">
                            <label class="mae-label">Organisation Logo <span class="mae-optional">(optional)</span></label>
                            <div class="mae-file-zone">
                                <input type="file" name="sponsor_logo" class="mae-spon-logo-input" accept="image/*">
                                <label class="mae-file-label">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span class="mae-file-label-text">Upload logo — PNG, JPG, or SVG (max 2 MB)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="mae-field-group">
                            <label class="mae-label">Additional Notes or Requests <span class="mae-optional">(optional)</span></label>
                            <textarea name="sponsor_message" class="mae-textarea" rows="3"
                                      placeholder="Any specific requirements, questions, or proposals…"></textarea>
                        </div>

                        <!-- Contribution & Payment -->
                        <div class="mae-form-block-label">Contribution &amp; Payment</div>

                        <div class="mae-spon-amount-wrap">
                            <div class="mae-field-group">
                                <label class="mae-label">Sponsorship Amount (USD) <span class="mae-optional">(optional)</span></label>
                                <div class="mae-amount-input-row">
                                    <span class="mae-currency-symbol">$</span>
                                    <input type="number" name="sponsor_payment_amount"
                                           class="mae-input mae-spon-amount-input"
                                           min="1" step="0.01" placeholder="0.00">
                                </div>
                                <p class="mae-field-hint">Enter an amount to pay online now, or leave blank to submit as an enquiry and arrange payment later.</p>
                            </div>
                        </div>

                        <div class="mae-spon-payment-wrap" style="display:none;">
                            <?php if ($gateway === 'stripe') : ?>
                            <div class="mae-spon-payment-element mae-payment-element"></div>
                            <div class="mae-spon-stripe-error mae-msg mae-msg-error" style="display:none;"></div>
                            <?php elseif ($gateway === 'paypal') : ?>
                            <div class="mae-spon-paypal-btn"></div>
                            <?php endif; ?>
                        </div>

                        <div class="mae-form-msg mae-msg" style="display:none;"></div>

                        <div class="mae-actions">
                            <button type="submit" class="mae-btn-submit">
                                <span class="mae-submit-label">Send Partnership Enquiry</span>
                                <span class="mae-submit-spinner mae-spinner" style="display:none;"></span>
                            </button>
                        </div>

                    </form>
                </div><!-- .mae-popup-body -->

            </div><!-- .mae-popup-modal -->
        </div><!-- .mae-popup-overlay -->
        <?php
        return ob_get_clean();
    }

    /**
     * [mae_sponsor_form event_id="0" button_text="Become a Sponsor"]
     */
    public function sponsor_form($atts) {
        static $count = 0;
        $count++;

        $atts = shortcode_atts([
            'event_id'    => 0,
            'button_text' => 'Become a Sponsor',
        ], $atts, 'mae_sponsor_form');

        $event_id   = absint($atts['event_id']);
        $overlay_id = 'mae-spon-overlay-' . $count;
        $gateway    = get_option('mae_payment_gateway', 'stripe');

        // Enqueue payment SDKs so they're ready when the popup opens
        if ($gateway === 'stripe' && get_option('mae_stripe_publishable_key', '')) {
            wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);
        } elseif ($gateway === 'paypal' && get_option('mae_paypal_client_id', '')) {
            $cid = get_option('mae_paypal_client_id', '');
            wp_enqueue_script('paypal-sdk', "https://www.paypal.com/sdk/js?client-id={$cid}&currency=USD", [], null, true);
        }

        ob_start();
        ?>
        <div class="mae-shortcode-form-wrap">
            <button type="button" class="mae-popup-trigger mae-btn-apply"
                    data-target="#<?php echo $overlay_id; ?>">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
        </div>

        <div id="<?php echo $overlay_id; ?>" class="mae-popup-overlay" aria-hidden="true" style="display:none;">
            <div class="mae-popup-modal" role="dialog" aria-modal="true" aria-label="Sponsorship Enquiry">

                <div class="mae-popup-header">
                    <h2 class="mae-popup-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        Partnership &amp; Sponsorship Enquiry
                    </h2>
                    <button type="button" class="mae-popup-close" aria-label="Close">&times;</button>
                </div>

                <div class="mae-popup-body">
                    <form class="mae-spon-form" enctype="multipart/form-data" novalidate
                          data-gateway="<?php echo esc_attr($gateway); ?>">
                        <input type="hidden" name="registration_type" value="sponsor">
                        <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                        <input type="hidden" name="payment_id" class="mae-spon-payment-id" value="">

                        <div class="mae-form-banner mae-spon-banner">
                            <div class="mae-form-banner-icon">🤝</div>
                            <div>
                                <div class="mae-form-banner-title">Partner with us!</div>
                                <div class="mae-form-banner-sub">Amplify your impact and connect with our community.</div>
                            </div>
                        </div>

                        <!-- Organisation Details -->
                        <div class="mae-form-block-label">Organisation Details</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Organisation Name <span class="mae-req">*</span></label>
                                <input type="text" name="company_name" class="mae-input" placeholder="e.g. Ministry of Culture" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Organisation Type <span class="mae-req">*</span></label>
                                <select name="org_type" class="mae-select" required>
                                    <option value="">Select type…</option>
                                    <option value="private-business">Private Business / Corporation</option>
                                    <option value="government">Government Office / Department</option>
                                    <option value="ngo">NGO / Non-profit</option>
                                    <option value="educational">Educational Institution</option>
                                    <option value="media">Media / Press</option>
                                    <option value="embassy">Embassy / Consulate</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Primary Contact Person <span class="mae-req">*</span></label>
                                <input type="text" name="contact_person" class="mae-input" placeholder="Full name &amp; designation" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Official Email <span class="mae-req">*</span></label>
                                <input type="email" name="sponsor_email" class="mae-input" placeholder="name@organisation.org" required>
                            </div>
                        </div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Phone / Office Number <span class="mae-req">*</span></label>
                                <input type="tel" name="sponsor_phone" class="mae-input" placeholder="+977 01 000 0000" required>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Website <span class="mae-optional">(optional)</span></label>
                                <input type="url" name="website" class="mae-input" placeholder="https://organisation.org">
                            </div>
                        </div>

                        <!-- Participation -->
                        <div class="mae-form-block-label">
                            How would you like to participate? <span class="mae-req">*</span>
                            <span class="mae-block-hint">Select all that apply</span>
                        </div>
                        <div class="mae-participation-grid">
                            <?php
                            $opts = [
                                'financial-sponsorship' => ['💰', 'Financial Sponsorship',    'Monetary contribution to support the event'],
                                'venue-space'           => ['🏛️', 'Venue / Space',             'Provide a venue or outdoor space'],
                                'in-kind-support'       => ['📦', 'In-Kind Support',            'Goods, materials, or services'],
                                'media-coverage'        => ['📰', 'Media &amp; Press Coverage', 'Press, broadcast, or digital coverage'],
                                'food-beverages'        => ['🍽️', 'Food &amp; Beverages',      'Catering or refreshments'],
                                'technical-support'     => ['🎙️', 'Technical / AV Support',    'Sound, lighting, or equipment'],
                                'equipment-resources'   => ['🖨️', 'Equipment / Resources',     'Printers, vehicles, supplies'],
                                'other'                 => ['✏️', 'Other',                     'Something not listed above'],
                            ];
                            foreach ($opts as $val => [$emoji, $label, $desc]) : ?>
                            <label class="mae-participation-option">
                                <input type="checkbox" name="participation_interests[]" value="<?php echo esc_attr($val); ?>">
                                <div class="mae-part-inner">
                                    <span class="mae-part-emoji"><?php echo $emoji; ?></span>
                                    <div>
                                        <div class="mae-part-label"><?php echo $label; ?></div>
                                        <div class="mae-part-desc"><?php echo $desc; ?></div>
                                    </div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Sponsorship Details -->
                        <div class="mae-form-block-label">Sponsorship Details</div>
                        <div class="mae-row-2">
                            <div class="mae-field-group">
                                <label class="mae-label">Partnership Level <span class="mae-req">*</span></label>
                                <select name="sponsorship_type" class="mae-select" required>
                                    <option value="">Select level…</option>
                                    <option value="platinum">🏆 Platinum Partner</option>
                                    <option value="gold">🥇 Gold Partner</option>
                                    <option value="silver">🥈 Silver Partner</option>
                                    <option value="bronze">🥉 Bronze Partner</option>
                                    <option value="in-kind">📦 In-Kind Partner</option>
                                    <option value="custom">✏️ Custom / To be discussed</option>
                                </select>
                            </div>
                            <div class="mae-field-group">
                                <label class="mae-label">Estimated Budget / Contribution <span class="mae-req">*</span></label>
                                <select name="budget" class="mae-select" required>
                                    <option value="">Select range…</option>
                                    <option value="under-500">Under $500</option>
                                    <option value="500-1000">$500 – $1,000</option>
                                    <option value="1000-5000">$1,000 – $5,000</option>
                                    <option value="5000-10000">$5,000 – $10,000</option>
                                    <option value="over-10000">Over $10,000</option>
                                    <option value="in-kind">In-Kind (non-monetary)</option>
                                    <option value="tbd">To be discussed</option>
                                </select>
                            </div>
                        </div>

                        <!-- Logo -->
                        <div class="mae-form-block-label">Branding</div>
                        <div class="mae-field-group">
                            <label class="mae-label">Organisation Logo <span class="mae-optional">(optional)</span></label>
                            <div class="mae-file-zone">
                                <input type="file" name="sponsor_logo" class="mae-spon-logo-input" accept="image/*">
                                <label class="mae-file-label">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <span class="mae-file-label-text">Upload logo — PNG, JPG, or SVG (max 2 MB)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Message -->
                        <div class="mae-field-group">
                            <label class="mae-label">Additional Notes or Requests <span class="mae-optional">(optional)</span></label>
                            <textarea name="sponsor_message" class="mae-textarea" rows="3"
                                      placeholder="Any specific requirements, questions, or proposals…"></textarea>
                        </div>

                        <!-- ── Contribution & Payment ──────────────────── -->
                        <div class="mae-form-block-label">Contribution &amp; Payment</div>

                        <div class="mae-spon-amount-wrap">
                            <div class="mae-field-group">
                                <label class="mae-label">Sponsorship Amount (USD) <span class="mae-optional">(optional)</span></label>
                                <div class="mae-amount-input-row">
                                    <span class="mae-currency-symbol">$</span>
                                    <input type="number" name="sponsor_payment_amount"
                                           class="mae-input mae-spon-amount-input"
                                           min="1" step="0.01" placeholder="0.00">
                                </div>
                                <p class="mae-field-hint">Enter an amount to pay online now, or leave blank to submit as an enquiry and arrange payment later.</p>
                            </div>
                        </div>

                        <!-- Payment element (shown once a valid amount is entered) -->
                        <div class="mae-spon-payment-wrap" style="display:none;">
                            <?php if ($gateway === 'stripe') : ?>
                            <div class="mae-spon-payment-element mae-payment-element"></div>
                            <div class="mae-spon-stripe-error mae-msg mae-msg-error" style="display:none;"></div>
                            <?php elseif ($gateway === 'paypal') : ?>
                            <div class="mae-spon-paypal-btn"></div>
                            <?php endif; ?>
                        </div>

                        <div class="mae-form-msg mae-msg" style="display:none;"></div>

                        <div class="mae-actions">
                            <button type="submit" class="mae-btn-submit">
                                <span class="mae-submit-label">Send Partnership Enquiry</span>
                                <span class="mae-submit-spinner mae-spinner" style="display:none;"></span>
                            </button>
                        </div>

                    </form>
                </div><!-- .mae-popup-body -->

            </div><!-- .mae-popup-modal -->
        </div><!-- .mae-popup-overlay -->
        <?php
        return ob_get_clean();
    }
}
