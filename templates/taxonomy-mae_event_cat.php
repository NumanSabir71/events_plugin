<?php
/**
 * Event category taxonomy template — rich Mithila-branded layout.
 *
 * @package Mithila_Art_Events
 * @version 2.0.0
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

// Category hero image
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
// Fallback: use a different event image if available
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
        <p class="mae-tax-hero__kicker"><?php esc_html_e('Programs &amp; Events', 'mithila-art-events'); ?></p>
        <h1 class="mae-tax-hero__title"><?php echo esc_html($term->name); ?></h1>
        <div class="mae-tax-hero__divider"></div>
        <?php if ($term->description) : ?>
        <p class="mae-tax-hero__desc"><?php echo esc_html($term->description); ?></p>
        <?php else : ?>
        <p class="mae-tax-hero__desc"><?php esc_html_e('Celebrating 3,000 years of Mithila creativity — our exhibitions present authentic Madhubani painting, contemporary Mithila-inspired works, and cross-cultural dialogues that bring this living tradition to audiences across the United States and the world.', 'mithila-art-events'); ?></p>
        <?php endif; ?>
        <div class="mae-tax-hero__pills">
            <span class="mae-tax-hero__pill"><?php echo $total_count; ?> <?php esc_html_e('Total Events', 'mithila-art-events'); ?></span>
            <?php if ($upcoming_count > 0) : ?>
            <span class="mae-tax-hero__pill mae-tax-hero__pill--green"><?php echo $upcoming_count; ?> <?php esc_html_e('Upcoming', 'mithila-art-events'); ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     2. ABOUT THIS CATEGORY — cream intro
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-about">
    <div class="mae-tax-container mae-tax-about__grid">

        <div class="mae-tax-about__body">
            <p class="mae-tax-about__kicker"><?php esc_html_e('Our Exhibition Program', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-about__heading"><?php esc_html_e('Art That Connects Tradition &amp; the World', 'mithila-art-events'); ?></h2>
            <div class="mae-tax-about__divider"></div>
            <p><?php esc_html_e('Mithila Center USA curates exhibitions designed to preserve the visual language of Mithila while presenting it to wider and more diverse audiences. Our programs showcase traditional and contemporary work from master and emerging artists across Nepal, India, and diaspora communities.', 'mithila-art-events'); ?></p>
            <p><?php esc_html_e('Each exhibition is thoughtfully connected to broader cultural conversations — sustainability, gender equality, education, and human rights — using the unique storytelling power of Mithila art. Many of our shows are directly tied to the UN Sustainable Development Goals, reflecting the organization\'s commitment to using art as a vehicle for positive global change.', 'mithila-art-events'); ?></p>
            <p><?php esc_html_e('From intimate gallery shows to major international platforms including the United Nations Headquarters in New York, Mithila Center USA brings this extraordinary art form to the stages it deserves. We welcome artists, collectors, educators, and curious visitors alike.', 'mithila-art-events'); ?></p>

            <ul class="mae-tax-about__features">
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Authenticated Madhubani &amp; Mithila artwork from Bihar, Nepal &amp; diaspora', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Artist talks, workshops &amp; guided educational tours', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Hybrid in-person &amp; virtual access for global audiences', 'mithila-art-events'); ?></span>
                </li>
                <li class="mae-tax-about__feature">
                    <span class="mae-tax-about__feature-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    <span><?php esc_html_e('Proceeds directly support artists in Bihar &amp; Nepal', 'mithila-art-events'); ?></span>
                </li>
            </ul>

            <a href="<?php echo esc_url(home_url('/art-for-sdgs/')); ?>" class="mae-tax-about__btn"><?php esc_html_e('Learn About Art for SDGs', 'mithila-art-events'); ?> &#x2192;</a>
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
                <?php esc_html_e('Mithila art — a living 3,000-year tradition', 'mithila-art-events'); ?>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     3. UPCOMING EVENTS
═══════════════════════════════════════════════════════════ -->
<?php if (!empty($upcoming)) : ?>
<section class="mae-tax-events-section mae-tax-events-upcoming">
    <div class="mae-tax-container">
        <div class="mae-tax-section-hdr">
            <div>
                <p class="mae-tax-section-kicker"><?php esc_html_e('Don\'t Miss Out', 'mithila-art-events'); ?></p>
                <h2 class="mae-tax-section-heading"><?php esc_html_e('Upcoming Exhibitions', 'mithila-art-events'); ?></h2>
            </div>
            <span class="mae-tax-count-badge"><?php echo $upcoming_count; ?> <?php esc_html_e('Events', 'mithila-art-events'); ?></span>
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
                                        <a href="<?php echo esc_url(MAE_Checkout::url($id)); ?>" class="mae-card-btn-apply">Apply Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="mae-ev-nav mae-ev-nav--next" aria-label="Next">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
        </div>
        <div class="mae-ev-dots"></div>
    </div>
</section>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     4. WHAT TO EXPECT — brown feature band
═══════════════════════════════════════════════════════════ -->
<section class="mae-tax-why">
    <div class="mae-tax-container">
        <p class="mae-tax-why__kicker"><?php esc_html_e('The Mithila Experience', 'mithila-art-events'); ?></p>
        <h2 class="mae-tax-why__heading"><?php esc_html_e('What Our Exhibitions Offer', 'mithila-art-events'); ?></h2>
        <div class="mae-tax-why__divider"></div>
        <ul class="mae-tax-why__grid">
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/><path d="M0 0h24v24H0z" fill="none"/><path d="M12 3a9 9 0 1 0 0 18A9 9 0 0 0 12 3zm0 16a7 7 0 1 1 0-14 7 7 0 0 1 0 14z"/><path fill="none" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Authentic Artwork', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Every piece displayed is authenticated Mithila art — from centuries-old ritual wall paintings to contemporary Madhubani canvases created by master artists in Bihar, Nepal, and the diaspora.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Community &amp; Connection', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Our exhibitions are living community events — bringing together Mithila diaspora families, art lovers, students, and international visitors in shared appreciation of a 3,000-year-old creative tradition.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M11.5 2C6.81 2 3 5.81 3 10.5S6.81 19 11.5 19h.5v3c4.86-2.34 8-7 8-11.5C20 5.81 16.19 2 11.5 2zm1 14.5h-2v-2h2v2zm0-4h-2c0-3.25 3-3 3-5 0-1.1-.9-2-2-2s-2 .9-2 2h-2c0-2.21 1.79-4 4-4s4 1.79 4 4c0 2.5-3 2.75-3 5z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Education &amp; Dialogue', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Beyond display, our exhibitions include artist talks, workshops, guided tours, and educational programming connecting Mithila art to global themes — sustainability, gender equality, and human dignity.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('World-Class Venues', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('From the United Nations Headquarters to New York\'s Diversity Plaza and acclaimed galleries, our exhibitions reach prestigious platforms that give Mithila art its rightful place on the world stage.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Virtual &amp; In-Person', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('Our exhibitions are accessible to audiences worldwide through hybrid in-person and virtual formats — so that geography is never a barrier to experiencing Mithila culture.', 'mithila-art-events'); ?></p>
            </li>
            <li class="mae-tax-why__card">
                <div class="mae-tax-why__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
                </div>
                <h3 class="mae-tax-why__card-title"><?php esc_html_e('Support Artists Directly', 'mithila-art-events'); ?></h3>
                <p class="mae-tax-why__card-desc"><?php esc_html_e('A portion of every ticket sale and artwork purchase directly supports Mithila artists in Bihar and Nepal — ensuring that the art form remains economically sustainable for the communities that created it.', 'mithila-art-events'); ?></p>
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
                <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <?php esc_html_e('Featured Program', 'mithila-art-events'); ?>
            </div>
        </div>

        <div class="mae-tax-spotlight__text-col">
            <p class="mae-tax-spotlight__kicker"><?php esc_html_e('Our Artistic Legacy', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-spotlight__heading"><?php esc_html_e('Three Thousand Years of Living Art', 'mithila-art-events'); ?></h2>
            <div class="mae-tax-spotlight__divider"></div>

            <blockquote class="mae-tax-spotlight__quote">
                <?php esc_html_e('"Mithila painting is not merely decoration — it is a language, a ritual, a record of life itself."', 'mithila-art-events'); ?>
            </blockquote>

            <p class="mae-tax-spotlight__body"><?php esc_html_e('Originating in the Mithila region of Bihar, India and the Terai plains of Nepal, Madhubani painting has been passed from mother to daughter for over three millennia. Rooted in ritual wall paintings created for weddings, harvests, and festivals, this tradition carries encoded wisdom about the natural world, social relationships, and the divine.', 'mithila-art-events'); ?></p>

            <p class="mae-tax-spotlight__body"><?php esc_html_e('Today, Mithila Center USA is a bridge — bringing these stories to international stages while ensuring that the artists and communities who created this tradition receive the recognition and economic support they deserve. Every exhibition we host is an act of cultural preservation and global dialogue.', 'mithila-art-events'); ?></p>

            <div class="mae-tax-spotlight__stats">
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">3,000+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Years of Tradition', 'mithila-art-events'); ?></span>
                </div>
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">70+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Shows &amp; Programs', 'mithila-art-events'); ?></span>
                </div>
                <div class="mae-tax-spotlight__stat">
                    <span class="mae-tax-spotlight__stat-num">250+</span>
                    <span class="mae-tax-spotlight__stat-label"><?php esc_html_e('Artists Supported', 'mithila-art-events'); ?></span>
                </div>
            </div>

            <div class="mae-tax-spotlight__actions">
                <a href="<?php echo esc_url(home_url('/art-for-sdgs/')); ?>" class="mae-tax-spotlight__btn mae-tax-spotlight__btn--primary">
                    <?php esc_html_e('Explore Art for SDGs', 'mithila-art-events'); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <a href="<?php echo esc_url($donate_url); ?>" class="mae-tax-spotlight__btn mae-tax-spotlight__btn--ghost">
                    <?php esc_html_e('Support the Artists', 'mithila-art-events'); ?>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </a>
            </div>
        </div>

    </div>
</section>


<!-- ═══════════════════════════════════════════════════════════
     5. PAST EXHIBITIONS
═══════════════════════════════════════════════════════════ -->
<?php if (!empty($past)) : ?>
<section class="mae-tax-events-section mae-tax-events-past">
    <div class="mae-tax-container">
        <div class="mae-tax-section-hdr">
            <div>
                <p class="mae-tax-section-kicker"><?php esc_html_e('Our History', 'mithila-art-events'); ?></p>
                <h2 class="mae-tax-section-heading mae-tax-past-heading-text"><?php esc_html_e('Past Exhibitions', 'mithila-art-events'); ?></h2>
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
            <h3><?php esc_html_e('No events in this category yet', 'mithila-art-events'); ?></h3>
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
            <p class="mae-tax-cta__kicker"><?php esc_html_e('Stay in the Loop', 'mithila-art-events'); ?></p>
            <h2 class="mae-tax-cta__heading"><?php esc_html_e('Be the First to Know About New Exhibitions', 'mithila-art-events'); ?></h2>
            <p class="mae-tax-cta__desc"><?php esc_html_e('Our exhibitions sell out quickly. Subscribe to get early access, artist announcements, and exclusive event invitations delivered to your inbox.', 'mithila-art-events'); ?></p>
        </div>
        <div class="mae-tax-cta__actions">
            <form class="mae-tax-cta__form" method="post" action="#" onsubmit="return false;" novalidate>
                <label for="mae-tax-email" class="screen-reader-text"><?php esc_html_e('Email address', 'mithila-art-events'); ?></label>
                <input id="mae-tax-email" type="email" name="email" placeholder="<?php esc_attr_e('Your email address…', 'mithila-art-events'); ?>" class="mae-tax-cta__input" required />
                <button type="submit" class="mae-tax-cta__btn"><?php esc_html_e('Subscribe', 'mithila-art-events'); ?></button>
            </form>
            <a href="<?php echo esc_url($donate_url); ?>" class="mae-tax-cta__donate"><?php esc_html_e('Support Our Exhibitions', 'mithila-art-events'); ?> &#x2665;</a>
        </div>
    </div>
</section>

</div><!-- .mae-tax-rich -->

<?php get_footer(); ?>
