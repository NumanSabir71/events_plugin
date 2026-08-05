<?php
/**
 * Event category template — Youth Engagement & Empowerment
 * Overrides taxonomy-mae_event_cat.php for this specific term.
 *
 * @package Mithila_Art_Events
 * @version 1.0.0
 */
defined('ABSPATH') || exit;

get_header();

$term    = get_queried_object();
$today   = date('Y-m-d');

$all_events = get_posts([
    'post_type'   => 'mae_event',
    'numberposts' => -1,
    'tax_query'   => [['taxonomy' => 'mae_event_cat', 'terms' => $term->term_id]],
    'meta_key'    => '_mae_date',
    'orderby'     => 'meta_value',
    'order'       => 'ASC',
]);

$upcoming = [];
$past     = [];
foreach ($all_events as $ev) {
    $d = get_post_meta($ev->ID, '_mae_date', true);
    if (!$d || $d >= $today) $upcoming[] = $ev;
    else $past[] = $ev;
}
$past_reversed = array_reverse($past);

$cat_img_id  = (int) get_term_meta($term->term_id, '_mae_cat_image_id', true);
$cat_img_url = '';
if (!$cat_img_id) {
    foreach ($all_events as $ev) {
        if (has_post_thumbnail($ev->ID)) { $cat_img_id = get_post_thumbnail_id($ev->ID); break; }
    }
}
if ($cat_img_id) {
    $src = wp_get_attachment_image_src($cat_img_id, 'large');
    if ($src) $cat_img_url = $src[0];
}

// About section image
$about_img_id  = (int) get_term_meta($term->term_id, '_mae_cat_about_image_id', true);
$about_img_url = '';
if ($about_img_id) {
    $src = wp_get_attachment_image_src($about_img_id, 'large');
    if ($src) $about_img_url = $src[0];
}
if (!$about_img_url) {
    foreach (array_reverse($all_events) as $ev) {
        if (has_post_thumbnail($ev->ID) && get_post_thumbnail_id($ev->ID) !== $cat_img_id) {
            $src = wp_get_attachment_image_src(get_post_thumbnail_id($ev->ID), 'large');
            if ($src) { $about_img_url = $src[0]; break; }
        }
    }
}

$total_count    = count($all_events);
$upcoming_count = count($upcoming);
$past_count     = count($past);
$donate_url     = function_exists('mithila_art_get_donations_page_url') ? mithila_art_get_donations_page_url() : home_url('/donation/');
?>

<div class="mae-tax-page mae-tax-rich">

