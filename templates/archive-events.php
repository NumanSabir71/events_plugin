<?php

/**
 * Events archive template.
 *
 * @package Mithila_Art_Events
 * @version 2.1.0
 */
defined('ABSPATH') || exit;

get_header();

$today      = date('Y-m-d');
$categories = get_terms(['taxonomy' => 'mae_event_cat', 'hide_empty' => false]);

// Aggregate totals for hero stats
$total_events    = 0;
$upcoming_events = 0;
$total_cats      = is_array($categories) && !is_wp_error($categories) ? count($categories) : 0;

if ($total_cats) {
    foreach ($categories as $cat) {
        $evs = get_posts([
            'post_type'   => 'mae_event',
            'numberposts' => -1,
            'tax_query'   => [['taxonomy' => 'mae_event_cat', 'terms' => $cat->term_id]],
            'fields'      => 'ids',
        ]);
        $total_events += count($evs);
        foreach ($evs as $eid) {
            $d = get_post_meta($eid, '_mae_date', true);
            if (!$d || $d >= $today) $upcoming_events++;
        }
    }
}

// Helper: render a single event card
function mae_arch_event_card($post_id, $is_past = false)
{
    $date     = get_post_meta($post_id, '_mae_date',     true);
    $end_date = get_post_meta($post_id, '_mae_end_date', true);
    $loc     = get_post_meta($post_id, '_mae_location', true);
    $type    = get_post_meta($post_id, '_mae_type', true) ?: 'free';
    $price   = (float) get_post_meta($post_id, '_mae_price', true);
    $is_paid = $type === 'paid';
    $cats    = get_the_terms($post_id, 'mae_event_cat');
    $link    = get_permalink($post_id);
    $title   = get_the_title($post_id);
    $class   = 'mae-event-card' . ($is_past ? ' mae-event-card--past' : '');
?>
    <article class="<?php echo $class; ?>">
        <a href="<?php echo esc_url($link); ?>" class="mae-card-img-link">
            <?php if (has_post_thumbnail($post_id)) : echo get_the_post_thumbnail($post_id, 'medium_large', ['class' => 'mae-card-img']);
            else : ?>
                <div class="mae-card-img-placeholder"></div>
            <?php endif; ?>
            <?php if ($is_past) : ?>
                <span class="mae-card-past-badge">Past</span>
            <?php else : ?>
                <div class="mae-card-badge <?php echo $is_paid ? 'mae-badge-paid' : 'mae-badge-free'; ?>">
                    <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                </div>
            <?php endif; ?>
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
                <?php if ($cats && !is_wp_error($cats)) : ?>
                    <span class="mae-card-cat-tag"><?php echo esc_html($cats[0]->name); ?></span>
                <?php endif; ?>
                <h3 class="mae-card-title"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a></h3>
                <?php if ($loc) : ?>
                    <p class="mae-card-location">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                        </svg>
                        <?php echo esc_html($loc); ?>
                    </p>
                <?php endif; ?>
                <div class="mae-card-footer">
                    <div class="mae-card-price-tag <?php echo $is_paid ? 'paid' : 'free'; ?>">
                        <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free Entry'; ?>
                    </div>
                    <a href="<?php echo esc_url($link); ?>" class="mae-card-btn">
                        <?php echo $is_past ? 'View Details' : 'Register'; ?> &#x2192;
                    </a>
                </div>
            </div>
        </div>
    </article>
<?php
}
?>