<!-- ═══════════════════════════════════════════════════════════
     1. HERO BANNER
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-hero" <?php if ($cat_img_url) echo 'style="--hero-img:url(\'' . esc_url($cat_img_url) . '\')"'; ?>>
    <div class="mae-tax-hero__overlay"></div>
    <div class="mae-tax-container mae-tax-hero__inner">
        <p class="mae-tax-hero__kicker"><?php esc_html_e('Programs &amp; Initiatives', 'mithila-art-events'); ?></p>
        <h1 class="mae-tax-hero__title"><?php echo esc_html($term->name); ?></h1>
        <div class="mae-tax-hero__divider"></div>
        <p class="mae-tax-hero__desc"><?php esc_html_e('Mithila Center USA believes the future of Mithila art lives in its young people. Our youth programs create hands-on learning, scholarship recognition, and mentorship pathways — passing a 3,000-year-old living tradition into the next generation of artists, storytellers, and cultural ambassadors.', 'mithila-art-events'); ?></p>
        <div class="mae-tax-hero__pills">
            <span class="mae-tax-hero__pill"><?php echo $total_count; ?> <?php esc_html_e('Total Programs', 'mithila-art-events'); ?></span>
            <?php if ($upcoming_count > 0) : ?>
            <span class="mae-tax-hero__pill mae-tax-hero__pill--green"><?php echo $upcoming_count; ?> <?php esc_html_e('Upcoming', 'mithila-art-events'); ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     2. ABOUT — cream intro
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-about">
    <div class="mae-tax-container mae-tax-about__grid">

        <div class="mae-tax-about__body">
            <p class="mae-tax-about__kicker"><?php esc_html_e('Our Youth Programs', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-about__heading"><?php esc_html_e('Empowering the Next Generation of Mithila Artists', 'mithila-art-events'); ?></h2>
            <div class="mae-tax-about__divider"></div>
            <p><?php esc_html_e('From first brushstroke to scholarship stage, Mithila Center USA offers a continuum of youth programming that meets young people where they are. Our Kids Art Workshops introduce children as young as five to the visual language of Madhubani painting — its symbols, stories, and spiritual meanings — in a joyful, hands-on environment led by master artists from Bihar and Nepal.', 'mithila-art-events'); ?></p>
            <p><?php esc_html_e('As young participants grow, our Student Exhibition and Scholarship Awards program provides a formal platform for recognition. Annual showcases celebrate artistic development, cultural commitment, and emerging leadership — awarding scholarships to students who demonstrate exceptional dedication to preserving and advancing Mithila art in their communities.', 'mithila-art-events'); ?></p>
            <p><?php esc_html_e('Our Meet the Mentors initiative connects young artists directly with established Mithila practitioners, creating intergenerational dialogue that no classroom can replicate. These relationships are the living thread of cultural transmission — ensuring that the techniques, symbolism, and philosophy of Mithila art are passed forward, not archived.', 'mithila-art-events'); ?></p>
            <ul class="mae-tax-about__features">
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Hands-on Madhubani workshops led by master artists from Bihar &amp; Nepal', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Annual scholarship awards recognising exceptional young cultural ambassadors', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Meet the Mentors: one-on-one connections between youth &amp; established practitioners', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Family-inclusive sessions that build intergenerational cultural bonds', 'mithila-art-events'); ?></span>
                </li>
            </ul>

            <a href="<?php echo esc_url(home_url('/volunteer/')); ?>" class="mae-tax-about__btn"><?php esc_html_e('Get Involved', 'mithila-art-events'); ?> &#x2192;</a>
        </div>

        <div class="mae-tax-about__img-wrap">
            <?php if ($about_img_url) : ?>
            <img src="<?php echo esc_url($about_img_url); ?>"
                 alt="<?php echo esc_attr($term->name); ?>"
                 class="mae-tax-about__img"
                 loading="lazy">
            <?php else : ?>
            <div class="mae-tax-about__img-placeholder" aria-hidden="true">
                <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" width="64" height="64">
                    <rect width="80" height="80" rx="8" fill="rgba(131,77,34,.08)"/>
                    <path d="M20 56l14-18 10 12 8-10 8 16H20z" fill="rgba(131,77,34,.2)"/>
                    <circle cx="54" cy="28" r="7" fill="rgba(131,77,34,.2)"/>
                </svg>
                <p><?php esc_html_e('Add an image via Events &rsaquo; Categories', 'mithila-art-events'); ?></p>
            </div>
            <?php endif; ?>
            <div class="mae-tax-about__img-caption">
                <span class="mae-tax-about__img-caption-dot" aria-hidden="true"></span>
                <?php esc_html_e('Young artists discovering the living tradition of Mithila', 'mithila-art-events'); ?>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     3. UPCOMING PROGRAMS
═══════════════════════════════════════════════════════════ -->
<?php if (!empty($upcoming)) : ?>
<section class="mae-tax-events-section mae-tax-events-upcoming">
    <div class="mae-tax-container">
        <div class="mae-tax-section-hdr">
            <div>
                <p class="mae-tax-section-kicker"><?php esc_html_e('Register Today', 'mithila-art-events'); ?></p>
                <h2 class="mae-tax-section-heading"><?php esc_html_e('Upcoming Youth Programs', 'mithila-art-events'); ?></h2>
            </div>
            <span class="mae-tax-count-badge"><?php echo $upcoming_count; ?> <?php esc_html_e('Programs', 'mithila-art-events'); ?></span>
        </div>

        <div class="mae-ev-slider-wrap">
            <button class="mae-ev-nav mae-ev-nav--prev" aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="mae-ev-slider">
                <div class="mae-ev-track" id="mae-ev-upcoming-track">
            <?php foreach ($upcoming as $ev) :
                $id       = $ev->ID;
                $date     = get_post_meta($id, '_mae_date',     true);
                $end_date = get_post_meta($id, '_mae_end_date', true);
                $time     = get_post_meta($id, '_mae_time',     true);
                $loc      = get_post_meta($id, '_mae_location', true);
                $type     = get_post_meta($id, '_mae_type',     true) ?: 'free';
                $price    = (float) get_post_meta($id, '_mae_price', true);
                $is_paid  = $type === 'paid';
                $is_today = $date === $today;
                $excerpt  = get_the_excerpt($ev);
            ?>
            <article class="mae-event-card">
                <a href="<?php echo get_permalink($id); ?>" class="mae-card-img-link">
                    <?php if (has_post_thumbnail($id)) : echo get_the_post_thumbnail($id, 'medium_large', ['class' => 'mae-card-img']); else : ?>
                    <div class="mae-card-img-placeholder"></div>
                    <?php endif; ?>
                    <?php if ($is_today) : ?><span class="mae-card-today">Today</span><?php endif; ?>
                    <div class="mae-card-badge <?php echo $is_paid ? 'mae-badge-paid' : 'mae-badge-free'; ?>">
                        <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                    </div>
                </a>
                <div class="mae-card-body">
                    <?php if ($date) : ?>
                    <div class="mae-card-date-strip">
                        <?php $s=strtotime($date);$e=$end_date?strtotime($end_date):0;$sm=$e&&date('nY',$s)===date('nY',$e);?>
                        <span class="mae-card-month"><?php echo date('M',$s);if($e&&!$sm)echo' &ndash; '.date('M',$e);?></span>
                        <span class="mae-card-day"><?php echo date('j',$s);if($sm)echo'&ndash;'.date('j',$e);?></span>
                    </div>
                    <?php endif; ?>
                    <div class="mae-card-content">
                        <h3 class="mae-card-title"><a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a></h3>
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
                                <?php else : ?>Free Entry<?php endif; ?>
                            </div>
                            <div class="mae-card-actions">
                                <a href="<?php echo get_permalink($id); ?>" class="mae-card-btn-view">View</a>
                                <a href="<?php echo esc_url(MAE_Checkout::url($id)); ?>" class="mae-card-btn-apply">Register</a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
                </div><!-- /.mae-ev-track -->
            </div><!-- /.mae-ev-slider -->
            <button class="mae-ev-nav mae-ev-nav--next" aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div><!-- /.mae-ev-slider-wrap -->
        <div class="mae-ev-dots"></div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     4. WHAT OUR YOUTH PROGRAMS OFFER — brown feature band
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-why">
    <div class="mae-tax-container">
        <p class="mae-tax-why__kicker"><?php esc_html_e('The Mithila Learning Experience', 'mithila-art-events'); ?></p>
        <h2 class="mae-tax-why__heading"><?php esc_html_e('What Our Youth Programs Offer', 'mithila-art-events'); ?></h2>
        <div class="mae-tax-why__divider"></div>
        <ul class="mae-tax-why__grid">
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Hands-On Art Workshops', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Children and teens learn authentic Madhubani techniques directly from master Mithila artists — brush handling, natural pigments, motif symbolism, and the ritual stories behind each design. Every session is an immersive cultural experience.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 3c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm7 13H5v-.23c0-.62.28-1.2.76-1.58C7.47 15.82 9.64 15 12 15s4.53.82 6.24 2.19c.48.38.76.97.76 1.58V19z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Scholarship Recognition', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Our annual Student Exhibition and Scholarship Awards celebrate young participants who show exceptional artistic talent, cultural dedication, and community leadership — providing financial support and public recognition to support their creative journeys.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Mentorship &amp; Guidance', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Meet the Mentors connects aspiring young artists with established Mithila practitioners for one-on-one and small-group learning. These relationships go beyond technique — they transmit the cultural values, community responsibility, and creative philosophy embedded in Mithila art.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Cultural Storytelling', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Each Mithila motif carries a story — from the Ramayana to fertility rites to daily village life. Our programs teach young people to read and tell these stories through art, giving them a living vocabulary for their own art and identity.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M11.5 2C6.81 2 3 5.81 3 10.5S6.81 19 11.5 19h.5v3c4.86-2.34 8-7 8-11.5C20 5.81 16.19 2 11.5 2zm1 14.5h-2v-2h2v2zm0-4h-2c0-3.25 3-3 3-5 0-1.1-.9-2-2-2s-2 .9-2 2h-2c0-2.21 1.79-4 4-4s4 1.79 4 4c0 2.5-3 2.75-3 5z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Cross-Cultural Dialogue', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Our youth programs bring together children from diverse backgrounds — Mithila diaspora families, American youth, and international communities. Art becomes a shared language, building empathy, curiosity, and friendships that cross cultural borders.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Family Participation', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Many of our youth programs welcome parents and guardians to participate alongside children — creating shared cultural experiences within families and strengthening intergenerational bonds through the act of making art together.', 'mithila-art-events'); ?></p>
            </li>
        </ul>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     4b. SPOTLIGHT — image left, text right
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-spotlight">
    <div class="mae-tax-container mae-tax-spotlight__grid">

        <div class="mae-tax-spotlight__img-col">
            <?php if ($cat_img_url) : ?>
            <img src="<?php echo esc_url($cat_img_url); ?>"
                 alt="<?php echo esc_attr($term->name); ?>"
                 class="mae-tax-spotlight__img"
                 loading="lazy">
            <?php else : ?>
            <div class="mae-tax-spotlight__img-ph" aria-hidden="true">
                <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg" width="64" height="64">
                    <rect width="80" height="80" rx="8" fill="rgba(253,246,227,.15)"/>
                    <path d="M20 56l14-18 10 12 8-10 8 16H20z" fill="rgba(253,246,227,.3)"/>
                    <circle cx="54" cy="28" r="7" fill="rgba(253,246,227,.3)"/>
                </svg>
            </div>
            <?php endif; ?>
            <div class="mae-tax-spotlight__img-badge">
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zM5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82z"/></svg>
                <?php esc_html_e('Youth Initiative', 'mithila-art-events'); ?>
            </div>
        </div>

        <div class="mae-tax-spotlight__text-col">
            <p class="mae-tax-spotlight__kicker"><?php esc_html_e('Inspiring the Next Generation', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-spotlight__heading"><?php esc_html_e('Where Young Artists Find Their Voice', 'mithila-art-events'); ?></h2>
            <div class="mae-tax-spotlight__divider"></div>

            <blockquote class="mae-tax-spotlight__quote">
                <?php esc_html_e('"When a child holds a brush for the first time and paints a fish or a lotus, they are writing themselves into a living history."', 'mithila-art-events'); ?>
            </blockquote>

            <p class="mae-tax-spotlight__body"><?php esc_html_e('The future of Mithila art is not a museum piece — it is a living practice that must be learned, felt, and carried forward by young hands. Our youth programs start with simple brushstrokes and grow into full artistic and cultural identity. Children as young as five begin their journey with joyful, hands-on Madhubani workshops, discovering the symbolism of fish, elephants, peacocks, and lotuses that have carried Mithila stories for 3,000 years.', 'mithila-art-events'); ?></p>

            <p class="mae-tax-spotlight__body"><?php esc_html_e('As participants grow in skill and confidence, our scholarship recognition programs provide formal celebration of their commitment. The most dedicated young artists are connected with established Mithila masters through our Meet the Mentors initiative — creating irreplaceable intergenerational bonds that no curriculum can replicate.', 'mithila-art-events'); ?></p>

            <div class="mae-tax-spotlight__stats">
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">200+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Young Artists Trained', 'mithila-art-events'); ?></span>
                </div>
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">50+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Workshops &amp; Sessions', 'mithila-art-events'); ?></span>
                </div>
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">3K+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Families Reached', 'mithila-art-events'); ?></span>
                </div>
            </div>

            <div class="mae-tax-spotlight__actions">
                <a href="<?php echo esc_url(home_url('/volunteer/')); ?>" class="mae-tax-spotlight__btn mae-tax-spotlight__btn--primary">
                    <?php esc_html_e('Get Involved', 'mithila-art-events'); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="<?php echo esc_url($donate_url); ?>" class="mae-tax-spotlight__btn mae-tax-spotlight__btn--ghost">
                    <?php esc_html_e('Fund a Young Artist', 'mithila-art-events'); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </a>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     5. PAST PROGRAMS
═══════════════════════════════════════════════════════════ -->
<?php if (!empty($past)) : ?>
<section class="mae-tax-events-section mae-tax-events-past">
    <div class="mae-tax-container">
        <div class="mae-tax-section-hdr">
            <div>
                <p class="mae-tax-section-kicker"><?php esc_html_e('Our History', 'mithila-art-events'); ?></p>
                <h2 class="mae-tax-section-heading mae-tax-past-heading-text"><?php esc_html_e('Past Youth Programs', 'mithila-art-events'); ?></h2>
            </div>
            <span class="mae-tax-count-badge mae-tax-count-badge--past"><?php echo $past_count; ?> <?php esc_html_e('Completed', 'mithila-art-events'); ?></span>
        </div>

        <div class="mae-ev-slider-wrap">
            <button class="mae-ev-nav mae-ev-nav--prev" aria-label="Previous">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <div class="mae-ev-slider">
                <div class="mae-ev-track" id="mae-ev-past-track">
            <?php foreach ($past_reversed as $ev) :
                $id       = $ev->ID;
                $date     = get_post_meta($id, '_mae_date',     true);
                $end_date = get_post_meta($id, '_mae_end_date', true);
                $loc      = get_post_meta($id, '_mae_location', true);
                $type    = get_post_meta($id, '_mae_type',     true) ?: 'free';
                $price   = (float) get_post_meta($id, '_mae_price', true);
                $is_paid = $type === 'paid';
                $excerpt = get_the_excerpt($ev);
            ?>
            <article class="mae-event-card mae-card-past mae-card-past--themed">
                <a href="<?php echo get_permalink($id); ?>" class="mae-card-img-link">
                    <?php if (has_post_thumbnail($id)) : echo get_the_post_thumbnail($id, 'medium_large', ['class' => 'mae-card-img']); else : ?>
                    <div class="mae-card-img-placeholder"></div>
                    <?php endif; ?>
                    <div class="mae-card-past-overlay">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                        <?php esc_html_e('Concluded', 'mithila-art-events'); ?>
                    </div>
                    <div class="mae-card-badge <?php echo $is_paid ? 'mae-badge-paid' : 'mae-badge-free'; ?>">
                        <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free'; ?>
                    </div>
                </a>
                <div class="mae-card-body">
                    <?php if ($date) : ?>
                    <div class="mae-card-date-strip mae-date-past-themed">
                        <?php $s=strtotime($date);$e=$end_date?strtotime($end_date):0;$sm=$e&&date('nY',$s)===date('nY',$e);?>
                        <span class="mae-card-month"><?php echo date('M',$s);if($e&&!$sm)echo' &ndash; '.date('M',$e);?></span>
                        <span class="mae-card-day"><?php echo date('j',$s);if($sm)echo'&ndash;'.date('j',$e);?></span>
                    </div>
                    <?php endif; ?>
                    <div class="mae-card-content">
                        <h3 class="mae-card-title"><a href="<?php echo get_permalink($id); ?>"><?php echo esc_html($ev->post_title); ?></a></h3>
                        <div class="mae-card-meta">
                            <?php if ($date) : ?>
                            <div class="mae-card-meta-item">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <?php echo mae_format_date_range($date, $end_date); ?>
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
                                <?php echo $is_paid ? '$' . number_format($price, 2) : 'Free Entry'; ?>
                            </div>
                            <a href="<?php echo get_permalink($id); ?>" class="mae-card-btn mae-card-btn--past">
                                <?php esc_html_e('View →', 'mithila-art-events'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
                </div><!-- /.mae-ev-track -->
            </div><!-- /.mae-ev-slider -->
            <button class="mae-ev-nav mae-ev-nav--next" aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div><!-- /.mae-ev-slider-wrap -->
        <div class="mae-ev-dots"></div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     6. EMPTY STATE
═══════════════════════════════════════════════════════════ -->
<?php if (empty($upcoming) && empty($past)) : ?>
<section class="mae-tax-events-section">
    <div class="mae-tax-container">
        <div class="mae-empty-state">
            <div class="mae-empty-icon">
                <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <h3><?php esc_html_e('No programs in this category yet', 'mithila-art-events'); ?></h3>
            <p><a href="<?php echo get_post_type_archive_link('mae_event'); ?>"><?php esc_html_e('← Back to all programs', 'mithila-art-events'); ?></a></p>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     7. NEWSLETTER / CTA BAND
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-cta">
    <div class="mae-tax-container mae-tax-cta__inner">
        <div class="mae-tax-cta__text">
            <p class="mae-tax-cta__kicker"><?php esc_html_e('Support Young Artists', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-cta__heading"><?php esc_html_e('Help a Child Discover Their Cultural Voice', 'mithila-art-events'); ?></h2>
            <p class="mae-tax-cta__desc"><?php esc_html_e('Subscribe to hear about new youth workshops, scholarship opportunities, and mentorship programs before they fill up. Your support directly funds scholarships and art supplies for young participants.', 'mithila-art-events'); ?></p>
        </div>
        <div class="mae-tax-cta__actions">
            <form class="mae-tax-cta__form" method="post" action="#" onsubmit="return false;" novalidate>
                <label for="mae-tax-email-yee" class="screen-reader-text"><?php esc_html_e('Email address', 'mithila-art-events'); ?></label>
                <input id="mae-tax-email-yee" type="email" name="email" placeholder="<?php esc_attr_e('Your email address…', 'mithila-art-events'); ?>" class="mae-tax-cta__input" required />
                <button type="submit" class="mae-tax-cta__btn"><?php esc_html_e('Subscribe', 'mithila-art-events'); ?></button>
            </form>
            <a href="<?php echo esc_url($donate_url); ?>" class="mae-tax-cta__donate"><?php esc_html_e('Fund a Young Artist', 'mithila-art-events'); ?> &#x2665;</a>
        </div>
    </div>
</section>

</div><!-- .mae-tax-rich -->

<?php get_footer(); ?>