<div class="mae-archive-page mae-tax-rich">

    <!-- ═══════════════════ HERO ═══════════════════ -->
    <section class="mae-arch-hero">
        <div class="mae-arch-hero__bg-pattern"></div>
        <div class="mae-arch-hero__inner mae-tax-container">
            <p class="mae-arch-hero__kicker">Programs &amp; Events</p>
            <h1 class="mae-arch-hero__title">Where Mithila Art<br>Comes Alive</h1>
            <div class="mae-arch-hero__divider"></div>
            <p class="mae-arch-hero__desc">From hands-on art workshops for children to international cultural festivals, Mithila Center USA hosts programs that celebrate, preserve, and advance Mithila art across the United States and beyond. Every event is an invitation — to learn, to connect, and to be part of something enduring.</p>
            <div class="mae-arch-hero__pills">
                <span class="mae-arch-hero__pill"><?php echo $total_cats; ?> Program Areas</span>
                <?php if ($upcoming_events > 0) : ?>
                    <span class="mae-arch-hero__pill mae-arch-hero__pill--accent"><?php echo $upcoming_events; ?> Upcoming</span>
                <?php endif; ?>
                <span class="mae-arch-hero__pill">Open to All</span>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ IMPACT STATS ═══════════════════ -->
    <section class="mae-arch-stats">
        <div class="mae-tax-container">
            <div class="mae-arch-stats__grid">
                <div class="mae-arch-stats__item">
                    <div class="mae-arch-stats__num-row">
                        <span class="mae-arch-stats__number" data-count="15">0</span><span class="mae-arch-stats__suffix">+</span>
                    </div>
                    <span class="mae-arch-stats__label">Years of Programming</span>
                </div>
                <div class="mae-arch-stats__item">
                    <div class="mae-arch-stats__num-row">
                        <span class="mae-arch-stats__number" data-count="50">0</span><span class="mae-arch-stats__suffix">+</span>
                    </div>
                    <span class="mae-arch-stats__label">Annual Events</span>
                </div>
                <div class="mae-arch-stats__item">
                    <div class="mae-arch-stats__num-row">
                        <span class="mae-arch-stats__number" data-count="10">0</span><span class="mae-arch-stats__suffix">K+</span>
                    </div>
                    <span class="mae-arch-stats__label">Community Members Reached</span>
                </div>
                <div class="mae-arch-stats__item">
                    <div class="mae-arch-stats__num-row">
                        <span class="mae-arch-stats__number" data-count="25">0</span><span class="mae-arch-stats__suffix">+</span>
                    </div>
                    <span class="mae-arch-stats__label">Partner Organisations</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ CATEGORY GRID ═══════════════════ -->
    <?php if (!empty($categories) && !is_wp_error($categories)) : ?>

        <section class="mae-arch-cats">
            <div class="mae-tax-container">
                <p class="mae-arch-section__kicker">Explore by Program Area</p>
                <h2 class="mae-arch-section__heading">Our Event Categories</h2>
                <div class="mae-arch-section__divider"></div>

                <div class="mae-cat-grid">
                    <?php foreach ($categories as $cat) :

                        $events = get_posts([
                            'post_type'   => 'mae_event',
                            'numberposts' => -1,
                            'tax_query'   => [['taxonomy' => 'mae_event_cat', 'terms' => $cat->term_id]],
                            'meta_key'    => '_mae_date',
                            'orderby'     => 'meta_value',
                            'order'       => 'ASC',
                        ]);

                        $total      = count($events);
                        $upcoming   = 0;
                        $cat_img_id = (int) get_term_meta($cat->term_id, '_mae_cat_image_id', true);
                        $thumb_id   = $cat_img_id ?: 0;

                        foreach ($events as $ev) {
                            $d = get_post_meta($ev->ID, '_mae_date', true);
                            if (!$d || $d >= $today) $upcoming++;
                            if (!$thumb_id && has_post_thumbnail($ev->ID)) {
                                $thumb_id = get_post_thumbnail_id($ev->ID);
                            }
                        }
                    ?>
                        <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="mae-cat-card">

                            <div class="mae-cat-img-wrap">
                                <?php if ($thumb_id) : ?>
                                    <?php echo wp_get_attachment_image($thumb_id, 'medium_large', false, ['class' => 'mae-cat-img']); ?>
                                <?php else : ?>
                                    <div class="mae-cat-img-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div class="mae-cat-overlay"></div>
                            </div>

                            <div class="mae-cat-body">
                                <h2 class="mae-cat-name"><?php echo esc_html($cat->name); ?></h2>

                                <?php if ($cat->description) : ?>
                                    <p class="mae-cat-desc"><?php echo esc_html(wp_trim_words($cat->description, 16, '…')); ?></p>
                                <?php endif; ?>

                                <div class="mae-cat-counts">
                                    <span class="mae-cat-count-pill upcoming">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <?php echo $upcoming; ?> upcoming
                                    </span>
                                    <span class="mae-cat-count-pill past">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <?php echo $total - $upcoming; ?> past
                                    </span>
                                </div>

                                <div class="mae-cat-arrow">
                                    View Programs
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <line x1="5" y1="12" x2="19" y2="12" />
                                        <polyline points="12 5 19 12 12 19" />
                                    </svg>
                                </div>
                            </div>

                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php endif; ?>

    <!-- ═══════════════════ UPCOMING EVENTS ═══════════════════ -->
    <?php
    $upcoming_q = new WP_Query([
        'post_type'      => 'mae_event',
        'posts_per_page' => 6,
        'meta_key'       => '_mae_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [
            ['key' => '_mae_date', 'value' => $today, 'compare' => '>=', 'type' => 'DATE'],
        ],
    ]);
    if ($upcoming_q->have_posts()) : ?>
        <section class="mae-arch-events-section">
            <div class="mae-tax-container">
                <div class="mae-arch-events-hdr">
                    <div>
                        <p class="mae-arch-section__kicker">What&#x27;s Coming Up</p>
                        <h2 class="mae-arch-section__heading">Upcoming Events</h2>
                        <div class="mae-arch-section__divider mae-arch-section__divider--flush"></div>
                    </div>
                </div>
                <div class="mae-events-grid">
                    <?php while ($upcoming_q->have_posts()) : $upcoming_q->the_post();
                        mae_arch_event_card(get_the_ID(), false);
                    endwhile;
                    wp_reset_postdata(); ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ═══════════════════ PAST EVENTS ═══════════════════ -->
    <?php
    $past_q = new WP_Query([
        'post_type'      => 'mae_event',
        'posts_per_page' => -1,
        'meta_key'       => '_mae_date',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
        'meta_query'     => [
            ['key' => '_mae_date', 'value' => $today, 'compare' => '<', 'type' => 'DATE'],
        ],
    ]);
    if ($past_q->have_posts()) : ?>
        <section class="mae-arch-events-section mae-arch-events-section--past">
            <div class="mae-tax-container">
                <div class="mae-arch-events-hdr">
                    <div>
                        <p class="mae-arch-section__kicker">Look Back</p>
                        <h2 class="mae-arch-section__heading">Past Events</h2>
                        <div class="mae-arch-section__divider mae-arch-section__divider--flush"></div>
                    </div>
                </div>
                <div class="mae-past-carousel">
                    <div class="mae-past-slider-wrap">
                        <button class="mae-past-nav mae-past-nav--prev" aria-label="<?php esc_attr_e('Previous events', 'mae'); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="15 18 9 12 15 6" />
                            </svg>
                        </button>
                        <div class="mae-past-slider">
                            <div class="mae-past-track" id="mae-past-track">
                                <?php while ($past_q->have_posts()) : $past_q->the_post();
                                    mae_arch_event_card(get_the_ID(), true);
                                endwhile;
                                wp_reset_postdata(); ?>
                            </div>
                        </div>
                        <button class="mae-past-nav mae-past-nav--next" aria-label="<?php esc_attr_e('Next events', 'mae'); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="9 18 15 12 9 6" />
                            </svg>
                        </button>
                    </div>
                    <div class="mae-past-dots"></div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- ═══════════════════ MITHILA ART ABOUT STRIP ═══════════════════ -->
    <section class="mae-arch-about">
        <div class="mae-tax-container mae-arch-about__inner">
            <div class="mae-arch-about__text">
                <p class="mae-arch-about__kicker">About Our Programming</p>
                <h2 class="mae-arch-about__heading">Art That Connects Communities</h2>
                <div class="mae-arch-about__divider"></div>
                <p>Mithila art — the ancient painting tradition of the Mithila region of Bihar and Nepal — is among the world\'s most recognisable folk art forms. Its bold geometric motifs, intricate line work, and vibrant natural pigments have adorned the walls of homes for centuries, telling stories of the Ramayana, celebrating life rituals, and honoring the rhythms of the natural world.</p>
                <p>At Mithila Center USA, our programs bring this living tradition to American soil — through exhibitions, workshops, festivals, advocacy, and youth education. Every event we host is rooted in the belief that Mithila art deserves a permanent, celebrated place in the multicultural fabric of the United States and the global art world.</p>
                <a href="<?php echo esc_url(home_url('/about/')); ?>" class="mae-arch-about__btn">Learn Our Story &#x2192;</a>
            </div>
            <div class="mae-arch-about__features">
                <div class="mae-arch-about__feature">
                    <div class="mae-arch-about__feat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                    </div>
                    <div>
                        <strong>UNESCO-Recognised Art Form</strong>
                        <p>Mithila art is listed among the world\'s significant intangible cultural traditions.</p>
                    </div>
                </div>
                <div class="mae-arch-about__feature">
                    <div class="mae-arch-about__feat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                            <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                        </svg>
                    </div>
                    <div>
                        <strong>Open to Everyone</strong>
                        <p>Our events welcome diaspora families, curious neighbors, students, and art lovers of all backgrounds.</p>
                    </div>
                </div>
                <div class="mae-arch-about__feature">
                    <div class="mae-arch-about__feat-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z" />
                        </svg>
                    </div>
                    <div>
                        <strong>Year-Round Programming</strong>
                        <p>From intimate workshops to major international festivals, something is always happening.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══════════════════ GET INVOLVED CTA ═══════════════════ -->
    <section class="mae-arch-cta">
        <div class="mae-arch-cta__bg-pattern"></div>
        <div class="mae-tax-container mae-arch-cta__inner">
            <p class="mae-arch-cta__kicker">Get Involved</p>
            <h2 class="mae-arch-cta__heading">There Are Many Ways to Participate</h2>
            <div class="mae-arch-cta__divider"></div>
            <div class="mae-arch-cta__cards">
                <div class="mae-arch-cta__card">
                    <div class="mae-arch-cta__card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z" />
                        </svg>
                    </div>
                    <h3>Attend</h3>
                    <p>Browse upcoming events and register. Many programs are free and open to the public.</p>
                    <a href="<?php echo esc_url(home_url('/events/')); ?>" class="mae-arch-cta__card-link">See Events &#x2192;</a>
                </div>
                <div class="mae-arch-cta__card">
                    <div class="mae-arch-cta__card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
                            <path d="M16.5 3c-1.74 0-3.41.81-4.5 2.09C10.91 3.81 9.24 3 7.5 3 4.42 3 2 5.42 2 8.5c0 3.78 3.4 6.86 8.55 11.54L12 21.35l1.45-1.32C18.6 15.36 22 12.28 22 8.5 22 5.42 19.58 3 16.5 3z" />
                        </svg>
                    </div>
                    <h3>Volunteer</h3>
                    <p>Help our events run smoothly. Volunteers are the heartbeat of every program we host.</p>
                    <a href="<?php echo esc_url(home_url('/volunteer/')); ?>" class="mae-arch-cta__card-link">Volunteer &#x2192;</a>
                </div>
                <div class="mae-arch-cta__card">
                    <div class="mae-arch-cta__card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28">
                            <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z" />
                        </svg>
                    </div>
                    <h3>Sponsor</h3>
                    <p>Support our mission as a corporate or individual sponsor and gain visibility with our community.</p>
                    <a href="<?php echo esc_url(home_url('/partner-with-us/')); ?>" class="mae-arch-cta__card-link">Become a Sponsor &#x2192;</a>
                </div>
            </div>
        </div>
    </section>

</div><!-- .mae-archive-page -->

<?php get_footer(); ?>